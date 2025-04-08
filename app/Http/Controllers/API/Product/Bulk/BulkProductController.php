<?php

namespace App\Http\Controllers\API\Product\Bulk;

use App\Http\Controllers\Controller;
use Illuminate\Http\Response;
use App\Models\Product\Bulk\BulkProduct;
use App\Repositories\Product\Interface\BulkProductRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Helpers\Helper;

class BulkProductController extends Controller
{
    private $bulkProductRepository;

    public function __construct(BulkProductRepositoryInterface $bulkProductRepository)
    {
        $this->bulkProductRepository = $bulkProductRepository;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        return $this->bulkProductRepository->all($request);
    }

    public function findById($id)
    {
        return $this->bulkProductRepository->findById($id);
    }

    public function filter(Request $request)
    {
        return $this->bulkProductRepository->filter($request);
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
            'name' => 'required|string|unique:bulk_products',
            'price' => 'required',
            'gateway_fee' => 'required',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            'serial' => 'required',
            'tag_id' => 'required',
            'payment_method' => 'required',
            'minimum_quantity' => 'required',

        ]);

        $serial_count = count(array_filter(explode("\n", $request->serial), 'trim'));
        if ($serial_count < $request->minimum_quantity) {
            return Helper::error('Serial count is less than the minimum quantity required', Response::HTTP_BAD_REQUEST);
        }

        return $this->bulkProductRepository->store($request);
    }

    /**
     * Display the specified resource.
     */
    public function show(BulkProduct $product)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(BulkProduct $product)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request)
    {
        $request->validate([
            'name' => 'required|string|unique:bulk_products,name,' . $request->id,
            'id' => 'required',
            'price' => 'required',
            'gateway_fee' => 'required',
            'image' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
            'serial' => 'required',
            'tag_id' => 'required',
            'payment_method' => 'required',
            'minimum_quantity' => 'required',

        ]);
        
        $serial_count = count(array_filter(explode("\n", $request->serial), 'trim'));
        if ($serial_count < $request->minimum_quantity) {
            return Helper::error('Serial count is less than the minimum quantity required', Response::HTTP_BAD_REQUEST);
        }
        return $this->bulkProductRepository->update($request);
    }


    public function delete($bulk_product_id)
    {
        return $this->bulkProductRepository->delete($bulk_product_id);
    }
   
}
