<?php

use App\Http\Controllers\AdminArticleController;
use App\Http\Controllers\ArticleController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\RepostController;
use App\Http\Controllers\StarController;
use Illuminate\Support\Facades\Route;

Route::name('admin_posts_articles')->prefix("admin")->group(function () {
    // To-do: Add Authorization middleware to all routes in this group
    Route::name("admin_crud_articles")->prefix("posts")->group(function () {
        Route::get('/articles', [AdminArticleController::class, "getArticles"])->name('admin_get_articles');
        Route::get('/articles/preview', [AdminArticleController::class, "getArticlesPreview"])->name('admin_get_articles_preview');
        Route::get('/{author_id}/articles', [AdminArticleController::class, "getArticlesByAuthorId"])->name('admin_get_articles');
        Route::get('/articles/{id}', [AdminArticleController::class, "getArticleById"])->name('admin_get_article_by_id');
        Route::put('/articles', [AdminArticleController::class, "updateArticleById"])->name('admin_update_article');
        Route::post('/articles', [AdminArticleController::class, "store"])->name('admin_store_article');
        Route::delete('/articles', [AdminArticleController::class, "deleteArticleById"])->name('admin_delete_article');
    });
});

Route::name('posts_articles')->prefix("posts")->group(function () {
    // To-do: Add Authorization middleware to all routes in this group
    Route::name("crud_articles")->group(
        function () {
            Route::get('articles/search', [ArticleController::class, "searchArticles"])->name('search_articles');
            Route::get('articles/popular', [ArticleController::class, "getPopularArticles"])->name('get_popular_articles');
            Route::get('{author_id}/articles', [ArticleController::class, "getArticles"])->name('get_articles');
            Route::get('{author_id}/articles/{id}', [ArticleController::class, "getArticleById"])->name('get_article_by_id');
            Route::get('articles/{slug}', [ArticleController::class, "searchArticlesBySlug"])->name('search_articles_by_slug');
            Route::get('articles/{slug}/content', [ArticleController::class, "streamArticleContentBySlug"])->name('stream_article_content_by_slug');
            // Route::get('{author_id}/articles/{slug}/content', [ArticleController::class, "streamArticleContentBySlug"])->name('stream_article_content_by_slug');
            Route::put('{author_id}/articles', [ArticleController::class, "updateArticleById"])->name('update_article');
            Route::post('{author_id}/articles', [ArticleController::class, "store"])->name('store_article');
            Route::delete('{author_id}/articles', [ArticleController::class, "deleteArticleById"])->name('delete_article');
        }
    );

    Route::name("crud_articles_management")->group(function () {
        Route::put('{author_id}/articles/restores', [ArticleController::class, "restoreArticleById"])->name('restore_article');
        Route::put('{author_id}/articles/status', [ArticleController::class, "updateArticleStatusById"])->name('update_article_status');
        Route::post('articles/{article_id}/stars/{identity_id}', [ArticleController::class, "trackArticleStar"])->name('set_track_article_star');
        Route::post('articles/{article_id}/reposts/{identity_id}', [ArticleController::class, "trackArticleReposted"])->name('set_track_article_repost');
        Route::post('articles/{article_id}/unstars/{id}', [ArticleController::class, "trackArticleUnstar"])->name('set_track_article_unstar');
        Route::post('articles/{article_id}/unreposts/{id}', [ArticleController::class, "trackArticleUnreposted"])->name('set_track_article_unreposted');
        Route::post('articles/{slug}/visit', [ArticleController::class, "trackArticleVisitor"])->name('set_track_article_visitor');
    });

});

// categories posts related routes
Route::name("posts_categories")->prefix('posts')->group(function () {
    Route::get('categories', [CategoryController::class, "getCategories"])->name('get_categories_paginate');
});


// posts stars related routes
Route::name("posts_reposts")->prefix('posts')->group(function () {
    Route::get('stars/{user_id}', [StarController::class, "getAllUserStars"])->name('get_all_user_stars');
});

// posts stars related routes
Route::name("posts_stars")->prefix('posts')->group(function () {
    Route::get('reposts/{user_id}', [RepostController::class, "getAllUserReposts"])->name('get_all_user_stars');
});

