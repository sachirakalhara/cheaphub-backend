<?php

namespace App\Repositories\User\Interface;

interface UserLevelRepositoryInterface
{
    public function findByScope($scope);
}
