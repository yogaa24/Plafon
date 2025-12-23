<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        // ==========================================
        // APPROVERS
        // ==========================================
        
        // Approver 1
        User::updateOrCreate(
            ['email' => 'approver1@kiu.com'],
            [
                'name' => 'Koor SC',
                'password' => Hash::make('karisma'),
                'role' => 'approver1',
            ]
        );

        // ==========================================
        // APPROVER 2 - TC (5 Users)
        // ==========================================
        $tcUsers = [
            ['email' => 'tc1@kiu.com', 'name' => 'TC - Zahra'],
            ['email' => 'tc2@kiu.com', 'name' => 'TC - Farida'],
            ['email' => 'tc3@kiu.com', 'name' => 'TC - Tiya'],
            ['email' => 'tc4@kiu.com', 'name' => 'TC - Novita'],
            ['email' => 'tc5@kiu.com', 'name' => 'TC - Natasya'],
            ['email' => 'tc6@kiu.com', 'name' => 'TC - Hendra'],
        ];

        foreach ($tcUsers as $tc) {
            User::updateOrCreate(
                ['email' => $tc['email']],
                [
                    'name' => $tc['name'],
                    'password' => Hash::make('karisma'),
                    'role' => 'approver2',
                ]
            );
        }

        // Approver 3
        User::updateOrCreate(
            ['email' => 'approver3@kiu.com'],
            [
                'name' => 'Kabag KEU',
                'password' => Hash::make('karisma'),
                'role' => 'approver3',
            ]
        );

        // Approver 4
        User::updateOrCreate(
            ['email' => 'approver4@kiu.com'],
            [
                'name' => 'Kadep KEU',
                'password' => Hash::make('karisma'),
                'role' => 'approver4',
            ]
        );

        // Approver 5
        User::updateOrCreate(
            ['email' => 'approver5@kiu.com'],
            [
                'name' => 'Kadep KEU & HRD',
                'password' => Hash::make('karisma'),
                'role' => 'approver5',
            ]
        );

        // Approver 6
        User::updateOrCreate(
            ['email' => 'approver6@kiu.com'],
            [
                'name' => 'Direksi',
                'password' => Hash::make('karisma'),
                'role' => 'approver6',
            ]
        );

        // ==========================================
        // VIEWER
        // ==========================================
        User::updateOrCreate(
            ['email' => 'sukma@kiu.com'],
            [
                'name' => 'Sukma',
                'password' => Hash::make('karisma'),
                'role' => 'viewer',
            ]
        );

        // ==========================================
        // SALES
        // ==========================================
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
            $email = strtolower(str_replace(' ', '', $name)) . '@kiu.com';

            User::updateOrCreate(
                ['email' => $email],
                [
                    'name' => $name,
                    'role' => 'sales',
                    'password' => Hash::make('karisma'),
                ]
            );
        }

        $this->command->info('✅ Seeder selesai!');
        $this->command->info('   - Approver 1: 1 user (Koor SC)');
        $this->command->info('   - Approver 2: 5 users (TC)');
        $this->command->info('   - Approver 3-6: masing-masing 1 user');
        $this->command->info('   - Viewer: 1 user');
        $this->command->info('   - Sales: ' . count($salesList) . ' users');
    }
}