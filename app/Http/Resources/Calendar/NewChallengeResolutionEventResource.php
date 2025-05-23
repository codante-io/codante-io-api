<?php

namespace App\Http\Resources\Calendar;

use Illuminate\Http\Resources\Json\JsonResource;

class NewChallengeResolutionEventResource extends JsonResource
{

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        
        $event = [
            'id' => $this->id . '-' . 'challenge-resolution',
            'title' => $this->title ?? $this->name,
            'description' => $this->short_description,
            'slug' => $this->slug,
            'url' => '/mini-projetos/' . $this->slug,
            'type' => 'challenge-resolution',
            'datetime' => $this->solution_publish_date,
            'image_url' => $this->image_url,
        ];


        return $event;
    }
} 