<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\InstructorRequest;
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
        CRUD::setRoute(config("backpack.base.route_prefix") . "/instructor");
        CRUD::setEntityNameStrings("instructor", "instructors");
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
        CRUD::column("company");
        CRUD::column("email");
        CRUD::column("bio");
        CRUD::column("slug");
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

        $this->crud->addField([
            "name" => "name",
            "label" => "Nome",
            "type" => "text",
            "tab" => "Perfil",
        ]);
        $this->crud->addField([
            "name" => "email",
            "label" => "E-mail",
            "type" => "email",
            "tab" => "Perfil",
        ]);
        $this->crud->addField([
            "name" => "company",
            "label" => "Empresa",
            "type" => "text",
            "tab" => "Perfil",
        ]);
        $this->crud->addField([
            "name" => "bio",
            "label" => "Perfil (Bio)",
            "type" => "easymde",
            "easymdeAttributes" => [
                "spellChecker" => false,
            ],
            "tab" => "Perfil",
        ]);
        $this->crud->addField([
            "name" => "avatar_url",
            "label" => "Foto de Perfil",
            "type" => "url",
            "tab" => "Perfil",
        ]);
        $this->crud->addField([
            "name" => "slug",
            "label" => "Slug",
            "type" => "text",
            "tab" => "Perfil",
        ]);

        $this->crud->addField([
            "name" => "links",
            "label" => "Social Links",
            "type" => "table",
            "hint" =>
                'Links para redes sociais do instrutor. Em "social media" preencha apenas github, twitter, linkedin, website, se houver. Para consistência, preencha nessa ordem.',
            "max" => 4,
            "min" => 0,
            "columns" => [
                "type" => "Social Media (github, twitter, linkedin, website)",
                "url" => "Url",
            ],
            "tab" => "Perfil",
        ]);

        $this->crud->addField([
            "name" => "cpf",
            "label" => "CPF",
            "type" => "text",
            "tab" => "Cadastro",
        ]);

        $this->crud->addField([
            "name" => "birth_date",
            "label" => "Data de Nascimento",
            "type" => "date",
            "tab" => "Cadastro",
        ]);
        $this->crud->addField([
            "name" => "discord_username",
            "label" => "Username Discord",
            "type" => "text",
            "tab" => "Cadastro",
        ]);
        $this->crud->addField([
            "name" => "github_username",
            "label" => "Username Github",
            "type" => "text",
            "tab" => "Cadastro",
        ]);
        $this->crud->addField([
            "name" => "bank_data",
            "label" => "Dados Bancários",
            "type" => "textarea",
            "tab" => "Cadastro",
        ]);
        $this->crud->addField([
            "name" => "address",
            "label" => "Endereço",
            "type" => "textarea",
            "tab" => "Cadastro",
        ]);
        $this->crud->addField([
            "name" => "phone",
            "label" => "Telefone",
            "type" => "text",
            "tab" => "Cadastro",
        ]);

        $this->crud->addField([
            "name" => "details",
            "label" => "Detalhes e Infos importantes",
            "type" => "textarea",
            "tab" => "Cadastro",
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
