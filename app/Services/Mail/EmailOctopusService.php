<?php

namespace App\Services\Mail;

use App\Models\User;
use Illuminate\Support\Facades\Http;

class EmailOctopusService
{
    private $api_key;
    private $listId = "4a67da48-0ed2-11ee-988e-5101d064b06e"; // Codante.io
    private $leadsListId = "bfc2319e-1872-11ef-8cff-bbbcfb46d456"; // Leads

    public function __construct()
    {
        $this->api_key = config("services.email_octopus.api_key");
    }

    public function addUser(User $user)
    {
        $nameParts = explode(" ", trim($user->name));
        $firstName = $nameParts[0];
        $lastName = end($nameParts);

        Http::post(
            "https://emailoctopus.com/api/1.6/lists/$this->listId/contacts",
            [
                "api_key" => $this->api_key,
                "email_address" => $user->email,
                "fields" => [
                    "FirstName" => $firstName,
                    "LastName" => $lastName,
                ],
            ]
        );
    }

    public function updateUser(User $user)
    {
        // https://emailoctopus.com/api-documentation/lists/update-contact
        $isPro = $user->is_pro;
        $nameParts = explode(" ", trim($user->name));
        $firstName = $nameParts[0];
        $lastName = end($nameParts);

        $emailHash = md5(strtolower(trim($user->email)));

        Http::put(
            "https://emailoctopus.com/api/1.6/lists/$this->listId/contacts/$emailHash",
            [
                "api_key" => $this->api_key,
                "fields" => [
                    "FirstName" => $firstName,
                    "LastName" => $lastName,
                    "is_pro" => $isPro,
                ],
            ]
        );
    }

    public function deleteUser(User $user)
    {
        $emailHash = md5(strtolower(trim($user->email)));

        Http::delete(
            "https://emailoctopus.com/api/1.6/lists/$this->listId/contacts/$emailHash",
            [
                "api_key" => $this->api_key,
            ]
        );
    }

    public function addLead($email)
    {
        Http::post(
            "https://emailoctopus.com/api/1.6/lists/$this->leadsListId/contacts",
            [
                "api_key" => $this->api_key,
                "email_address" => $email,
            ]
        );
    }
}
