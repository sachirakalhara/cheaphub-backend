<?php

namespace App\Repositories\Ticket;

use App\Helpers\Helper;
use App\Models\Payment\Order;
use App\Models\Ticket\Ticket;
use App\Repositories\Ticket\Interface\TicketRepositoryInterface;
use Illuminate\Http\Response;

class TicketRepository implements TicketRepositoryInterface
{
    public function all($request)
    {

        $query = Ticket::query();
        $user = auth()->user();
        $role = $user->getRoleNames();
        if ($request->filled('ticket_number')) {
            $query->where('ticket_number', 'like', '%' . $request->ticket_number . '%');
        }

        if($role  == 'super-admin'){

        }
        if ($request->filled('user_id')) {
            $query->where('customer_id',  $request->user_id );
        }

        if ($request->input('all', false)) {
            $order_list = $query->with(['order', 'customer', 'comments.user'])->get();
        } else {
            $order_list = $query->with(['order', 'customer', 'comments.user'])->orderBy('created_at', 'desc')->paginate(10);
        }

        if ($order_list->isNotEmpty()) {
            return $order_list;
        } else {
            return Helper::success(Response::$statusTexts[Response::HTTP_NO_CONTENT], Response::HTTP_NO_CONTENT);
        }

    }

    public function store($data)
    {
        $user = auth()->user();

        if (!Order::where('id', $data->order_id)->exists()) {
            return response()->json([
                'message' => 'Invalid order ID.'
            ], 400);
        }

        $ticket = Ticket::create([
            'ticket_number' => Ticket::generateTicketNumber(),
            'order_id' => $data->order_id,
            'customer_id' => $user->id,
            'customer' => $user,
            'subject' => $data->subject,
            'description' => $data->description,
        ]);

        return response()->json($ticket, 201);
    }

    public function addComment($data)
    {
        $ticket = Ticket::findOrFail($data->id);

        $comment = $ticket->comments()->create([
            'user_id' => auth()->id(),
            'message' => $data->message,
        ]);

        return response()->json([
            'comment' => $comment->load('user'),
        ], 201);
    }

    public function statusChange($data)
    {
        $ticket = Ticket::findOrFail($data->id);

        $ticket->status = $data->status;
        $ticket->save();

        return response()->json([
            'message' => 'Ticket status updated successfully.',
            'ticket' => $ticket
        ], 200);
    }


}
