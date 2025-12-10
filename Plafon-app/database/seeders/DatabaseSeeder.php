<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        // Approver 1
        User::create([
            'name' => 'Approver 1',
            'email' => 'approver1@demo.com',
            'password' => Hash::make('karisma'),
            'role' => 'approver1'
        ]);

        // Approver 2
        User::create([
            'name' => 'Approver 2',
            'email' => 'approver2@demo.com',
            'password' => Hash::make('karisma'),
            'role' => 'approver2'
        ]);

        // Viewer
        User::create([
            'name' => 'Nila',
            'email' => 'nila@gmail.com',
            'password' => Hash::make('karisma'),
            'role' => 'viewer'
        ]);

        /**
         * Tambahan Daftar Sales Baru
         */
        $salesList = [
            'Zakia',
            'Reni',
            'Yuyun',
            'Faris',
            'Ariyani',
            'Hendra',
            'Sheila',
            'Others',
            'general',
        ];

        foreach ($salesList as $name) {
            $email = strtolower(str_replace(' ', '', $name)) . '@gmail.com';

            User::updateOrCreate(
                ['email' => $email],
                [
                    'name' => $name,
                    'role' => 'sales',
                    'password' => Hash::make('karisma'),
                    'is_level3_approver' => false,
                    'approver_name' => null,
                ]
            );
        }
    }
}
