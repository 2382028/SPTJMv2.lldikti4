<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
  public function run(): void
  {
    $users = [
      ['email' => 'admin', 'password' => 'triomaung', 'role' => 'admin', 'active' => 1, 'cp' => null],
      ['email' => 'cucu', 'password' => 'cucu', 'role' => 'pic', 'active' => 0, 'cp' => null],
      ['email' => 'fadhil', 'password' => 'fadhil', 'role' => 'pic', 'active' => 1, 'cp' => '87728949707'],
      ['email' => 'ilham', 'password' => 'ilham', 'role' => 'pic', 'active' => 1, 'cp' => '81395154262'],
      ['email' => 'itjen', 'password' => 'itjen2024', 'role' => 'pic', 'active' => 0, 'cp' => null],
      ['email' => 'nandang', 'password' => 'nandang', 'role' => 'pic', 'active' => 1, 'cp' => '81395562732'],
      ['email' => 'rani', 'password' => 'rani', 'role' => 'pic', 'active' => 1, 'cp' => '85624220686'],
      ['email' => 'rika', 'password' => 'rika', 'role' => 'pic', 'active' => 1, 'cp' => '8975645346'],
      ['email' => 'salman', 'password' => 'salman', 'role' => 'pic', 'active' => 1, 'cp' => '81322775698'],
      ['email' => 'yuda', 'password' => 'yuda', 'role' => 'pic', 'active' => 1, 'cp' => '87727888358'],
    ];

    foreach ($users as $user) {
      DB::table('users')->insert([
        'email' => $user['email'],
        'password' => $user['password'],
        'role' => $user['role'],
        'active' => $user['active'],
        'cp' => $user['cp'],
        'created_at' => now(),
        'updated_at' => now(),
      ]);
    }
  }
}
