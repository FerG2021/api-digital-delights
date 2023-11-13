<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;


class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        User::create([
            'account_id' => 1,
            'email' => 'admin.bunker',
            'password' => '$2y$10$kpNjrvtFVVWSNvHWOagvRO.wngVrQ/OkCvQDYgNTvNdncGlvR7MqK',
            'name' => 'Administrador',
            'lastname' => 'Administrador',
        ]);
    }
}
