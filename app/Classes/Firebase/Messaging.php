<?php

namespace App\Classes\Firebase;

use Google;

class Messaging
{

    protected $message;

    public function setMessage($token, $title = "", $body = "", $data = [])
    {
        $preData = [
            'title' => $title,
            'body' => $body,
        ];
        $data = array_merge($data, $preData);
        $this->message = [
            "message" => [
                "token" => $token,
                "notification" => [
                    "title" => $title,
                    "body" => $body
                ],
                "data" => $data
            ]
        ];
    }

    public function send()
    {
        $url = "https://fcm.googleapis.com/v1/projects/chg-intranet/messages:send";
        putenv("GOOGLE_APPLICATION_CREDENTIALS=/var/www/html/Laravel/public/service-account.json");
        $client = new Google\Client();
        $client->useApplicationDefaultCredentials();
        $client->addScope(Google\Service\FirebaseCloudMessaging::CLOUD_PLATFORM);
        $httpClient = $client->authorize();
        $httpClient->post($url, ["json" => $this->message]);
    }
}