<?php

namespace App\Http\Controllers\API\Cart;

use App\Http\Controllers\Controller;
use App\Repositories\Cart\Interface\CartItemRepositoryInterface;
use Illuminate\Http\Request;

class CartItemController extends Controller
{

    private $cartItemRepository;

    public function __construct(CartItemRepositoryInterface $cartItemRepository)
    {
        $this->cartItemRepository = $cartItemRepository;
    }


    public function addToCart(Request $request)
    {
        $request->validate([
            'bulk_product_id' => 'nullable|exists:bulk_products,id|required_without:package_id',
            'package_id' => 'nullable|exists:packages,id|required_without:bulk_product_id',        
            'quantity' => 'required|integer|min:0',
            'remove_qty' => 'required|integer|min:0',
        ]);

        return $this->cartItemRepository->addToCart($request);
    }

    public function removeItemByItemId($id)
    {
        return $this->cartItemRepository->removeItemByItemId($id);

    }
}
