<?php

namespace App\Services;

use App\Notifications\Discord;
use Http;

class VimeoThumbnailService
{
  protected $client;
  protected $videoId;

  public function __construct()
  {
    $apiToken = config("services.vimeo.secret");
    $this->client = Http::withToken($apiToken);
  }

  public function CheckAllVideoThumbnails()
  {
    new Discord("-- Início do Saneamento de Thumbnails do Vimeo `(Este robô pega todas as lessons sem thumbnail e pega a url das thumbnails e salva na base de dados.`)", 'notificacoes-site');

    $lessons = \App\Models\Lesson::whereNull('thumbnail_url')->get();

    foreach ($lessons as $lesson) {
      $this->videoId = $lesson->vimeo_id;
      $thumbnail = $this->getVideoThumbnail($this->videoId);
      $lesson->thumbnail_url = $thumbnail;
      $lesson->save();
      new Discord("Atualizada a lesson id: $lesson->id", 'notificacoes-site');
    }

    new Discord("-- Fim do Saneamento de Thumbnails do Vimeo", 'notificacoes-site');
  }

  private function getVideoThumbnail(int $videoId)
  {
    $response = $this->client->get(
      "https://api.vimeo.com/videos/{$videoId}/pictures"
    );
    $data = $response->json()["data"] ?? "";

    if (!isset($data) || !isset($data[0]) || !isset($data[0]["sizes"])) {
      throw new \Exception("Não foi possível obter o thumbnail");
    }

    return end($data[0]["sizes"])["link"] ?? "";
  }
}
