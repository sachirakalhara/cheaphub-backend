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
        $request->validate([
            'message' => 'required|string',
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
