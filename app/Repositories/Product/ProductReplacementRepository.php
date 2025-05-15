<?php

namespace App\Repositories\Product;

use App\Helpers\Helper;
use App\Models\Product\Contribution\ProductReplacement;
use App\Models\Payment\OrderItems;

use App\Models\Product\Contribution\ProductReplacementSerial;
use App\Models\Product\Contribution\RemovedContributionProductSerial;
use App\Models\Product\Contribution\RemovedProductReplacementSerial;
use App\Models\Subscription\Package;
use App\Repositories\Product\Interface\ProductReplacementRepositoryInterface;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class ProductReplacementRepository implements ProductReplacementRepositoryInterface
{

    public function getAvalableCount($package_id)
    {
        $user_id = Auth::user()->id;
        // Get all order IDs for the given package ID for the current user
        $order_ids = OrderItems::where('package_id', $package_id)
            ->where('user_id', $user_id)
            ->pluck('order_id')
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
            if ($productReplacement->avalable_replace_count <= 0) {
                return response()->json([
                    'status' => false,
                    'message' => 'No available replacement count',
                ], Response::HTTP_OK);
            }
            $productReplacement->avalable_replace_count -= 1;
        } else {
            $productReplacement->avalable_replace_count = max($package->replace_count - 1, 0);
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
            $productReplacement->avalable_replace_count += 1;
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

        $package->subscription->serial = implode("\n", array_filter($allSerials, fn($serial) => $serial !== $randomSerial));
        $package->subscription->available_serial_count -= 1;
        $package->subscription->save();

        return response()->json([
            'status' => true,
            'message' => 'Replacement updated successfully',
            'data' => [
                'user_id' => $productReplacement->user_id,
                'package_id' => $productReplacement->package_id,
                'user_purchase_serial'=> RemovedContributionProductSerial::find($request->user_purchase_serial_id)->serial,
                'selected_serial' => $randomSerial,
                'available_replace_count' => $productReplacement->avalable_replace_count,
            ],
        ], Response::HTTP_OK);
    }


}