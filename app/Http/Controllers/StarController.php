<?php

namespace App\Http\Controllers;

use App\Models\Star;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StarController extends Controller
{
    public function getAllUserStars(Request $request): JsonResponse
    {
        $user_id = $request->user_id;

        $stars = Star::query()
        ->where('user_id', $user_id)
        ->with(['article' => function ($query) {
            $query
            ->select('id', 'title', 'slug', 'description', 'image_cover', 'status', 'visit_count', 'created_at', 'author_id')
            ->with('stars')
            ->with('reposts')
            ->with('categories');

        }])
        ->get();

        return response()->json(['data' => $stars]);
    }
}
