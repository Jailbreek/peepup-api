<?php

namespace App\Http\Controllers;

use App\Models\Article;
use Illuminate\Http\Request;

class ArticleController extends Controller {

    // search articles query
    public function searchArticles() {
        $status = request()->query('status');
        $title = request()->query('title');
        $sort = request()->query('sort');
        $sortby = request()->query('sort_by');

        // check if status is not equal to dumped or deleted
        // combined with query filter for status
        // combined with query filter for sorting data by asc or desc
        $articles = Article::where('status', '!=', 'dumped')
        ->where('status', '!=', 'deleted')
        ->when($title, function ($query, $title) {
            return $query->where('title', 'like', '%' . $title . '%');
        })
        ->when($status, function ($query, $status) {
            return $query->where('status', $status);
        })
        ->when($sort, function ($query, $sort) {
            return $query->orderBy($sortby ?? "created_at", $sort);
        })
        ->get();

        if (count($articles) == 0) {
            return response()->json(['data' => []], 200);
        }

        return response()->json(['data' => $articles, 'total' => sizeof($articles)], 200);
    }

    public function getArticles(string $author_id) {
        $status = request()->query('status');
        $sort = request()->query('sort');
        $sort_by = request()->query('sort_by');

        // check if status is not equal to dumped or deleted
        // combined with query filter for status
        // combined with query filter for sorting data by asc or desc
        $articles = Article::where('author_id', $author_id)
        ->where('status', '!=', 'dumped')
        ->where('status', '!=', 'deleted')
        ->when($status, function ($query, $status) {
            return $query->where('status', $status);
        })
        ->when($sort, function ($query, $sort) {
            return $query->orderBy($sort_by ?? "created_at", $sort);
        })
        ->get();

        if (count($articles) == 0) {
            return response()->json(['data' => []], 200);
        }

        return response()->json(['data' => $articles, 'total' => sizeof($articles)], 200);
    }

    public function getArticleById(string $author_id, string $id) {

        if (($author_id != null && uuid_is_valid($author_id) == false) || $author_id == null) {
            return response()->json(['errors' => [
                'status' => 400,
                'title' => 'Bad Request',
                'detail' => 'The id parameter is invalid',
                'source' => [
                    'parameter' => 'id',
                ]]], 400);
        }

        if (($id != null && uuid_is_valid($id) == false) || $id == null) {
            return response()->json(['errors' => [
                'status' => 400,
                'title' => 'Bad Request',
                'detail' => 'The id parameter is invalid',
                'source' => [
                    'parameter' => 'id',
                ]]], 400);
        }

        // get articles by id and author_id
        $data = Article::where('author_id', $author_id)->where('id', $id)
        ->where('status', '!=', 'dumped')
        ->where('status', '!=', 'deleted')
        ->get();

        if($data == null) {
            return response()->json(['data' => [ ]], 200);
        }

        return response()->json([ 'data' => $data], 200);
    }

