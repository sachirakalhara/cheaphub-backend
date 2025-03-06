<?php

namespace App\Repositories\Subscription;

use App\Helpers\Helper;
use App\Http\Resources\Subscription\SubscriptionCollection;
use App\Http\Resources\Subscription\SubscriptionResource;
use App\Models\Product\Contribution\ContributionProduct;
use App\Models\Subscription\Subscription;
use App\Repositories\Subscription\Interface\SubscriptionRepositoryInterface;
use Illuminate\Http\Response;

class SubscriptionRepository implements SubscriptionRepositoryInterface
{

    public function all($request)
    {

        if($request->input('all', '') == 1) {
            $subscription_list = Subscription::where('contribution_product_id',$request->contribution_product_id)->get();
        } else {
            $subscription_list = Subscription::where('contribution_product_id',$request->contribution_product_id)->orderBy('created_at', 'desc')->paginate(10);
        }

        if (count($subscription_list) > 0) {
            return new SubscriptionCollection($subscription_list);
        } else {
            return Helper::success(Response::$statusTexts[Response::HTTP_NO_CONTENT], Response::HTTP_NO_CONTENT);
        }
    }
    
    public function store($request)
    {
        $contributionProduct = ContributionProduct::find($request->contribution_product_id);
        if($contributionProduct == null){
            return Helper::error("Contribution Product is not selected", 404);
        }
        $subscription = new Subscription();
        $subscription->name = $request->name;
        $subscription->contribution_product_id = $request->contribution_product_id;
        $subscription->serial = $request->serial;
        $subscription->available_serial_count = count(array_filter(explode("\n", $request->serial), 'trim'));
        $subscription->gateway_fee = $request->gateway_fee;

        if ($subscription->save()) {

            activity('subscription')->causedBy($subscription)->performedOn($subscription)->log('created');
            return new SubscriptionResource($subscription);
        } else {
            return Helper::error(Response::$statusTexts[Response::HTTP_NO_CONTENT], Response::HTTP_NO_CONTENT);
        }
    }

    public function update($request)
    {
        $subscription = Subscription::find($request->id);
        if ($subscription == null) {
            return Helper::error("Subscription not found", 404);
        }

        $subscription->name = $request->name;
        $subscription->serial = $request->serial;
        $subscription->available_serial_count = count(array_filter(explode("\n", $request->serial), 'trim'));
        $subscription->gateway_fee = $request->gateway_fee;

        if ($subscription->save()) {
            activity('subscription')->causedBy($subscription)->performedOn($subscription)->log('updated');
            return new SubscriptionResource($subscription);
        } else {
            return Helper::error("Failed to update subscription", Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    
    public function delete($product_id){
        $subscription_list = Subscription::where('product_id',$product_id)->get();
        foreach ($subscription_list as $subscription) {
            $subscription->delete();
        }
        return Helper::success(Response::$statusTexts[Response::HTTP_OK], Response::HTTP_OK);
    }

    public function deleteBydID($id){
        $subscription = Subscription::find($id);
        if ($subscription == null) {
            return Helper::error("Subscription not found", 404);
        }

        if ($subscription->delete()) {
            activity('subscription')->causedBy($subscription)->performedOn($subscription)->log('deleted');
            return Helper::success(Response::$statusTexts[Response::HTTP_OK], Response::HTTP_OK);
        } else {
            return Helper::error("Failed to delete subscription", Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    


}
