<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller {


    public function getCategories(Request $request) {
        $page = $request->query('page', 1); // default to page 1 if not provided
        $size = $request->query('size', 10);

        // get all categories with pagination
        $categories = Category::paginate($size, ['*'], 'page', $page);

        return response()->json($categories, 200);
    }

}
