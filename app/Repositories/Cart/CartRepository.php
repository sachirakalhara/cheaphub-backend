<?php

namespace App\Repositories\Cart;

use App\Helpers\Helper;
use App\Http\Resources\Cart\CartCollection;
use App\Http\Resources\Cart\CartResource;
use App\Models\Cart\Cart;
use App\Models\Coupon\Coupon;
use App\Models\Product\Bulk\BulkProduct;
use App\Models\Subscription\Package;
use App\Repositories\Cart\Interface\CartRepositoryInterface;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class CartRepository implements CartRepositoryInterface
{

    public function cartDetails($request)
    {
        $coupon = null;

        if ($request->coupon_code) {
            $coupon = Coupon::where('coupon_code', $request->coupon_code)->first();

            if ($coupon && $coupon->expiry_date < now()) {
                return response()->json(['message' => 'Coupon has expired'], Response::HTTP_BAD_REQUEST);
            }
        }
        
        $user_id = Auth::id(); 
        $cart = Cart::with('cartItems')->where('user_id', $user_id)->first();

        if (!$cart) {
            return response()->json(['message' => 'Cart not found'], Response::HTTP_NOT_FOUND);
        }

        // Calculate total price for packages and bulk products
        $packagesTotalPrice = $cart->cartItems->whereNotNull('package_id')->sum(function ($item) {
            $package = Package::find($item->package_id);
            return $package ? $package->price * $item->quantity : 0;
        });

        $bulkProductsTotalPrice = $cart->cartItems->whereNotNull('bulk_product_id')->sum(function ($item) {
            $bulkProduct = BulkProduct::find($item->bulk_product_id);
            return $bulkProduct ? $bulkProduct->price * $item->quantity : 0;
        });

        $totalPrice = $packagesTotalPrice + $bulkProductsTotalPrice;
        $discount = 0;

        if ($coupon) {
            if (in_array($coupon->product_type, ['subscription', 'both'])) {
                $discount += $packagesTotalPrice * $coupon->discount_percentage / 100;
            }

            if (in_array($coupon->product_type, ['bulk', 'both'])) {
                $discount += $bulkProductsTotalPrice * $coupon->discount_percentage / 100;
            }

            $discount = min($discount, $coupon->max_discount_amount);

            if ($discount > $totalPrice) {
                return response()->json(['message' => 'Discount exceeds total price'], Response::HTTP_BAD_REQUEST);
            }

            // Update the cart with the coupon code
            $cart->update(['coupon_code' => $request->coupon_code]);
        }

        $data = [
            'cart' => $cart->cartItems,
            'total_price' => $totalPrice,
            'discount' => $discount,
            'final_price' => $totalPrice - $discount,
        ];

        return response()->json(['message' => 'Cart details retrieved successfully', 'data' => $data]);
    }

    public function getCart()
    {
        $cart = Cart::with('cartItems')->where('user_id', Auth::id())->first();

        if ($cart) {
            return new CartResource($cart);
        } else {
            return Helper::error(Response::$statusTexts[Response::HTTP_NO_CONTENT], Response::HTTP_NO_CONTENT);
        }
    }

    public function removeFromCart($id)
    {
        $cart = Cart::with('cartItems')->where('user_id', Auth::id())->first();

        if (!$cart) {
            return response()->json(['message' => 'Cart not found'], Response::HTTP_NOT_FOUND);
        }

        $cartItem = $cart->cartItems->where('id', $id)->first();

        if (!$cartItem) {
            return response()->json(['message' => 'Cart item not found'], Response::HTTP_NOT_FOUND);
        }

        if ($cartItem->delete()) {
            if ($cart->cartItems()->count() === 0) {
                $cart->delete();
            }

            return response()->json(['message' => 'Product removed from cart'], Response::HTTP_OK);
        } else {
            return response()->json(['message' => 'Failed to remove product from cart'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function clearCart()
    {
        $userId = Auth::id();

        // Fetch the cart for the user
        $cart = Cart::with('cartItems')->where('user_id', $userId)->first();

        if (!$cart) {
            return response()->json(['message' => 'Cart is already empty'], Response::HTTP_NO_CONTENT);
        }

        // Delete all cart items
        $cart->cartItems()->delete();

        // Delete the cart itself
        $cart->delete();

        return response()->json(['message' => 'Cart cleared successfully'], Response::HTTP_OK);
    }

}