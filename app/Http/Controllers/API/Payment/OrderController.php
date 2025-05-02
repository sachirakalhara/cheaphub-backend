<?php

namespace App\Http\Controllers\API\Payment;

use App\Http\Controllers\Controller;
use App\Models\Payment\Order;
use App\Repositories\Payment\Interface\OrderRepositoryInterface;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    private $orderRepository;

    public function __construct(OrderRepositoryInterface $orderRepository)
    {
        $this->orderRepository = $orderRepository;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
    }

    public function getOrderByID($id)
    {
        return $this->orderRepository->findById($id);
    }

    public function getOrdersByUserID($user_id)
    {
        return $this->orderRepository->getOrdersByUserID($user_id);
    }


    public function getWalletOrdersByUserID($user_id)
    {
        return $this->orderRepository->getWalletOrdersByUserID($user_id);
    }
    
    
    public function filter(Request $request)
    {
        return $this->orderRepository->filter($request);
    }

    public function totalCustomerWithSpend()
    {
        return $this->orderRepository->totalCustomerCountWithSpend();
    }
    
    
    public function changeStatus(Request $request)
    {
        return $this->orderRepository->changeStatus($request);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Order $order)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Order $order)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Order $order)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Order $order)
    {
        //
    }
}
