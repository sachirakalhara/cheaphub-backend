<?php

namespace App\Http\Controllers\API;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\Product\Bulk\BulkProduct;
use App\Models\Product\Contribution\ContributionProduct;
use App\Models\User\User;
use App\Notifications\ProductAnnouncementNotification;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class AnnouncementController extends Controller
{
    public function recipientCount(Request $request)
    {
        $request->validate([
            'audience' => 'required|in:all_customers,purchased_customers',
        ]);

        $count = $this->buildAudienceQuery($request->audience)->count();

        return response()->json([
            'status' => 'success',
            'success' => true,
            'code' => Response::HTTP_OK,
            'message' => 'Recipient count retrieved',
            'data' => ['count' => $count],
        ], Response::HTTP_OK);
    }

    public function send(Request $request)
    {
        $request->validate([
            'announcement_type' => 'required|in:new_product,restock',
            'product_ids' => 'required|array|min:1',
            'product_ids.*.id' => 'required|integer',
            'product_ids.*.type' => 'required|in:bulk,contribution',
            'audience' => 'required|in:all_customers,purchased_customers',
            'subject' => 'required|string|max:150',
        ]);

        $products = $this->resolveProducts($request->product_ids);

        if (empty($products)) {
            return Helper::error('No valid products found for the given IDs', Response::HTTP_BAD_REQUEST);
        }

        $query = $this->buildAudienceQuery($request->audience);
        $totalTargeted = $query->count();
        $totalSent = 0;
        $totalFailed = 0;

        $query->chunk(50, function ($customers) use ($products, $request, &$totalSent, &$totalFailed) {
            foreach ($customers as $customer) {
                try {
                    $customer->notify(new ProductAnnouncementNotification(
                        $products,
                        $request->announcement_type,
                        $request->subject
                    ));
                    $totalSent++;
                } catch (\Exception $e) {
                    $totalFailed++;
                    Log::error("Failed to send announcement email to user #{$customer->id}: {$e->getMessage()}");
                }
            }
        });

        Log::info("Announcement sent: type={$request->announcement_type}, targeted={$totalTargeted}, sent={$totalSent}, failed={$totalFailed}");

        return response()->json([
            'status' => 'success',
            'success' => true,
            'code' => Response::HTTP_OK,
            'message' => 'Announcement sent successfully',
            'data' => [
                'total_targeted' => $totalTargeted,
                'total_sent' => $totalSent,
                'total_failed' => $totalFailed,
            ],
        ], Response::HTTP_OK);
    }

    private function buildAudienceQuery(string $audience)
    {
        $query = User::whereDoesntHave('roles', function ($q) {
            $q->where('name', 'super_admin');
        })->where('active', 1)->whereNotNull('email')->where('email', '!=', '');

        if ($audience === 'purchased_customers') {
            $query->whereHas('order', function ($q) {
                $q->whereIn('payment_status', ['paid', 'completed']);
            });
        }

        return $query;
    }

    private function resolveProducts(array $productIds): array
    {
        $products = [];
        $clientUrl = rtrim(config('app.client_url'), '/');

        foreach ($productIds as $item) {
            $id = $item['id'];
            $type = $item['type'];

            if ($type === 'bulk') {
                $product = BulkProduct::find($id);
                if ($product) {
                    $slug = Str::slug($product->name);
                    $products[] = [
                        'name' => $product->name,
                        'price' => $product->price,
                        'url' => $clientUrl . '/bulk/' . $product->id . '/' . $slug,
                    ];
                }
            } elseif ($type === 'contribution') {
                $product = ContributionProduct::find($id);
                if ($product) {
                    $slug = Str::slug($product->name);
                    $products[] = [
                        'name' => $product->name,
                        'price' => null,
                        'url' => $clientUrl . '/contribution/' . $product->id . '/' . $slug,
                    ];
                }
            }
        }

        return $products;
    }
}
