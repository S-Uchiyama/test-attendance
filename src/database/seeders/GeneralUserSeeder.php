<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class GeneralUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $users = [
            ['name' => '山田太郎','email' => 'user1@example.com'],
            ['name' => '佐藤花子','email' => 'user2@example.com'],
            ['name' => '鈴木一郎','email' => 'user3@example.com'],
        ];

        foreach($users as $user){
            \App\Models\User::updateOrCreate(
                ['email' => $user['email']],
                [
                    'name' => $user['name'],
                    'password' => \Illuminate\Support\Facades\Hash::make('password123'),
                    'role' => 'user',
                    'email_verified_at' => now(),
                ]
            );
        }
    }
}
