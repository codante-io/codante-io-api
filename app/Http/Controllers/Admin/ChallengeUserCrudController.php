<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\ChallengeUserRequest;
use App\Models\ChallengeUser;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Notification;

/**
 * Class ChallengeUserCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class ChallengeUserCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;

    /**
     * Configure the CrudPanel object. Apply settings to all operations.
     *
     * @return void
     */
    public function setup()
    {
        CRUD::setModel(\App\Models\ChallengeUser::class);
        CRUD::setRoute(
            config("backpack.base.route_prefix") . "/challenge-user"
        );
        CRUD::setEntityNameStrings("challengeUser", "challengeUsers");
    }

    /**
     * Define what happens when the List operation is loaded.
     *
     * @see https://backpackforlaravel.com/docs/crud-operation-list-entries
     * @return void
     */

    /**
     * Define what happens when the List operation is loaded.
     *
     * @see  https://backpackforlaravel.com/docs/crud-operation-list-entries
     * @return void
     */
    protected function setupListOperation()
    {
        CRUD::column("id");
        CRUD::column("challenge_id")->searchLogic(function (
            $query,
            $column,
            $searchTerm
        ) {
            $query->orWhere("challenge_id", $searchTerm);
        });
        CRUD::column("user_id")
            ->label("User Name")
            ->searchLogic(function ($query, $column, $searchTerm) {
                $query->orWhereHas("user", function ($query) use ($searchTerm) {
                    $query->where("name", "like", "%" . $searchTerm . "%");
                });
            });
        CRUD::column("listed");
    }

    /**
     * Define what happens when the Update operation is loaded.
     *
     * @see https://backpackforlaravel.com/docs/crud-operation-update
     * @return void
     */
    protected function setupUpdateOperation()
    {
        CRUD::setValidation(ChallengeUserRequest::class);
        CRUD::field("id")
            ->type("number")
            ->attributes(["readonly" => "readonly"])
            ->tab("Principal");
        CRUD::field("challenge_id")
            ->type("number")
            ->attributes(["readonly" => "readonly"])
            ->tab("Principal");
        CRUD::field("user_id")
            ->type("number")
            ->attributes(["readonly" => "readonly"])
            ->tab("Principal");
        CRUD::field("listed")
            ->type("boolean")
            ->tab("Principal");

        $this->crud->addField([
            "name" => "notifica",
            "type" => "notify-discord",
            "tab" => "Ações",
            "data" => [
                "title" => "Email: Notifica usuário",
                "notification-url" =>
                    "/admin/submission-unlisted/email-launched/",
            ],
        ]);
    }

    protected function notifySubmissionUnlistedEmail($challengeUserId)
    {
        $challengeUser = ChallengeUser::find($challengeUserId);
        Notification::send(
            $challengeUser->user,
            new \App\Notifications\UnlistedChallengeUserNotification(
                $challengeUser
            )
        );
    }
}
