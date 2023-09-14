<?php

namespace App\Services;

use Http;

class VimeoThumbnail
{
  protected $client;
  protected $videoId;

  // public function __construct($videoId)
  // {
  //   $apiToken = config("services.vimeo.secret");
  //   $this->client = Http::withToken($apiToken);
  //   $this->videoId = $videoId;
  // }

  // public function getVideoThumbnail()
  // {
  //   $response = $this->client->get(
  //     "https://api.vimeo.com/videos/{$this->videoId}/pictures"
  //   );
  //   $data = $response->json()["data"] ?? "";

  //   if (!isset($data) || !isset($data[0]) || !isset($data[0]["sizes"])) {
  //     throw new \Exception("Não foi possível obter o thumbnail");
  //   }

  //   return end($data[0]["sizes"])["link"] ?? "";
  // }
}
