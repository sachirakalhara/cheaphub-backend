<?php

namespace App\Repositories\Cart;

use App\Helpers\Helper;
use App\Http\Resources\Cart\CartCollection;
use App\Models\Cart\Cart;
use App\Models\Coupon\Coupon;
use App\Models\Product\Bulk\BulkProduct;
use App\Models\Subscription\Package;
use App\Repositories\Cart\Interface\CartRepositoryInterface;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class CartRepository implements CartRepositoryInterface
{

    public function addToCart($request)
    {
        $product_type = null;
        $product_price = 0;

        if ($request->bulk_product_id) {
            $product_type = 'bulk';
            $bulkProduct = BulkProduct::find($request->bulk_product_id);
            $product_price = $bulkProduct->price;
            if (!$bulkProduct || $bulkProduct->serial_count < $request->quantity) {
                return response()->json(['message' => 'Not enough stock for the bulk product'], Response::HTTP_BAD_REQUEST);
            }
        }

        if ($request->package_id) {
            $product_type = 'subscription';
            $bulkProduct = Package::find($request->package_id);
            $product_price = $bulkProduct->price;
            if (!$bulkProduct || $bulkProduct->subscription->available_serial_count < $request->quantity) {
                return response()->json(['message' => 'Not enough stock for the bulk product'], Response::HTTP_BAD_REQUEST);
            }
        }


        $coupon = null;
        if ($request->coupon_code) {
            $coupon = Coupon::where('coupon_code', $request->coupon_code)->first();

            if (!$coupon) {
            return response()->json(['message' => 'Invalid coupon code'], Response::HTTP_BAD_REQUEST);
            }

            if ($coupon->expiry_date < now()) {
            return response()->json(['message' => 'Coupon has expired'], Response::HTTP_BAD_REQUEST);
            }

            if ($product_type != 'bulk' || $product_type != 'subscription' || $coupon->product_type != 'both') {
            return response()->json(['message' => 'Coupon is not applicable for this product type'], Response::HTTP_BAD_REQUEST);
            }

            $total_price = $product_price * $request->quantity;
            $discount = $total_price * $coupon->discount_percentage / 100;

            if ($discount > $coupon->max_discount_amount) {
            $discount = $coupon->max_discount_amount;
            }

            if ($discount > $total_price) {
            return response()->json(['message' => 'Discount exceeds total price'], Response::HTTP_BAD_REQUEST);
            }
        }
        

        $cart = Cart::updateOrCreate(
            [
                'user_id' => Auth::id(), 
                'bulk_product_id' => $request->bulk_product_id,
                'package_id' => $request->package_id
            ],
            [
                'quantity' => $request->quantity,
                'coupon_code' => $request->coupon_code
            ]
        );
        return response()->json(['message' => 'Product added to cart', 'cart' => $cart]);
    }

    public function getCart()
    {
        $cartItems = Cart::with('bulkProduct','package')->where('user_id', Auth::id())->get();

        if (count($cartItems) > 0) {
            return new CartCollection($cartItems);
        } else {
            return Helper::error(Response::$statusTexts[Response::HTTP_NO_CONTENT], Response::HTTP_NO_CONTENT);
        }
    }

    public function removeFromCart($id)
    {
        $cartItem = Cart::where('user_id', Auth::id())->find($id);
        if($cartItem->delete()){
            return response()->json(['message' => 'Product removed from cart']);
        }else{
            return Helper::error(Response::$statusTexts[Response::HTTP_NO_CONTENT], Response::HTTP_NO_CONTENT);
        }
    }

    public function clearCart(){
        $userId = Auth::id();
        
        // Fetch cart items first
        $cartItems = Cart::where('user_id', $userId)->get();

        if ($cartItems->isEmpty()) {
            return response()->json(['message' => 'Cart is already empty'], Response::HTTP_NO_CONTENT);
        }

        // Delete cart items
        $deleted = Cart::where('user_id', $userId)->delete();

        if ($deleted) {
            return response()->json(['message' => 'All products removed from cart'], Response::HTTP_OK);
        }

        return response()->json(['message' => 'Failed to clear cart'], Response::HTTP_INTERNAL_SERVER_ERROR);
    }
    
    

}