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
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class TicketRepository implements TicketRepositoryInterface
{
    public function all($request)
    {

        $query = Ticket::query();
        $user = auth()->user();
        $isAdmin = $user->hasRole('super_admin');

        if (!$isAdmin) {
            $query->where('customer_id', $user->id);
        } elseif ($request->filled('user_id')) {
            $query->where('customer_id', $request->user_id);
        }

        if ($request->filled('ticket_number')) {
            $query->where('ticket_number', 'like', '%' . $request->ticket_number . '%');
        }
        
        if ($request->filled('status')) {
            $query->where('status',  $request->status );
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

        $order = Order::find($data->order_id);
        if (!$order) {
            return response()->json([
                'message' => 'Invalid order ID.'
            ], 400);
        }

        if ($order->user_id !== $user->id) {
            return response()->json([
                'message' => 'Unauthorized'
            ], 403);
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

        $user = auth()->user();
        $isAdmin = $user->user_level_id == 1;

        if (!$isAdmin && $ticket->customer_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Optional image attachment — uploaded to the same S3 disk used for
        // product/category/profile images. Upload happens before the comment is
        // saved, so a failed upload never leaves a half-sent message behind.
        $attachment = [];
        if ($data->hasFile('attachment')) {
            $file = $data->file('attachment');
            $disk = Storage::disk('s3');
            // Random, unguessable name: support screenshots can show payment or
            // account details, and uniqid() (used for public product images) is
            // just a sequential timestamp. Extension is guessed from the file's
            // content (already validated as an allowed image), not the
            // client-supplied filename.
            $path = 'ticket/attachment/' . Str::random(40) . '.' . $file->extension();

            // The s3 disk is configured with 'throw' => false, so a failure
            // comes back as false rather than an exception.
            if (!$disk->put($path, file_get_contents($file))) {
                return response()->json(['message' => 'Failed to upload image. Please try again.'], Response::HTTP_INTERNAL_SERVER_ERROR);
            }

            $attachment = [
                'attachment_url' => $disk->url($path),
                'attachment_type' => $file->getMimeType(),
                'attachment_size' => $file->getSize(),
            ];
        }

        $comment = $ticket->comments()->create(array_merge([
            'user_id' => $user->id,
            // The message column is not nullable; an image-only message stores ''.
            'message' => $data->message ?? '',
        ], $attachment));

        if ($isAdmin) {
            $user = User::find($ticket->order->user_id);
            $user->notify(new TicketReplyNotification($ticket, 'customer'));
        } else {
            $admins = User::where('user_level_id', 1)->get();
            foreach ($admins as $admin) {
                $admin->notify(new TicketReplyNotification($ticket, 'admin'));
            }
        }


        return response()->json([
            'comment' => $comment->load('user'),
        ], 201);
    }

    public function statusChange($data)
    {
        $ticket = Ticket::findOrFail($data->id);

        $user = auth()->user();
        $isAdmin = $user->user_level_id == 1;

        if (!$isAdmin && $ticket->customer_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $ticket->status = $data->status;
        $ticket->save();

        return response()->json([
            'message' => 'Ticket status updated successfully.',
            'ticket' => $ticket
        ], 200);
    }


}
