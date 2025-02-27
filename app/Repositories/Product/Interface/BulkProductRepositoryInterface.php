<?php

namespace App\Repositories\Product\Interface;

interface BulkProductRepositoryInterface
{
    public function all($request);
    public function findById($id);
    public function findBySlug($slug);
    public function filter($request);

    public function store($request);
    public function update($request);
    public function delete($bulk_product_id);

//    public function updateProductSerial($request);

}
