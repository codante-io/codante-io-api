<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\CertificateRequest;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

/**
 * Class CertificateCrudController
 *
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class CertificateCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;

    /**
     * Configure the CrudPanel object. Apply settings to all operations.
     *
     * @return void
     */
    public function setup()
    {
        CRUD::setModel(\App\Models\Certificate::class);
        CRUD::setRoute(config('backpack.base.route_prefix').'/certificate');
        CRUD::setEntityNameStrings('certificate', 'certificates');
    }

    /**
     * Define what happens when the List operation is loaded.
     *
     * @see  https://backpackforlaravel.com/docs/crud-operation-list-entries
     *
     * @return void
     */
    protected function setupListOperation()
    {
        CRUD::column('id');
        CRUD::column('user_id');
        CRUD::column('status');
    }

    /**
     * Define what happens when the Create operation is loaded.
     *
     * @see https://backpackforlaravel.com/docs/crud-operation-create
     *
     * @return void
     */
    protected function setupCreateOperation()
    {
        CRUD::setValidation(CertificateRequest::class);

        CRUD::field('user_id')->label('Usuário');

        // CRUD::field("certifiable_type")->label("Source Type");
        // CRUD::field("certifiable_id")
        //     ->label("Id do Source")
        //     ->type("number");

        $this->crud->addField([
            'name' => 'status',
            'label' => 'Status',
            'type' => 'radio',
            'options' => [
                'pending' => 'Pending',
                'published' => 'Published',
            ],
            'wrapper' => [
                'class' => 'form-group col-md-6',
            ],
        ]);
    }

    /**
     * Define what happens when the Update operation is loaded.
     *
     * @see https://backpackforlaravel.com/docs/crud-operation-update
     *
     * @return void
     */
    protected function setupUpdateOperation()
    {
        $this->setupCreateOperation();

        CRUD::field('certifiable_description')
            ->label('Source')
            ->type('text')
            ->attributes(['readonly' => 'readonly']);
    }
}
