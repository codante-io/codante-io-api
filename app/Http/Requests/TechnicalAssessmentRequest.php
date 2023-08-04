<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TechnicalAssessmentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // only allow updates if the user is logged in
        return backpack_auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            "title" => "required|min:2|max:255",
            "slug" => "required|unique:technical_assessments,slug," . $this->id,
            "type" => "required|in:frontend,backend,fullstack",
            "status" => "required|in:draft,published",
            "company_name" => "required|min:5|max:255",
        ];
    }
}
