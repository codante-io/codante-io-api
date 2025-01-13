<?php

namespace App\Http\Controllers\Admin;

use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

/**
 * Class TestimonialCrudController
 *
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class TestimonialCrudController extends CrudController
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
        CRUD::setModel(\App\Models\Testimonial::class);
        CRUD::setRoute(config('backpack.base.route_prefix').'/testimonial');
        CRUD::setEntityNameStrings('testimonial', 'testimonials');
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
        /**
         * Columns can be defined using the fluent syntax or array syntax:
         * - CRUD::column('price')->type('number');
         * - CRUD::addColumn(['name' => 'price', 'type' => 'number']);
         */
        CRUD::addColumn([
            'name' => 'name',
            'type' => 'text',
            'label' => 'Nome',
        ]);

        CRUD::addColumn([
            'name' => 'avatar_url',
            'type' => 'image',
            'label' => 'Avatar',
        ]);

        CRUD::addColumn([
            'name' => 'body',
            'type' => 'text',
            'label' => 'Conteúdo',
        ]);

        CRUD::addColumn([
            'name' => 'source',
            'type' => 'text',
            'label' => 'Origem',
        ]);
        CRUD::addColumn([
            'name' => 'featured',
            'type' => 'text',
            'label' => 'Destaque',
        ]);
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
        CRUD::setValidation([
            // 'name' => 'required|min:2',
        ]);

        /**
         * Fields can be defined using the fluent syntax or array syntax:
         * - CRUD::field('price')->type('number');
         * - CRUD::addField(['name' => 'price', 'type' => 'number']));
         */
        CRUD::addField([
            'name' => 'name',
            'type' => 'text',
            'label' => 'Nome',
        ]);
        CRUD::addField([
            'name' => 'body',
            'type' => 'textarea',
            'label' => 'Conteúdo',
        ]);
        $this->crud->addField([
            'name' => 'avatar_url',
            'label' => 'Imagem de Avatar',
            'type' => 'image',
            'crop' => true,
            'aspect_ratio' => 1,
            'upload' => true,
            'disk' => 's3',
        ]);

        CRUD::addField([
            'name' => 'company',
            'type' => 'text',
            'label' => 'Empresa',
        ]);
        CRUD::addField([
            'name' => 'source',
            'type' => 'text',
            'label' => 'Origem',
        ]);
        CRUD::addField([
            'name' => 'featured',
            'type' => 'text',
            'label' => 'Destaque',
            'hint' => 'Por exemplo, "landing"',
        ]);
        CRUD::addField([
            'name' => 'social_media_link',
            'type' => 'text',
            'label' => 'Link da rede social',
        ]);
        CRUD::addField([
            'name' => 'social_media_nickname',
            'type' => 'text',
            'label' => 'Arroba da rede social',
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
    }
}
