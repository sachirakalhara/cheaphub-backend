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

    public function cartDetails(Request $equest)
    {
        return $this->cartRepository->cartDetails($equest);
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
