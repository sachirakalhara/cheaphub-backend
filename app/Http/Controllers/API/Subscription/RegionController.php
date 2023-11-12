<?php

namespace App\Http\Controllers\API\Subscription;

use App\Http\Controllers\Controller;
use App\Models\Subscription\Region;
use App\Repositories\Subscription\Interface\MonthRepositoryInterface;
use App\Repositories\Subscription\Interface\RegionRepositoryInterface;
use Illuminate\Http\Request;

class RegionController extends Controller
{
    public function __construct(RegionRepositoryInterface $regionRepository)
    {
        $this->regionRepository = $regionRepository;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        return $this->regionRepository->all($request);
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
            'region_name' => 'required|string|unique:regions',
            'months.*' => 'required'
        ]);

        return $this->regionRepository->store($request);
    }

    /**
     * Display the specified resource.
     */
    public function show(Region $region)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Region $region)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request)
    {
        $request->validate([
            'id' => 'required',
            'region_name' => 'required|unique:regions,region_name,' . $request->id,

            'months.*' => 'required'
        ]);
        return $this->regionRepository->update($request);

    }


    /**
     * Remove the specified resource from storage.
     */
    public function delete($id)
    {
        return $this->regionRepository->delete($id);
    }
}
