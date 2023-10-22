<?php


namespace App\Repositories\User;


use App\Models\User\User;
use App\Models\User\UserLevel;
use App\Repositories\User\Interface\UserLevelRepositoryInterface;
use Spatie\Permission\Models\Role;

class UserLevelRepository implements UserLevelRepositoryInterface
{
    public function findByScope($scope){
        return UserLevel::where('scope',$scope)->get();
    }
}
