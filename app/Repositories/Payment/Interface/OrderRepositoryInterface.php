<?php

namespace App\Repositories\Payment\Interface;

interface OrderRepositoryInterface
{
    public function findById($id);
    public function getOrdersByUserID($user_id);
    public function getWalletOrdersByUserID($user_id);
    public function filter($request);
    public function totalCustomerCountWithSpend();
    public function changeStatus($request);

}
