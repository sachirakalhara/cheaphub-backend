<?php

namespace App\Http\Resources\Payment;

use App\Http\Resources\Product\Bulk\BulkProductResource;
use App\Models\Product\Bulk\RemovedBulkProductSerial;
use App\Models\Product\Contribution\ContributionProduct;
use App\Models\Subscription\Subscription;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\Product\Contribution\ProductReplacement;
use App\Models\Product\Contribution\ProductReplacementSerial;
use App\Models\Product\Contribution\RemovedContributionProductSerial;
use App\Models\Payment\OrderItems;

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
        $available_replace_count = null;
        if ($package) {
            $subscription = Subscription::find($package->subscription_id);
            if ($subscription) {
                // $order_id = OrderItems::where('package_id', $package->id)->first()->order_id;

                $productReplacement = ProductReplacement::where('order_id', $this->order_id)
                    ->where('package_id', $package->id)
                    ->first();
                $available_replace_count = $productReplacement ? $productReplacement->available_replace_count : optional($package)->replace_count;

                $user_purchase_serials = RemovedContributionProductSerial::with(['removedProductReplacementSerials.product_replacement_serial'])
                    ->where('order_item_id', $this->id)
                    ->get();

                foreach ($user_purchase_serials as $serial) {
                    $replacementSerial = optional($serial->removedProductReplacementSerials->first())->product_replacement_serial ?? null;
                    $serial->serial = $replacementSerial ? $replacementSerial->serial : $serial->serial;
                        null;
                }

                $contributionProduct = ContributionProduct::find($subscription->contribution_product_id);
                if ($contributionProduct && $contributionProduct->image) {
                    $image = $disk->url($contributionProduct->image);
                }
            }
        }else{
             $user_purchase_serials = RemovedBulkProductSerial::where('order_item_id', $this->id)
                    ->get();
        }

        return [
            'id' => $this->id,
            'quantity' => $this->quantity,
            'user_purchase_serials' => $user_purchase_serials,
            'available_replace_count' => $available_replace_count,
            'created_at' => $this->created_at,
            'bulk_product' =>[
                'id' => optional($this->bulkProduct)->id,
                'name' => optional($this->bulkProduct)->name,
                'tag_id' => optional($this->bulkProduct)->tag_id,
                'description' => optional($this->bulkProduct)->description,
                'bulk_type' => optional($this->bulkProduct)->bulk_type,
                'price' => optional($this->bulkProduct)->price,
                'gateway_fee' => optional($this->bulkProduct)->gateway_fee,
                'categories' => optional($this->bulkProduct)->categories,
                'image' => $this->bulkProduct && $this->bulkProduct->image ? $disk->url($this->bulkProduct->image) : null,
                'visibility' => optional($this->bulkProduct)->visibility,
                'service_info' => optional($this->bulkProduct)->service_info,
                'url' => $this->bulkProduct && Auth::check()
                    ? "https://cheaphub.io/bulk/{$this->bulkProduct->id}/{$this->bulkProduct->name}"
                    : null,
            ],
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
