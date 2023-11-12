<?php

namespace App\Repositories\Product;

use App\Helpers\Helper;
use App\Http\Resources\Product\ProductCollection;
use App\Http\Resources\Product\ProductResource;
use App\Http\Resources\User\UserCollection;
use App\Http\Resources\User\UserResource;
use App\Models\Product\Product;
use App\Models\Serial\Serial;
use App\Models\Subscription\Subscription;
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
        $serial = new Serial();
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

        if ($request->hasFile('serial')) {
            $serialContents = file_get_contents($request->file('serial')->getRealPath());

            $containsPipe = strpos($serialContents, '|') !== false;
            $serial->name = $serialContents;
            $serial->min_count = 1;
            $serial->max_count = 1;

            if ($containsPipe) {
                $serial->type = 'normal';
            } else {
                $serial->type = 'bulk';
            }
        }

        if ($product->save()) {
            $serial->product_id = $product->id;
            $serial->save();
            $categories = json_decode($request->categories);
            $product->categories()->attach($categories);

            activity('product')->causedBy($product)->performedOn($product)->log('created');
            return new ProductResource($product);
        } else {
            return Helper::error(Response::$statusTexts[Response::HTTP_NO_CONTENT], Response::HTTP_NO_CONTENT);
        }
    }

    public function updateProductSerial($request)
    {
        $product = Product::find($request->product_id);
        $productSerial = $product->serials[0];
        if ( $productSerial->type == 'normal') {
            $serial = Serial::find($productSerial->id);
        }else{
            $serial = new Serial();
        }

        if ($request->hasFile('serial')) {
            $serialContents = file_get_contents($request->file('serial')->getRealPath());

            $containsPipe = strpos($serialContents, '|') !== false;
            $serial->name = $serialContents;

            if ($containsPipe && $productSerial->type == 'normal') {
                $serial->type = 'normal';
                $serial->min_count = 1;
                $serial->max_count = 1;
            } else if($containsPipe == false && $productSerial->type == 'bulk'){
                $serial->type = 'bulk';
                $serial->min_count = $productSerial->min_count;
                $serial->max_count = $productSerial->max_count;
            }else{
                return Helper::error('Serial type is different', 205);
            }
        }

        $serial->product_id = $product->id;

        if ($serial->save()) {
            activity('serial')->causedBy($serial)->performedOn($serial)->log('updated');
            return new ProductResource($product);
        } else {
            return Helper::error(Response::$statusTexts[Response::HTTP_NO_CONTENT], Response::HTTP_NO_CONTENT);
        }
    }
    public function update($request)
    {
        $product = Product::find($request->id);
        $serial = Serial::find($request->serial_id);
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

        if ($serial->type == 'bulk') {
            $serial->min_count = $request->min_count ? $request->min_count : 1;
            $serial->max_count = $request->max_count ? $request->max_count : 1;
            $serial->save();
        }

        $categories = json_decode($request->categories);
        $product->categories()->sync($categories);

        if ($product->save()) {

            activity('product')->causedBy($product)->performedOn($product)->log('updated');
            return new ProductResource($product);
        } else {
            return Helper::error(Response::$statusTexts[Response::HTTP_NO_CONTENT], Response::HTTP_NO_CONTENT);
        }
    }
}
