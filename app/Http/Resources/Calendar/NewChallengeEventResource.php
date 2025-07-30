<?php

namespace App\Http\Resources\Calendar;

use Illuminate\Http\Resources\Json\JsonResource;

class NewChallengeEventResource extends JsonResource
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
            'id' => $this->id.'-'.'challenge',
            'title' => $this->title ?? $this->name,
            'description' => $this->short_description,
            'slug' => $this->slug,
            'url' => '/mini-projetos/'.$this->slug,
            'type' => 'challenge',
            'datetime' => $this->weekly_featured_start_date,
            'image_url' => $this->image_url,
        ];

        return $event;
    }
}
