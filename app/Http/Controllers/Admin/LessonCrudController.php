<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\LessonRequest;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

/**
 * Class LessonCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class LessonCrudController extends CrudController
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
        CRUD::setModel(\App\Models\Lesson::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/lesson');
        CRUD::setEntityNameStrings('lesson', 'lessons');
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
        CRUD::column('workshop_id');
        CRUD::column('description');
        CRUD::column('created_at');
        CRUD::column('updated_at');
        CRUD::column('slug');
        CRUD::column('content');
    }

    /**
     * Define what happens when the Create operation is loaded.
     * 
     * @see https://backpackforlaravel.com/docs/crud-operation-create
     * @return void
     */
    protected function setupCreateOperation()
    {
        CRUD::setValidation(LessonRequest::class);

        CRUD::field('name');
        CRUD::field('description');
        CRUD::field('content');
        CRUD::field('slug');
        CRUD::field('slug')->type('slug')->hint('Se não preenchido, será gerado automaticamente')->target('name');
        $this->crud->addField(
            [
                'name' => 'workshop_id',
                'label' => 'Workshop',
                'type' => 'select',
                'model' => 'App\Models\Workshop',
                'entity' => 'workshop',
                'attribute' => 'name'
            ]
        );
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
