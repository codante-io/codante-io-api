<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\WorkshopRequest;
use Artisan;
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
        CRUD::setRoute(config("backpack.base.route_prefix") . "/workshop");
        CRUD::setEntityNameStrings("workshop", "workshops");

        $this->crud->addSaveAction([
            "name" => "save_action_one",
            "redirect" => function ($crud, $request, $itemId) {
                Artisan::call("cache:clear");
                return $crud->route;
            }, // what's the redirect URL, where the user will be taken after saving?

            // OPTIONAL:
            "button_text" => "Salvar e Limpar Cache! (streaming)", // override text appearing on the button
            // You can also provide translatable texts, for example:
            // 'button_text' => trans('backpack::crud.save_action_one'),
            "visible" => function ($crud) {
                return true;
            }, // customize when this save action is visible for the current operation
            "referrer_url" => function ($crud, $request, $itemId) {
                return $crud->route;
            }, // override http_referrer_url
            "order" => 1, // change the order save actions are in
        ]);
    }

    /**
     * Define what happens when the List operation is loaded.
     *
     * @see  https://backpackforlaravel.com/docs/crud-operation-list-entries
     * @return void
     */
    protected function setupListOperation()
    {
        // create a filter to only show standalone workshops
        $this->crud->addFilter(
            [
                "name" => "is_standalone",
                "type" => "simple",
                "label" => "É independente?",
            ],
            true,
            function ($value) {
                // if the filter is active
                // convert "true" to true
                $value = filter_var($value, FILTER_VALIDATE_BOOLEAN);
                $this->crud->addClause("where", "is_standalone", $value);
            }
        );

        CRUD::column("id");
        CRUD::column("name");
        CRUD::column("instructor_id");
        CRUD::column("challenge_id");
        CRUD::column("track_id");
        CRUD::column("track_position");
        CRUD::column("status");
        CRUD::column("created_at");
        CRUD::column("published_at");
        CRUD::column("updated_at");
        CRUD::column("short_description");
        CRUD::column("description");
        CRUD::column("slug");
        CRUD::column("is_standalone");
        CRUD::column("difficulty");
        CRUD::column("duration_in_minutes");
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

        CRUD::field("name");
        CRUD::field("short_description")
            ->label("Resumo")
            ->limit(255);
        CRUD::field("description")
            ->label("Descrição (markdown)")
            ->type("easymde")
            ->easymdeAttributes(["spellChecker" => false]);
        CRUD::field("video_url")
            ->type("url")
            ->label("Link do Vídeo");
        CRUD::field("slug")
            ->type("slug")
            ->hint("Se não preenchido, será gerado automaticamente")
            ->target("name");

        CRUD::field("streaming_url")->type("text");

        $this->crud->addField([
            "name" => "status",
            "label" => "Status",
            "type" => "radio",
            "hint" =>
                "Se for streaming, preencha o link do streaming acima. Depois use a opção SALVAR LIMPANDO O CACHE",
            "options" => [
                "archived" => "archived",
                "draft" => "draft",
                "published" => "published",
                "streaming" => "streaming",
                "soon" => "soon",
                "unlisted" => "unlisted",
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
            // Table
            "name" => "resources",
            "label" => "Recursos",
            "type" => "table",
            "entity_singular" => "resource", // used on the "Add X" button
            "columns" => [
                "name" => "Nome do Recurso",
                "type" => "Tipo: 'file' | 'figma' | 'url' | 'github'",
                "url" => "Resource URL",
            ],
            "max" => 10, // maximum rows allowed in the table
            "min" => 0, // minimum rows allowed in the table
        ]);

        CRUD::field("duration_in_minutes");
        $this->crud->addField([
            "name" => "instructor_id",
            "label" => "Instrutor",
            "type" => "select",
            "model" => "App\Models\Instructor",
            "entity" => "instructor",
            "attribute" => "name",
        ]);
        $this->crud->addField([
            "name" => "tracks",
            "label" => "Trilhas",
            "type" => "relationship",
        ]);
        $this->crud->addField([
            "name" => "tags",
            "label" => "Tags",
            "type" => "relationship",
        ]);

        $this->crud->addField([
            "name" => "challenge_id",
            "label" => "Mini Projeto",
            "type" => "select2",
            "model" => "App\Models\Challenge",
            "entity" => "challenge",
            "attribute" => "name",
            "hint" =>
                'Se esse workshop é uma resolução de mini-projeto, desmarque a opção "é independente" e selecione o mini-projeto aqui.',
        ]);

        $this->crud->addField([
            "name" => "image_url",
            "label" => "Imagem",
            "type" => "image",
            "crop" => true,
            "aspect_ratio" => 16 / 9,
            // 'upload' => true,
            "disk" => "s3",
        ]);

        CRUD::field("is_standalone")
            ->label("É independente?")
            ->hint("Se não for resolução de mini-projeto, marque essa opção.");
        CRUD::field("featured")
            ->label("Featured")
            ->hint('Por exemplo, "landing"');
        CRUD::field("published_at")->type("datetime");
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
