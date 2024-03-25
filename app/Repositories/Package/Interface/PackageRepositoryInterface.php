<?php

namespace App\Repositories\Package\Interface;

interface PackageRepositoryInterface
{
    public function all($request);
    public function store($request);
    public function update($request);
    public function delete($category_id);

}
