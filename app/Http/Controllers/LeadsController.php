<?php

namespace App\Http\Controllers;

use App\Models\Leads;
use App\Services\Mail\EmailOctopusService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class LeadsController extends Controller
{
    public function create(Request $request)
    {
        try {
            $request->validate(
                [
                    "email" => "required|email",
                ],
                [
                    "email.required" => "O campo email é obrigatório.",
                    "email.email" =>
                        "O campo email deve ser um endereço de email válido.",
                ]
            );
        } catch (ValidationException $e) {
            $errors = $e->errors();
            $status =
                array_key_exists("email", $errors) &&
                in_array("O campo email é obrigatório.", $errors["email"])
                    ? 400
                    : 422;
            return response()->json(["error" => $errors], $status);
        }

        $lead = new Leads();
        $lead->email = $request->email;
        $lead->save();

        $emailOctopus = new EmailOctopusService();
        $emailOctopus->addLead($lead->email);
    }
}
