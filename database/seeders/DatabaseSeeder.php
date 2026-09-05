<?php

namespace Database\Seeders;

use App\Models\ToolPage;
use App\Models\User;
use App\Support\Province;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $email = env('ADMIN_EMAIL');
        $password = env('ADMIN_PASSWORD');

        if ($email && $password) {
            User::query()->updateOrCreate(
                ['email' => $email],
                [
                    'name' => 'Admin',
                    'password' => Hash::make($password),
                    'is_admin' => true,
                ],
            );
        }

        ToolPage::query()->updateOrCreate(
            ['slug' => 'canada-paycheck-calculator'],
            [
                'name' => 'Canadian Paycheck Calculator',
                'category' => 'payroll',
                'calculator_key' => 'paycheck',
                'title' => 'Canada Paycheck Calculator',
                'meta_title' => 'Canada Paycheck Calculator — Take-home pay',
                'meta_description' => 'Estimate Canadian take-home pay after income tax, CPP or QPP, and EI.',
                'intro_content' => 'Calculate your estimated Canadian take-home pay by salary, province, and pay frequency.',
                'is_published' => true,
                'is_indexable' => true,
            ],
        );

        foreach (Province::all() as $province) {
            ToolPage::query()->updateOrCreate(
                ['slug' => $province->slug().'-paycheck-calculator'],
                [
                    'name' => $province->name().' Paycheck Calculator',
                    'category' => 'payroll',
                    'calculator_key' => 'paycheck',
                    'title' => $province->name().' Paycheck Calculator',
                    'meta_title' => $province->name().' Paycheck Calculator — Take-home pay',
                    'meta_description' => 'Estimate '.$province->name().' take-home pay after federal tax, provincial or territorial tax, CPP or QPP, and EI.',
                    'is_published' => true,
                    'is_indexable' => true,
                ],
            );
        }
    }
}
