<?php

namespace Database\Seeders;

use App\Models\Notification;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class NotificationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
    $notification = [
        [
            'title' => 'change Password',
            'message' => 'lorium ipsum',
            'user_id' => 1,
            'created_at' =>now(),
            'updated_at' =>now()
        ],
        [
            'title' => 'verified Passweord',
            'message' => 'lorium ipsum',
            'user_id' => 1,
            'created_at' =>now(),
            'updated_at' =>now()
        ],
        [
            'title' => 'notification from app',
            'message' => 'lorium ipsum',
            'user_id' => 1,
            'created_at' =>now(),
            'updated_at' =>now()
            
        ]
    ];

    Notification::insert($notification);
    
    }
}
