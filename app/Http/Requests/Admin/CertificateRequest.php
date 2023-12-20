<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class CertificateRequest extends FormRequest
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
    $rules = [
      'user_id' => 'required',
      'source_type' => 'required|in:challenge,workshop',
    ];

    if ($this->source_type == 'challenge') {
      $rules['challenge_id'] = 'required';
    }

    if ($this->source_type == 'workshop') {
      $rules['workshop_id'] = 'required';
    }

    return $rules;
  }

  /**
   * Get the validation attributes that apply to the request.
   *
   * @return array
   */
  public function attributes()
  {
    return [
      //
    ];
  }

  /**
   * Get the validation messages that apply to the request.
   *
   * @return array
   */
  public function messages()
  {
    return [
      //
    ];
  }
}
