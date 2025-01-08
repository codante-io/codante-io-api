<?php

namespace App\Http\Controllers\Admin;

use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

/**
 * Class CommentCrudController
 *
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class CommentCrudController extends CrudController
{
    // use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    // use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;

    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;

    /**
     * Configure the CrudPanel object. Apply settings to all operations.
     *
     * @return void
     */
    public function setup()
    {
        CRUD::setModel(\App\Models\Comment::class);
        CRUD::setRoute(config('backpack.base.route_prefix').'/comment');
        CRUD::setEntityNameStrings('comment', 'comments');
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
        CRUD::column('id');
        CRUD::column('user_id');
        CRUD::column('comment');
        CRUD::addColumn([
            'name' => 'commentable_url',
            'label' => 'URL',
            'type' => 'url',
        ]);
        CRUD::addColumn([
            'name' => 'responded',
            'label' => 'Respondido pela equipe',
            'type' => 'closure',
            'function' => function ($entry) {
                $teamUserIds = [395, 397, 1];
                if (
                    $entry->replying_to !== null ||
                    in_array($entry->user_id, $teamUserIds)
                ) {
                    return '-';
                }

                $hasResponseFromTeam = \App\Models\Comment::where(
                    'replying_to',
                    $entry->id
                )
                    ->whereIn('user_id', $teamUserIds)
                    ->exists();

                return $hasResponseFromTeam ? 'Respondido' : 'Não respondido';
            },
        ]);
    }
}
