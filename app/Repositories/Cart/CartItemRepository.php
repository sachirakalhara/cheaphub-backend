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
        $removeQty = $request->remove_qty ?? 0;

        if ($request->bulk_product_id) {
            $bulkProduct = BulkProduct::find($request->bulk_product_id);
            if (!$bulkProduct) {
                return response()->json(['message' => 'Bulk product not found'], Response::HTTP_NOT_FOUND);
            }
            if ($bulkProduct && $bulkProduct->bulk_type == 'serial_based') {
                $totalQty = $cartItemBulkProductsQty + $qty - $removeQty;
                if ($totalQty < 0) {
                    return response()->json(['message' => 'Quantity cannot be negative'], Response::HTTP_BAD_REQUEST);
                }

                //chec maximum quantity
                if ($totalQty > $bulkProduct->maximum_quantity) {
                    return response()->json(['message' => 'Maximum quantity exceeded'], Response::HTTP_BAD_REQUEST);
                }

                //check minimum quantity
                if ($totalQty < $bulkProduct->minimum_quantity) {
                    return response()->json(['message' => 'Minimum quantity not met'], Response::HTTP_BAD_REQUEST);
                }

                if ($bulkProduct->serial_count < $totalQty) {
                    return response()->json(['message' => 'Not enough stock for the bulk product'], Response::HTTP_BAD_REQUEST);
                }
            }
            
            if($bulkProduct && $bulkProduct->bulk_type == 'service_based') {
                $totalQty = $cartItemBulkProductsQty + $qty - $removeQty;
            }
        }

        if ($request->package_id) {
            $package = Package::find($request->package_id);
            if (!$package) {
                return response()->json(['message' => 'Package not found'], Response::HTTP_NOT_FOUND);
            }

            $totalQty = $cartItemPackagesQty + $qty - $removeQty;
            if ($totalQty < 1) {
                return response()->json(['message' => 'Quantity cannot be negative'], Response::HTTP_BAD_REQUEST);
            }

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
