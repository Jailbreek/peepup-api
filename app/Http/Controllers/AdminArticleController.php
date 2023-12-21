<?php

namespace App\Http\Controllers;

use App\Models\Article;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminArticleController extends Controller
{
    public function getArticles(Request $request): JsonResponse
    {
        $page = $request->query('page', 1); // default to page 1 if not provided
        $size = $request->query('size', 10);

        $articles = Article::with('categories')->with("stars")->paginate($size, ['*'], 'page', $page);


        return response()->json($articles, 200);

    }


    public function getArticlesPreview(Request $request): JsonResponse
    {

        $page = $request->query('page', 1); // default to page 1 if not provided
        $size = $request->query('size', 10);

        $articles = Article::query()
            ->with("categories")
            ->with("stars")
            ->with("reposts")
            ->withCount('reposts')
            ->where('status', '=', 'published')
            ->orderBy("visit_count", "desc")
            ->select('id', 'title', 'slug', 'description', 'image_cover', 'author_id', 'created_at', "visit_count")
            ->paginate($size, ['*'], 'page', $page);

        if (count($articles) == 0) {
            return response()->json(['data' => []], 200);
        }


        return response()->json(['data' => $articles->items(), "nextCursor" => $articles->nextPageUrl(), 'total' => sizeof($articles)], 200);
    }

    public function getArticlesByAuthorId(string $author_id): JsonResponse
    {
        $articles = Article::where('author_id', $author_id)->get();

        if (count($articles) == 0) {
            return response()->json(['data' => []], 200);
        }

        return response()->json(['data' => $articles, 'total' => sizeof($articles)], 200);
    }

    public function getArticleById(string $id): JsonResponse
    {
        if (($id != null && uuid_is_valid($id) == false) || $id == null) {
            return response()->json(
                ['errors' => [
                    'status' => 400,
                    'title' => 'Bad Request',
                    'detail' => 'The id parameter is invalid',
                    'source' => [
                        'parameter' => 'id',
                    ]
                ]],
                400
            );
        }

        $data =  Article::find($id);

        if ($data == null) {
            return response()->json(['data' => []], 200);
        }


        return response()->json(['data' => $data], 200);
    }

    public function store(Request $request)
    {
        $parsed = $request->validate(
            [
                'title' => 'required',
                'slug' => 'required',
                'description' => 'required',
                'content' => 'required',
                'image_cover' => 'required',
                'categories' => 'required',
                'status' => 'required',
                'likes_count' => 'required',
                'visits_count' => 'required',
                'reposts_count' => 'required',
                'author_id' => 'required',
            ]
        );

        Article::create($request->all());
        return response()->json(['data' => $request->all()], 201);
    }

    public function updateArticleById(Request $request): JsonResponse
    {
        $id = $request->query('id');

        if ($id != null && uuid_is_valid($id) == false) {
            return response()->json(
                ['errors' => [
                    'status' => 400,
                    'title' => 'Bad Request',
                    'detail' => 'The id parameter is invalid',
                    'source' => [
                        'parameter' => 'id',
                    ]
                ]],
                400
            );
        }

        $request->validate(
            [
                'title' => 'required',
                'slug' => 'required',
                'description' => 'required',
                'content' => 'required',
                'image_cover' => 'required',
                'categories' => 'required',
                'status' => 'required',
                'likes_count' => 'required',
                'visits_count' => 'required',
                'reposts_count' => 'required',
                'author_id' => 'required',
            ]
        );


        $article = Article::findOrFail($id);
        $article->update($request->all());

        return response()->json(['message' => 'The article with id ' . $id . ' has been updated'], 200);
    }

    public function deleteArticleById(Request $request): JsonResponse
    {
        $id = $request->query('id');

        if (($id != null && uuid_is_valid($id) == false) || $id == null) {
            return response()->json(
                ['errors' => [
                    'status' => 400,
                    'title' => 'Bad Request',
                    'detail' => 'The id parameter is invalid',
                    'source' => [
                        'parameter' => 'id',
                    ]
                ]],
                400
            );
        }

        $data =  Article::find($id);

        if ($data == null) {
            return response()->json(
                ['erorrs' => [
                    'status' => 400,
                    'message' => 'The article with id ' . $id . ' does not exist',
                ]],
                400
            );
        }

        $data->delete();
        return response()->json(['message' => 'The article with id ' . $id . ' has been deleted'], 200);
    }
}
