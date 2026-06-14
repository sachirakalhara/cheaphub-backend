<?php

namespace App\Repositories\Product;

use App\Helpers\Helper;
use App\Models\Product\Contribution\ProductReplacement;
use App\Models\Payment\Order;
use App\Models\Payment\OrderItems;

use App\Models\Product\Contribution\ProductReplacementSerial;
use App\Models\Product\Contribution\RemovedContributionProductSerial;
use App\Models\Product\Contribution\RemovedProductReplacementSerial;
use App\Models\Subscription\Package;
use App\Repositories\Product\Interface\ProductReplacementRepositoryInterface;
use App\Services\StockNotificationService;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class ProductReplacementRepository implements ProductReplacementRepositoryInterface
{

    public function getAvalableCount($package_id)
    {
        $user_id = Auth::user()->id;
        // Get all order IDs for the given package ID for the current user
        $order_ids = Order::with('orderItems')
            ->where('user_id', $user_id)
            ->whereHas('orderItems', function ($query) use ($package_id) {
                $query->where('package_id', $package_id);
            })
            ->pluck('id')
            ->toArray();

        $productReplacements = ProductReplacement::whereIn('order_id', $order_ids)
            ->where('user_id', $user_id)
            ->where('package_id', $package_id)
            ->get();
      
        return response()->json([
            'status' => true,
            'message' => 'Product found',
            'data' => $productReplacements,
        ], Response::HTTP_OK);
        
    }  

    public function store($request)
    {
        $userId = Auth::id();

        $package = Package::find($request->package_id);
        if (!$package) {
            return response()->json([
                'status' => false,
                'message' => 'Package not found',
            ], Response::HTTP_NOT_FOUND);
        }

        // Block replacements once the subscription period for this order has ended.
        // Expiry = order purchase date + package expiry_duration (in months), mirroring
        // the frontend's checkOrderValidStatus()/afterDateTimeConverter() logic.
        $order = Order::find($request->order_id);
        if (!$order) {
            return response()->json([
                'status' => false,
                'message' => 'Order not found',
            ], Response::HTTP_NOT_FOUND);
        }

        $expiryDate = Carbon::parse($order->created_at)->addMonths((int) $package->expiry_duration);
        if (now()->greaterThan($expiryDate)) {
            return response()->json([
                'status' => false,
                'message' => 'This subscription has expired. Replacements are no longer available for this product.',
            ], Response::HTTP_OK);
        }

        if ($package->subscription->available_serial_count <= 0) {
            return response()->json([
                'status' => false,
                'message' => 'No available serials for this package',
            ], Response::HTTP_OK);
        }

        $productReplacement = ProductReplacement::firstOrNew([
            'order_id' => $request->order_id,
            'user_id' => $userId,
            'package_id' => $request->package_id,
        ]);

        if ($productReplacement->exists) {
            if ($productReplacement->available_replace_count <= 0) {
                return response()->json([
                    'status' => false,
                    'message' => 'No available replacement count',
                ], Response::HTTP_OK);
            }
            $productReplacement->available_replace_count -= 1;
        } else {
            $productReplacement->available_replace_count = max($package->replace_count - 1, 0);
        }

        $productReplacement->save();

        // Step 1: Get all serials from the subscription
        $allSerials = array_filter(explode("\n", $package->subscription->serial), 'trim');

        // Step 2: Get already used serials for this replacement
        $usedSerials = ProductReplacementSerial::where('product_replacement_id', $productReplacement->id)
            ->pluck('serial')
            ->toArray();

        // Step 3: Get available (unused) serials
        $availableSerials = array_values(array_diff($allSerials, $usedSerials));

        // Step 4: Check if no serials are available
        if (empty($availableSerials)) {
            $productReplacement->available_replace_count += 1;
            $productReplacement->save();

            return response()->json([
                'status' => false,
                'message' => 'All serials have already been used for this package.',
            ], Response::HTTP_OK);
        }

        // Step 5: Randomly pick one from available serials
        $randomSerial = $availableSerials[array_rand($availableSerials)];

        // (Optional) Save the selected serial
        $productReplacementSerial = ProductReplacementSerial::create([
            'product_replacement_id' => $productReplacement->id,
            'serial' => $randomSerial,
        ]);


        RemovedProductReplacementSerial::create([
            'removed_contribution_product_serial_id' =>$request->user_purchase_serial_id,
            'product_replacement_serial_id' => $productReplacementSerial->id,
        ]);

        $oldSubCount = $package->subscription->available_serial_count;

        // Remove exactly ONE occurrence of the issued serial. Removing by value
        // (array_filter with !==) deletes ALL identical lines when duplicate
        // serial strings exist in stock, desyncing the text from the counter.
        $idx = array_search($randomSerial, $allSerials, true);
        if ($idx !== false) {
            unset($allSerials[$idx]);
        }
        $package->subscription->serial = implode("\n", $allSerials);
        $package->subscription->available_serial_count -= 1;
        $package->subscription->save();

        StockNotificationService::checkAndNotify(
            $package->subscription->name, 'Subscription', $package->subscription->available_serial_count, $oldSubCount, $package->subscription->id
        );

        return response()->json([
            'status' => true,
            'message' => 'Replacement updated successfully',
            'data' => [
                'user_id' => $productReplacement->user_id,
                'package_id' => $productReplacement->package_id,
                'user_purchase_serial'=> RemovedContributionProductSerial::find($request->user_purchase_serial_id)->serial,
                'selected_serial' => $randomSerial,
                'available_replace_count' => $productReplacement->available_replace_count,
            ],
        ], Response::HTTP_OK);
    }


}