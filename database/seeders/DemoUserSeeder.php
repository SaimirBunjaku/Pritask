<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoUserSeeder extends Seeder
{
    public function run(): void
    {
        $legacyOwner = User::where('email', 'alex@example.com')->first();

        if ($legacyOwner && ! User::where('email', 'pritech@example.com')->whereKeyNot($legacyOwner->id)->exists()) {
            $legacyOwner->update([
                'name' => 'Pritech Owner',
                'email' => 'pritech@example.com',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]);
        }

        $accounts = [
            ['name' => 'Pritech Owner', 'email' => 'pritech@example.com'],
            ['name' => 'Jordan Dev', 'email' => 'jordan@example.com'],
            ['name' => 'Sam QA', 'email' => 'sam@example.com'],
        ];

        foreach ($accounts as $account) {
            User::updateOrCreate(
                ['email' => $account['email']],
                [
                    'name' => $account['name'],
                    'password' => Hash::make('password'),
                    'email_verified_at' => now(),
                ]
            );
        }
    }
}
