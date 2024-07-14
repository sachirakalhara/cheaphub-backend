<?php

namespace App\Repositories\Cart;

use App\Helpers\Helper;
use App\Http\Resources\Cart\CartCollection;
use App\Models\Cart\Cart;
use App\Repositories\Cart\Interface\CartRepositoryInterface;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class CartRepository implements CartRepositoryInterface
{

    public function addToCart($request)
    {

        $cart = Cart::updateOrCreate(
            [
                'user_id' => Auth::id(), 
                'bulk_product_id' => $request->bulk_product_id,
                'contribution_product_id' => $request->contribution_product_id
            ],
            [
                'quantity' => $request->quantity
            ]
        );
        return response()->json(['message' => 'Product added to cart', 'cart' => $cart]);
    }

    public function getCart()
    {
        $cartItems = Cart::with('bulkProduct','contributionProduct')->where('user_id', Auth::id())->get();

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

}