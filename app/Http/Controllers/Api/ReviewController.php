<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreReviewRequest;
use App\Http\Requests\UpdateReviewRequest;
use App\Http\Resources\ReviewResource;
use App\Models\Review;

class ReviewController extends Controller
{
    public function index()
    {
        $reviews = Review::all();

        return response()->json([
            'data' => ReviewResource::collection($reviews),
        ], 200);
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
        $review = Review::find($id);

        if (!$review) {
            return response()->json([
                'message' => 'Review not found',
            ], 404);
        }

        return response()->json([
            'data' => new ReviewResource($review),
        ], 200);
    }

    public function update(UpdateReviewRequest $request, $id)
    {
        $review = Review::find($id);

        if (!$review) {
            return response()->json([
                'message' => 'Review not found',
            ], 404);
        }

        $review->update($request->validated());

        return response()->json([
            'message' => 'Review updated successfully',
            'data' => new ReviewResource($review),
        ], 200);
    }

    public function destroy($id)
    {
        $review = Review::find($id);

        if (!$review) {
            return response()->json([
                'message' => 'Review not found',
            ], 404);
        }

        $review->delete();

        return response()->json([
            'message' => 'Review deleted successfully',
        ], 200);
    }

    public function showByBookingId($bookingId)
    {
        $reviews = Review::where('service_booking_id', $bookingId)->get();

        if ($reviews->isEmpty()) {
            return response()->json([
                'message' => 'No reviews found for this booking',
            ], 404);
        }

        return response()->json([
            'data' => ReviewResource::collection($reviews),
        ], 200);
    }

    public function showByUserId($userId)
    {
        $reviews = Review::where('user_id', $userId)->get();

        if ($reviews->isEmpty()) {
            return response()->json([
                'message' => 'No reviews found for this user',
            ], 404);
        }

        return response()->json([
            'data' => ReviewResource::collection($reviews),
        ], 200);
    }
}