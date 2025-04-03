<?php

namespace App\Repositories\Package;

use App\Helpers\Helper;
use App\Http\Resources\Subscription\PackageCollection;
use App\Http\Resources\Subscription\PackageResource;
use App\Models\Payment\Order;
use App\Models\Payment\OrderItems;
use App\Models\Product\Contribution\ContributionProduct;
use App\Models\Product\Contribution\ContributionProductCategory;
use App\Models\Subscription\Package;
use App\Models\Subscription\Subscription;
use App\Repositories\Package\Interface\PackageRepositoryInterface;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class PackageRepository implements PackageRepositoryInterface
{
    public function all($request)
    {

        if($request->input('all', '') == 1) {
            $category_list = Package::where('subscription_id',$request->subscription_id)->get();
        } else {
            $category_list = Package::where('subscription_id',$request->subscription_id)->orderBy('created_at', 'desc')->paginate(10);
        }

        if (count($category_list) > 0) {
            return new PackageCollection($category_list);
        } else {
            return Helper::success(Response::$statusTexts[Response::HTTP_NO_CONTENT], Response::HTTP_NO_CONTENT);
        }
    }


    
    public function replaceCount($package_id)
    {

        $user_id = Auth::user()->id;
        $orderItem = OrderItems::where('package_id',$package_id)->where('user_id', $user_id)->first();

        $category_list = Package::where('subscription_id',$request->subscription_id)->get();


        if (count($category_list) > 0) {
            return new PackageCollection($category_list);
        } else {
            return Helper::success(Response::$statusTexts[Response::HTTP_NO_CONTENT], Response::HTTP_NO_CONTENT);
        }
    }

    public function store($request)
    {
        $subscription = Subscription::find($request->subscription_id);
        if($subscription == null){
            return Helper::error("Subscription is not selected", 404);
        }
        $package = new Package();
        $package->name = $request->name;
        $package->subscription_id = $request->subscription_id;
        $package->price = $request->price;
        $package->payment_method = $request->payment_method;
        $package->expiry_duration = $request->expiry_duration;
        $package->replace_count = $request->replace_count;

        if ($package->save()) {
            activity('package')->causedBy($package)->performedOn($package)->log('created');
            return new PackageResource($package);
        } else {
            return Helper::error(Response::$statusTexts[Response::HTTP_NO_CONTENT], Response::HTTP_NO_CONTENT);
        }
    }
    
    public function update($request)
    {
        $package = Package::find($request->id);
        if (!$package) {
            return Helper::error("Package not found", Response::HTTP_NOT_FOUND);
        }

        $package->fill($request->only([
            'name', 
            'price', 
            'payment_method', 
            'expiry_duration', 
            'replace_count'
        ]));

        if ($package->save()) {
            activity('package')->causedBy($package)->performedOn($package)->log('updated');
            return new PackageResource($package);
        } else {
            return Helper::error("Failed to update package", Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function delete($package_id)
    {
        $orderItem = OrderItems::where('package_id', $package_id)->first();
        if ($orderItem) {
            $is_order = Order::where('payment_status', 'paid')->find($orderItem->order_id);
            if ($is_order) {
                return Helper::error("Cannot delete package with active orders", Response::HTTP_CONFLICT);
            }
        }

        $package = Package::find($package_id);
        if (!$package) {
            return Helper::error("Package not found", Response::HTTP_NOT_FOUND);
        }

        if ($package->delete()) {
            activity('package')->causedBy($package)->performedOn($package)->log('deleted');
            return Helper::success(Response::$statusTexts[Response::HTTP_OK], Response::HTTP_OK);
        } else {
            return Helper::error("Failed to delete package", Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

}
