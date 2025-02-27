<?php

namespace App\Repositories\Product\Interface;

interface ContributionProductRepositoryInterface
{
    public function all($request);
    public function filter($request);

    public function store($request);
    public function findById($id);
    public function findBySlug($slug);
   public function update($request);
//    public function updateProductSerial($request);

}