    public function store(Request $request, string $author_id) {
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
        ]);

        $parsed['author_id'] = $author_id;

        if (($author_id != null && uuid_is_valid($author_id) == false) || $author_id == null) {
            return response()->json(['errors' => [
                'status' => 400,
                'title' => 'Bad Request',
                'detail' => 'The author_id parameter is invalid',
                'source' => [
                    'parameter' => 'id',
                ]]], 400);
        }

        Article::create($parsed);
        return response()->json(['data' => $request->all()], 201);
    }

    public function updateArticleById(Request $request, string $author_id) {
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

        if (($author_id != null && uuid_is_valid($author_id) == false) || $author_id == null) {
            return response()->json(['errors' => [
                'status' => 400,
                'title' => 'Bad Request',
                'detail' => 'The author_id parameter is invalid',
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
            ]
        );


        $article = Article::where('author_id', $author_id)->where('id', $id)->first();

        if ($article->status == 'deleted' || $article->status == 'dumped') {
            return response()->json(['errors' => [
                'status' => 400,
                'title' => 'Bad Request',
                'detail' => 'The article with id ' . $id . ' has been deleted',
                'source' => [
                    'parameter' => 'id',
                ]]], 400);
        }

        $reuest['updated_at'] = now();
        $article->update($request->all());

        return response()->json(['message' => 'The article with id ' . $id . ' has been updated' ], 200);
    }

    public function deleteArticleById(Request $request, string $author_id) {
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

        if (($author_id != null && uuid_is_valid($author_id) == false) || $author_id == null) {
            return response()->json(['errors' => [
                'status' => 400,
                'title' => 'Bad Request',
                'detail' => 'The author_id parameter is invalid',
                'source' => [
                    'parameter' => 'id',
                ]]], 400);
        }

        $data = Article::where('author_id', $author_id)->where('id', $id)->first();

        if ($data == null) {
            return response()->json(['erorrs' => [
                'status' => 400,
                'message' => 'The article with id ' . $id . ' does not exist',
            ]], 400);
        }

        $data->update(['status' => 'dumped', 'updated_at' => now()]);
        $data->save();

        return response()->json(['message' => 'The article with id ' . $id . ' has been deleted' ], 200);
    }


    public function restoreArticleById(Request $request, string $author_id) {
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

        if (($author_id != null && uuid_is_valid($author_id) == false) || $author_id == null) {
            return response()->json(['errors' => [
                'status' => 400,
                'title' => 'Bad Request',
                'detail' => 'The author_id parameter is invalid',
                'source' => [
                    'parameter' => 'id',
                ]]], 400);
        }

        $data = Article::where('author_id', $author_id)->where('id', $id)->first();

        if ($data == null) {
            return response()->json(['erorrs' => [
                'status' => 400,
                'message' => 'The article with id ' . $id . ' does not exist',
            ]], 400);
        }

        $data->update(['status' => 'published', 'updated_at' => now()]);
        $data->save();
        return response()->json(['message' => 'The article with id ' . $id . ' has been restored' ], 200);
    }


    public function updateArticleStatusById(Request $request, string $author_id) {
        $id = $request->query('id');
        $status = $request->input('status');
        $request->validate(['status' => 'required']);


         if ($status == 'deleted' || $status == 'dumped' || $status == null) {
            return response()->json(['errors' => [
                'status' => 400,
                'title' => 'Bad Request',
                'detail' => 'Status ' . implode($status) . ' is not allowed',
                'source' => [
                    'attribute' => 'status',
                    'status_enum' => ['published', 'archived', 'draft']
                ]]], 400);
        }

        if (($id != null && uuid_is_valid($id) == false) || $id == null) {
            return response()->json(['errors' => [
                'status' => 400,
                'title' => 'Bad Request',
                'detail' => 'The id parameter is invalid',
                'source' => [
                    'parameter' => 'id',
                ]]], 400);
        }

        if (($author_id != null && uuid_is_valid($author_id) == false) || $author_id == null) {
            return response()->json(['errors' => [
                'status' => 400,
                'title' => 'Bad Request',
                'detail' => 'The author_id parameter is invalid',
                'source' => [
                    'parameter' => 'id',
                ]]], 400);
        }

        $data = Article::where('author_id', $author_id)->where('id', $id)->first();

        if ($data == null) {
            return response()->json(['erorrs' => [
                'status' => 400,
                'message' => 'The article with id ' . $id . ' does not exist',
            ]], 400);
        }

        $data->update(['status' => $status,'updated_at' => now()]);
        $data->save();
        return response()->json(['message' => 'The status of article with id ' . $id . ' has been updated' ], 200);
    }


    public function articlesLike(Request $request, string $article_id) {
        $operator = $request->query('o');

         if ($operator == null) {
            return response()->json(['errors' => [
                'status' => 400,
                'title' => 'Bad Request',
                'detail' => 'Operator ' . $operator . ' is not allowed',
                'source' => [
                    'attribute' => 'status',
                    'status_enum' => ['published', 'archived', 'draft']
                ]]], 400);
        }


        $data = Article::where('id', $article_id)->first();

        if ($data == null) {
            return response()->json(['erorrs' => [
                'status' => 400,
                'message' => 'The article with id ' . $article_id . ' does not exist',
            ]], 400);
        }


        if ($operator == "incr") {
            $data->increment('like_count');
        } else {
            if ($data['like_count'] == null) {
                $data['like_count'] = 0;
                return;
            }

            $data->decrement('like_count');
        }

        $data->save();

        return;
    }
}
