<?php

namespace App\Repositories\Product;

use App\Helpers\Helper;
use App\Http\Resources\Product\Contribution\ContributionProductCollection;
use App\Http\Resources\Product\Contribution\ContributionProductResource;
use App\Http\Resources\Product\Contribution\PublicContributionProductCollection;
use App\Models\Payment\Order;
use App\Models\Product\Contribution\ContributionProduct;
use App\Models\User\User;
use App\Models\User\UserLevel;
use App\Repositories\Product\Interface\ContributionProductRepositoryInterface;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ContributionProductRepository implements ContributionProductRepositoryInterface
{
    // public function all($request)
    // {

    //     if($request->input('all', '') == 1) {
    //         $product_list = ContributionProduct::where('visibility','open')->get();
    //     } else {
    //         $product_list = ContributionProduct::where('visibility','open')->orderBy('created_at', 'desc')->paginate(10);
    //     }

    //     if (count($product_list) > 0) {
    //         return new ContributionProductCollection($product_list);
    //     } else {
    //         return Helper::success(Response::$statusTexts[Response::HTTP_NO_CONTENT], Response::HTTP_NO_CONTENT);
    //     }
    // }

    public function all($request)
    {
        $user = User::find(Auth::user()->id);
        $query = ContributionProduct::query();

        if($user && $user->userLevel->scope != "super_admin"){
            $query->where('visibility', 'open');
        }  
    
        if ($request->input('all', '') == 1) {
            $product_list = $query->get();
        } else {
            $product_list = $query->orderBy('created_at', 'desc')->paginate(10);
        }
    
        if ($product_list->count() > 0) {
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
            $product_list = Helper::paginate($query->get());
        }
        if ($product_list->isNotEmpty()) {
            return new PublicContributionProductCollection($product_list);
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
        if (!$product) {
            return Helper::error('Product not found', Response::HTTP_NOT_FOUND);
        }

        $product->name = $request->name;
        $product->description = $request->description;
        $product->tag_id = $request->tag_id;
        $product->service_info = $request->service_info;
        $product->visibility = $request->visibility;

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $disk = Storage::disk('s3');
            if ($product->image && $disk->exists($product->image)) {
                $disk->delete($product->image);
            }

            $filename = 'product/image/' . uniqid() . '.' . $image->getClientOriginalExtension();
            $disk->put($filename, file_get_contents($image));
            $product->image = $filename;
        }

        $categories = json_decode($request->categories, true);
        if (is_array($categories)) {
            $product->categories()->sync($categories);
        }

        if ($product->save()) {
            activity('product')->causedBy($product)->performedOn($product)->log('updated');
            return new ContributionProductResource($product);
        } else {
            return Helper::error('Failed to update product', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function delete($id)
    {
        $contributionProduct = ContributionProduct::with('subscriptions.packages')->find($id);
    
        if (!$contributionProduct) {
            return Helper::error('Product not found', Response::HTTP_NOT_FOUND);
        }
    
        // Check if any related order is paid
        $packageIds = $contributionProduct->subscriptions
            ->flatMap->packages
            ->pluck('id')
            ->unique();
    
        $hasPaidOrder = Order::where('payment_status', 'paid')
            ->whereHas('items', function ($query) use ($packageIds) {
                $query->whereIn('package_id', $packageIds);
            })->exists();
    
        if ($hasPaidOrder) {
            return Helper::error('Cannot delete product with active paid orders', Response::HTTP_CONFLICT);
        }
    
        DB::beginTransaction();
    
        try {
            // Delete image from storage
            $disk = Storage::disk('s3');
            if ($contributionProduct->image && $disk->exists($contributionProduct->image)) {
                $disk->delete($contributionProduct->image);
            }
    
            // Detach categories
            $contributionProduct->categories()->detach();
    
            // Delete subscriptions and packages
            foreach ($contributionProduct->subscriptions as $subscription) {
                $subscription->packages()->delete();
                $subscription->delete();
            }
    
            // Delete the product
            $contributionProduct->delete();
    
            DB::commit();
    
            return Helper::success('Product deleted successfully', Response::HTTP_OK);
    
        } catch (\Exception $e) {
            DB::rollBack();
            return Helper::error('Failed to delete product. ' . $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    
    
}
