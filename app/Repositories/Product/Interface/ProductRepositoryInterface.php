<?php

namespace App\Repositories\Product\Interface;

interface ProductRepositoryInterface
{
    public function all($request);
    public function store($request);
    public function update($request);
}
