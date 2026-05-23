<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run()
    {
        User::create([
            'name'=>'管理者',
            'email'=>'admin@example.com',
            'password'=>Hash::make('password123'),
            'role'=>'admin',
            'email_verified_at'=>now(),
        ]);

        User::create([
            'name'=>'いち花',
            'email'=>'user@example.com',
            'password'=>Hash::make('password123'),
            'role'=>'user',
            'email_verified_at'=>now(),
        ]);
    }
}