<?php

namespace App\Repositories\Product;

use App\Helpers\Helper;
use App\Models\Product\Contribution\ProductReplacement;
use App\Models\Subscription\Package;
use App\Repositories\Product\Interface\ProductReplacementRepositoryInterface;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class ProductReplacementRepository implements ProductReplacementRepositoryInterface
{

    public function getAvalableCount($package_id)
    {
        $user_id = Auth::user()->id;
        $productReplacement = ProductReplacement::where('user_id',$user_id)->where('package_id',$package_id)->first();
        $data = [
            'user_id' => $user_id,
            'package_id' => $package_id,
            'available_count' => $productReplacement ? $productReplacement->avalable_replace_count : Package::find($package_id)->replace_count,
        ];
        return response()->json([
            'status' => true,
            'message' => 'Product found',
            'data' => $data,
        ], Response::HTTP_OK);
        
    }  
    
    
    public function store($request)
    {
        $userId = Auth::id();

        // Validate package existence once
        $package = Package::find($request->package_id);
        if (!$package) {
            return response()->json([
                'status' => false,
                'message' => 'Package not found',
            ], Response::HTTP_NOT_FOUND);
        }

        // Get or create the ProductReplacement for the user and package
        $productReplacement = ProductReplacement::firstOrNew([
            'user_id' => $userId,
            'package_id' => $request->package_id,
        ]);

        // If it's already created before
        if ($productReplacement->exists) {
            if ($productReplacement->avalable_replace_count <= 0) {
                return response()->json([
                    'status' => false,
                    'message' => 'No available replacement count',
                ], Response::HTTP_OK);
            }
            $productReplacement->avalable_replace_count -= 1;
        } else {
            // If it's a new record, set initial replacement count
            $productReplacement->avalable_replace_count = max($package->replace_count - 1, 0);
        }

        $productReplacement->save();

        
        $serials = array_filter(explode("\n", $package->subscription->serial), 'trim');
        $randomSerial = $serials ? $serials[array_rand($serials)] : null;
        $data = [
            $productReplacement,
            $randomSerial
        ];

        return response()->json([
            'status' => true,
            'message' => 'Replacement updated successfully',
            'data' => [
                [
                    'user_id' => $productReplacement->user_id,
                    'package_id' => $productReplacement->package_id,
                    'avalable_replace_count' => $productReplacement->avalable_replace_count,
                    'updated_at' => $productReplacement->updated_at,
                    'created_at' => $productReplacement->created_at,
                    'id' => $productReplacement->id,
                    'sirial' => $randomSerial,
                ]
            ]
        ], Response::HTTP_OK);
    }
}