<?php

namespace App\Http\Controllers;

use App\Http\Requests\Collection\CollectionRequest;
use App\Http\Resources\Collection\CollectionCollection;
use App\Http\Resources\Collection\CollectionResource;
use App\Models\Bookmark;
use App\Models\Collection;
use Illuminate\Support\Facades\Auth;

class CollectionController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    public function index()
    {
        // Get the currently authenticated user's ID
        $userId = Auth::id();

        // Check if 'tags' parameter exists
        $tags = request('tags');
        
        if ($tags) {
            // Call the index method of TagController
            $collections = app(TagController::class)->index(new Collection(), $tags);
            // Query tagged collections for the current user with fields 'bookmarks' and 'tags'
            $collections = $collections->where('user_id', $userId)->with(['tags', 'bookmarks']);
        } else {
            // Query bookmarks for the current user with fields 'bookmarks' and 'tags'
            $collections = Collection::query()->where('user_id', $userId)->with(['tags', 'bookmarks']);
        }

        $collections = $collections->allowedSorts(['name', 'created_at', 'updated_at'])
            ->allowedFilters(['name', 'description', 'month', 'year'])
            ->jsonPaginate();

        return CollectionCollection::make($collections);
    }

    public function store(CollectionRequest $request)
    {
        // Get the request data
        $requestData = $request->validated();
        
        // Extract the attributes from the request data
        $attributes = $requestData['data']['attributes'];
        
        // Get the currently authenticated user's ID
        $userId = Auth::id();
        
        $collection = Collection::create([
            'user_id' => $userId,
            'name' => $attributes['name'],
            'description' => $attributes['description'],
        ]);

        // Check if 'tags' key exists before accessing it
        if (isset($attributes['tags']) && $attributes['tags']) {
            app(TagController::class)->store($collection, $attributes['tags']);
        }

        // Eager load the bookmarks relationship and tags relationship
        $collection->load(['bookmarks', 'tags']);

        return CollectionResource::make($collection);
    }

    public function show(Collection $collection)
    {
        // Get the currently authenticated user's ID
        $userId = Auth::id();

        // Check if the collection belongs to the current user
        if ($collection->user_id !== $userId) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Eager load the bookmarks relationship and tags relationship
        $collection->load(['tags', 'bookmarks']);

        return CollectionResource::make($collection);
    }

    public function update(Collection $collection, CollectionRequest $request)
    {
        // Get the request data
        $requestData = $request->validated();
        // Extract the attributes from the request data
        $attributes = $requestData['data']['attributes'];

        $collection->update([
            'name' => $attributes['name'],
            'description' => $attributes['description'],
        ]);

        // Check if 'tags' key exists before accessing it
        if (isset($attributes['tags']) && $attributes['tags']) {
            app(TagController::class)->update($collection, $attributes['tags']);
        }

        // Eager load the bookmarks relationship and tags relationship
        $collection->load(['bookmarks', 'tags']);

        return CollectionResource::make($collection);
    }

    public function destroy(Collection $collection)
    {
        $collection->delete();

        return response()->json([
            "message" => "The collection " . $collection->id . " has been deleted successfully."
        ]);
    }

    public function addBookmark(Collection $collection, Bookmark $bookmark)
    {
        $collection->bookmarks()->attach($bookmark);

        return response()->json([
            "message" => "The bookmark " . $bookmark->id . " has been added to the collection " . $collection->id . "."
        ]);
    }

    public function removeBookmark(Collection $collection, Bookmark $bookmark)
    {
        $collection->bookmarks()->detach($bookmark);

        return response()->json([
            "message" => "The bookmark " . $bookmark->id . " has been deleted from the collection " . $collection->id . "."
        ]);
    }
}
