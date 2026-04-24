<?php

namespace App\Http\Controllers\Api;

use App\Models\Review;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreReviewRequest;
use App\Http\Requests\UpdateReviewRequest;
use App\Http\Resources\ReviewResource;

class ReviewController extends Controller
{
    public function index()
    {
        $reviews = Review::all();

        return ReviewResource::collection($reviews);
    }

    public function store(StoreReviewRequest $request)
    {
        $review = Review::create($request->validated());

        return response()->json([
            'message' => 'Review created successfully',
            'data' => new ReviewResource($review),
        ], 201);
    }

    public function show($id)
    {
        $review = Review::findOrFail($id);

        return new ReviewResource($review);
    }

    public function update(UpdateReviewRequest $request, $id)
    {
        $review = Review::findOrFail($id);

        $review->update($request->validated());

        return response()->json([
            'message' => 'Review updated successfully',
            'data' => new ReviewResource($review),
        ]);
    }

    public function destroy($id)
    {
        $review = Review::findOrFail($id);

        $review->delete();

        return response()->json([
            'message' => 'Review deleted successfully',
        ]);
    }
}