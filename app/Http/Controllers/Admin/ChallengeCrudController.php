<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\ChallengeRequest;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

/**
 * Class ChallengeCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class ChallengeCrudController extends CrudController
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
        CRUD::setModel(\App\Models\Challenge::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/challenge');
        CRUD::setEntityNameStrings('challenge', 'challenges');
    }

    /**
     * Define what happens when the List operation is loaded.
     * 
     * @see  https://backpackforlaravel.com/docs/crud-operation-list-entries
     * @return void
     */
    protected function setupListOperation()
    {
        CRUD::column('name');
        CRUD::column('short_description');
        CRUD::column('description');
        CRUD::column('image_url');
        CRUD::column('slug');
        CRUD::column('status');
        CRUD::column('difficulty');
        CRUD::column('duration_in_minutes');
        CRUD::column('repository_url');
        CRUD::column('track_id');
        CRUD::column('track_position');
        CRUD::column('published_at');
        CRUD::column('created_at');
        CRUD::column('updated_at');

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
        CRUD::setValidation(ChallengeRequest::class);

        CRUD::field('name');
        CRUD::field('short_description');
        CRUD::field('description');
        CRUD::field('image_url');
        CRUD::field('slug');
        CRUD::field('status');
        CRUD::field('difficulty');
        CRUD::field('duration_in_minutes');
        CRUD::field('repository_url');
        CRUD::field('track_id');
        CRUD::field('track_position');
        CRUD::field('published_at');

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
