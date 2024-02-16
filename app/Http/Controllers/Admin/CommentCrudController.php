<?php

namespace App\Http\Controllers\Admin;

use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

/**
 * Class CommentCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class CommentCrudController extends CrudController
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
        CRUD::setModel(\App\Models\Comment::class);
        CRUD::setRoute(config("backpack.base.route_prefix") . "/comment");
        CRUD::setEntityNameStrings("comment", "comments");
    }

    /**
     * Define what happens when the List operation is loaded.
     *
     * @see  https://backpackforlaravel.com/docs/crud-operation-list-entries
     * @return void
     */
    protected function setupListOperation()
    {
        CRUD::column("id");
        CRUD::column("user_id");
        CRUD::column("comment");
        CRUD::addColumn([
            "name" => "commentable_type",
            "type" => "model_function",
            "function_name" => "getCommentableType",
            "label" => "Commentable Type",
        ]);

        CRUD::addColumn([
            "name" => "commentable_id",
            "type" => "model_function",
            "function_name" => "getCommentableId",
            "label" => "Commentable ID",
        ]);
    }

    /**
     * Define what happens when the Create operation is loaded.
     *
     * @see https://backpackforlaravel.com/docs/crud-operation-create
     * @return void
     */
    protected function setupCreateOperation()
    {
        CRUD::field("user_id")
            ->label("Usuário")
            ->default(backpack_user()->id)
            ->type("hidden");

        CRUD::field("commentable_type")
            ->label("Tipo")
            ->type("radio")
            ->options([
                "App\Models\ChallengeUser" => "ChallengeUser",
                "App\Models\Lesson" => "Lesson",
            ]);

        CRUD::field("commentable_id")
            ->label("Id do Commentable")
            ->type("number");

        CRUD::field("replying_to")
            ->label("Respondendo para")
            ->type("number")
            ->allowsNull(true);

        CRUD::field("comment")
            ->label("Comentário")
            ->type("textarea");
    }

    /**
     * Define what happens when the Update operation is loaded.
     *
     * @see https://backpackforlaravel.com/docs/crud-operation-update
     * @return void
     */
    protected function setupUpdateOperation()
    {
        $this->setupCreateOperation();
    }
}
