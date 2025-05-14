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
use App\Models\Payment\Order;
use App\Models\Subscription\Subscription;
use App\Models\Payment\OrderItems;

use App\Models\Coupon\Coupon;
use App\Models\Product\Contribution\RemovedContributionProductSerial;

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
        $discount = 0;

        if ($amount <= 0) {
            return response()->json(['message' => 'Invalid payment amount.'], Response::HTTP_BAD_REQUEST);
        }

        $wallet = Wallet::where('user_id', $user->id)->first();
        if (!$wallet || $amount > $wallet->balance) {
            return response()->json(['message' => 'Insufficient wallet balance.'], Response::HTTP_BAD_REQUEST);
        }

        $cart = Cart::with('cartItems')->where('user_id', $user->id)->first();
        if (!$cart || $cart->cartItems->isEmpty()) {
            return response()->json(['message' => 'Cart is empty.'], Response::HTTP_BAD_REQUEST);
        }

        // Apply coupon if present
        if ($cart->coupon_code) {
            $coupon = Coupon::where('coupon_code', $cart->coupon_code)->first();

            if (!$coupon) {
                return response()->json(['message' => 'Invalid coupon code'], Response::HTTP_BAD_REQUEST);
            }

            if ($coupon->expiry_date < now()) {
                return response()->json(['message' => 'Coupon has expired'], Response::HTTP_BAD_REQUEST);
            }

            // Calculate the total price for packages and bulk products
            $packagesTotalPrice = $cart->cartItems->whereNotNull('package_id')->sum(function ($item) {
                $package = Package::find($item->package_id);
                return $package ? $package->price * $item->quantity : 0;
            });

            $bulkProductsTotalPrice = $cart->cartItems->whereNotNull('bulk_product_id')->sum(function ($item) {
                $bulkProduct = BulkProduct::find($item->bulk_product_id);
                return $bulkProduct ? $bulkProduct->price * $item->quantity : 0;
            });

            $totalPrice = $packagesTotalPrice + $bulkProductsTotalPrice;

            // Apply discount based on coupon type
            if (in_array($coupon->product_type, ['subscription', 'both'])) {
                $discount += $packagesTotalPrice * $coupon->discount_percentage / 100;
            }

            if (in_array($coupon->product_type, ['bulk', 'both'])) {
                $discount += $bulkProductsTotalPrice * $coupon->discount_percentage / 100;
            }

            // Ensure discount does not exceed maximum allowed value
            $discount = min($discount, $coupon->max_discount_amount);

            // Prevent discount from exceeding total price
            if ($discount > $totalPrice) {
                return response()->json(['message' => 'Discount exceeds total price'], Response::HTTP_BAD_REQUEST);
            }

            // Adjust amount after discount
            $amount = $totalPrice - $discount;
        }

        // Create order
        $order = Order::create([
            'user_id'        => $user->id,
            'amount'         => $amount,
            'amount_paid'    => $amount,
            'discount'       => $discount,
            'currency'       => $wallet->currency,
            'description'    => $data['description'] ?? '',
            'payment_status' => 'pending',
            'is_wallet'      => true,
            'order_id'       => 'order_' . now()->format('YmdHis'),
            'payment_method' => 'wallet',
        ]);

        // Create order items
        foreach ($cart->cartItems as $cartItem) {
            OrderItems::create([
                'order_id'        => $order->id,
                'bulk_product_id' => $cartItem->bulk_product_id,
                'package_id'      => $cartItem->package_id,
                'quantity'        => $cartItem->quantity,
                'price'           => $cartItem->price,
            ]);
        }

        // Process each item in the order
        foreach ($order->orderItems as $orderItem) {
            if ($orderItem->bulk_product_id) {
                $this->processBulkProductStock($order, $orderItem);
            }

            if ($orderItem->package_id) {
                $this->processPackageStock($order, $orderItem);
            }
        }

        // Begin transaction for payment processing
        DB::beginTransaction();
        try {
            $order->update(['payment_status' => 'paid']);
            $wallet->decrement('balance', $amount); // Deduct wallet balance
            $cart->cartItems()->delete(); // Clear the cart

            DB::commit();

            return response()->json(['message' => 'Payment successful', 'order_id' => $order->order_id], Response::HTTP_OK);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Something went wrong', 'error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // Process bulk product stock
    private function processBulkProductStock($order, $orderItem)
    {
        $bulkProduct = BulkProduct::find($orderItem->bulk_product_id);

        if ($bulkProduct) {
            // Parse the serials into an array
            $allSerials = array_values(array_filter(explode("\n", $bulkProduct->serial), 'trim'));

            if (count($allSerials) < $orderItem->quantity) {
                $order->update(['payment_status' => 'failed']);
                return response()->json(['message' => 'Not enough stock for the bulk product'], Response::HTTP_BAD_REQUEST);
            }

            //maximum quantity check
            if ($orderItem->quantity > $bulkProduct->maximum_quantity) {
                $order->update(['payment_status' => 'failed']);
                return response()->json(['message' => 'Maximum quantity exceeded'], Response::HTTP_BAD_REQUEST);
            }


            // Check minimum quantity
            if ($orderItem->quantity < $bulkProduct->minimum_quantity) {
                $order->update(['payment_status' => 'failed']);
                return response()->json(['message' => 'Minimum quantity not met'], Response::HTTP_BAD_REQUEST);
            }

            // Remove the required serials
            $removedSerials = array_splice($allSerials, 0, $orderItem->quantity);

            // Update the bulk product stock
            $bulkProduct->serial = implode("\n", $allSerials);
            $bulkProduct->serial_count = count($allSerials);
            $bulkProduct->save();

            // Record removed serials
            foreach ($removedSerials as $serial) {
                RemovedBulkProductSerial::create([
                    'bulk_product_id' => $orderItem->bulk_product_id,
                    'order_item_id'   => $orderItem->id,
                    'serial'          => $serial,
                ]);
            }
        }
    }

    // Process package stock
    private function processPackageStock($order, $orderItem)
    {
        $package = Package::find($orderItem->package_id);

        if ($package) {
            $subscription = Subscription::find($package->subscription_id);

            if ($subscription && $orderItem->quantity > $subscription->available_serial_count) {
                $order->update(['payment_status' => 'failed']);
                return response()->json(['message' => 'Not enough stock for the subscription'], Response::HTTP_BAD_REQUEST);
            }

            $allSerials = array_values(array_filter(explode("\n", $subscription->serial), 'trim'));

            if (!empty($allSerials)) {
                // Remove the first $orderItem->quantity serials
                $removedSerials = array_splice($allSerials, 0, $orderItem->quantity);

                // Update subscription stock
                $subscription->serial = implode("\n", $allSerials);
                $subscription->available_serial_count = max(0, $subscription->available_serial_count - $orderItem->quantity);
                $subscription->save();

                // Record removed serials
                foreach ($removedSerials as $serial) {
                    RemovedContributionProductSerial::create([
                        'package_id' => $package->id,
                        'order_item_id' => $orderItem->id,
                        'serial' => $serial,
                    ]);
                }
            }
        }
    }


}
