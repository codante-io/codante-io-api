<?php

namespace App\Http\Controllers\Admin;

use Backpack\CRUD\app\Http\Controllers\CrudController;

class BackupCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;

    public function setup()
    {
        $this->crud->setModel('App\\Models\\Backup');
        $this->crud->setRoute('admin/backups');
        $this->crud->setEntityNameStrings('Backups', 'Backups');
    }

    protected function setupUpdateOperation()
    {
        $this->setupCreateOperation();
    }

    protected function setupListOperation()
    {
        $this->crud->addColumns([
            [
                'name' => 'id',
                'label' => 'ID',
            ],

            [
                'name' => 'name',
                'label' => 'Nome',
            ],

            [
                'name' => 'size_gb',
                'label' => 'Tamanho em GB',
            ],

            [
                'name' => 'number_of_files',
                'label' => 'Número de Arquivos',
            ],

            [
                'name' => 'details',
                'label' => 'Observações',
            ],

            [
                'label' => 'Workshops IDs',
                'type' => 'select_multiple',
                'name' => 'workshops',
                'entity' => 'workshops',
                'attribute' => 'id',
                'model' => 'App\Models\Workshop',
            ],

            [
                'label' => 'Challenges IDs',
                'type' => 'select_multiple',
                'name' => 'challenges',
                'entity' => 'challenges',
                'attribute' => 'id',
                'model' => 'App\Models\Challenge',
            ],

            [
                'label' => 'Disco / HD',
                'type' => 'select_multiple',
                'name' => 'disks',
                'entity' => 'disks',
                'attribute' => 'name',
                'model' => 'App\Models\BackupDisk',
            ],
        ]);
    }

    protected function setupCreateOperation()
    {
        $this->crud->addFields([
            [
                'name' => 'id',
                'label' => 'Backup ID',
                'type' => 'number',
            ],

            [
                'name' => 'name',
                'label' => 'Nome do Backup',
                'type' => 'text',
            ],

            [
                'name' => 'size_gb',
                'label' => 'Tamanho (GB)',
                'type' => 'number',
            ],

            [
                'name' => 'number_of_files',
                'label' => 'Número de Arquivos',
                'type' => 'number',
            ],

            [
                'name' => 'details',
                'label' => 'Detalhes',
                'type' => 'textarea',
            ],

            [
                'label' => 'Workshops IDs',
                'type' => 'select_multiple',
                'name' => 'workshops',
                'entity' => 'workshops',
                'attribute' => 'id',
                'model' => 'App\Models\Workshop',
                'pivot' => true,
            ],

            [
                'label' => 'Challenges IDs',
                'type' => 'select_multiple',
                'name' => 'challenges',
                'entity' => 'challenges',
                'attribute' => 'id',
                'model' => 'App\Models\Challenge',
                'pivot' => true,
            ],

            [
                'name' => 'disks',
                'label' => 'Discos / HDs',
                'entity' => 'disks',
                'model' => 'App\Models\BackupDisk',
                'attribute' => 'name',
                'type' => 'select2_multiple',
                'pivot' => true,
            ],

            [
                'name' => 'created_at',
                'label' => 'Data de Criação do Backup',
                'type' => 'date',
            ],
        ]);
    }
}
