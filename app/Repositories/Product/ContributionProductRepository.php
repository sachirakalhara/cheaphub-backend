<?php

namespace App\Repositories\Product;

use App\Helpers\Helper;
use App\Http\Resources\Product\Contribution\ContributionProductCollection;
use App\Http\Resources\Product\Contribution\ContributionProductResource;
use App\Models\Product\Contribution\ContributionProduct;
use App\Models\Serial\Serial;
use App\Repositories\Product\Interface\ContributionProductRepositoryInterface;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;

class ContributionProductRepository implements ContributionProductRepositoryInterface
{
    public function all($request)
    {

        if($request->input('all', '') == 1) {
            $product_list = ContributionProduct::all();
        } else {
            $product_list = ContributionProduct::orderBy('created_at', 'desc')->paginate(10);
        }

        if (count($product_list) > 0) {
            return new ContributionProductCollection($product_list);
        } else {
            return Helper::success(Response::$statusTexts[Response::HTTP_NO_CONTENT], Response::HTTP_NO_CONTENT);
        }
    }

    public function findById($id)
    {
        $product = ContributionProduct::find($id);
        if ($product) {
            return new ContributionProductResource($product);
        } else {
            return Helper::success(Response::$statusTexts[Response::HTTP_NO_CONTENT], Response::HTTP_NO_CONTENT);
        }
    }

    public function filter($request)
    {
        $query = ContributionProduct::query();

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
            return new ContributionProductCollection($product_list);
        } else {
            return Helper::success(Response::$statusTexts[Response::HTTP_NO_CONTENT], Response::HTTP_NO_CONTENT);
        }
    }


    public function findBySlug($slug)
    {
        $product = ContributionProduct::where('slug_url',$slug)->first();
        if ($product) {
            return new ContributionProductResource($product);
        } else {
            return Helper::success(Response::$statusTexts[Response::HTTP_NO_CONTENT], Response::HTTP_NO_CONTENT);
        }
    }
    public function store($request)
    {

        $product = new ContributionProduct();
        $product->name = $request->name;
        $product->description = $request->description;
        $product->tag_id = $request->tag_id;
        $product->service_info = $request->service_info;
        $product->slug_url = $request->slug_url;
        $product->visibility = $request->visibility;

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

            activity('contribution_product')->causedBy($product)->performedOn($product)->log('created');
            return new ContributionProductResource($product);
        } else {
            return Helper::error(Response::$statusTexts[Response::HTTP_NO_CONTENT], Response::HTTP_NO_CONTENT);
        }
    }

    public function updateProductSerial($request)
    {
        $product = ContributionProduct::find($request->product_id);
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
            return new ContributionProductResource($product);
        } else {
            return Helper::error(Response::$statusTexts[Response::HTTP_NO_CONTENT], Response::HTTP_NO_CONTENT);
        }
    }
    public function update($request)
    {
        $product = ContributionProduct::find($request->id);
        // $serial = Serial::find($request->serial_id);
        $product->name = $request->name;
        $product->description = $request->description;
        $product->price = $request->price;
        $product->gateway_fee = $request->gateway_fee;
        $product->tag_id = $request->tag_id;

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
        // if ($serial->type == 'bulk') {
        //     $serial->min_count = $request->min_count ? $request->min_count : 1;
        //     $serial->max_count = $request->max_count ? $request->max_count : 1;
        //     $serial->save();
        // }

        $categories = json_decode($request->categories);
        $product->categories()->sync($categories);

        if ($product->save()) {

            activity('product')->causedBy($product)->performedOn($product)->log('updated');
            return new ContributionProductResource($product);
        } else {
            return Helper::error(Response::$statusTexts[Response::HTTP_NO_CONTENT], Response::HTTP_NO_CONTENT);
        }
    }
}
