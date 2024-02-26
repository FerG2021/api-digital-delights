<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\NotificationType;


class NotificationTypesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        NotificationType::create([
            'account_id' => 2,
            'name' => 'birthday',
        ]);

        NotificationType::create([
            'account_id' => 2,
            'name' => 'sell',
        ]);
    }
}
