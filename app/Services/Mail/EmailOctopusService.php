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

    public function registerEmailOctopusContact($email, $fields, $tags = [])
    {
        Http::post(
        "https://emailoctopus.com/api/1.6/lists/$this->listId/contacts",
        [
            "api_key" => $this->api_key,
            "email_address" => $email,
            "fields" => $fields,
            "tags" => $tags,
            ]
        );
    }

    public function updateEmailOctopusContact($email, $fields, $tags = [])
    {
        $emailHash = md5(strtolower(trim($email)));
        Http::put(
            "https://emailoctopus.com/api/1.6/lists/$this->listId/contacts/$emailHash",
            [
                "api_key" => $this->api_key,
                "fields" => $fields,
                "tags" => $tags,
            ]
        );
    }

    public function updateLeadAfterSignUp(User $user, $tags = [])
    {
        $nameParts = explode(" ", trim($user->name));
        $firstName = $nameParts[0];
        $lastName = end($nameParts);

        $this->updateEmailOctopusContact(
            $user->email,
            [
                "FirstName" => $firstName,
                "LastName" => $lastName,
                "is_pro" => false,
                "is_registered_user" => true,
            ],
            $tags
        );
    }

    public function createLead($email, $tags = [], $firstName = null, $lastName = null)
    {
        $this->registerEmailOctopusContact(
            $email,
            [
                "is_registered_user" => false,
                "is_pro" => false,
                "FirstName" => $firstName,
                "LastName" => $lastName,
            ],
            $tags
        );
    }

    public function updateLead($email, $tags = [])
    {
        $this->updateEmailOctopusContact($email, [], $tags);
    }

    public function addUser(User $user)
    {
        $nameParts = explode(" ", trim($user->name));
        $firstName = $nameParts[0];
        $lastName = end($nameParts);

        $this->registerEmailOctopusContact($user->email, [
            "FirstName" => $firstName,
            "LastName" => $lastName,
            "is_pro" => false,
            "is_registered_user" => true,
        ]);
    }

    public function updateUser(User $user)
    {
        // https://emailoctopus.com/api-documentation/lists/update-contact
        $isPro = $user->is_pro;
        $nameParts = explode(" ", trim($user->name));
        $firstName = $nameParts[0];
        $lastName = end($nameParts);

        $this->updateEmailOctopusContact($user->email, [
            "FirstName" => $firstName,
            "LastName" => $lastName,
            "is_pro" => $isPro,
        ]);
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
}
