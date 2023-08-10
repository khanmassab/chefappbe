<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Apn\Apn;


class SendPushNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'send:push';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $options = [
            'token' => [
                'key' => 'storage/AuthKey_MH2A67T7Y7.p8',
                'keyId' => 'MH2A67T7Y7',
                'teamId' => 'BB875NP8VY',
            ],
            'production' => false,
        ];

        $apn = new Apn($options);

        $payload = [
            'aps' => [
                'alert' => [
                    'title' => 'Your notification title',
                    'body' => 'Your notification body',
                ],
                'badge' => 1,
                'sound' => 'default',
            ],
            'data' => [
                'custom_key' => 'custom_value',
            ],
        ];

        $apn->send('device_token', $payload);
    }
}
