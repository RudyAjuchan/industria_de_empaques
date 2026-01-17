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
            'name' => 'Jonathan',
            'email' => 'jonathan@jeple.com',
            'password' => Hash::make('jonathan123'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $userId4 = DB::table('users')->insertGetId([
            'name' => 'Mario',
            'email' => 'mario@jeple.com',
            'password' => Hash::make('mario123'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $userId5 = DB::table('users')->insertGetId([
            'name' => 'Edwin',
            'email' => 'edwin@jeple.com',
            'password' => Hash::make('edwin123'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $userId6 = DB::table('users')->insertGetId([
            'name' => 'Irma Pérez',
            'email' => 'irma@jeple.com',
            'password' => Hash::make('irma123'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $userId7 = DB::table('users')->insertGetId([
            'name' => 'José',
            'email' => 'jose@jeple.com',
            'password' => Hash::make('jose123'),
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
        
    }
}
