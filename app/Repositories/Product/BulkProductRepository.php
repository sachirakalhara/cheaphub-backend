<?php

namespace App\Repositories\Product;

use App\Helpers\Helper;
use App\Http\Resources\Employer\EmployerCollection;
use App\Http\Resources\Product\Bulk\BulkProductCollection;
use App\Http\Resources\Product\Bulk\BulkProductResource;
use App\Models\Employer\Employer;
use App\Models\Product\Bulk\BulkProduct;
use App\Models\Serial\Serial;
use App\Repositories\Product\Interface\BulkProductRepositoryInterface;
use Illuminate\Http\Response;

class BulkProductRepository implements BulkProductRepositoryInterface
{
    public function all($request)
    {

        if($request->input('all', '') == 1) {
            $product_list = BulkProduct::all();
        } else {
            $product_list = BulkProduct::orderBy('created_at', 'desc')->paginate(10);
        }

        if (count($product_list) > 0) {
            return new BulkProductCollection($product_list);
        } else {
            return Helper::success(Response::$statusTexts[Response::HTTP_NO_CONTENT], Response::HTTP_NO_CONTENT);
        }
    }

    public function filter($request)
    {
        $query = BulkProduct::query();

        if ($request->filled('products_name')) {
            $query->where('name', 'like', '%' . $request->products_name . '%');
        }

        if ($request->filled('product_category_id')) {
            $query->whereHas('categories', function ($q) use ($request) {
                $q->where('categories.id', $request->product_category_id); // Specify the table name
            });
        }


        if ($request->input('all', false)) {
            $product_list = $query->get();
        } else {
            $product_list = Helper::paginate($query->get());
        }

        if ($product_list->isNotEmpty()) {
            return new BulkProductCollection($product_list);
        } else {
            return Helper::success(Response::$statusTexts[Response::HTTP_NO_CONTENT], Response::HTTP_NO_CONTENT);
        }
    }

    public function findById($id)
    {
        $product = BulkProduct::find($id);
        if ($product) {
            return new BulkProductResource($product);
        } else {
            return Helper::success(Response::$statusTexts[Response::HTTP_NO_CONTENT], Response::HTTP_NO_CONTENT);
        }
    }

    public function findBySlug($slug)
    {
        $product = BulkProduct::where('slug_url',$slug)->first();
        if ($product) {
            return new BulkProductResource($product);
        } else {
            return Helper::success(Response::$statusTexts[Response::HTTP_NO_CONTENT], Response::HTTP_NO_CONTENT);
        }
    }


    public function store($request)
    {
        $product = new BulkProduct();
        $product->name = $request->name;
        $product->description = $request->description;
        $product->price = $request->price;
        $product->gateway_fee = $request->gateway_fee;
        $product->tag_id = $request->tag_id;
        $product->payment_method = $request->payment_method;
        $product->minimum_quantity = $request->minimum_quantity;
        $product->maximum_quantity = $request->maximum_quantity;
        $product->service_info = $request->service_info;
        $product->slug_url = $request->slug_url;
        $product->visibility = $request->visibility;
        $product->serial = $request->serial;
        $product->serial_count = count(array_filter(explode("\n", $request->serial), 'trim'));

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = time() . '.' . $image->getClientOriginalExtension();
            $image->move(public_path('uploads'), $imageName);
            $product->image = $imageName;
        }

        if ($product->save()) {
            $categories = json_decode($request->categories);
            $product->categories()->attach($categories);

            activity('bulk_product')->causedBy($product)->performedOn($product)->log('created');
            return new BulkProductResource($product);
        } else {
            return Helper::error(Response::$statusTexts[Response::HTTP_NO_CONTENT], Response::HTTP_NO_CONTENT);
        }
    }
    public function update($request)
    {
        $product = BulkProduct::find($request->id);
        $product->name = $request->name;
        $product->description = $request->description;
        $product->price = $request->price;
        $product->gateway_fee = $request->gateway_fee;
        $product->tag_id = $request->tag_id;
        $product->payment_method = $request->payment_method;
        $product->minimum_quantity = $request->minimum_quantity;
        $product->maximum_quantity = $request->maximum_quantity;
        $product->service_info = $request->service_info;
        $product->slug_url = $request->slug_url;
        $product->visibility = $request->visibility;
        $product->serial = $request->serial;
        $product->serial_count = count(array_filter(explode("\n", $request->serial), 'trim'));

        if ($request->hasFile('image')) {
            if ($product->image) {
                $oldImagePath = public_path('uploads/' . $product->image);
                if (file_exists($oldImagePath)) {
                    unlink($oldImagePath);
                }
            }

            $image = $request->file('image');
            $imageName = time() . '.' . $image->getClientOriginalExtension();
            $image->move(public_path('uploads'), $imageName);
            $product->image = $imageName;
        }
//        $categories = json_decode($request->categories);
        $product->categories()->sync($request->categories);
        if ($product->save()) {
            activity('bulk_product')->causedBy($product)->performedOn($product)->log('update');
            return new BulkProductResource($product);
        } else {
            return Helper::error(Response::$statusTexts[Response::HTTP_NO_CONTENT], Response::HTTP_NO_CONTENT);
        }
    }
}
