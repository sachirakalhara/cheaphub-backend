<?php

namespace App\Repositories\Payment\Interface;

interface OrderRepositoryInterface
{
    public function findById($id);
    public function filter($request);
    
}
