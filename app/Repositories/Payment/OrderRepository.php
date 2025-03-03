<?php

namespace App\Repositories\Payment;

use App\Helpers\Helper;
use App\Http\Resources\Payment\OrderCollection;
use App\Http\Resources\Payment\OrderResource;
use App\Models\Payment\Order;
use App\Repositories\Payment\Interface\OrderRepositoryInterface;
use Illuminate\Http\Response;

class OrderRepository implements OrderRepositoryInterface
{
    public function findById($id)
    {
        $order = Order::find($id);
        if ($order) {
            return new OrderResource($order);
        } else {
            return Helper::error('Order not found', Response::HTTP_NOT_FOUND);
        }
    }

    public function filter($request)
    {
        $query = Order::query();
        $query->where('user_id',  $request->user_id );
        $query->where('is_wallet',  false );

        if ($request->filled('order_id')) {
            $query->where('order_id', 'like', '%' . $request->order_id . '%');
        }

        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        if ($request->filled('type') && $request->type == 'bulk') {
            $query->whereHas('orderItems', function ($q) {
                $q->whereNotNull('bulk_product_id');
            });
        }

        if ($request->filled('type') && $request->type == 'contribution') {
            $query->whereHas('orderItems', function ($q) {
                $q->whereNotNull('contribution_product_id');
            });
        }

        if ($request->filled('transaction_id')) {
            $query->where('transaction_id', 'like', '%' . $request->transaction_id . '%');
        }

        if ($request->filled('from_date') && $request->filled('to_date')) {
            $query->whereBetween('created_at', [$request->from_date, $request->to_date]);
        }


        if ($request->input('all', false)) {
            $order_list = $query->get();
        } else {
            $order_list = Helper::paginate($query);
        }

        if ($order_list->isNotEmpty()) {
            return new OrderCollection($order_list);
        } else {
            return Helper::success(Response::$statusTexts[Response::HTTP_NO_CONTENT], Response::HTTP_NO_CONTENT);
        }
    }

}
