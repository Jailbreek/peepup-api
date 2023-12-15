<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller {
    /**
     * Get paginated categories.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCategories(Request $request) {
        $page = $request->query('page', 1);
        $size = $request->query('size', 10);

        $categories = Category::query()->paginate($size, ['*'], 'page', $page);

        return response()->json($categories, 200);
    }
}
