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
            'name' => 'Empleado',
            'email' => 'empleado@jeple.com',
            'password' => Hash::make('empleado123'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $user2 = User::find($userId2);
        $user2->assignRole('Asesor');
    }
}
