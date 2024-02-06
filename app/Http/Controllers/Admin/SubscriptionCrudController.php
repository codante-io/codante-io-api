<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\SubscriptionRequest;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

/**
 * Class SubscriptionCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class SubscriptionCrudController extends CrudController
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
        CRUD::setModel(\App\Models\Subscription::class);
        CRUD::setRoute(config("backpack.base.route_prefix") . "/subscription");
        CRUD::setEntityNameStrings("subscription", "subscriptions");
    }

    /**
     * Define what happens when the List operation is loaded.
     *
     * @see  https://backpackforlaravel.com/docs/crud-operation-list-entries
     * @return void
     */
    protected function setupListOperation()
    {
        $this->crud->addFilter(
            [
                "name" => "status",
                "type" => "dropdown",
                "label" => "Status",
            ],
            [
                "active" => "Ativos",
                "pending" => "Pendentes",
            ],
            function ($value) {
                $this->crud->addClause("where", "status", $value);
            }
        );

        $this->crud->addFilter(
            [
                "name" => "acquisition_type",
                "type" => "dropdown",
                "label" => "AcquisitionType",
            ],
            [
                "purchase" => "Purchase",
                "free" => "Free",
            ],
            function ($value) {
                $this->crud->addClause("where", "acquisition_type", $value);
            }
        );

        CRUD::column("id");
        CRUD::column("user_id");
        CRUD::column("plan_id")->type("relationship");
        // CRUD::column("provider_id");
        CRUD::column("status");
        CRUD::column("price_paid_in_cents");
        CRUD::column("payment_method");
        // CRUD::column("boleto_url");
        // CRUD::column("boleto_barcode");
        CRUD::column("starts_at");
        CRUD::column("ends_at");
        CRUD::column("canceled_at");
        CRUD::column("acquisition_type");
        // CRUD::column("created_at");
        // CRUD::column("updated_at");

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
        // CRUD::setValidation(Request::class);

        // CRUD::field("id");
        CRUD::field("user_id");
        CRUD::field("plan_id");
        CRUD::field("provider_id");
        CRUD::field("status");
        CRUD::field("price_paid_in_cents");
        CRUD::field("payment_method");
        CRUD::field("boleto_url");
        CRUD::field("boleto_barcode");
        CRUD::field("starts_at");
        CRUD::field("ends_at");
        CRUD::field("canceled_at");
        CRUD::field("acquisition_type");

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
