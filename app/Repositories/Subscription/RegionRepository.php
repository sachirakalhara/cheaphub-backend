<?php

namespace App\Repositories\Subscription;

use App\Helpers\Helper;
use App\Http\Resources\Region\RegionCollection;
use App\Http\Resources\Region\RegionResource;
use App\Models\Subscription\Region;
use App\Models\Subscription\RegionMonth;
use App\Repositories\Subscription\Interface\RegionRepositoryInterface;
use Illuminate\Http\Response;

class RegionRepository implements RegionRepositoryInterface
{
    public function all($request)
    {

        if($request->input('all', '') == 1) {
            $region_list = Region::all();
        } else {
            $region_list = Region::orderBy('created_at', 'desc')->paginate(10);
        }

        if (count($region_list) > 0) {
            return new RegionCollection($region_list);
        } else {
            return Helper::success(Response::$statusTexts[Response::HTTP_NO_CONTENT], Response::HTTP_NO_CONTENT);
        }
    }

    public function store($request)
    {
        $region = new Region();
        $region->region_name = $request->region_name;
        if ($region->save()) {
            $region->months()->attach($request->months);
            activity('region')->causedBy($region)->performedOn($region)->log('created');
            return new RegionResource($region);
        } else {
            return Helper::error(Response::$statusTexts[Response::HTTP_NO_CONTENT], Response::HTTP_NO_CONTENT);
        }
    }
    public function update($request)
    {
        $region = Region::find($request->id);
        $region->region_name = $request->region_name;
        $region->months()->sync($request->months);
        if ($region->save()) {
            activity('region')->causedBy($region)->performedOn($region)->log('updated');
            return new RegionResource($region);
        } else {
            return Helper::error(Response::$statusTexts[Response::HTTP_NO_CONTENT], Response::HTTP_NO_CONTENT);
        }
    }
    public function delete($region_id)
    {
        $region = Region::find($region_id);
        $regionMonths = RegionMonth::where('region_id',$region_id)->get();
        foreach ($regionMonths as $regionMonth) {
            $regionMonth->delete();
        }
        if ($region->delete()) {
            return Helper::success(Response::$statusTexts[Response::HTTP_OK], Response::HTTP_OK);
        } else {
            return Helper::error(Response::$statusTexts[Response::HTTP_NO_CONTENT], Response::HTTP_NO_CONTENT);
        }
    }
}
