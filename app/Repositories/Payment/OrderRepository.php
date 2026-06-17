<?php

namespace App\Repositories\Payment;

use App\Helpers\Helper;
use App\Http\Resources\Payment\OrderCollection;
use App\Http\Resources\Payment\OrderResource;
use App\Models\Payment\Order;
use App\Models\Payment\Wallet;
use App\Models\Payment\OrderNote;
use App\Models\User\User;
use App\Notifications\OrderRefunded;
use App\Repositories\Payment\Interface\OrderRepositoryInterface;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

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

    
    public function getOrdersByUserID($user_id, $perPage = 10)
    {
        $orders = Order::where('user_id', $user_id)->paginate($perPage);

        if ($orders->isNotEmpty()) {
            return new OrderCollection($orders);
        } else {
            return Helper::error('No orders found for this user', Response::HTTP_NO_CONTENT);
        }
    }


    public function getWalletOrdersByUserID($user_id, $perPage = 10)
    {
        $orders = Order::where('is_wallet',true)->where('user_id', $user_id)->paginate($perPage);

        if ($orders->isNotEmpty()) {
            return new OrderCollection($orders);
        } else {
            return Helper::error('No orders found for this user', Response::HTTP_NO_CONTENT);
        }
    }
    
    
    public function totalCustomerCountWithSpend()
    {
        $orders = Order::select('user_id', DB::raw('COALESCE(SUM(amount_paid), 0) as total_spend'))
            ->groupBy('user_id')
            ->havingRaw('SUM(amount_paid) > 0')
            ->get();

        $totalUsers = $orders->count();
        $totalSpend = $orders->sum('total_spend');

        if ($orders->isEmpty()) {
            return response()->json([
                'user_count' => 0,
                'total_spend' => 0
            ], Response::HTTP_OK);
        }

        return response()->json([
            'user_count' => $totalUsers,
            'total_spend' => $totalSpend
        ], Response::HTTP_OK);
    }

    public function filter($request)
    {
        $query = Order::query()->with(['orderItems.bulkProduct', 'orderItems.package']);
        // $query->where('is_wallet',  false );

        if ($request->filled('user_id')) {
            $query->where('user_id',  $request->user_id );
        }

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
                $q->whereNotNull('package_id');
            });
        }

        if ($request->filled('transaction_id')) {
            $query->where('transaction_id', 'like', '%' . $request->transaction_id . '%');
        }

        if ($request->filled('from_date') && $request->filled('to_date')) {
            $query->whereBetween('created_at', [
            Carbon::parse($request->from_date)->startOfDay(),
            Carbon::parse($request->to_date)->endOfDay()
            ]);
        }


        if ($request->input('all', false)) {
            $order_list = $query->get();
        } else {
            $order_list = $query->orderBy('created_at', 'desc')->paginate(10);
        }
        if ($order_list->isNotEmpty()) {
            return new OrderCollection($order_list);
        } else {
            return Helper::success(Response::$statusTexts[Response::HTTP_NO_CONTENT], Response::HTTP_NO_CONTENT);
        }
    }


    public function changeStatus($data)
    {
        $order = Order::find($data->id);

        if (!$order) {
            return Helper::error('Order not found', Response::HTTP_NOT_FOUND);
        }

        $validStatuses = ['pending', 'paid', 'failed'];
        if (!in_array($data->status, $validStatuses)) {
            return Helper::error('Invalid status provided', Response::HTTP_BAD_REQUEST);
        }

        $order->payment_status = $data->status;
        $order->save();

        return Helper::success('Order status updated successfully', Response::HTTP_OK);
    }

    /**
     * Refund a paid order.
     *
     * Two refund types:
     *  - 'wallet'  : credits the refund amount back to the customer's wallet balance.
     *  - 'gateway' : only marks the order as refunded; the admin processes the actual
     *                money return manually (Marx dashboard / own crypto wallet).
     *
     * Serials are NOT returned to stock — the customer keeps whatever was delivered.
     */
    public function refund($data)
    {
        $order = Order::find($data->id);

        if (!$order) {
            return Helper::error('Order not found', Response::HTTP_NOT_FOUND);
        }

        $refundType = $data->refund_type ?? null;
        if (!in_array($refundType, ['wallet', 'gateway'])) {
            return Helper::error('Invalid refund type. Use "wallet" or "gateway".', Response::HTTP_BAD_REQUEST);
        }

        if ($order->payment_status !== 'paid') {
            return Helper::error('Only paid orders can be refunded.', Response::HTTP_BAD_REQUEST);
        }

        // Refund the amount actually paid; fall back to the order total if not recorded.
        $refundAmount = ($order->amount_paid && $order->amount_paid > 0)
            ? $order->amount_paid
            : $order->amount;

        DB::beginTransaction();
        try {
            if ($refundType === 'wallet') {
                $wallet = Wallet::firstOrCreate(
                    ['user_id' => $order->user_id],
                    ['balance' => 0, 'currency' => $order->currency ?? 'USD']
                );
                $wallet->increment('balance', $refundAmount);
            }

            $order->payment_status = 'refunded';
            $order->save();

            $noteText = $refundType === 'wallet'
                ? 'Refund issued to customer wallet: ' . number_format($refundAmount, 2) . ' ' . ($order->currency ?? 'USD') . '. Order marked as refunded.'
                : 'Payment gateway refund marked. Admin will process the actual refund manually (Marx / crypto). Amount: ' . number_format($refundAmount, 2) . ' ' . ($order->currency ?? 'USD') . '.';

            $note = new OrderNote();
            $note->order_id = $order->id;
            $note->user_id = Auth::id();
            $note->note = $noteText;
            $note->save();

            DB::commit();

            // Notify the customer (email + database). A mail hiccup must not fail an
            // already-committed refund, so this is outside the transaction and guarded.
            try {
                $customer = User::find($order->user_id);
                if ($customer) {
                    $customer->notify(new OrderRefunded($order, $refundType, $refundAmount));
                }
            } catch (\Exception $e) {
                \Log::error('Refund notification failed for order ' . $order->order_id . ': ' . $e->getMessage());
            }

            return Helper::success('Order refunded successfully', Response::HTTP_OK);
        } catch (\Exception $e) {
            DB::rollBack();
            return Helper::error('Refund failed: ' . $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function walletHistory($perPage = 10)
    {
        $user = Auth::user();

        if (!$user) {
            return Helper::error('User not authenticated', Response::HTTP_UNAUTHORIZED);
        }

        $orders = Order::where('user_id', $user->id)
            ->where('payment_status', 'paid')
            ->where(function ($query) {
                $query->where('is_wallet', true)
                    ->orWhere('payment_method', 'wallet');
            })
            ->latest()
            ->paginate($perPage);

        if ($orders->isEmpty()) {
            return Helper::error('No wallet-related orders found', Response::HTTP_NO_CONTENT);
        }

        $formatted = $orders->map(function ($order) {
            $isCredit = $order->is_wallet === true;

            return [
                'date'  => Carbon::parse($order->updated_at)->format('D M d, Y'),
                'value' => ($isCredit ? '+' : '-') . number_format($order->amount_paid, 2),
                'type'  => $isCredit ? 'credit' : 'debit',
            ];
        });

        return response()->json([
            'data' => $formatted,
            'pagination' => [
                'current_page' => $orders->currentPage(),
                'last_page'    => $orders->lastPage(),
                'per_page'     => $orders->perPage(),
                'total'        => $orders->total(),
            ]
        ]);
    }

}
