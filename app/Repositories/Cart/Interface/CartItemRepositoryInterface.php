<?php

namespace App\Repositories\Cart\Interface;

interface CartItemRepositoryInterface
{
    public function addToCart($request);
    public function removeItemByItemId($id);
    

}
