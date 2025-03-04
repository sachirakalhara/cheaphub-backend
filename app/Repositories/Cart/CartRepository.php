<?php

namespace App\Repositories\Cart;

use App\Helpers\Helper;
use App\Http\Resources\Cart\CartCollection;
use App\Models\Cart\Cart;
use App\Models\Product\Bulk\BulkProduct;
use App\Repositories\Cart\Interface\CartRepositoryInterface;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class CartRepository implements CartRepositoryInterface
{

    public function addToCart($request)
    {
        if ($request->bulk_product_id) {
            $bulkProduct = BulkProduct::find($request->bulk_product_id);
            if (!$bulkProduct || $bulkProduct->serial_count < $request->quantity) {
                return response()->json(['message' => 'Not enough stock for the bulk product'], Response::HTTP_BAD_REQUEST);
            }
        }

        $cart = Cart::updateOrCreate(
            [
                'user_id' => Auth::id(), 
                'bulk_product_id' => $request->bulk_product_id,
                'package_id' => $request->package_id
            ],
            [
                'quantity' => $request->quantity
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