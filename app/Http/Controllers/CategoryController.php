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

    public function store(Request $request): JsonResponse
    {

        $label = $request->input('label');

        if ($label == null || $label == "" || strlen($label) == 0 || strlen($label) > 20) {
            return response()->json(['errors' => ['label' => 'label is required and must be between 1 and 20 characters long.']], 400);
        }

        $category = Category::query()->where('label', $label)->first();

        if ($category != null) {
            return response()->json(['errors' => ['label' => 'label already exists.']], 409);
        }

        $category = new Category();
        $category->label = $request->input('label');
        $category->save();

        return response()->json($category, 201);
    }
}
