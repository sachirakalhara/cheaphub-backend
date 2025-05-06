<?php

namespace App\Repositories\Product;

use App\Helpers\Helper;
use App\Models\Product\Contribution\ProductReplacement;
use App\Models\Product\Contribution\ProductReplacementSerial;
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
    
    
    // public function store($request)
    // {
    //     $userId = Auth::id();

    //     // Validate package existence once
    //     $package = Package::find($request->package_id);
    //     if (!$package) {
    //         return response()->json([
    //             'status' => false,
    //             'message' => 'Package not found',
    //         ], Response::HTTP_NOT_FOUND);
    //     }

    //     // Get or create the ProductReplacement for the user and package
    //     $productReplacement = ProductReplacement::firstOrNew([
    //         'user_id' => $userId,
    //         'package_id' => $request->package_id,
    //     ]);

    //     // If it's already created before
    //     if ($productReplacement->exists) {
    //         if ($productReplacement->avalable_replace_count <= 0) {
    //             return response()->json([
    //                 'status' => false,
    //                 'message' => 'No available replacement count',
    //             ], Response::HTTP_OK);
    //         }
    //         $productReplacement->avalable_replace_count -= 1;
    //     } else {
    //         // If it's a new record, set initial replacement count
    //         $productReplacement->avalable_replace_count = max($package->replace_count - 1, 0);
    //     }

    //     $productReplacement->save();

        
    //     $serials = array_filter(explode("\n", $package->subscription->serial), 'trim');
    //     $randomSerial = $serials ? $serials[array_rand($serials)] : null;

    //     return response()->json([
    //         'status' => true,
    //         'message' => 'Replacement updated successfully',
    //         'data' => [
    //             [
    //                 'user_id' => $productReplacement->user_id,
    //                 'package_id' => $productReplacement->package_id,
    //                 'avalable_replace_count' => $productReplacement->avalable_replace_count,
    //                 'updated_at' => $productReplacement->updated_at,
    //                 'created_at' => $productReplacement->created_at,
    //                 'id' => $productReplacement->id,
    //                 'serial' => $randomSerial,
    //             ]
    //         ]
    //     ], Response::HTTP_OK);
    // }



    public function store($request)
    {
        $userId = Auth::id();

        // Step 1: Validate Package
        $package = Package::find($request->package_id);
        if (!$package) {
            return response()->json([
                'status' => false,
                'message' => 'Package not found',
            ], Response::HTTP_NOT_FOUND);
        }

        // Step 2: Get or create a ProductReplacement record
        $productReplacement = ProductReplacement::firstOrNew([
            'user_id' => $userId,
            'package_id' => $request->package_id,
        ]);

        if ($productReplacement->exists) {
            if ($productReplacement->avalable_replace_count <= 0) {
                return response()->json([
                    'status' => false,
                    'message' => 'No available replacement count',
                ], Response::HTTP_OK);
            }
            $productReplacement->avalable_replace_count -= 1;
        } else {
            $productReplacement->avalable_replace_count = max($package->replace_count - 1, 0);
        }

        $productReplacement->save();

        // Step 3: Get all serials from the subscription
        $allSerials = array_filter(explode("\n", $package->subscription->serial), 'trim');

        // Step 4: Ensure productReplacement ID is valid
        if (!$productReplacement->id) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to create product replacement record.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        // Step 5: Get already used serials for this replacement
        $usedSerials = ProductReplacementSerial::where('product_replacement_id', $productReplacement->id)
            ->pluck('serial')
            ->toArray();

        // Step 6: Get available (unused) serials
        $availableSerials = array_values(array_diff($allSerials, $usedSerials));

        // Step 7: Check if no serials are available
        if (empty($availableSerials)) {
            return response()->json([
                'status' => false,
                'message' => 'No available serials for replacement',
            ], Response::HTTP_OK);
        }

        // Step 8: Pick the first available serial
        $replacementSerial = $availableSerials[0];

        // Step 9: Save the used serial
        ProductReplacementSerial::create([
            'product_replacement_id' => $productReplacement->id,
            'serial' => $replacementSerial,
        ]);

        // Step 10: Return success response
        return response()->json([
            'status' => true,
            'message' => 'Replacement successful',
            'data' => [
                'serial' => $replacementSerial,
                'remaining_replacements' => $productReplacement->avalable_replace_count,
            ]
        ], Response::HTTP_OK);
    }



}