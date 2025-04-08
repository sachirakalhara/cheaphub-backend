<?php

namespace App\Repositories\Product\Interface;

interface ProductReplacementRepositoryInterface
{
    public function getAvalableCount($package_id);
    public function store($request);
}
