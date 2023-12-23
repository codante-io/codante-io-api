<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\CertificateRequest;
use App\Models\Certificate;
use App\Notifications\Discord;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

/**
 * Class CertificateCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class CertificateCrudController extends CrudController
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
    CRUD::setModel(\App\Models\Certificate::class);
    CRUD::setRoute(config("backpack.base.route_prefix") . "/certificate");
    CRUD::setEntityNameStrings("certificate", "certificates");
  }

  /**
   * Define what happens when the List operation is loaded.
   *
   * @see  https://backpackforlaravel.com/docs/crud-operation-list-entries
   * @return void
   */
  protected function setupListOperation()
  {
    CRUD::column("id");
    CRUD::column("user_id");
    CRUD::column("source_type");
    CRUD::column("status");
  }

  /**
   * Define what happens when the Create operation is loaded.
   *
   * @see https://backpackforlaravel.com/docs/crud-operation-create
   * @return void
   */
  protected function setupCreateOperation()
  {
    CRUD::setValidation(CertificateRequest::class);

    CRUD::field("user_id")->label("Usuário");

    $this->crud->addField([
      "name" => "source_type",
      "label" => "Certificado gerado a partir de",
      "type" => "radio",
      "options" => [
        "workshop" => "Workshop",
        "challenge" => "Mini Projeto",
      ],
      "wrapper" => [
        "class" => "form-group col-md-6",
      ],
    ]);

    CRUD::field("workshop_id")->label("Workshop");
    CRUD::field("challenge_id")->label("Mini Projeto");

    $this->crud->addField([
      "name" => "metadata",
      "label" => "Valores adicionais",
      "type" => "table",
      "entity_singular" => "metadata", // used on the "Add X" button
      "columns" => [
        "type" => "'time'",
        "value" => "Valor (ex: 2 horas e 30 minutos)",
      ],
      "max" => 10, // maximum rows allowed in the table
      "min" => 0, // minimum rows allowed in the table
    ]);

    $this->crud->addField([
      "name" => "status",
      "label" => "Status",
      "type" => "radio",
      "options" => [
        "pending" => "Pending",
        "published" => "Published",
      ],
      "wrapper" => [
        "class" => "form-group col-md-6",
      ],
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
