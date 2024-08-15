<?php

namespace App\Repositories\Category\Interface;

interface CategoryRepositoryInterface
{
    public function all($request);
    public function filter($request);

    public function store($request);
    public function update($request);
    public function delete($category_id);

}
