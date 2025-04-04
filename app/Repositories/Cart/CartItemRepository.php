<?php

namespace App\Repositories\Cart;

use App\Models\Cart\Cart;
use App\Models\Cart\CartItem;
use App\Models\Product\Bulk\BulkProduct;
use App\Models\Subscription\Package;
use App\Repositories\Cart\Interface\CartItemRepositoryInterface;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class CartItemRepository implements CartItemRepositoryInterface
{
    public function addToCart($request)
    {
        $userId = Auth::id();
        $totalQty = 0;
        if (!$userId) {
            return response()->json(['message' => 'User not authenticated'], Response::HTTP_UNAUTHORIZED);
        }

        $cart = Cart::firstOrCreate(
            ['user_id' => $userId],
            ['user_id' => $userId, 'created_at' => now(), 'updated_at' => now()]
        );

        $cartItemPackagesQty = CartItem::where('cart_id', $cart->id)
            ->where('package_id', $request->package_id)
            ->sum('quantity');

        $cartItemBulkProductsQty = CartItem::where('cart_id', $cart->id)
            ->where('bulk_product_id', $request->bulk_product_id)
            ->sum('quantity');

        $qty = $request->quantity ?? 0;

        if ($request->bulk_product_id) {
            $bulkProduct = BulkProduct::find($request->bulk_product_id);
            if (!$bulkProduct) {
                return response()->json(['message' => 'Bulk product not found'], Response::HTTP_NOT_FOUND);
            }

            $totalQty = $qty + $cartItemBulkProductsQty;
            if ($bulkProduct->serial_count < $totalQty) {
                return response()->json(['message' => 'Not enough stock for the bulk product'], Response::HTTP_BAD_REQUEST);
            }
        }

        if ($request->package_id) {
            $package = Package::find($request->package_id);
            if (!$package) {
                return response()->json(['message' => 'Package not found'], Response::HTTP_NOT_FOUND);
            }

            $totalQty = $qty + $cartItemPackagesQty;
            if ($package->subscription->available_serial_count < $totalQty) {
                return response()->json(['message' => 'Not enough stock for the package'], Response::HTTP_BAD_REQUEST);
            }
        }

        $cartItem = CartItem::updateOrCreate(
            [
                'cart_id' => $cart->id,
                'bulk_product_id' => $request->bulk_product_id,
                'package_id' => $request->package_id,
            ],
            [
                'quantity' => $totalQty,
            ]
        );

        return response()->json(['message' => 'Product added to cart', 'cart_item' => $cartItem]);
    }


    public function removeItemByItemId($id)
    {
        $cartItem = CartItem::find($id);

        if (!$cartItem) {
            return response()->json(['message' => 'Cart item not found'], Response::HTTP_NOT_FOUND);
        }

        try {
            $cartItem->delete();
            return response()->json(['message' => 'Product removed from cart'], Response::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to remove product from cart',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
