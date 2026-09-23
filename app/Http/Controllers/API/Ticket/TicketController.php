<?php

namespace App\Http\Controllers\API\Ticket;

use App\Http\Controllers\Controller;
use App\Repositories\Ticket\Interface\TicketRepositoryInterface;
use Illuminate\Http\Request;

class TicketController extends Controller
{
    private $ticketRepository;

    public function __construct(TicketRepositoryInterface $ticketRepository)
    {
        $this->ticketRepository = $ticketRepository;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        return $this->ticketRepository->all($request);
    }

    public function store(Request $request)
    {

        $request->validate([
            'order_id' => 'required|exists:orders,id',
            'subject' => 'required|string',
            'description' => 'required|string',
        ]);
        return $this->ticketRepository->store($request);
    }

    public function addComment(Request $request)
    {
        // A message needs text, an image, or both. The image is checked on the
        // server too (type by file content, size), since client-side checks can
        // be bypassed.
        $request->validate([
            'message' => 'required_without:attachment|nullable|string',
            'attachment' => 'nullable|file|image|mimes:jpg,jpeg,png,webp,gif|max:2048',
        ], [
            'message.required_without' => 'Please enter a message or attach an image.',
            'attachment.image' => 'Please select an image under 2MB (jpg, png, webp, gif)',
            'attachment.mimes' => 'Please select an image under 2MB (jpg, png, webp, gif)',
            'attachment.max' => 'Please select an image under 2MB (jpg, png, webp, gif)',
            'attachment.uploaded' => 'Failed to upload image. Please try again.',
        ]);

        return $this->ticketRepository->addComment($request);

    }

    public function statusChange(Request $request)
    {
        $request->validate([
            'status' => 'required|string',
        ]);

        return $this->ticketRepository->statusChange($request);

    }

}
