<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\TrackItemRequest;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

/**
 * Class TrackItemCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class TrackItemCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\FetchOperation;

    /**
     * Configure the CrudPanel object. Apply settings to all operations.
     *
     * @return void
     */
    public function setup()
    {
        CRUD::setModel(\App\Models\TrackItem::class);
        CRUD::setRoute(config("backpack.base.route_prefix") . "/track-item");
        CRUD::setEntityNameStrings("track item", "track items");
    }

    /**
     * Define what happens when the List operation is loaded.
     *
     * @see  https://backpackforlaravel.com/docs/crud-operation-list-entries
     * @return void
     */
    protected function setupListOperation()
    {
        CRUD::column("name");
        CRUD::column("type");
        CRUD::column("content");
        CRUD::column("position");
        CRUD::column("status");

        CRUD::column("tags");

        /**
         * Columns can be defined using the fluent syntax or array syntax:
         * - CRUD::column('price')->type('number');
         * - CRUD::addColumn(['name' => 'price', 'type' => 'number']);
         */
    }

    /**
     * Define what happens when the Create operation is loaded.
     *
     * @see https://backpackforlaravel.com/docs/crud-operation-create
     * @return void
     */
    protected function setupCreateOperation()
    {
        CRUD::field("tags");
        CRUD::field("name");
        $this->crud->addField([
            "name" => "type",
            "label" => "Tipo",
            "type" => "radio",

            "options" => [
                "external_link" => "Link externo",
                "markdown" => "Markdown",
            ],
        ]);
        CRUD::field("content")
            ->label("Conteúdo (link ou markdown)")
            ->type("easymde")
            ->easymdeAttributes(["spellChecker" => false]);

        CRUD::field("position");
        $this->crud->addField([
            "name" => "tracks",
            "type" => "relationship",
        ]);
        $this->crud->addField([
            "name" => "status",
            "label" => "Status",
            "type" => "radio",

            "options" => [
                "archived" => "archived",
                "draft" => "draft",
                "published" => "published",
                "soon" => "soon",
                "unlisted" => "unlisted",
            ],
        ]);
        /**
         * Fields can be defined using the fluent syntax or array syntax:
         * - CRUD::field('price')->type('number');
         * - CRUD::addField(['name' => 'price', 'type' => 'number']));
         */
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
