<?php

namespace App\Repositories\Subscription\Interface;

interface RegionRepositoryInterface
{
    public function all($request);
    public function store($request);
    public function update($request);
    public function delete($region_id);


}
