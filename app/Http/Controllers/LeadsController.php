<?php

namespace App\Http\Controllers;

use App\Events\LeadRegistered;
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
                    'email' => 'required|email',
                    'tags' => 'array',
                    'name' => 'nullable|string',
                    'phone' => 'nullable|string',
                ],
                [
                    'email.required' => 'O campo email é obrigatório.',
                    'email.email' => 'O campo email deve ser um endereço de email válido.',
                ]
            );

            $emailOctopus = new EmailOctopusService();
            $existingLead = Leads::where('email', $request->email)->first();
            $existingLeadByTag = Leads::where('email', $request->email)->where('tag', $request->tags[0])->first();

            if ($existingLeadByTag) {
                return response()->json(['error' => 'Esse e-mail já foi cadastrado anteriormente.'], 409);
            }

            $lead = new Leads();
            $lead->email = $request->email;
            $lead->name = $request->name;
            $lead->phone = $request->phone;
            $lead->tag = $request->tags[0];
            $lead->save();

            if ($existingLead) {
                $emailOctopus->updateLead($lead->email, [$lead->tag]);
            } else {
                $emailOctopus->createLead($lead->email, [$lead->tag], $lead->name);
                event(new LeadRegistered($lead->email));
            }

            return response()->json(['message' => 'Lead cadastrado com sucesso']);
        } catch (ValidationException $e) {
            $errors = $e->errors();
            $errorMsg = $errors['email'][0];

            switch ($errorMsg) {
                case 'O campo email deve ser um endereço de email válido.':
                    $status = 422;
                    break;
                default:
                    $status = 400;
                    break;
            }

            return response()->json(['error' => $errors], $status);
        }
    }
}
