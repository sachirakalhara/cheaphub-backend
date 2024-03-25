<?php

namespace App\Repositories\Tag;

use App\Helpers\Helper;
use App\Http\Resources\Tag\TagCollection;
use App\Http\Resources\Tag\TagResource;
use App\Models\Product\Contribution\ContributionProduct;
use App\Models\Tag\Tag;
use App\Repositories\Tag\Interface\TagRepositoryInterface;
use Illuminate\Http\Response;

class TagRepository implements TagRepositoryInterface
{
    public function all($request)
    {

        if($request->input('all', '') == 1) {
            $tag_list = Tag::all();
        } else {
            $tag_list = Tag::orderBy('created_at', 'desc')->paginate(10);
        }

        if (count($tag_list) > 0) {
            return new TagCollection($tag_list);
        } else {
            return Helper::success(Response::$statusTexts[Response::HTTP_NO_CONTENT], Response::HTTP_NO_CONTENT);
        }
    }

    public function findById($id)
    {

        $tag = Tag::find($id);


        if ($tag) {
            return new TagResource($tag);
        } else {
            return Helper::success(Response::$statusTexts[Response::HTTP_NO_CONTENT], Response::HTTP_NO_CONTENT);
        }
    }

    public function store($request)
    {
        $tag = new Tag();
        $tag->name = $request->name;
        $tag->description = $request->description;
        if ($tag->save()) {
            activity('tag')->causedBy($tag)->performedOn($tag)->log('created');
            return new TagResource($tag);
        } else {
            return Helper::error(Response::$statusTexts[Response::HTTP_NO_CONTENT], Response::HTTP_NO_CONTENT);
        }
    }
    public function update($request)
    {
        $tag = Tag::find($request->id);
        $tag->name = $request->name;
        $tag->description = $request->description;
        if ($tag->save()) {
            activity('tag')->causedBy($tag)->performedOn($tag)->log('updated');
            return new TagResource($tag);
        } else {
            return Helper::error(Response::$statusTexts[Response::HTTP_NO_CONTENT], Response::HTTP_NO_CONTENT);
        }
    }

    public function delete($tag_id)
    {
        $tag = Tag::find($tag_id);
        $productCategory = ContributionProduct::where('tag_id',$tag_id)->first();
        if($productCategory){
            return Helper::error(Response::$statusTexts[Response::HTTP_IM_USED], Response::HTTP_IM_USED);
        }
        if ($tag->delete()) {
            activity('tag')->causedBy($tag)->performedOn($tag)->log('delete');
            return Helper::success(Response::$statusTexts[Response::HTTP_OK], Response::HTTP_OK);
        } else {
            return Helper::error(Response::$statusTexts[Response::HTTP_NO_CONTENT], Response::HTTP_NO_CONTENT);
        }
    }

}
