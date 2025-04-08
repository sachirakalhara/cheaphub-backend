<?php

namespace App\Http\Controllers\API\Product\Contribution;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Repositories\Product\Interface\ProductReplacementRepositoryInterface;

class ProductReplacementController extends Controller
{
    private $productReplacementRepository;

    public function __construct(ProductReplacementRepositoryInterface $productReplacementRepository)
    {
        $this->productReplacementRepository = $productReplacementRepository;
    }

   
    public function getAvalableCount($package_id)
    {
        return $this->productReplacementRepository->getAvalableCount($package_id);
    }

    public function store(Request $request)
    {
        return $this->productReplacementRepository->store($request);
    }

}