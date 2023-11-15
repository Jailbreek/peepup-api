<?php

use App\Http\Controllers\AdminArticleController;
use App\Http\Controllers\ArticleController;
use Illuminate\Support\Facades\Route;


Route::name('admin_posts_articles')->prefix("admin")->group(function() {
    // To-do: Add Authorization middleware to all routes in this group
    Route::name("admin_crud_articles")->prefix("posts")->group(function() {
        Route::get('/articles', [AdminArticleController::class, "getArticles"])->name('admin_get_articles');
        Route::get('/{author_id}/articles', [AdminArticleController::class, "getArticlesByAuthorId"])->name('get_articles');
        Route::get('/articles/{id}', [AdminArticleController::class, "getArticleById"])->name('get_article_by_id');
        Route::put('/articles', [AdminArticleController::class, "updateArticleById"])->name('update_article');
        Route::post('/articles', [AdminArticleController::class, "store"])->name('store_article');
        Route::delete('/articles', [AdminArticleController::class, "deleteArticleById"])->name('delete_article');
    });
});

Route::name('posts_articles')->prefix("posts")->group(function() {
    // To-do: Add Authorization middleware to all routes in this group
    Route::name("crud_articles")->group( function () {
            Route::get('{author_id}/articles', [ArticleController::class, "getArticles"])->name('get_articles');
            Route::get('{author_id}/articles/{id}', [ArticleController::class, "getArticleById"])->name('get_article_by_id');
            Route::put('{author_id}/articles', [ArticleController::class, "updateArticleById"])->name('update_article');
            Route::post('{author_id}/articles', [ArticleController::class, "store"])->name('store_article');
            Route::delete('{author_id}/articles', [ArticleController::class, "deleteArticleById"])->name('delete_article');
        }
    );
});
