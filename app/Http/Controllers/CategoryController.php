<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CategoryController extends Controller {


    public function getCategories(Request $request): JsonResponse
    {
        $page = $request->query('page');
        $size = $request->query('size');


        $categories = null;

        $isGetAll = $page === null && $size === null;

        if ($isGetAll) {
            $categories = Category::all();
            return response()->json($categories, 200);
        }

        $categories = Category::query()->paginate($size, ['*'], 'page', $page);

        return response()->json($categories, 200);
    }
}
