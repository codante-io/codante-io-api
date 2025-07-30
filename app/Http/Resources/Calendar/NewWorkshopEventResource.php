<?php

namespace App\Http\Resources\Calendar;

use Illuminate\Http\Resources\Json\JsonResource;

class NewWorkshopEventResource extends JsonResource
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
            'id' => $this->id.'-'.'workshop',
            'title' => $this->title ?? $this->name,
            'description' => $this->short_description,
            'slug' => $this->slug,
            'url' => '/workshops/'.$this->slug,
            'type' => 'workshop',
            'datetime' => $this->published_at,
            'image_url' => $this->image_url,
        ];

        return $event;
    }
}
