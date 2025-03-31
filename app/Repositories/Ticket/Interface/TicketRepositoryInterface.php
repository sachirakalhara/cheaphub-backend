<?php

namespace App\Repositories\Ticket\Interface;

interface TicketRepositoryInterface
{
    public function all($request);
    public function store($request);
    public function addComment($request);
    public function statusChange($request);
    
}
