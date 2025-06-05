<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create predefined categories for each user
        $categories = [
            'Work',
            'Personal',
            'Health',
            'Finance',
            'Education',
            'Home',
            'Shopping',
            'Family',
        ];

        // Get all users
        $users = User::all();

        foreach ($users as $user) {
            foreach ($categories as $category) {
                Category::create([
                    'name' => $category,
                    'user_id' => $user->id,
                ]);
            }
        }
    }
}
