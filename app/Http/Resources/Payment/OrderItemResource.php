<?php

namespace App\Http\Resources\Payment;

use App\Http\Resources\Category\CategoryResource;
use App\Http\Resources\Product\Bulk\BulkProductResource;
use App\Http\Resources\Subscription\PackageResource;
use App\Http\Resources\Subscription\SubscriptionResource;
use App\Models\Subscription\Subscription;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class OrderItemResource extends JsonResource
{
    public static $wrap = 'item';

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {

        $disk = Storage::disk('s3');

        $contributionProduct = optional(optional(optional($this->package)->subscription)->contributionProduct);
        $image = $contributionProduct->image ? $disk->url($contributionProduct->image) : null;
        $subscription = Subscription::find($this->package->subscription_id);
        return [
            'id' => $this->id,
            'quantity' => $this->quantity,
            'created_at' => $this->created_at,

            'bulk_product' => new BulkProductResource($this->bulkProduct),

            'package' => [
                'id' => optional($this->package)->id,
                'name' => optional($this->package)->name,
                'price' => optional($this->package)->price,
                'replace_count' => optional($this->package)->replace_count,
                'expiry_duration' => optional($this->package)->expiry_duration,
            ],

            'subscription' => [
                'id' => $subscription->id,
                'name' => $subscription->name,
                // 'serial' => $subscription->serial,
                'available_serial_count' => $subscription->available_serial_count,
                'gateway_fee' => $subscription->gateway_fee,
            ],

            'contribution_product' => [
                'id' => $contributionProduct->id,
                'name' => $contributionProduct->name,
                'tag_id' => $contributionProduct->tag_id,
                'description' => $contributionProduct->description,
                'image' => $image,
                'visibility' => $contributionProduct->visibility,
                'service_info' => $contributionProduct->service_info,
                'url' =>!empty(Auth::user()->id) ? "cheaphub.io/contribution/{$contributionProduct->id}/{$contributionProduct->name}" : null,
            ],
        ];

            // 'id'=>$this->id,
            // // 'order'=>OrderResource::collection($this->order),
            // 'quantity'=> $this->quantity,
            // // 'bulkProduct'=> $this->bulkProduct,
            // // 'package'=> $this->package,
            // 'created_at'=>$this->created_at,
            
            // 'bulk_product' => new BulkProductResource($this->bulkProduct),
            // // 'package' => new PackageResource($this->package),
            // 'package' =>  [
            //     'id' => $this->package->id,
            //     'name' => $this->package->name,
            //     'price' => $this->package->price,
            //     'replace_count' => $this->package->replace_count,
            //     'expiry_duration' => $this->package->expiry_duration,
            //     'payment_method' => $this->package->payment_method,
            //     'contributionProduct'=> [
            //         'id' => $this->package->subscription->contributionProduct->id,
            //         'name' => $this->package->subscription->contributionProduct->name,
            //         'tag_id' => $this->package->subscription->contributionProduct->tag_id,
            //         'description' => $this->package->subscription->contributionProduct->description,
            //         'categories' => CategoryResource::collection($this->package->subscription->contributionProduct->categories),
            //         'image' => $image,
            //         'visibility' => $this->package->subscription->contributionProduct->visibility,
            //         'service_info' => $this->package->subscription->contributionProduct->service_info,
            //         'url' =>!empty(Auth::user()->id) ? "cheaphub.io/contribution/{$this->package->subscription->contributionProduct->id}/{$this->package->subscription->contributionProduct->name}" : null,
            //         'subscriptions' => SubscriptionResource::collection($this->package->subscription->contributionProduct->subscriptions),
        
            //     ];
            


    
    }

}
