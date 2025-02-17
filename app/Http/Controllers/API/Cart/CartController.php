<?php

namespace App\Http\Controllers\API\Cart;

use App\Http\Controllers\Controller;
use App\Repositories\Cart\Interface\CartRepositoryInterface;
use Illuminate\Http\Request;

class CartController extends Controller
{
    private $cartRepository;

    public function __construct(CartRepositoryInterface $cartRepository)
    {
        $this->cartRepository = $cartRepository;
    }

    public function addToCart(Request $request)
    {
        $request->validate([
            'bulk_product_id' => 'nullable|exists:bulk_products,id|required_without:contribution_product_id',
            'contribution_product_id' => 'nullable|exists:contribution_products,id|required_without:bulk_product_id',        
            'quantity' => 'required|integer|min:1',
            'user_id' => 'required'
        ]);

        return $this->cartRepository->addToCart($request);
    }

    public function getCart()
    {
        return $this->cartRepository->getCart();

    }

    public function removeFromCart($id)
    {
        return $this->cartRepository->removeFromCart($id);
    }

    public function clearCart()
    {
        return $this->cartRepository->clearCart();
    }

    
}
