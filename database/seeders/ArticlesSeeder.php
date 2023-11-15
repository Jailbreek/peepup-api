<?php

namespace Database\Seeders;

use App\Models\Article;
use Illuminate\Database\Seeder;

class ArticlesSeeder extends Seeder {

    public function run(): void {
        Article::truncate();
        $faker = \Faker\Factory::create();
        $dummy = new Article([
                'title' => $faker->sentence(2),
                'slug' => $faker->slug,
                'description' => $faker->paragraph(1),
                'content' => $faker->paragraph(7),
                'image' => $faker->imageUrl(),
                'categories' => $faker->numberBetween(1, 3),
                'status' => 'published',
                'like_count' => $faker->numberBetween(0, 100),
                'click_count' => $faker->numberBetween(0, 100),
                'repost_count' => $faker->numberBetween(0, 100),
                'author_id' => $faker->uuid,
            ]
        );

        $out = new \Symfony\Component\Console\Output\ConsoleOutput();
        $out->writeln($dummy->toJson());

        for ($i = 0; $i < 3; $i++) {
            Article::create([
                'title' => $faker->sentence(2),
                'slug' => $faker->slug,
                'description' => $faker->paragraph(1),
                'content' => $faker->paragraph(7),
                'image' => $faker->imageUrl(),
                'categories' => $faker->numberBetween(1, 3),
                'status' => 'published',
                'like_count' => $faker->numberBetween(0, 100),
                'click_count' => $faker->numberBetween(0, 100),
                'repost_count' => $faker->numberBetween(0, 100),
                'author_id' => $faker->uuid,
            ]);
        }
    }
}
