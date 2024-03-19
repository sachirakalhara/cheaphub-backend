<?php

namespace App\Repositories\Subscription;

use App\Helpers\Helper;
use App\Http\Resources\Subscription\SubscriptionCollection;
use App\Http\Resources\Subscription\SubscriptionResource;
use App\Models\Product\Product;
use App\Models\Subscription\Month;
use App\Models\Subscription\Region;
use App\Models\Subscription\Subscription;
use App\Repositories\Subscription\Interface\SubscriptionRepositoryInterface;
use Illuminate\Http\Response;

class SubscriptionRepository implements SubscriptionRepositoryInterface
{

    public function all($request)
    {

        if($request->input('all', '') == 1) {
            $subscription_list = Subscription::where('product_id',$request->product_id)->get();
        } else {
            $subscription_list = Subscription::where('product_id',$request->product_id)->orderBy('created_at', 'desc')->paginate(10);
        }

        if (count($subscription_list) > 0) {
            return new SubscriptionCollection($subscription_list);
        } else {
            return Helper::success(Response::$statusTexts[Response::HTTP_NO_CONTENT], Response::HTTP_NO_CONTENT);
        }
    }
    public function store($request)
    {
        $subscription = new Subscription();
        $subscription->type = $request->type;
        $subscription->month_id = $request->month_id;
        $subscription->product_id = $request->product_id;

        $subscription_type_check = Subscription::where('type', '!=',$request->type)
            ->where('product_id',$request->product_id)->first();
            
        if($subscription_type_check){
            return Helper::error("Subscription type is can't added", Response::HTTP_IM_USED);
        }

        $subscription_duplicate = Subscription::
        where('type',$request->type)->
        where('month_id',$request->month_id)->
        where('product_id',$request->product_id)->
        first();

        if($subscription_duplicate){
            return Helper::error("Subscription is duplicate", Response::HTTP_IM_USED);
        }

        if ($subscription->save()) {

            activity('subscription')->causedBy($subscription)->performedOn($subscription)->log('created');
            return new SubscriptionResource($subscription);
        } else {
            return Helper::error(Response::$statusTexts[Response::HTTP_NO_CONTENT], Response::HTTP_NO_CONTENT);
        }
    }

    public function delete($product_id){
        $subscription_list = Subscription::where('product_id',$product_id)->get();
        foreach ($subscription_list as $subscription) {
            $subscription->delete();
        }
        return Helper::success(Response::$statusTexts[Response::HTTP_OK], Response::HTTP_OK);
    }


}
