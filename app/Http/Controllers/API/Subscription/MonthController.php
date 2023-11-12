<?php

namespace App\Http\Controllers\API\Subscription;

use App\Http\Controllers\Controller;
use App\Models\Subscription\Month;
use App\Repositories\Category\Interface\CategoryRepositoryInterface;
use App\Repositories\Subscription\Interface\MonthRepositoryInterface;
use Illuminate\Http\Request;

class MonthController extends Controller
{
    private $monthRepository;

    public function __construct(MonthRepositoryInterface $monthRepository)
    {
        $this->monthRepository = $monthRepository;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        return $this->monthRepository->all($request);
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
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Month $month)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Month $month)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Month $month)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Month $month)
    {
        //
    }
}
