<?php

namespace App\Repositories\Review;

use App\Helpers\Helper;
use App\Models\Payment\OrderItems;
use App\Models\Review\Review;
use App\Repositories\Review\Interface\ReviewRepositoryInterface;
use Illuminate\Http\Response;

class ReviewRepository implements ReviewRepositoryInterface
{
    public function store($request)
    {
        $user_id = auth()->user()->id;

        if (!$this->hasPurchased($user_id, $request->product_type, $request->product_id)) {
            return response()->json([
                'message' => 'You can only review products you have purchased.'
            ], Response::HTTP_FORBIDDEN);
        }

        $review = Review::updateOrCreate(
            [
                'user_id' => $user_id,
                'product_type' => $request->product_type,
                'product_id' => $request->product_id,
            ],
            [
                'review' => $request->review,
                'rating' => $request->rating,
            ]
        );

        if ($review) {
            activity('review')
                ->performedOn($review)
                ->causedBy(auth()->user())
                ->withProperties(['name' => $review->product_type])
                ->log($review->wasRecentlyCreated ? 'created' : 'updated');

            return Helper::success(Response::$statusTexts[Response::HTTP_OK], Response::HTTP_OK);
        } else {
            return Helper::error(Response::$statusTexts[Response::HTTP_NO_CONTENT], Response::HTTP_NO_CONTENT);
        }
    }

    public function update($request)
    {
        $review = Review::find($request->id);

        if (!$review) {
            return Helper::error(Response::$statusTexts[Response::HTTP_NOT_FOUND], Response::HTTP_NOT_FOUND);
        }

        if ($review->user_id !== auth()->user()->id) {
            return response()->json([
                'message' => 'You can only update your own reviews.'
            ], Response::HTTP_FORBIDDEN);
        }

        $review->review = $request->review;
        $review->rating = $request->rating;

        if ($review->save()) {
            activity('review')
                ->performedOn($review)
                ->causedBy(auth()->user())
                ->withProperties(['name' => $review->product_type])
                ->log('updated');

            return Helper::success(Response::$statusTexts[Response::HTTP_OK], Response::HTTP_OK);
        } else {
            return Helper::error(Response::$statusTexts[Response::HTTP_NO_CONTENT], Response::HTTP_NO_CONTENT);
        }
    }

    public function deleteReview($id)
    {
        $review = Review::find($id);

        if (!$review) {
            return Helper::error(Response::$statusTexts[Response::HTTP_NO_CONTENT], Response::HTTP_NO_CONTENT);
        }

        if ($review->user_id !== auth()->user()->id) {
            return response()->json([
                'message' => 'You can only delete your own reviews.'
            ], Response::HTTP_FORBIDDEN);
        }

        $temp = $review;
        if ($review->delete()) {
            activity('review')
                ->performedOn($temp)
                ->causedBy(auth()->user())
                ->withProperties(['name' => $temp->product_type])
                ->log('deleted');

            return Helper::success(Response::$statusTexts[Response::HTTP_OK], Response::HTTP_OK);
        } else {
            return Helper::error(Response::$statusTexts[Response::HTTP_NO_CONTENT], Response::HTTP_NO_CONTENT);
        }
    }

    /**
     * Check the user has at least one paid order containing this product.
     */
    private function hasPurchased($user_id, $product_type, $product_id)
    {
        $query = OrderItems::whereHas('order', function ($q) use ($user_id) {
            $q->where('user_id', $user_id)->where('payment_status', 'paid');
        });

        if ($product_type === 'bulk') {
            $query->where('bulk_product_id', $product_id);
        } elseif ($product_type === 'contribution') {
            $query->whereHas('package.subscription', function ($q) use ($product_id) {
                $q->where('contribution_product_id', $product_id);
            });
        } else {
            return false;
        }

        return $query->exists();
    }
}
