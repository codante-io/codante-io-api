<?php

namespace App\Http\Controllers\Admin;

use Backpack\CRUD\app\Http\Controllers\CrudController;

class BackupDiskCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;

    public function setup()
    {
        $this->crud->setModel('App\\Models\\BackupDisk');
        $this->crud->setRoute('admin/backup-disks');
        $this->crud->setEntityNameStrings('Disco', 'Discos');
    }

    protected function setupUpdateOperation()
    {
        $this->setupCreateOperation();
    }

    protected function setupListOperation()
    {
        $this->crud->addColumn([
            'name' => 'id',
            'label' => 'ID',
        ]);
        $this->crud->addColumn([
            'name' => 'name',
            'label' => 'Nome',
        ]);

        $this->crud->addColumn([
            'name' => 'size_gb',
            'label' => 'Tamanho em GB',
        ]);

        $this->crud->addColumn([
            'name' => 'local',
            'label' => 'Local do Disco',
        ]);
    }

    protected function setupCreateOperation()
    {
        $this->crud->addField([
            'name' => 'id',
            'label' => 'ID',
            'type' => 'number',
        ]);

        $this->crud->addField([
            'name' => 'name',
            'label' => 'Nome',
            'type' => 'text',
        ]);

        $this->crud->addField([
            'name' => 'size_gb',
            'label' => 'Tamanho em GB',
            'type' => 'number',
        ]);

        $this->crud->addField([
            'name' => 'local',
            'label' => 'Local do Disco',
            'type' => 'text',
        ]);
    }
}
