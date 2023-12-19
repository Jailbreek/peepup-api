<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Identity;
use App\Models\Repost;
use App\Models\Star;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ArticleController extends Controller
{

    /*
     * Get search articles
     *
     * @return /Illuminate/Http/JsonResponse
     * @return /App/Models/Article
     * */
    public function searchArticles()
    {
        $sort = request()->query('sort');
        $slug = request()->query('slug');
        $title = request()->query('title');
        $status = request()->query('status');
        $categories = request()->query('categories');
        $categoriesArray = explode(',', $categories);

        // Trim each category to remove leading and trailing whitespaces
        $categoriesArray = array_map('trim', $categoriesArray);

        $articles = Article::query()->where('status', '=', 'published')
            ->with("stars")
            ->with("reposts")
            ->with("categories")
            ->when(
                $title,
                function ($query, $title) {
                    return $query->where('title', 'like', '%' . $title . '%');
                }
            )
            ->when(
                $status,
                function ($query, $status) {
                    return $query->where('status', $status);
                }
            )
            ->when(
                $sort,
                function ($query, $sort) {
                    return $query->orderBy($sort ?? "created_at", $sort);
                }
            )
            ->when(
                $slug,
                function ($query, $slug) {
                    return $query->where('slug', $slug);
                }
            )
            ->when(
                $categoriesArray,
                function ($query, $categoriesArray) {
                    if (is_array($categoriesArray)) {
                        return $query->whereHas(
                            'categories',
                            function ($query) use ($categoriesArray) {
                                $query->whereIn('label', $categoriesArray);
                            }
                        );
                    } else {
                        return $query->whereHas(
                            'categories',
                            function ($query) use ($categoriesArray) {
                                $query->where('label', $categoriesArray);
                            }
                        );
                    }
                }
            )
            ->select('id', 'title', 'slug', 'description', 'image_cover', 'status', 'visit_count', 'created_at')
            ->limit(10)
            ->get();

        if (count($articles) == 0) {
            return response()->json(['data' => []], 200);
        }

        return response()->json(['data' => $articles, 'total' => sizeof($articles)], 200);
    }

    public function getArticles(string $author_id)
    {
        $status = request()->query('status');
        $sort = request()->query('sort');

        $articles = Article::query()->where('author_id', $author_id)
            ->where('status', '!=', 'dumped')
            ->where('status', '!=', 'deleted')
            ->when(
                $status,
                function ($query, $status) {
                    return $query->where('status', $status);
                }
            )
            ->when(
                $sort,
                function ($query, $sort) {
                    return $query->orderBy($sort ?? "created_at", $sort);
                }
            )
            ->get();

        if (count($articles) == 0) {
            return response()->json(['data' => []], 200);
        }

        return response()->json(['data' => $articles, 'total' => sizeof($articles)], 200);
    }

    // Popular articles

    public function getPopularArticles(Request $request)
    {

        $page = $request->query('page', 1);
        $size = $request->query('size', 10);

        $articles = Article::query()->where('status', '=', 'published')
            ->limit(10)
            ->orderBy("visit_count", "desc")
            ->select('id', 'title', 'slug', 'description', 'image_cover', 'status', 'visit_count', 'created_at')
            ->paginate($size, ['*'], 'page', $page);

        if (count($articles) == 0) {
            return response()->json(['data' => []], 200);
        }

        return response()->json(['data' => $articles->items(), 'total' => sizeof($articles)], 200);
    }

    public function searchArticlesBySlug(string $slug)
    {

        if ($slug == null || empty($slug) || $slug == ":slug") {
            return response()->json(
                ['errors' => [
                'status' => 400,
                'title' => 'Bad Request',
                'detail' => 'The id parameter is invalid',
                'source' => [
                    'parameter' => 'id',
                ]]],
                400
            );
        }

        // get articles by id and author_id
        $data = Article::query()->where('slug', $slug)
            ->with('stars')
            ->with('reposts')
            ->with('categories')
            ->where('status', '!=', 'dumped')
            ->where('status', '!=', 'deleted')
            ->select('id', 'title', 'slug', 'description', 'image_cover', 'status', 'visit_count', 'created_at')
            ->first();


        if($data == null) {
            return response()->json(['data' => [ ]], 200);
        }

        return response()->json([ 'data' => $data], 200);
    }

    public function getArticleById(string $author_id, string $id)
    {

        if (($author_id != null && uuid_is_valid($author_id) == false) || $author_id == null) {
            return response()->json(
                ['errors' => [
                'status' => 400,
                'title' => 'Bad Request',
                'detail' => 'The id parameter is invalid',
                'source' => [
                    'parameter' => 'id',
                ]]],
                400
            );
        }

        if (($id != null && uuid_is_valid($id) == false) || $id == null) {
            return response()->json(
                ['errors' => [
                'status' => 400,
                'title' => 'Bad Request',
                'detail' => 'The id parameter is invalid',
                'source' => [
                    'parameter' => 'id',
                ]]],
                400
            );
        }

        // get articles by id and author_id
        $data = Article::query()->where('author_id', $author_id)->where('id', $id)
            ->where('status', '!=', 'dumped')
            ->where('status', '!=', 'deleted')
            ->get();

        if($data == null) {
            return response()->json(['data' => [ ]], 200);
        }

        return response()->json([ 'data' => $data], 200);
    }

    // streamArticleContentBySlug


    public function streamArticleContentBySlug(string $slug)
    {

        /* if (($author_id != null && uuid_is_valid($author_id) == false) || $author_id == null) {
            return response()->json(
                ['errors' => [
                'status' => 400,
                'title' => 'Bad Request',
                'detail' => 'The id parameter is invalid',
                'source' => [
                    'parameter' => 'id',
                ]]],
                400
            );
        } */

        if ($slug == null || empty($slug) || $slug == ":slug") {
            return response()->json(
                ['errors' => [
                'status' => 400,
                'title' => 'Bad Request',
                'detail' => 'Slug ' . $slug . ' is not allowed',
                'source' => [
                    'parameter' => 'id',
                ]]],
                400
            );
        }

        // get articles by id and author_id
        $data = Article::query()->where('slug', $slug)
            ->where('status', '!=', 'dumped')
            ->where('status', '!=', 'deleted')
            ->select('content')
            ->first();

        if($data == null) {
            return response(null, 404);
        }

        $headers = [
               'Content-Type' => 'application/octet-stream',
               'Content-Disposition' => 'attachment; filename=' . $slug . '.html',
           ];

        return response()->json($data->content, 200, $headers);
    }

    public function streamArticleContentById(string $author_id, string $id)
    {

        if (($author_id != null && uuid_is_valid($author_id) == false) || $author_id == null) {
            return response()->json(
                ['errors' => [
                'status' => 400,
                'title' => 'Bad Request',
                'detail' => 'The id parameter is invalid',
                'source' => [
                    'parameter' => 'id',
                ]]],
                400
            );
        }

        if (($id != null && uuid_is_valid($id) == false) || $id == null) {
            return response()->json(
                ['errors' => [
                'status' => 400,
                'title' => 'Bad Request',
                'detail' => 'The id parameter is invalid',
                'source' => [
                    'parameter' => 'id',
                ]]],
                400
            );
        }

        // get articles by id and author_id
        $data = Article::query()->where('author_id', $author_id)->where('id', $id)
            ->where('status', '!=', 'dumped')
            ->where('status', '!=', 'deleted')
            ->select('content')
            ->first();

        if($data == null) {
            return response()->json(['data' => [ ]], 200);
        }

        $headers = [
               'Content-Type' => 'application/octet-stream',
               'Content-Disposition' => 'attachment; filename=your_file_name.html',
           ];

        return response()->json($data->content, 200, $headers);
    }

    public function store(Request $request, string $author_id)
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
            'visit_count' => 'required',
            'reposts_count' => 'required',
            ]
        );

        $parsed['author_id'] = $author_id;

        if (($author_id != null && uuid_is_valid($author_id) == false) || $author_id == null) {
            return response()->json(
                ['errors' => [
                'status' => 400,
                'title' => 'Bad Request',
                'detail' => 'The author_id parameter is invalid',
                'source' => [
                    'parameter' => 'id',
                ]]],
                400
            );
        }

        Article::create($parsed);
        return response()->json(['data' => $request->all()], 201);
    }

    public function updateArticleById(Request $request, string $author_id)
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
                ]]],
                400
            );
        }

        if (($author_id != null && uuid_is_valid($author_id) == false) || $author_id == null) {
            return response()->json(
                ['errors' => [
                'status' => 400,
                'title' => 'Bad Request',
                'detail' => 'The author_id parameter is invalid',
                'source' => [
                    'parameter' => 'id',
                ]]],
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
                'visit_count' => 'required',
                'reposts_count' => 'required',
            ]
        );


        $article = Article::query()->where('author_id', $author_id)->where('id', $id)->first();

        if ($article->status == 'deleted' || $article->status == 'dumped') {
            return response()->json(
                ['errors' => [
                'status' => 400,
                'title' => 'Bad Request',
                'detail' => 'The article with id ' . $id . ' has been deleted',
                'source' => [
                    'parameter' => 'id',
                ]]],
                400
            );
        }

        $request['updated_at'] = now();
        $article->update($request->all());

        return response()->json(['message' => 'The article with id ' . $id . ' has been updated' ], 200);
    }

    public function deleteArticleById(Request $request, string $author_id)
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
                ]]],
                400
            );
        }

        if (($author_id != null && uuid_is_valid($author_id) == false) || $author_id == null) {
            return response()->json(
                ['errors' => [
                'status' => 400,
                'title' => 'Bad Request',
                'detail' => 'The author_id parameter is invalid',
                'source' => [
                    'parameter' => 'id',
                ]]],
                400
            );
        }

        $data = Article::query()->where('author_id', $author_id)->where('id', $id)->first();

        if ($data == null) {
            return response()->json(
                ['erorrs' => [
                'status' => 400,
                'message' => 'The article with id ' . $id . ' does not exist',
                ]],
                400
            );
        }

        $data->update(['status' => 'dumped', 'updated_at' => now()]);
        $data->save();

        return response()->json(['message' => 'The article with id ' . $id . ' has been deleted' ], 200);
    }

    public function restoreArticleById(Request $request, string $author_id)
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
                ]]],
                400
            );
        }

        if (($author_id != null && uuid_is_valid($author_id) == false) || $author_id == null) {
            return response()->json(
                ['errors' => [
                'status' => 400,
                'title' => 'Bad Request',
                'detail' => 'The author_id parameter is invalid',
                'source' => [
                    'parameter' => 'id',
                ]]],
                400
            );
        }

        $data = Article::query()->where('author_id', $author_id)->where('id', $id)->first();

        if ($data == null) {
            return response()->json(
                ['erorrs' => [
                'status' => 400,
                'message' => 'The article with id ' . $id . ' does not exist',
                ]],
                400
            );
        }

        $data->update(['status' => 'published', 'updated_at' => now()]);
        $data->save();
        return response()->json(['message' => 'The article with id ' . $id . ' has been restored' ], 200);
    }

    public function updateArticleStatusById(Request $request, string $author_id)
    {
        $id = $request->query('id');
        $status = $request->input('status');
        $request->validate(['status' => 'required']);


        if ($status == 'deleted' || $status == 'dumped' || $status == null) {
            return response()->json(
                ['errors' => [
                'status' => 400,
                'title' => 'Bad Request',
                'detail' => 'Status ' . implode($status) . ' is not allowed',
                'source' => [
                   'attribute' => 'status',
                   'status_enum' => ['published', 'archived', 'draft']
                ]]],
                400
            );
        }

        if (($id != null && uuid_is_valid($id) == false) || $id == null) {
            return response()->json(
                ['errors' => [
                'status' => 400,
                'title' => 'Bad Request',
                'detail' => 'The id parameter is invalid',
                'source' => [
                    'parameter' => 'id',
                ]]],
                400
            );
        }

        if (($author_id != null && uuid_is_valid($author_id) == false) || $author_id == null) {
            return response()->json(
                ['errors' => [
                'status' => 400,
                'title' => 'Bad Request',
                'detail' => 'The author_id parameter is invalid',
                'source' => [
                    'parameter' => 'id',
                ]]],
                400
            );
        }

        $data = Article::query()->where('author_id', $author_id)
        ->where('id', $id)
        ->where('status', '=', 'published')
        ->first();

        if ($data == null) {
            return response()->json(
                ['erorrs' => [
                'status' => 400,
                'message' => 'The article with id ' . $id . ' does not exist',
                ]],
                400
            );
        }

        $user = Identity::query()->where('id', "472e9998-01d9-4812-a930-95353f548600")->select('id')->first();
        $star = new Star([]);
        $data->update(['status' => $status,'updated_at' => now()]);
        $data->save();
        return response()->json(['message' => 'The status of article with id ' . $id . ' has been updated' ], 200);
    }

    public function trackArticleVisitor(string $slug)
    {
        $data = Article::query()->where('slug', $slug)->first();

        if ($data == null) {
            return response()->json(
                ['erorrs' => [
                'status' => 400,
                'message' => 'The article with slug: ' . $slug . ' does not exist',
                ]],
                400
            );
        }

        if ($data['visit_count'] == null) {
            $data['visit_count'] = 0;
        }

        $data->increment('visit_count');
        $data->save();

        return response()->noContent();
    }

    public function trackArticleStar(string $article_id, string $identity_id)
    {

        if ($identity_id == null) {
            return response()->json(
                ['errors' => [
                'status' => 400,
                'title' => 'Bad Request',
                'detail' => 'The identity_id parameter is invalid',
                'source' => [
                   'attribute' => 'status',
                   'status_enum' => ['published', 'archived', 'draft']
                ]]],
                400
            );
        }

        $data = Article::query()->where('id', $article_id)
            ->where('status', '=', 'published')
            ->first();

        if ($data == null) {
            return response()->json(
                ['erorrs' => [
                'status' => 400,
                'message' => 'The article with id ' . $article_id . ' does not exist' . ' or the article is not published yet',
                ]],
                400
            );
        }

        $users = DB::connection("identity_db_server")
            ->table("identities")
            ->where('id', $identity_id)
            ->where('state', '=', 'active')
            ->select('id')
            ->first();

        $existedStar = Star::query()->where('article_id', $article_id)
            ->where('user_id', $users->id)
            ->first();

        if ($existedStar != null) {
            return response()->json(
                ['erorrs' => [
                'status' => 400,
                'message' => 'The article with id ' . $article_id . ' has been liked by user with id ' . $users->id,
                ]],
                400
            );
        }

        $star = new Star([
            'star_value' => 1,
            'article_id' => $article_id,
            'user_id' => $users->id,
        ]);

        $star->save();
        return response()->noContent();
    }

    public function trackArticleUnstar(string $article_id, string $id)
    {

        if ($id == null) {
            return response()->json(
                ['errors' => [
                'status' => 400,
                'title' => 'Bad Request',
                'detail' => 'The identity_id parameter is invalid',
                'source' => [
                   'attribute' => 'status',
                   'status_enum' => ['published', 'archived', 'draft']
                ]]],
                400
            );
        }

        $data = Article::query()->where('id', $article_id)
            ->with('stars')
            ->where('status', '=', 'published')
            ->first();


        if ($data->stars->isEmpty()) {
            return response()->noContent();
        }

        if ($data->stars == null) {
            return response()->noContent();
        }

        $data->stars->where('user_id', $id)->each->delete();
        $data->save();

        return response()->noContent();
    }

    public function trackArticleReposted(string $article_id, string $identity_id)
    {

        if ($identity_id == null) {
            return response()->json(
                ['errors' => [
                'status' => 400,
                'title' => 'Bad Request',
                'detail' => 'The identity_id parameter is invalid',
                'source' => [
                   'attribute' => 'status',
                   'status_enum' => ['published', 'archived', 'draft']
                ]]],
                400
            );
        }

        $data = Article::query()->where('id', $article_id)
            ->where('status', '=', 'published')
            ->first();

        if ($data == null) {
            return response()->json(
                ['erorrs' => [
                'status' => 400,
                'message' => 'The article with id ' . $article_id . ' does not exist' . ' or the article is not published yet',
                ]],
                400
            );
        }

        $users = DB::connection("identity_db_server")
            ->table("identities")
            ->where('id', $identity_id)
            ->where('state', '=', 'active')
            ->select('id')
            ->first();

        $alreadyReposted = Repost::query()->where('article_id', $article_id)
            ->where('user_id', $users->id)
            ->first();

        if ($alreadyReposted != null) {
            return response()->noContent();
        }

        $repost = new Repost([
            'article_id' => $article_id,
            'user_id' => $users->id,
        ]);

        $repost->save();
        return response()->noContent();
    }

    public function trackArticleUnreposted(string $article_id, string $id)
    {

        if ($id == null) {
            return response()->json(
                ['errors' => [
                'status' => 400,
                'title' => 'Bad Request',
                'detail' => 'The identity_id parameter is invalid',
                'source' => [
                   'attribute' => 'status',
                   'status_enum' => ['published', 'archived', 'draft']
                ]]],
                400
            );
        }

        $data = Article::query()->where('id', $article_id)
            ->where('status', '=', 'published')
            ->first();

        if ($data == null) {
            return response()->noContent();
        }

        $repost = Repost::query()->where('article_id', $article_id)
            ->where('id', $id)
            ->first();

        if ($repost == null) {
            return response()->noContent();
        }

        $repost->delete();
        return response()->noContent();
    }
}
