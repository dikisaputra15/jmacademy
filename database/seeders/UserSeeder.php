<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Seed the database with users and assign roles.
     */
    public function run(): void
    {
        // Admin
        $admin = User::firstOrCreate(
            ['email' => 'admin@jmacademy.com'],
            [
                'name' => 'Administrator',
                'password' => Hash::make('admin12345'),
            ]
        );
        $admin->assignRole('admin');

        // Guru
        $guru = User::firstOrCreate(
            ['email' => 'guru@jmacademy.com'],
            [
                'name' => 'Guru',
                'password' => Hash::make('guru12345'),
            ]
        );
        $guru->assignRole('guru');

        // Student
        $student = User::firstOrCreate(
            ['email' => 'student@jmacademy.com'],
            [
                'name' => 'Student',
                'password' => Hash::make('student12345'),
            ]
        );
        $student->assignRole('student');
    }
}
