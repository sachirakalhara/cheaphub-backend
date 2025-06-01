<?php

namespace App\Repositories\Review\Interface;

interface ReviewRepositoryInterface
{
    public function store($request);
    public function update($request);
    public function deleteReview($review_id);

}
