<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $userId = User::query()->value('id');
        if (!$userId) {
            $userId = User::factory()->create([
                'name' => 'Demo User',
                'email' => 'demo@example.com',
            ])->id;
        }

        $expense = [
            ['name' => 'طعام', 'icon' => 'bi-egg-fried'],
            ['name' => 'تسوق', 'icon' => 'bi-cart2'],
            ['name' => 'فواتير', 'icon' => 'bi-receipt'],
            ['name' => 'ترفيه', 'icon' => 'bi-mic'],
            ['name' => 'هاتف', 'icon' => 'bi-phone'],
            ['name' => 'رياضة', 'icon' => 'bi-activity'],
            ['name' => 'تجميل', 'icon' => 'bi-person-hearts'],
            ['name' => 'تعليم', 'icon' => 'bi-journal-text'],
            ['name' => 'اجتماعي', 'icon' => 'bi-people'],
            ['name' => 'سكن', 'icon' => 'bi-house'],
            ['name' => 'نقل', 'icon' => 'bi-bus-front'],
            ['name' => 'وقود', 'icon' => 'bi-fuel-pump'],
            ['name' => 'قهوة', 'icon' => 'bi-cup-hot'],
            ['name' => 'ترفيه منزلي', 'icon' => 'bi-music-note'],
        ];

        $income = [
            ['name' => 'راتب', 'icon' => 'bi-cash-coin'],
            ['name' => 'مكافأة', 'icon' => 'bi-gift'],
            ['name' => 'استثمار', 'icon' => 'bi-graph-up-arrow'],
            ['name' => 'تحويل', 'icon' => 'bi-arrow-left-right'],
            ['name' => 'عمل حر', 'icon' => 'bi-tools'],
        ];

        $rows = [];
        foreach ($expense as $row) {
            $rows[] = [
                'user_id' => $userId,
                'name' => $row['name'],
                'type' => 'expense',
                'icon' => $row['icon'],
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        foreach ($income as $row) {
            $rows[] = [
                'user_id' => $userId,
                'name' => $row['name'],
                'type' => 'income',
                'icon' => $row['icon'],
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        Category::upsert(
            $rows,
            ['user_id', 'name', 'type'],
            ['icon', 'updated_at']
        );
    }
}
