<?php

namespace App\Repositories\Cart\Interface;

interface CartRepositoryInterface
{
    public function cartDetails($request);
    public function getCart();
    public function removeFromCart($id);
    public function clearCart();

}
