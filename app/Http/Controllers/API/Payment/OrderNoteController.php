<?php

namespace App\Http\Controllers\API\Payment;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class OrderNoteController extends Controller
{
   
    private $noteRepository;

    public function __construct(OrderNoteRepositoryInterface $noteRepository)
    {
        $this->noteRepository = $noteRepository;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
    }

    public function addNote(Request $request)
    {
        return $this->orderRepository->addNote($request);
    }

    public function getNotesByOrderID($order_id)
    {
        return $this->noteRepository->getNotesByOrderID($order_id);
    }
}
