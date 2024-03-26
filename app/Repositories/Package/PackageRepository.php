<?php

namespace App\Repositories\Package;

use App\Helpers\Helper;
use App\Http\Resources\Subscription\PackageCollection;
use App\Http\Resources\Subscription\PackageResource;
use App\Models\Subscription\Package;
use App\Models\Subscription\Subscription;
use App\Repositories\Package\Interface\PackageRepositoryInterface;
use Illuminate\Http\Response;

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
        $package->qty = $request->qty;
        $package->expiry_duration = $request->expiry_duration;

        if ($package->save()) {
            activity('package')->causedBy($package)->performedOn($package)->log('created');
            return new PackageResource($package);
        } else {
            return Helper::error(Response::$statusTexts[Response::HTTP_NO_CONTENT], Response::HTTP_NO_CONTENT);
        }
    }
    public function update($request)
    {

    }

    public function delete($category_id)
    {
//        $category = Category::find($category_id);
//        $productCategory = ContributionProductCategory::where('category_id',$category_id)->first();
//        if($productCategory){
//            return Helper::error(Response::$statusTexts[Response::HTTP_IM_USED], Response::HTTP_IM_USED);
//        }
//        if ($category->delete()) {
//            activity('category')->causedBy($category)->performedOn($category)->log('updated');
//            return Helper::success(Response::$statusTexts[Response::HTTP_OK], Response::HTTP_OK);
//        } else {
//            return Helper::error(Response::$statusTexts[Response::HTTP_NO_CONTENT], Response::HTTP_NO_CONTENT);
//        }
    }

}
