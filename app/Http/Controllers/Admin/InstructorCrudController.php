<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\InstructorRequest;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

/**
 * Class InstructorCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class InstructorCrudController extends CrudController
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
        CRUD::setModel(\App\Models\Instructor::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/instructor');
        CRUD::setEntityNameStrings('instructor', 'instructors');
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
        CRUD::column('company');
        CRUD::column('email');
        CRUD::column('bio');
        CRUD::column('slug');
    }

    /**
     * Define what happens when the Create operation is loaded.
     * 
     * @see https://backpackforlaravel.com/docs/crud-operation-create
     * @return void
     */
    protected function setupCreateOperation()
    {
        CRUD::setValidation(InstructorRequest::class);

        $this->crud->addField(
            [
                'name' => 'name',
                'label' => 'Nome',
                'type' => 'text'
            ]
        );
        $this->crud->addField(
            [
                'name' => 'email',
                'label' => 'E-mail',
                'type' => 'email'
            ]
        );
        $this->crud->addField(
            [
                'name' => 'company',
                'label' => 'Empresa',
                'type' => 'text'
            ]
        );
        $this->crud->addField(
            [
                'name' => 'bio',
                'label' => 'Perfil (Bio)',
                'type' => 'easymde',
                'easymdeAttributes' => [
                    'spellChecker' => false,
                ]
            ]
        );
        $this->crud->addField(
            [
                'name' => 'avatar_url',
                'label' => 'Foto de Perfil',
                'type' => 'url'
            ]
        );
        $this->crud->addField(
            [
                'name' => 'slug',
                'label' => 'Slug',
                'type' => 'text'
            ]
        );
        $this->crud->addField([
            'name' => 'links',
            'label' => 'Social Links',
            'type' => 'table',
            'hint' => 'Links para redes sociais do instrutor. Em "social media" preencha apenas github, twitter, linkedin, website, se houver. Para consistência, preencha nessa ordem.',
            'max' => 4,
            'min' => 0,
            'columns' => [
                'type' => 'Social Media (github, twitter, linkedin, website)',
                'url' => 'Url',
            ]
        ]);
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
