<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class CreateCheckoutLinkV2Request extends FormRequest
{
  /**
   * Determine if the user is authorized to make this request.
   */
  public function authorize(): bool
  {
    return true;
  }

  /**
   * Get the validation rules that apply to the request.
   *
   * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
   */
  public function rules(): array
  {
    return [
      'plan_slug' => ['required', 'string', 'max:255'],
      'plan_description' => ['nullable', 'string', 'max:1000'],
      'accepted_payment_methods' => ['nullable', 'array'],
      'accepted_payment_methods.*' => ['string', 'in:credit_card,boleto,pix'],
    ];
  }

  /**
   * Get custom messages for validator errors.
   *
   * @return array<string, string>
   */
  public function messages(): array
  {
    return [
      'plan_slug.required' => 'O identificador do plano é obrigatório.',
      'plan_slug.string' => 'O identificador do plano deve ser um texto.',
      'plan_slug.max' => 'O identificador do plano não pode ter mais de 255 caracteres.',
      'plan_description.string' => 'A descrição do plano deve ser um texto.',
      'plan_description.max' => 'A descrição do plano não pode ter mais de 1000 caracteres.',
      'accepted_payment_methods.array' => 'Os métodos de pagamento devem ser uma lista.',
      'accepted_payment_methods.*.in' => 'O método de pagamento deve ser: credit_card, boleto ou pix.',
    ];
  }
}
