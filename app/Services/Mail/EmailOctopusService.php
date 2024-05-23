<?php

namespace App\Services\Mail;

use App\Models\User;
use Illuminate\Support\Facades\Http;

class EmailOctopusService
{
    private $api_key;
    private $listId = "4a67da48-0ed2-11ee-988e-5101d064b06e"; // Codante.io

    public function __construct()
    {
        $this->api_key = config("services.email_octopus.api_key");
    }

    public function addUser(User|string $userOrEmail, $isLead = false)
    {
        if (is_string($userOrEmail)) {
            if ($isLead) {
                $this->addLead($userOrEmail);
            }
            return;
        }

        $nameParts = explode(" ", trim($userOrEmail->name));
        $firstName = $nameParts[0];
        $lastName = end($nameParts);

        Http::post(
            "https://emailoctopus.com/api/1.6/lists/$this->listId/contacts",
            [
                "api_key" => $this->api_key,
                "email_address" => $userOrEmail->email,
                "fields" => [
                    "FirstName" => $firstName,
                    "LastName" => $lastName,
                    "is_pro" => false,
                ],
            ]
        );
    }

    public function updateUser(User|string $userOrEmail, $isLead = false)
    {
        if (is_string($userOrEmail)) {
            if ($isLead) {
                $this->updateLeadToUser($userOrEmail);
            }
            return;
        }
        // https://emailoctopus.com/api-documentation/lists/update-contact
        $isPro = $userOrEmail->is_pro;
        $nameParts = explode(" ", trim($userOrEmail->name));
        $firstName = $nameParts[0];
        $lastName = end($nameParts);

        $emailHash = md5(strtolower(trim($userOrEmail->email)));

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
        $res = Http::post(
            "https://emailoctopus.com/api/1.6/lists/$this->listId/contacts",
            [
                "api_key" => $this->api_key,
                "email_address" => $email,
                "fields" => [
                    "is_registered_user" => false,
                ],
            ]
        );
    }

    public function updateLeadToUser($email)
    {
        $emailHash = md5(strtolower(trim($email)));
        Http::put(
            "https://emailoctopus.com/api/1.6/lists/$this->listId/contacts/$emailHash",
            [
                "api_key" => $this->api_key,
                "fields" => [
                    "is_registered_user" => true,
                    "is_pro" => false,
                ],
            ]
        );
    }
}
