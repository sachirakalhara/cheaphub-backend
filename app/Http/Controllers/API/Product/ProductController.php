<?php

namespace App\Http\Controllers\API\Product;

use App\Http\Controllers\Controller;
use App\Models\Product\Product;
use App\Repositories\Product\Interface\ProductRepositoryInterface;
use App\Repositories\User\Interface\UserRepositoryInterface;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    private $productRepository;

    public function __construct(ProductRepositoryInterface $productRepository)
    {
        $this->productRepository = $productRepository;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        return $this->productRepository->all($request);
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
            'name' => 'required|string|unique:products',
            'price' => 'required',
            'gateway_fee' => 'required',
            'image' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
            'serial' => 'required',
            'tag_id' => 'required'
            
        ]);
        return $this->productRepository->store($request);
    }

    /**
     * Display the specified resource.
     */
    public function show(Product $product)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Product $product)
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
            'name' => 'required|unique:products,name,' . $request->id,
            'tag_id' => 'required',
            'price' => 'required',
            'gateway_fee' => 'required',
            'image' => 'image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);
        return $this->productRepository->update($request);
    }

    public function updateProductSerial(Request $request)
    {
        $request->validate([
            'product_id' => 'required',
            'serial' => 'required',
        ]);
        return $this->productRepository->updateProductSerial($request);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product)
    {
        //
    }
}
