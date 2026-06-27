<?php

namespace App\Repositories\Subscription;

use App\Helpers\Helper;
use App\Http\Resources\Subscription\SubscriptionCollection;
use App\Http\Resources\Subscription\SubscriptionResource;
use App\Models\Product\Contribution\ContributionProduct;
use App\Models\Payment\OrderItems;
use App\Models\Subscription\Package;
use App\Models\Subscription\Subscription;
use App\Repositories\Subscription\Interface\SubscriptionRepositoryInterface;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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
        $deliveryType = $request->delivery_type ?? 'serial_based';

        $subscription = new Subscription();
        $subscription->name = $request->name;
        $subscription->contribution_product_id = $request->contribution_product_id;
        $subscription->gateway_fee = $request->gateway_fee;
        $subscription->delivery_type = $deliveryType;

        if ($deliveryType === 'service_based') {
            $serviceQty = (int) $request->service_qty;
            if ($serviceQty < 0) {
                return Helper::error('Available quantity cannot be negative', Response::HTTP_BAD_REQUEST);
            }
            $subscription->serial = '';
            $subscription->available_serial_count = 0;
            $subscription->service_qty = $serviceQty;
            $subscription->service_info = $request->service_info ?? '';
        } else {
            $subscription->serial = $request->serial;
            $subscription->available_serial_count = count(array_filter(explode("\n", $request->serial), 'trim'));
            $subscription->service_qty = 0;
        }

        $subscription->is_manually_out_of_stock = $request->has('is_manually_out_of_stock') ? (bool) $request->is_manually_out_of_stock : false;

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

        if ($subscription->delivery_type === 'service_based') {
            // Service-based subscriptions only manage a manual quantity.
            // Serial fields are left untouched (always empty/zero for these).
            $subscription->name = $request->name;
            $subscription->gateway_fee = $request->gateway_fee;

            if ($request->has('service_qty')) {
                $serviceQty = (int) $request->service_qty;
                if ($serviceQty < 0) {
                    return Helper::error('Available quantity cannot be negative', Response::HTTP_BAD_REQUEST);
                }
                $subscription->service_qty = $serviceQty;
            }

            if ($request->has('service_info')) {
                $subscription->service_info = $request->service_info;
            }
        } else {
            $subscription->name = $request->name;
            $subscription->serial = $request->serial;
            $subscription->available_serial_count = count(array_filter(explode("\n", $request->serial), 'trim'));
            $subscription->gateway_fee = $request->gateway_fee;
        }

        if ($request->has('is_manually_out_of_stock')) {
            $subscription->is_manually_out_of_stock = (bool) $request->is_manually_out_of_stock;
        }

        if ($subscription->save()) {
            activity('subscription')->causedBy($subscription)->performedOn($subscription)->log('updated');
            return new SubscriptionResource($subscription);
        } else {
            return Helper::error("Failed to update subscription", Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function deleteBydID($id){
        $subscription = Subscription::find($id);
        if ($subscription == null) {
            return Helper::error("Subscription not found", 404);
        }

        // Packages (pricing tiers/plans) that belong to this subscription variant.
        // The DB blocks a direct subscription delete because packages reference it
        // (packages.subscription_id), so every dependent child must be removed first,
        // in child-before-parent order, inside one transaction.
        $packageIds = Package::where('subscription_id', $id)->pluck('id')->toArray();

        if (!empty($packageIds)) {
            // Option A — block deletion when a customer is actively buying/using one of
            // these plans. Historical orders (failed / canceled / refunded) never block.
            $activeStatuses = ['pending', 'paid', 'completed'];
            $hasActiveOrders = OrderItems::whereIn('package_id', $packageIds)
                ->whereHas('order', function ($q) use ($activeStatuses) {
                    $q->whereIn('payment_status', $activeStatuses);
                })
                ->exists();

            if ($hasActiveOrders) {
                return Helper::error(
                    "Cannot delete this subscription — it has active orders linked to it. Please resolve all active orders first.",
                    Response::HTTP_CONFLICT
                );
            }
        }

        try {
            DB::transaction(function () use ($id, $packageIds, $subscription) {
                if (!empty($packageIds)) {
                    // order_items for these packages — they belong to historical orders
                    // only (active ones were blocked above). Orders are preserved.
                    $orderItemIds = OrderItems::whereIn('package_id', $packageIds)->pluck('id')->toArray();

                    // Delivered-serial records for these packages.
                    $removedSerialIds = DB::table('removed_contribution_product_serials')
                        ->whereIn('package_id', $packageIds)
                        ->pluck('id')->toArray();

                    // Replacement records + their issued serials for these packages.
                    $replacementIds = DB::table('product_replacements')
                        ->whereIn('package_id', $packageIds)
                        ->pluck('id')->toArray();
                    $replacementSerialIds = !empty($replacementIds)
                        ? DB::table('product_replacement_serials')
                            ->whereIn('product_replacement_id', $replacementIds)
                            ->pluck('id')->toArray()
                        : [];

                    // 1. removed_product_replacement_serials — links a delivered serial to
                    //    a replacement serial; references BOTH tables, so it goes first.
                    if (!empty($removedSerialIds) || !empty($replacementSerialIds)) {
                        DB::table('removed_product_replacement_serials')
                            ->where(function ($q) use ($removedSerialIds, $replacementSerialIds) {
                                if (!empty($removedSerialIds)) {
                                    $q->orWhereIn('removed_contribution_product_serial_id', $removedSerialIds);
                                }
                                if (!empty($replacementSerialIds)) {
                                    $q->orWhereIn('product_replacement_serial_id', $replacementSerialIds);
                                }
                            })
                            ->delete();
                    }

                    // 2. product_replacement_serials (child of product_replacements)
                    if (!empty($replacementIds)) {
                        DB::table('product_replacement_serials')
                            ->whereIn('product_replacement_id', $replacementIds)->delete();
                    }

                    // 3. removed_contribution_product_serials (child of packages + order_items)
                    if (!empty($removedSerialIds)) {
                        DB::table('removed_contribution_product_serials')
                            ->whereIn('id', $removedSerialIds)->delete();
                    }

                    // 4. product_replacements (child of packages)
                    if (!empty($replacementIds)) {
                        DB::table('product_replacements')->whereIn('id', $replacementIds)->delete();
                    }

                    // 5. cart_items (transient — safe to remove)
                    DB::table('cart_items')->whereIn('package_id', $packageIds)->delete();

                    // 6. order_items — preserve the order record, just detach the package.
                    if (!empty($orderItemIds)) {
                        DB::table('order_items')->whereIn('id', $orderItemIds)->update(['package_id' => null]);
                    }

                    // 7. packages
                    DB::table('packages')->whereIn('id', $packageIds)->delete();
                }

                // 8. finally, the subscription itself
                $subscription->delete();

                activity('subscription')->causedBy($subscription)->performedOn($subscription)->log('deleted');
            });

            return Helper::success(Response::$statusTexts[Response::HTTP_OK], Response::HTTP_OK);
        } catch (\Throwable $e) {
            Log::error('Subscription delete failed for id ' . $id . ': ' . $e->getMessage());
            return Helper::error(
                "Failed to delete subscription. Please try again or contact support.",
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }
    


}
