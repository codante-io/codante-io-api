<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\WorkshopRequest;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

/**
 * Class WorkshopCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class WorkshopCrudController extends CrudController
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
        CRUD::setModel(\App\Models\Workshop::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/workshop');
        CRUD::setEntityNameStrings('workshop', 'workshops');
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
        CRUD::column('instructor_id');
        CRUD::column('challenge_id');
        CRUD::column('track_id');
        CRUD::column('track_position');
        CRUD::column('status');
        CRUD::column('created_at');
        CRUD::column('published_at');
        CRUD::column('updated_at');
        CRUD::column('short_description');
        CRUD::column('description');
        CRUD::column('slug');
        CRUD::column('is_standalone');
        CRUD::column('difficulty');
        CRUD::column('duration_in_minutes');
    }

    /**
     * Define what happens when the Create operation is loaded.
     * 
     * @see https://backpackforlaravel.com/docs/crud-operation-create
     * @return void
     */
    protected function setupCreateOperation()
    {
        CRUD::setValidation(WorkshopRequest::class);

        CRUD::field('name');
        CRUD::field('short_description')->label('Resumo');
        CRUD::field('description');
        CRUD::field('image_url')->type('url')->label('Link da Imagem');
        CRUD::field('slug');
        $this->crud->addField(
            [
                'name'        => 'status',
                'label'       => 'Status',
                'type'        => 'radio',
                'options'     => [ 
                    0 => 'archived',
                    1 => 'draft',
                    2 => 'published',
                    3 => 'soon'
                ],
            ],
        );
        $this->crud->addField(
            [
                'name'        => 'difficulty',
                'label'       => 'Dificuldade (nível)',
                'type'        => 'radio',
                'options'     => [ 
                    0 => 1,
                    1 => 2,
                    2 => 3,
                ],
            ],
        );
        CRUD::field('is_standalone')->label('É independente?');
        CRUD::field('duration_in_minutes');
        $this->crud->addField(
            [
                'name' => 'instructor_id',
                'label' => 'Instrutor',
                'type' => 'select',
                'model' => 'App\Models\Instructor',
                'entity' => 'instructor',
                'attribute' => 'name'
            ]
        );
        CRUD::field('track_id');
        CRUD::field('track_position');
        CRUD::field('challenge_id');
        CRUD::field('published_at');
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
