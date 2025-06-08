<?php

namespace App\Repositories\Ticket;

use App\Helpers\Helper;
use App\Models\Payment\Order;
use App\Models\Ticket\Ticket;
use App\Notifications\TicketNotification;
use App\Repositories\Ticket\Interface\TicketRepositoryInterface;
use Illuminate\Http\Response;
use App\Models\User\User;
use App\Notifications\TicketReplyNotification;

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
            $order_list = $query->with(['order', 'order.items', 'customer', 'comments.user'])->orderBy('created_at', 'desc')->get();
        } else {
            $order_list = $query->with(['order', 'order.items', 'customer', 'comments.user'])->orderBy('created_at', 'desc')->paginate(10);
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
        $user->notify(new TicketNotification($ticket,'customer'));

        $admins = User::where('user_level_id', 1)->get();
        foreach ($admins as $admin) {
            $admin->notify(new TicketNotification($ticket,'admin'));
        }

        if (isset($data->message) && !empty($data->message)) {
            $comment = $ticket->comments()->create([
                'user_id' => $user->id,
                'message' => $data->message,
            ]);
            $ticket->load('comments.user');
        }
        return response()->json($ticket, 201);
    }

    public function addComment($data)
    {
        $ticket = Ticket::findOrFail($data->id);

        $comment = $ticket->comments()->create([
            'user_id' => auth()->id(),
            'message' => $data->message,
        ]);
        $user = User::find(auth()->id());
        $user->notify(new TicketReplyNotification($ticket,'customer'));

        $admins = User::where('user_level_id', 1)->get();
        foreach ($admins as $admin) {
            $admin->notify(new TicketReplyNotification($ticket,'admin'));
        }


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
