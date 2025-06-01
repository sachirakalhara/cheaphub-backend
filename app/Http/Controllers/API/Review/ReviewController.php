<?php
namespace App\Http\Controllers\API\Review;

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
            'rating' => 'required|numeric|min:0|max:5',
        ]);
        return $this->reviewRepository->store($request);
    }

    public function deleteReview($id){
        return $this->reviewRepository->deleteReview($id);
    }
    

    /**
     * Update the specified resource in storage.
     */
     public function update(Request $request)
     {
         $request->validate([
             'id' => 'required',
             'rating' => 'required|numeric|min:0|max:5',
         ]);
         return $this->reviewRepository->update($request);
     }

   
}
