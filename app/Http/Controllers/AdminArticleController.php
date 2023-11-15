<?php

namespace App\Http\Controllers;

use App\Models\Article;
use Illuminate\Http\Request;

class AdminArticleController extends Controller {
    public function getArticles() {
        $articles = Article::all();

        if (count($articles) == 0) {
            return response()->json(['data' => []], 200);
        }

        return response()->json(['data' => $articles, 'total' => sizeof($articles)], 200);
    }

    public function getArticlesByAuthorId(string $author_id) {
        $articles = Article::where('author_id', $author_id)->get();

        if (count($articles) == 0) {
            return response()->json(['data' => []], 200);
        }

        return response()->json(['data' => $articles, 'total' => sizeof($articles)], 200);
    }

    public function getArticleById(string $id) {
        if (($id != null && uuid_is_valid($id) == false) || $id == null) {
            return response()->json(['errors' => [
                'status' => 400,
                'title' => 'Bad Request',
                'detail' => 'The id parameter is invalid',
                'source' => [
                    'parameter' => 'id',
                ]]], 400);
        }

        $data =  Article::find($id);

        if($data == null) {
            return response()->json(['data' => [ ]], 200);
        }

        return response()->json([ 'data' => $data], 200);
    }

    public function store(Request $request) {
        $parsed = $request->validate([
            'title' => 'required',
            'slug' => 'required',
            'description' => 'required',
            'content' => 'required',
            'image' => 'required',
            'categories' => 'required',
            'status' => 'required',
            'like_count' => 'required',
            'click_count' => 'required',
            'repost_count' => 'required',
            'author_id' => 'required',
        ]);

        Article::create($parsed);

        return response()->json(['data' => $request->all()], 201);
    }

    public function updateArticleById(Request $request) {
        $id = $request->query('id');

        if ($id != null && uuid_is_valid($id) == false) {
            return response()->json(['errors' => [
                'status' => 400,
                'title' => 'Bad Request',
                'detail' => 'The id parameter is invalid',
                'source' => [
                    'parameter' => 'id',
                ]]], 400);
        }

        $request->validate(
            [
                'title' => 'required',
                'slug' => 'required',
                'description' => 'required',
                'content' => 'required',
                'image' => 'required',
                'categories' => 'required',
                'status' => 'required',
                'like_count' => 'required',
                'click_count' => 'required',
                'repost_count' => 'required',
                'author_id' => 'required',
            ]
        );


        $article = Article::findOrFail($id);
        $article->update($request->all());

        return response()->json(['message' => 'The article with id ' . $id . ' has been updated' ], 200);
    }

    public function deleteArticleById(Request $request) {
        $id = $request->query('id');

        if (($id != null && uuid_is_valid($id) == false) || $id == null) {
            return response()->json(['errors' => [
                'status' => 400,
                'title' => 'Bad Request',
                'detail' => 'The id parameter is invalid',
                'source' => [
                    'parameter' => 'id',
                ]]], 400);
        }

        $data =  Article::find($id);

        if ($data == null) {
            return response()->json(['erorrs' => [
                'status' => 400,
                'message' => 'The article with id ' . $id . ' does not exist',
            ]], 400);
        }

        $data->delete();
        return response()->json(['message' => 'The article with id ' . $id . ' has been deleted' ], 200);
    }
}
