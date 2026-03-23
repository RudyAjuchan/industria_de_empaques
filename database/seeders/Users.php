<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;


class Users extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $userId = DB::table('users')->insertGetId([
            'name' => 'Administrador',
            'email' => 'industrias@jeple.com',
            'password' => Hash::make('admin123'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $user = User::find($userId);
        $user->assignRole('Admin');

        //----usuario 2
        $userId2 = DB::table('users')->insertGetId([
            'name' => 'Asesor',
            'email' => 'asesor@jeple.com',
            'password' => Hash::make('asesor123'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $userId3 = DB::table('users')->insertGetId([
            'name' => 'User1',
            'email' => 'user1@jeple.com',
            'password' => Hash::make('user123'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $userId4 = DB::table('users')->insertGetId([
            'name' => 'User2',
            'email' => 'user2@jeple.com',
            'password' => Hash::make('user123'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $userId5 = DB::table('users')->insertGetId([
            'name' => 'User3',
            'email' => 'user3@jeple.com',
            'password' => Hash::make('user123'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $userId6 = DB::table('users')->insertGetId([
            'name' => 'User4',
            'email' => 'user4@jeple.com',
            'password' => Hash::make('user123'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $userId7 = DB::table('users')->insertGetId([
            'name' => 'User5',
            'email' => 'user5@jeple.com',
            'password' => Hash::make('user123'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $userId8 = DB::table('users')->insertGetId([
            'name' => 'User6',
            'email' => 'user6@jeple.com',
            'password' => Hash::make('user123'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $userId9 = DB::table('users')->insertGetId([
            'name' => 'User7',
            'email' => 'user7@jeple.com',
            'password' => Hash::make('user123'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $userId10 = DB::table('users')->insertGetId([
            'name' => 'User8',
            'email' => 'user8@jeple.com',
            'password' => Hash::make('user123'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $user2 = User::find($userId2);
        $user2->assignRole('Asesor');
        $user3 = User::find($userId3);
        $user3->assignRole('Producción');
        $user4 = User::find($userId4);
        $user4->assignRole('Producción');
        $user5 = User::find($userId5);
        $user5->assignRole('Producción');
        $user6 = User::find($userId6);
        $user6->assignRole('Producción');
        $user7 = User::find($userId7);
        $user7->assignRole('Producción');
        $user8 = User::find($userId8);
        $user8->assignRole('Producción');
        $user9 = User::find($userId9);
        $user9->assignRole('Producción');
        $user10 = User::find($userId10);
        $user10->assignRole('Producción');
        
    }
}
