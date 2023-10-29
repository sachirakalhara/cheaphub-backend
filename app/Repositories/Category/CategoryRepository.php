<?php

namespace App\Repositories\Category;

use App\Helpers\Helper;
use App\Http\Resources\Category\CategoryCollection;
use App\Http\Resources\Category\CategoryResource;
use App\Http\Resources\User\UserCollection;
use App\Http\Resources\User\UserResource;
use App\Models\Category\Category;
use App\Models\Product\Product;
use App\Models\Product\ProductCategory;
use App\Models\User\User;
use App\Repositories\Category\Interface\CategoryRepositoryInterface;
use Illuminate\Http\Response;

class CategoryRepository implements CategoryRepositoryInterface
{
    public function all($request)
    {

        if($request->input('all', '') == 1) {
            $category_list = Category::all();
        } else {
            $category_list = Category::orderBy('created_at', 'desc')->paginate(10);
        }

        if (count($category_list) > 0) {
            return new CategoryCollection($category_list);
        } else {
            return Helper::success(Response::$statusTexts[Response::HTTP_NO_CONTENT], Response::HTTP_NO_CONTENT);
        }
    }

    public function store($request)
    {
        $category = new Category();
        $category->name = $request->name;
        $category->description = $request->description;
        if ($category->save()) {
            activity('category')->causedBy($category)->performedOn($category)->log('created');
            return new CategoryResource($category);
        } else {
            return Helper::error(Response::$statusTexts[Response::HTTP_NO_CONTENT], Response::HTTP_NO_CONTENT);
        }
    }
    public function update($request)
    {
        $category = Category::find($request->id);
        $category->name = $request->name;
        $category->description = $request->description;
        if ($category->save()) {
            activity('category')->causedBy($category)->performedOn($category)->log('updated');
            return new CategoryResource($category);
        } else {
            return Helper::error(Response::$statusTexts[Response::HTTP_NO_CONTENT], Response::HTTP_NO_CONTENT);
        }
    }

    public function delete($category_id)
    {
        $category = Category::find($category_id);
        $productCategory = ProductCategory::where('category_id',$category_id)->first();
        if($productCategory){
            return Helper::error(Response::$statusTexts[Response::HTTP_IM_USED], Response::HTTP_IM_USED);
        }
        if ($category->delete()) {
            activity('category')->causedBy($category)->performedOn($category)->log('updated');
            return Helper::success(Response::$statusTexts[Response::HTTP_OK], Response::HTTP_OK);
        } else {
            return Helper::error(Response::$statusTexts[Response::HTTP_NO_CONTENT], Response::HTTP_NO_CONTENT);
        }
    }

}
