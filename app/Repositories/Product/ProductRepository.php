<?php

namespace App\Repositories\Product;

use App\Helpers\Helper;
use App\Http\Resources\Product\ProductCollection;
use App\Http\Resources\Product\ProductResource;
use App\Http\Resources\User\UserCollection;
use App\Http\Resources\User\UserResource;
use App\Models\Product\Product;
use App\Models\User\User;
use App\Repositories\Product\Interface\ProductRepositoryInterface;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;

class ProductRepository implements ProductRepositoryInterface
{
    public function all($request)
    {

        if($request->input('all', '') == 1) {
            $product_list = Product::all();
        } else {
            $product_list = Product::orderBy('created_at', 'desc')->paginate(10);
        }

        if (count($product_list) > 0) {
            return new ProductCollection($product_list);
        } else {
            return Helper::success(Response::$statusTexts[Response::HTTP_NO_CONTENT], Response::HTTP_NO_CONTENT);
        }
    }

    public function store($request)
    {
        $product = new Product();
        $product->subscription_id = $request->subscription_id;
        $product->name = $request->name;
        $product->description = $request->description;
        $product->price = $request->price;
        $product->gateway_fee = $request->gateway_fee;

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = time() . '.' . $image->getClientOriginalExtension();
            $image->move(public_path('uploads'), $imageName);
            $product->image = $imageName;
        }

        if ($product->save()) {
            $categories = json_decode($request->categories);
            $product->categories()->attach($categories);

            activity('product')->causedBy($product)->performedOn($product)->log('created');
            return new ProductResource($product);
        } else {
            return Helper::error(Response::$statusTexts[Response::HTTP_NO_CONTENT], Response::HTTP_NO_CONTENT);
        }
    }
    public function update($request)
    {
        $product = Product::find($request->id);
        $product->subscription_id = $request->subscription_id;
        $product->name = $request->name;
        $product->description = $request->description;
        $product->price = $request->price;
        $product->gateway_fee = $request->gateway_fee;

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = time() . '.' . $image->getClientOriginalExtension();
            $image->move(public_path('uploads'), $imageName);
            $product->image = $imageName;
        }
        //            $categories = json_decode($request->categories);
        $product->categories()->sync($request->categories);

        if ($product->save()) {

            activity('product')->causedBy($product)->performedOn($product)->log('updated');
            return new ProductResource($product);
        } else {
            return Helper::error(Response::$statusTexts[Response::HTTP_NO_CONTENT], Response::HTTP_NO_CONTENT);
        }
    }
}
