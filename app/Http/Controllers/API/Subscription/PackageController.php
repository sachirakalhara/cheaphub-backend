<?php

namespace App\Http\Controllers\API\Subscription;

use App\Http\Controllers\Controller;
use App\Models\Subscription\Package;
use App\Repositories\Package\Interface\PackageRepositoryInterface;
use Illuminate\Http\Request;

class PackageController extends Controller
{
    private $packageRepository;

    public function __construct(PackageRepositoryInterface $packageRepository)
    {
        $this->packageRepository = $packageRepository;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        return $this->packageRepository->all($request);
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
            'subscription_id' => 'required',
            'name' => 'required|string|unique:packages',
            'price' => 'required',
            'payment_method' => 'required',
            'replace_count' => 'required',
            'expiry_duration' => 'required'
        ]);
        return $this->packageRepository->store($request);
    }

    
    public function delete($package_id)
    {
        return $this->packageRepository->delete($package_id);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Package $package)
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
            'name' => 'required|string|unique:packages,name,' . $request->id,
            'price' => 'required',
            'payment_method' => 'required',
            'replace_count' => 'required',
            'expiry_duration' => 'required'
        ]);
        return $this->packageRepository->update($request);
    }


    
    public function replaceCount($package_id)
    {
        return $this->packageRepository->replaceCount($package_id);
    }
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Package $package)
    {
        //
    }
}
