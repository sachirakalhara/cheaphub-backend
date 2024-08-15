<?php
namespace App\Http\Controllers\API\Tag;

use App\Http\Controllers\Controller;
use App\Models\Tag\Tag;
use App\Repositories\Tag\Interface\TagRepositoryInterface;
use Illuminate\Http\Request;

class TagController extends Controller
{
    private $tagRepository;

    public function __construct(TagRepositoryInterface $tagRepository)
    {
        $this->tagRepository = $tagRepository;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        return $this->tagRepository->all($request);
    }
    public function filter(Request $request)
    {
        return $this->tagRepository->filter($request);
    }


/**
     * Display a listing of the resource.
     */
    public function findById($id)
    {
        return $this->tagRepository->findById($id);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|unique:tags'
        ]);

        return $this->tagRepository->store($request);
    }

    /**
     * Display the specified resource.
     */
    public function show(Tag $tag)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Tag $tag)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request)
    {
        $request->validate([
            'id' => 'required',
            'name' => 'required|unique:tags,name,' . $request->id,

        ]);

        return $this->tagRepository->update($request);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function delete($tag_id)
    {
        return $this->tagRepository->delete($tag_id);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Tag $tag)
    {
        //
    }
}
