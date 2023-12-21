<?php

namespace App\Http\Controllers;

use App\Models\Repost;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

class RepostController extends Controller
{
    public function getAllUserReposts(Request $request): JsonResponse
    {
        $user_id = $request->user_id;

        $reposts = Repost::query()->where('user_id', $user_id)
            ->with(['article' => function ($query) {
                $query
                ->select('id', 'title', 'slug', 'description', 'image_cover', 'status', 'visit_count', 'created_at', 'author_id')
                ->with('stars')
                ->with('reposts')
                ->with('categories');

            }])
            ->get();

        return response()->json(['data' => $reposts]);
    }

}
