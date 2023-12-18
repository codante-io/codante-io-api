<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Only the user themselves can update their profile
        return $this->user()->email === $this->email;
    }

    /**
     * Get the validation rules that apply to the request.
     * Email is the only key that is always necessary
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            "email" => "required|email|unique:users,email," . $this->user()->id,
            "name" => "sometimes|string|max:255",
            "password" => "sometimes|string|min:8|confirmed",
            "avatar_url" => "sometimes|url",
            "github_id" => "prohibited", //importante!
            "github_user" => "prohibited", //importante!
            "is_admin" => "prohibited", //importante!
            "is_pro" => "prohibited", // importante!
        ];
    }
}
