<?php

namespace App\Http\Controllers;

use App\Services\Discord;
use Illuminate\Http\Request;

class BugsnagWebhookController extends Controller
{
    public function notify(Request $request)
    {
        $error = $request->error;
        $errorMessage = $error['message'] ?? '';
        $errorExceptionClass = $error['exceptionClass'] ?? '';

        Discord::sendMessage("Erro não tratado na aplicação! \n".$errorExceptionClass."\n".$errorMessage, 'bugs');
    }
}
