<?php

namespace App\Repositories\Coupon\Interface;

interface CouponRepositoryInterface
{
    public function all($request);
    public function store($request);
    public function update($request);
    public function delete($id);
    
}
