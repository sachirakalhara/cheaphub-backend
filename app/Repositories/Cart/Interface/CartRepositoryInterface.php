<?php

namespace App\Repositories\Cart\Interface;

interface CartRepositoryInterface
{
    public function addToCart($request);
    public function getCart();
    public function removeFromCart($id);

}
