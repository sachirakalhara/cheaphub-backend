<?php

namespace App\Repositories\Payment;

use App\Helpers\Helper;
use App\Http\Resources\Payment\OrderNoteCollection;
use App\Http\Resources\Payment\OrderNoteResource;
use App\Models\Payment\OrderNote;
use App\Repositories\Payment\Interface\OrderNoteRepositoryInterface;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class OrderNoteRepository implements OrderNoteRepositoryInterface
{

    public function addNote($request)
    {
        $note = new OrderNote();
        $note->order_id = $request->order_id;
        $note->user_id = Auth::id();
        $note->note = $request->note;
        
        if ($note->save()) {
             return new OrderNoteResource($note);
        } else {
            return Helper::error('Order Note not found', Response::HTTP_NOT_FOUND);
        }

    }

    public function getNotesByOrderID($order_id)
    {
        // Implementation for retrieving notes by order ID
        $notes = OrderNote::where('order_id', $order_id)->get();

        if ($notes->isNotEmpty()) {
            return new OrderNoteCollection($notes);
        } else {
            return Helper::success(Response::$statusTexts[Response::HTTP_NO_CONTENT], Response::HTTP_NO_CONTENT);
        }

    }
}