<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class SidebarLessonCollection extends ResourceCollection
{
    public function __construct(public $resource, private $baseUrl)
    {
        parent::__construct($resource);
    }

    public function toArray(Request $request): array
    {
        return $this->resource
            ->map(function ($lesson) {
                return new SidebarLessonResource($lesson, $this->baseUrl);
            })
            ->toArray();
    }
}
