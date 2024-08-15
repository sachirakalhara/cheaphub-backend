<?php

namespace App\Repositories\Tag\Interface;

interface TagRepositoryInterface
{
    public function all($request);
    public function filter($request);

    public function store($request);
    public function update($request);
    public function findById($tag_id);
    public function delete($tag_id);

}
