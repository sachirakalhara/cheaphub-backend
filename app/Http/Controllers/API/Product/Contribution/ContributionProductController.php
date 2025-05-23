<?php

namespace App\Http\Controllers\API\Product\Contribution;

use App\Http\Controllers\Controller;
use App\Models\Product\Contribution\ContributionProduct;
use App\Repositories\Product\Interface\ContributionProductRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ContributionProductController extends Controller
{
    private $productRepository;

    public function __construct(ContributionProductRepositoryInterface $productRepository)
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
    
    public function getAllWithVisibility(Request $request)
    {
        return $this->productRepository->getAllWithVisibility($request);
    }

    public function filter(Request $request)
    {
        return $this->productRepository->filter($request);
    }



    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    public function findById($id)
    {
        return $this->productRepository->findById($id);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {

        $request->validate([
            'name' => 'required|string|unique:contribution_products',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            'tag_id' => 'required',
        ]);
        return $this->productRepository->store($request);
    }

    /**
     * Display the specified resource.
     */
    public function show(ContributionProduct $product)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ContributionProduct $product)
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
           'name' => 'required|unique:contribution_products,name,' . $request->id,
           'tag_id' => 'required',
           'image' => 'image|mimes:jpeg,png,jpg,gif|max:2048'
       ]);
       return $this->productRepository->update($request);
    }

    public function updateProductSerial(Request $request)
    {
//        $request->validate([
//            'product_id' => 'required',
//            'serial' => 'required',
//        ]);
//        return $this->productRepository->updateProductSerial($request);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function delete($id)
    {
        return $this->productRepository->delete($id);
    }
}
