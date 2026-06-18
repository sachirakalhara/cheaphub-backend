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
        $rules = [
            'contribution_product_id' => 'required',
            'name' => 'required|string',
            'gateway_fee' => 'required',
        ];
        if (($request->delivery_type ?? 'serial_based') === 'serial_based') {
            $rules['serial'] = 'required';
        } else {
            $rules['service_qty'] = 'required|integer|min:0';
        }
        $request->validate($rules);
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
        $rules = [
            'name' => 'required|string',
            'gateway_fee' => 'required',
        ];
        $subscription = Subscription::find($request->id);
        if (!$subscription || $subscription->delivery_type !== 'service_based') {
            $rules['serial'] = 'required';
        }
        $request->validate($rules);
        return $this->subscriptionRepository->update($request);
    }

    public function deleteBydID($deleteBydID)
    {
        return $this->subscriptionRepository->deleteBydID($deleteBydID);
    }
    
}
