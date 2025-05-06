<?php

namespace App\Repositories\Product;

use App\Helpers\Helper;
use App\Http\Resources\Product\Bulk\BulkProductCollection;
use App\Http\Resources\Product\Bulk\BulkProductResource;
use App\Models\Product\Bulk\BulkProduct;
use Illuminate\Support\Facades\Storage;
use App\Repositories\Product\Interface\BulkProductRepositoryInterface;
use Illuminate\Http\Response;

class BulkProductRepository implements BulkProductRepositoryInterface
{
    public function all($request)
    {

        if($request->input('all', '') == 1) {
            $product_list = BulkProduct::where('visibility','open')->get();
        } else {
            $product_list = BulkProduct::where('visibility','open')->orderBy('created_at', 'desc')->paginate(10);
        }

        if (count($product_list) > 0) {
            return new BulkProductCollection($product_list);
        } else {
            return Helper::success(Response::$statusTexts[Response::HTTP_NO_CONTENT], Response::HTTP_NO_CONTENT);
        }
    }

    public function getAllWithVisibility($request)
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

        if (count($product_list) > 0) {
            return new BulkProductCollection($product_list);
        } else {
            return Helper::success(Response::$statusTexts[Response::HTTP_NO_CONTENT], Response::HTTP_NO_CONTENT);
        }
    }

    public function filter($request)
    {
        $query = BulkProduct::query();
        $query->where('visibility','open');
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
            $product_list = Helper::paginate($query->get(),12);
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
        $product->visibility = $request->visibility;
        $product->serial = $request->serial;
        $product->serial_count = count(array_filter(explode("\n", $request->serial), 'trim'));


        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $disk = Storage::disk('s3');
            if ($product->image && $disk->exists($product->image)) {
                $disk->delete($product->image);
            }

            $image = $request->file('image');
            $filename = 'product/image/' . uniqid() . '.' . $image->getClientOriginalExtension();
            $disk->put($filename, file_get_contents($image));
            $product->image = $filename;
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
        $product->visibility = $request->visibility;
        $product->serial = $request->serial;
        $product->serial_count = count(array_filter(explode("\n", $request->serial), 'trim'));


        if ($request->hasFile('image')) {
            $image = $product->file('image');
            $disk = Storage::disk('s3');
            if ($product->image && $disk->exists($product->image)) {
                $disk->delete($product->image);
            }

            $image = $request->file('image');
            $filename = 'product/image/' . uniqid() . '.' . $image->getClientOriginalExtension();
            $disk->put($filename, file_get_contents($image));
            $product->image = $filename;
        }

        $product->categories()->sync($request->categories);
        if ($product->save()) {
            activity('bulk_product')->causedBy($product)->performedOn($product)->log('update');
            return new BulkProductResource($product);
        } else {
            return Helper::error(Response::$statusTexts[Response::HTTP_NO_CONTENT], Response::HTTP_NO_CONTENT);
        }
    }

    public function delete($bulk_product_id){
        $product = BulkProduct::find($bulk_product_id);
        if ($product) {
            $product->categories()->detach();
            $product->delete();
            return Helper::success(Response::$statusTexts[Response::HTTP_OK], Response::HTTP_OK);
        } else {
            return Helper::error(Response::$statusTexts[Response::HTTP_NO_CONTENT], Response::HTTP_NO_CONTENT);
        }
    }

}
