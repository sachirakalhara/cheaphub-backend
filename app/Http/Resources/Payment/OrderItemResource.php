<?php

namespace App\Http\Resources\Payment;

use App\Http\Resources\Product\Bulk\BulkProductResource;
use App\Models\Product\Contribution\ContributionProduct;
use App\Models\Subscription\Subscription;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\Product\Contribution\ProductReplacement;
use App\Models\Product\Contribution\ProductReplacementSerial;
use App\Models\Product\Contribution\RemovedContributionProductSerial;

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

        $package = $this->package;
        $subscription = null;
        $contributionProduct = null;
        $image = null;
        $user_purchase_serials = null;
        if ($package) {
            $subscription = Subscription::find($package->subscription_id);
            if ($subscription) {
                $user_purchase_serials = RemovedContributionProductSerial::where('order_item_id', $this->id)->get();
                $contributionProduct = ContributionProduct::find($subscription->contribution_product_id);
                if ($contributionProduct && $contributionProduct->image) {
                    $image = $disk->url($contributionProduct->image);
                }
            }
        }
     
        return [
            'id' => $this->id,
            'quantity' => $this->quantity,
            'user_purchase_serials' => $user_purchase_serials,
            'created_at' => $this->created_at,
            'bulk_product' => new BulkProductResource($this->bulkProduct),
            'package' => [
                'id' => optional($package)->id,
                'name' => optional($package)->name,
                'price' => optional($package)->price,
                'replace_count' => optional($package)->replace_count,
                'expiry_duration' => optional($package)->expiry_duration,
            ],

            'subscription' => $subscription ? [
                'id' => $subscription->id,
                'name' => $subscription->name,
                'available_serial_count' => $subscription->available_serial_count,
                'gateway_fee' => $subscription->gateway_fee,

            ] : null,

            'contribution_product' => $contributionProduct ? [
                'id' => $contributionProduct->id,
                'name' => $contributionProduct->name,
                'tag_id' => $contributionProduct->tag_id,
                'description' => $contributionProduct->description,
                'image' => $image,
                'visibility' => $contributionProduct->visibility,
                'service_info' => $contributionProduct->service_info,
                'url' => Auth::check()
                    ? "https://cheaphub.io/contribution/{$contributionProduct->id}/{$contributionProduct->name}"
                    : null,
            ] : null,
        ];
    }

}
