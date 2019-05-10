<?php

namespace App\Service;

use GuzzleHttp\Client;

class ShowsManager
{
    public function load()
    {
        $client = new Client();
        $body = $client->get('http://api.tvmaze.com/shows?page=1')->getBody();

        $obj = json_decode($body);

        return true;
    }
}