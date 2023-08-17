<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TechnicalAssessmentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {

        $resource = [
            'id' => $this->id,
            'title' => $this->title,
            'image_url' => $this->image_url,
            'image_url_dark' => $this->image_url_dark,
            'slug' => $this->slug,
            'type' => $this->type,
            'status' => $this->status,
            'tags' => $this->tags->pluck("name"),
            'company_name' => $this->company_name,
            'company_website' => $this->company_website,
            'company_headquarters' => $this->company_headquarters,
            'company_description' => $this->company_description,
            'company_size' => $this->company_size,
            'company_industry' => $this->company_industry,
            'company_linkedin' => $this->company_linkedin,
            'company_github' => $this->company_github,
            'assessment_description' => $this->assessment_description,
            'assessment_year' => $this->assessment_year,
            'assessment_instructions_url' => $this->assessment_instructions_url,
            'assessment_instructions_text' => $this->assessment_instructions_text,
            'job_position' => $this->job_position,
            'has_challenge' => !is_null($this->challenge_id),
            'zipped_files_url' => $this->zipped_files_url,
            'outdated_details' => $this->outdated_details
        ];

        return $resource;
    }
}
