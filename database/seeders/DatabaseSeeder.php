<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Owner;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {

        User::create([
            'name'=>'admin',
            'username'=>'admin',
            'password'=>Hash::make('admin')
        ]);
        
        Owner::create([
            'name'=>'edison',
            'username'=>'edison',
            'password'=>Hash::make('ed2025@25e')
        ]);
    }
}
