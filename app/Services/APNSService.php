<?php

namespace App\Services;

class APNSService
{
    private $deviceToken;
    private $passphrase;
    private $message;
    private $pemFile;

    public function __construct(string $deviceToken, $passphrase, string $message, string $pemFile)
    {
        $this->deviceToken = $deviceToken;
        $this->passphrase = '';
        $this->pemFile = $pemFile;
        $this->message = $message;
    }

    public function send()
    {
        $ctx = stream_context_create();
        stream_context_set_option($ctx, 'ssl', 'local_cert', $this->pemFile);

        dd( stream_socket_client(
            'ssl://gateway.sandbox.push.apple.com:2195', $err,
            $errstr, 60, STREAM_CLIENT_CONNECT|STREAM_CLIENT_PERSISTENT, $ctx));

        if (!$fp) {
            return "Failed to connect: $err $errstr";
        }

        dd($fp);

        \Log::info('Connected to APNS');

        $body['aps'] = array(
            'alert' => $this->message,
            'sound' => 'default'
        );

        $payload = json_encode($body);

        $msg = chr(0) . pack('n', 32) . pack('H*', $this->deviceToken) . pack('n', strlen($payload)) . $payload;

        $result = fwrite($fp, $msg, strlen($msg));

        if (!$result) {
            \Log::error('Message not delivered');
            return false;
        } else {
            \Log::info('Message successfully delivered');
            return true;
        }

        fclose($fp);
    }
}
