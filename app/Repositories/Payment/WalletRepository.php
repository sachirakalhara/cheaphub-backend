<?php

namespace App\Repositories\Payment;

use App\Helpers\Helper;
use App\Http\Resources\Payment\WalletResource;
use App\Models\Cart\Cart;
use App\Models\Payment\Wallet;
use App\Models\Product\Bulk\BulkProduct;
use App\Models\Product\Bulk\RemovedBulkProductSerial;
use App\Models\Subscription\Package;
use Illuminate\Http\Response;
use App\Repositories\Payment\Interface\WalletRepositoryInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Product\Contribution\ProductReplacement;
use App\Models\Product\Contribution\ProductReplacementSerial;

class WalletRepository implements WalletRepositoryInterface
{
    public function show()
    {
        $user = Auth::user();
        $wallet = Wallet::where('user_id', $user->id)->first();

        if (!$wallet) {
            return Helper::error('Wallet not found', Response::HTTP_NOT_FOUND);
        }

        return new WalletResource($wallet);
    }


    public function processWalletPaymentForProduct($data)
        {
            $user = Auth::user();
            $amount = $data['amount'] ?? 0;

            if ($amount <= 0) {
                return response()->json(['message' => 'Invalid payment amount.'], Response::HTTP_BAD_REQUEST);
            }

            $wallet = Wallet::where('user_id', $user->id)->first();
            if (!$wallet || $wallet->balance < $amount) {
                return response()->json(['message' => 'Insufficient wallet balance.'], Response::HTTP_BAD_REQUEST);
            }

            $cart = Cart::with('cartItems')->where('user_id', $user->id)->first();
            if (!$cart || $cart->cartItems->isEmpty()) {
                return response()->json(['message' => 'Cart is empty.'], Response::HTTP_BAD_REQUEST);
            }

            DB::beginTransaction();

            try {
                foreach ($cart->cartItems as $item) {
                    // 👉 Handle Packages
                    if ($item->package_id) {
                        $package = Package::find($item->package_id);
                        if (!$package || !$package->subscription) {
                            DB::rollBack();
                            return response()->json(['message' => 'Package or subscription not found.'], Response::HTTP_NOT_FOUND);
                        }

                        $subscription = $package->subscription;
                        if ($subscription->available_serial_count < $item->quantity) {
                            DB::rollBack();
                            return response()->json(['message' => 'Not enough stock for the package.'], Response::HTTP_BAD_REQUEST);
                        }

                        // Get related replacement record
                        $replacement = ProductReplacement::where('user_id', $user->id)
                            ->where('package_id', $package->id)
                            ->first();

                        if (!$replacement || $replacement->avalable_replace_count < $item->quantity) {
                            DB::rollBack();
                            return response()->json(['message' => 'Not enough available replacement count.'], Response::HTTP_BAD_REQUEST);
                        }

                        for ($i = 0; $i < $item->quantity; $i++) {
                            $serial = ProductReplacementSerial::where('product_replacement_id', $replacement->id)->first();
                            if (!$serial) {
                                DB::rollBack();
                                return response()->json(['message' => 'Replacement serial not available.'], Response::HTTP_BAD_REQUEST);
                            }

                            // Use it & delete or mark as used
                            $serial->delete();

                            // Reduce available count
                            $replacement->avalable_replace_count -= 1;
                            $replacement->save();
                        }

                        // Decrement actual subscription serials
                        $subscription->available_serial_count -= $item->quantity;
                        $subscription->save();
                    }

                    // 👉 Handle Bulk Products
                    if ($item->bulk_product_id) {
                        $bulkProduct = BulkProduct::find($item->bulk_product_id);
                        if (!$bulkProduct || $bulkProduct->serial_count < $item->quantity) {
                            DB::rollBack();
                            return response()->json(['message' => 'Not enough stock for the bulk product.'], Response::HTTP_BAD_REQUEST);
                        }

                        $allSerials = array_values(array_filter(explode("\n", $bulkProduct->serial), 'trim'));

                        // Check stock
                        if (count($allSerials) < $item->quantity) {
                            $order->update([
                                'payment_status' => 'failed',
                            ]);
                            return response()->json(['message' => 'Not enough stock for the bulk product'], Response::HTTP_BAD_REQUEST);
                        }

                        // Remove the needed number of serials
                        $removedSerials = array_splice($allSerials, 0, $item->quantity);

                        // Update bulk product
                        $bulkProduct->serial = implode("\n", $allSerials);
                        $bulkProduct->serial_count = count($allSerials);
                        $bulkProduct->save();

                        // Save each removed serial individually
                        foreach ($removedSerials as $serial) {
                            RemovedBulkProductSerial::create([
                                'bulk_product_id' => $item->bulk_product_id,
                                'order_item_id'   => $item->id,
                                'serial'          => $serial,
                            ]);
                        }

                    }
                }

                $wallet->balance -= $amount;
                $wallet->save();

                // Optionally mark cart as paid
                $cart->status = 'paid';
                $cart->save();

                DB::commit();
                return response()->json(['message' => 'Wallet payment processed successfully.']);
            } catch (\Exception $e) {
                DB::rollBack();
                return response()->json(['message' => 'Something went wrong.', 'error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        }

}
