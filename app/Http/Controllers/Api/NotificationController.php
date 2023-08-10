<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\APNSService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class NotificationController extends Controller
{
    public function sendNotification(Request $request)
    {
        $pemFilePath = storage_path('app/public/apns-cert.pem');
        if ($pemFilePath) {
            $pemFileContents = file_get_contents($pemFilePath);
            // dd($pemFileContents);
        } else {
            dd('Pem file does not exist');
        }


        $deviceToken = 'd6d035125d6d04fb8ea2baefe7300e8bdf215de9b3d16219a60f870360bba90a';
        $passphrase = $request->input('passphrase');
        $message = "Hello World";
        $pemFile = $pemFilePath;

        $apnsService = new APNSService($deviceToken, $passphrase, $message, $pemFile);
        $result = $apnsService->send();

        if($result){
            return response()->json(['success' => $result]);
        }

        // dd($result);

    }
}
