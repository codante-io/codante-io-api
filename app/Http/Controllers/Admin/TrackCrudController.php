<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\TrackRequest;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

/**
 * Class TrackCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class TrackCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;

    public function setup()
    {
        CRUD::setModel(\App\Models\Track::class);
        CRUD::setRoute(config("backpack.base.route_prefix") . "/track");
        CRUD::setEntityNameStrings("track", "tracks");
    }

    protected function setupListOperation()
    {
        // Filtros
        $this->crud->addFilter(
            [
                "type" => "dropdown",
                "name" => "status",
                "label" => "Status",
            ],
            [
                "archived" => "archived",
                "draft" => "draft",
                "published" => "published",
                "soon" => "soon",
            ],
            function ($value) {
                $this->crud->addClause("where", "status", $value);
            }
        );

        $this->crud->addFilter(
            [
                "type" => "dropdown",
                "name" => "difficulty",
                "label" => "Dificuldade",
            ],
            [
                1 => 1,
                2 => 2,
                3 => 3,
            ],
            function ($value) {
                $this->crud->addClause("where", "difficulty", $value);
            }
        );

        // Colunas
        CRUD::addColumns([
            [
                "name" => "name",
                "label" => "Nome",
                "type" => "text",
            ],
            [
                "name" => "status",
                "label" => "Status",
                "type" => "text",
            ],
            [
                "name" => "short_description",
                "label" => "Resumo",
                "type" => "text",
            ],
            [
                "name" => "description",
                "label" => "Descrição",
                "type" => "text",
            ],
            [
                "name" => "difficulty",
                "label" => "Dificuldade",
                "type" => "text",
            ],
            [
                "name" => "duration_in_minutes",
                "label" => "Duração (min)",
                "type" => "text",
            ],
        ]);
    }

    protected function setupCreateOperation()
    {
        // Validação
        CRUD::setValidation(TrackRequest::class);

        // Fields
        $this->crud->addField([
            "name" => "name",
            "label" => "Nome",
            "type" => "text",
        ]);
        $this->crud->addField([
            "name" => "short_description",
            "label" => "Resumo",
            "type" => "text",
        ]);
        $this->crud->addField([
            "name" => "description",
            "label" => "Descrição Completa",
            "type" => "text",
        ]);
        $this->crud->addField([
            "name" => "image_url",
            "label" => "Imagem",
            "type" => "url",
        ]);

        CRUD::field("slug")
            ->type("slug")
            ->hint("Se não preenchido, será gerado automaticamente")
            ->target("name");

        $this->crud->addField([
            "name" => "status",
            "label" => "Status",
            "type" => "radio",
            "options" => [
                "archived" => "archived",
                "draft" => "draft",
                "published" => "published",
                "soon" => "soon",
            ],
        ]);
        $this->crud->addField([
            "name" => "difficulty",
            "label" => "Dificuldade (nível)",
            "type" => "radio",
            "options" => [
                1 => 1,
                2 => 2,
                3 => 3,
            ],
        ]);
        $this->crud->addField([
            "name" => "duration_in_minutes",
            "label" => "Duração (min)",
            "type" => "text",
        ]);

        $this->crud->addField([
            "name" => "challenges",
            "type" => "relationship",
        ]);
        $this->crud->addField([
            "name" => "workshops",
            "type" => "relationship",
        ]);
        $this->crud->addField([
            "name" => "items",
            "type" => "relationship",
        ]);
        CRUD::field("featured")
            ->label("Featured")
            ->hint('Por exemplo, "landing"');
    }

    protected function setupUpdateOperation()
    {
        $this->setupCreateOperation();
    }
}
