<?php

namespace App\Http\Controllers\API\Category;

use App\Http\Controllers\Controller;
use App\Models\Category\Category;
use App\Repositories\Category\Interface\CategoryRepositoryInterface;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    private $categoryRepository;

    public function __construct(CategoryRepositoryInterface $categoryRepository)
    {
        $this->categoryRepository = $categoryRepository;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        return $this->categoryRepository->all($request);
    }

    public function filter(Request $request)
    {
        return $this->categoryRepository->filter($request);
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
            'name' => 'required|string|unique:categories'
        ]);

        return $this->categoryRepository->store($request);
    }

    /**
     * Display the specified resource.
     */
    public function show(Category $category)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Category $category)
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
            'name' => 'required|unique:categories,name,' . $request->id,

        ]);

        return $this->categoryRepository->update($request);
    }
    
    public function trendingCategory()
    {

        return $this->categoryRepository->trendingCategory();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function delete($category_id)
    {
        return $this->categoryRepository->delete($category_id);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Category $category)
    {
        //
    }
}
