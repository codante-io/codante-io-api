<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\BlogPostRequest;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

/**
 * Class BlogPostCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class BlogPostCrudController extends CrudController
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
        CRUD::setModel(\App\Models\BlogPost::class);
        CRUD::setRoute(config("backpack.base.route_prefix") . "/blog-post");
        CRUD::setEntityNameStrings("blog post", "blog posts");
    }

    /**
     * Define what happens when the List operation is loaded.
     *
     * @see  https://backpackforlaravel.com/docs/crud-operation-list-entries
     * @return void
     */
    protected function setupListOperation()
    {
        CRUD::column("instructor_id");
        CRUD::column("title");
        CRUD::column("content");
        CRUD::column("short_description");
        CRUD::column("slug");
        CRUD::column("image_url");
        CRUD::column("status");
        CRUD::column("created_at");
        CRUD::column("updated_at");

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
        CRUD::setValidation(BlogPostRequest::class);

        CRUD::field("title")
            ->type("text")
            ->attributes(["maxlength" => 60]);
        CRUD::field("content")
            ->type("easymde")
            ->easymdeAttributes(["spellChecker" => "false"]);
        CRUD::field("short_description")
            ->type("textarea")
            ->attributes(["maxlength" => 400]);
        CRUD::field("slug")
            ->type("slug")
            ->hint("Se não preenchido, será gerado automaticamente")
            ->target("title");
        CRUD::field("instructor_id");
        CRUD::field("status");
        CRUD::field("image_url");

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
