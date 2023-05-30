<?php

namespace App\Http\Controllers;

use App\Notifications\Discord;
use Illuminate\Http\Request;

class BugsnagWebhookController extends Controller
{
    public function notify(Request $request)
    {
        $error = $request->error;
        $errorMessage = $error['message'] ?? '';
        $errorExceptionClass = $error['exceptionClass'] ?? '';

        new Discord("Erro não tratado na aplicação!/n" . $errorExceptionClass . " | " . $errorMessage);
    }
}
