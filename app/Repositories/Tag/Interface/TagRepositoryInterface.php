<?php

namespace App\Repositories\Tag\Interface;

interface TagRepositoryInterface
{
    public function all($request);
    public function store($request);
    public function update($request);
    public function delete($tag_id);

}
