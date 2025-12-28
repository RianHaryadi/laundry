<?php

namespace Database\Seeders;

use App\Models\ExpenseCategory;
use Illuminate\Database\Seeder;

class ExpenseCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Supplies', 'icon' => 'heroicon-o-cube', 'color' => 'blue'],
            ['name' => 'Maintenance', 'icon' => 'heroicon-o-wrench', 'color' => 'orange'],
            ['name' => 'Utilities', 'icon' => 'heroicon-o-bolt', 'color' => 'yellow'],
            ['name' => 'Salaries', 'icon' => 'heroicon-o-users', 'color' => 'green'],
            ['name' => 'Rent', 'icon' => 'heroicon-o-home', 'color' => 'purple'],
            ['name' => 'Marketing', 'icon' => 'heroicon-o-megaphone', 'color' => 'pink'],
            ['name' => 'Transportation', 'icon' => 'heroicon-o-truck', 'color' => 'indigo'],
            ['name' => 'Other', 'icon' => 'heroicon-o-ellipsis-horizontal', 'color' => 'gray'],
        ];

        foreach ($categories as $category) {
            ExpenseCategory::create($category);
        }
    }
}