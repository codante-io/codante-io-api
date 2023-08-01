<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\TechnicalAssessmentRequest;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

/**
 * Class TechnicalAssessmentCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class TechnicalAssessmentCrudController extends CrudController
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
        CRUD::setModel(\App\Models\TechnicalAssessment::class);
        CRUD::setRoute(
            config("backpack.base.route_prefix") . "/technical-assessment"
        );
        CRUD::setEntityNameStrings(
            "technical assessment",
            "technical assessments"
        );
    }

    /**
     * Define what happens when the List operation is loaded.
     *
     * @see  https://backpackforlaravel.com/docs/crud-operation-list-entries
     * @return void
     */
    protected function setupListOperation()
    {
        CRUD::column("title");
        CRUD::column("status");
        CRUD::column("type");
        CRUD::column("slug");
        CRUD::column("assessment_year");
        CRUD::column("challenge_id");
        CRUD::column("company_name");
        CRUD::column("image_url");
        CRUD::column("description");
        // CRUD::column("assessment_instructions_text");
        CRUD::column("assessment_instructions_url");
        CRUD::column("job_position");
        CRUD::column("jobs_url");
        CRUD::column("updated_at");
        CRUD::column("created_at");

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
        CRUD::setValidation(TechnicalAssessmentRequest::class);

        CRUD::field("title");
        CRUD::field("company_name");
        CRUD::field("status")
            ->type("select_from_array")
            ->options([
                "draft" => "Draft",
                "published" => "Published",
            ]);
        CRUD::field("type")
            ->type("select_from_array")
            ->options([
                "frontend" => "Front End",
                "backend" => "Back End",
                "fullstack" => "FullStack",
            ]);
        CRUD::field("slug")
            ->type("slug")
            ->hint("Se não preenchido, será gerado automaticamente")
            ->target("title");
        $this->crud->addField([
            "name" => "image_url",
            "hint" => "Logo PNG (transparente) da empresa - fundo claro",
            "label" => "Logo da Empresa",
            "type" => "image",
            "crop" => true,
            "aspect_ratio" => 1,
            // 'upload' => true,
            "disk" => "s3",
        ]);
        $this->crud->addField([
            "name" => "image_url_dark",
            "label" => "Imagem - Dark Mode",
            "hint" => "Logo PNG (transparente) da empresa - fundo escuro",
            "type" => "image",
            "crop" => true,
            "aspect_ratio" => 1,
            "disk" => "s3",
        ]);
        CRUD::field("tags")
            ->type("select2_multiple")
            ->label("Tags")
            ->entity("tags")
            ->attribute("name")
            ->model("App\Models\Tag")
            ->pivot(true)
            ->multiple(true);
        CRUD::field("challenge_id");
        CRUD::field("company_url");
        CRUD::field("assessment_description")
            ->type("easymde")
            ->easymdeAttributes(["spellChecker" => false]);
        CRUD::field("assessment_year");
        CRUD::field("assessment_instructions_url");
        CRUD::field("assessment_instructions_text")
            ->type("easymde")
            ->easymdeAttributes([
                "spellChecker" => false,
            ]);
        CRUD::field("job_position");
        CRUD::field("company_url")->type('url');
        CRUD::field("company_headquarters");
        CRUD::field("company_description")->type('textarea');
        CRUD::field("company_size");
        CRUD::field("company_industry");
        CRUD::field("company_linkedin")->type('url');
        CRUD::field("company_github")->type('url');

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
