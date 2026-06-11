<?php
namespace App\Http\Controllers\API\Review;

use App\Repositories\Review\Interface\ReviewRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class ReviewController extends Controller
{
   private $reviewRepository;

    public function __construct(ReviewRepositoryInterface $reviewRepository)
    {
        $this->reviewRepository = $reviewRepository;
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'rating' => 'required|numeric|min:1|max:5',
            'product_type' => 'required|in:bulk,contribution',
            'product_id' => 'required|integer',
            'review' => 'nullable|string|max:2000',
        ]);
        return $this->reviewRepository->store($request);
    }

    public function deleteReview($id){
        return $this->reviewRepository->deleteReview($id);
    }

    /**
     * Latest reviews for the public homepage feedback section.
     */
    public function latest()
    {
        return $this->reviewRepository->latest(5);
    }
    

    /**
     * Update the specified resource in storage.
     */
     public function update(Request $request)
     {
         $request->validate([
             'id' => 'required',
             'rating' => 'required|numeric|min:1|max:5',
             'review' => 'nullable|string|max:2000',
         ]);
         return $this->reviewRepository->update($request);
     }

   
}
