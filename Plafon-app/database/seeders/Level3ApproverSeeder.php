<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class Level3ApproverSeeder extends Seeder
{
    public function run()
    {
        $approvers = [
            [
                'name' => 'Fairin',
                'email' => 'fairin@company.com',
                'approver_name' => 'Fairin'
            ],
            [
                'name' => 'Vita',
                'email' => 'vita@company.com',
                'approver_name' => 'Vita'
            ],
            [
                'name' => 'Diana',
                'email' => 'diana@company.com',
                'approver_name' => 'Diana'
            ],
            [
                'name' => 'Direktur',
                'email' => 'direktur@company.com',
                'approver_name' => 'Direktur'
            ],
        ];

        foreach ($approvers as $approver) {
            User::updateOrCreate(
                ['email' => $approver['email']],
                [
                    'name' => $approver['name'],
                    'password' => Hash::make('password123'),
                    'role' => 'approver3',
                    'is_level3_approver' => true,
                    'approver_name' => $approver['approver_name']
                ]
            );
        }
    }
}