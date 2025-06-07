<?php

namespace App\Repositories\Review;

use App\Helpers\Helper;
use App\Models\Review\Review;
use App\Repositories\Review\Interface\ReviewRepositoryInterface;
use Illuminate\Http\Response;

class ReviewRepository implements ReviewRepositoryInterface
{
   public function store($request)
    {
        $user_id = auth()->user()->id;
       
    
        $review = new Review();
        $review->review = $request->review;
        $review->rating = $request->rating;
        $review->user_id = $user_id;
        $review->product_type = $request->product_type;
        $review->product_id = $request->product_id;

        if ($review->save()) {

            activity('review')
                ->performedOn($review)
                ->causedBy(auth()->user())
                ->withProperties(['name' => $review->product_type])
                ->log('created');

            return Helper::success(Response::$statusTexts[Response::HTTP_OK], Response::HTTP_OK);
        } else {
            return Helper::error(Response::$statusTexts[Response::HTTP_NO_CONTENT], Response::HTTP_NO_CONTENT);
        }
    }

    public function update($request)
    {
        $review = Review::find($request->id);
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
        $temp = $review;
        if ($review) {

            if($review->delete()){

                activity('review')
                    ->performedOn($temp)
                    ->causedBy(auth()->user())
                    ->withProperties(['name' => $temp->product_type])
                    ->log('deleted');

                return Helper::success(Response::$statusTexts[Response::HTTP_OK], Response::HTTP_OK);

            }else{
                return Helper::error(Response::$statusTexts[Response::HTTP_NO_CONTENT], Response::HTTP_NO_CONTENT);
            }
        } else {
            return Helper::error(Response::$statusTexts[Response::HTTP_NO_CONTENT], Response::HTTP_NO_CONTENT);
        }
    }


}