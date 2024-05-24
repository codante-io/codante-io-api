<?php

namespace App\Http\Controllers;

use App\Events\LeadRegistered;
use App\Models\Leads;
use App\Services\Mail\EmailOctopusService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class LeadsController extends Controller
{
    public function create(Request $request)
    {
        try {
            $request->validate(
                [
                    "email" => "required|email|unique:leads|unique:users",
                    "tags" => "array",
                ],
                [
                    "email.required" => "O campo email é obrigatório.",
                    "email.email" =>
                        "O campo email deve ser um endereço de email válido.",
                    "email.unique" =>
                        "Esse e-mail já foi cadastrado anteriormente.",
                ]
            );
            $lead = new Leads();
            $lead->email = $request->email;
            $lead->save();

            $tags = $request->tags ?? [];

            $emailOctopus = new EmailOctopusService();
            $emailOctopus->createLead($lead->email, $tags);

            event(new LeadRegistered($lead->email));
        } catch (ValidationException $e) {
            $errors = $e->errors();
            $errorMsg = $errors["email"][0];

            switch ($errorMsg) {
                case "O campo email deve ser um endereço de email válido.":
                    $status = 422;
                    break;
                case "Esse e-mail já foi cadastrado anteriormente.":
                    $status = 409;
                    break;
                default:
                    $status = 400;
                    break;
            }

            return response()->json(["error" => $errors], $status);
        }
    }
}
