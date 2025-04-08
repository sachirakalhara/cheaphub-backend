<?php

namespace App\Http\Controllers\API\User;
use App\Http\Controllers\Controller;
use App\Repositories\User\Interface\UserRepositoryInterface;
use Illuminate\Http\Request;

class UserController extends Controller
{

    private $userRepository;

    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        return $this->userRepository->all($request);
    }

    /**
     * Display a listing of the resource.
     */
    public function update(Request $request)
    {
        $request->validate([
            'id' => 'required',
            'fname' => 'required|string',
            'lname' => 'required|string',
            'contact_no' => 'required|unique:users,contact_no,' . $request->id,
            'profile_photo' => 'image|mimes:jpeg,png,jpg,gif|max:2048'

        ]);

        return $this->userRepository->update($request);
    }

    public function getUserByID($id)
    {
        return $this->userRepository->getUserByID($id);
    }

    public function getUserInfoByID($id)
    {
        return $this->userRepository->getUserInfoByID($id);
    }

    
    


}
