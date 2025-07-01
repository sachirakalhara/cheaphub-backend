<?php

namespace App\Repositories\Payment\Interface;

interface OrderNoteRepositoryInterface
{
    public function addNote($request);
    public function getNotesByOrderID($order_id);

}
