<?php

namespace App\Repositories\Subscription\Interface;

interface SubscriptionRepositoryInterface
{
    public function all($request);
    public function store($request);
    public function delete($product_id);
}
