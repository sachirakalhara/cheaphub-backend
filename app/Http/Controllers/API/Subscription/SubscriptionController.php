<?php

namespace App\Http\Controllers\API\Subscription;

use App\Http\Controllers\Controller;
use App\Models\Subscription\Subscription;
use App\Repositories\Subscription\Interface\SubscriptionRepositoryInterface;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    private $subscriptionRepository;

    public function __construct(SubscriptionRepositoryInterface $subscriptionRepository)
    {
        $this->subscriptionRepository = $subscriptionRepository;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        return $this->subscriptionRepository->all($request);
    }
    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'contribution_product_id' => 'required',
            'name' => 'required|string',
            'serial' => 'required',
            'gateway_fee' => 'required'
        ]);
        return $this->subscriptionRepository->store($request);
    }

    /**
     * Display the specified resource.
     */
    public function show(Subscription $subscription)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Subscription $subscription)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'serial' => 'required',
            'gateway_fee' => 'required'
        ]);
        return $this->subscriptionRepository->update($request);
    }

    public function deleteBydID($deleteBydID)
    {
        return $this->subscriptionRepository->deleteBydID($deleteBydID);
    }
    
}
