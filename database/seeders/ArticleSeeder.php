<?php

namespace Database\Seeders;

use App\Models\Article;
use Illuminate\Database\Seeder;

class ArticleSeeder extends Seeder
{
    public function run(): void
    {

        Article::truncate();
        $faker = \Faker\Factory::create();

        $md =  <<<'HEREA'
            # Next.js Pages Iphp artisan make:seeder UserSeedern Next.js, a **page** is a [React Component](https://reactjs.org/docs/components-and-props.html) exported from a `.js`, `.jsx`, `.ts`, or `.tsx` file in the `pages` directory. Each page is associated with a route based on its file name.
        HEREA;

        for ($i = 0; $i < 20; $i++) {
            $categories_id = range(1, 10);
            shuffle($categories_id);
            $selectedCategories = array_slice($categories_id, 0, 2);

            $article = Article::create(
                [
                    'title' => $faker->words(10, true),
                    'slug' => $faker->slug,
                    'description' => $faker->words(20, true),
                    'content' => $md,
                    'image_cover' => $faker->imageUrl(),
                    'status' => 'published',
                    'visit_count' => 0,
                    'author_id' => $faker->uuid,
                ]
            );

            $article->categories()->attach($selectedCategories);
        }
    }
}
