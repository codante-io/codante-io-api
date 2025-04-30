<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\ChallengeRequest;
use App\Models\Challenge;
use App\Services\Discord;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

/**
 * Class ChallengeCrudController
 *
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class ChallengeCrudController extends CrudController
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
        CRUD::setModel(\App\Models\Challenge::class);
        CRUD::setRoute(config('backpack.base.route_prefix').'/challenge');
        CRUD::setEntityNameStrings('challenge', 'challenges');
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
        CRUD::column('name');
        CRUD::column('status');
        CRUD::column('difficulty');
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
        CRUD::setValidation(ChallengeRequest::class);

        CRUD::field('name')->tab('Principal');
        CRUD::field('short_description')
            ->label('Resumo')
            ->tab('Principal');

        CRUD::field('description')
            ->label('Descrição (markdown)')
            ->type('easymde')
            ->easymdeAttributes(['spellChecker' => false])
            ->tab('Readme');

        $this->crud->addField([
            'name' => 'image_url',
            'label' => 'Imagem',
            'type' => 'upload',
            'upload' => true,
            'disk' => 's3',
            'tab' => 'Principal',
        ]);

        $this->crud->addField([
            'name' => 'tracks',
            'type' => 'relationship',
            'tab' => 'Trilhas',
        ]);

        // CRUD::field('track_position')->type('number')->tab('Principal');
        CRUD::field('slug')
            ->type('slug')
            ->hint('Se não preenchido, será gerado automaticamente')
            ->target('name')
            ->tab('Principal');

        $this->crud->addField([
            'name' => 'status',
            'label' => 'Status',
            'type' => 'radio',
            'tab' => 'Principal',
            'options' => [
                'archived' => 'archived',
                'draft' => 'draft',
                'published' => 'published',
                'soon' => 'soon',
                'unlisted' => 'unlisted',
            ],
            'wrapper' => [
                'class' => 'form-group col-md-6',
            ],
        ]);

        $this->crud->addField([
            'name' => 'difficulty',
            'label' => 'Dificuldade (nível)',
            'type' => 'radio',
            'tab' => 'Principal',
            'options' => [
                'newbie' => 'Iniciante',
                'intermediate' => 'Intermediário',
                'advanced' => 'Avançado',
            ],
            'wrapper' => [
                'class' => 'form-group col-md-6',
            ],
        ]);

        $this->crud->addField([
            'name' => 'category',
            'label' => 'Categoria',
            'type' => 'radio',
            'tab' => 'Principal',
            'options' => [
                'frontend' => 'Front-end',
                'fullstack' => 'Fullstack',
            ],
            'wrapper' => [
                'class' => 'form-group col-md-6',
            ],
        ]);

        $this->crud->addField([
            'name' => 'is_premium',
            'label' => 'É premium?',
            'type' => 'radio',
            'tab' => 'Principal',
            'options' => [
                true => 'Premium',
                false => 'Gratuito',
            ],
            'wrapper' => [
                'class' => 'form-group col-md-6',
            ],
        ]);

        $this->crud->addField([
            'label' => 'Duração (em minutos)',
            'name' => 'duration_in_minutes',
            'type' => 'number',
            'tab' => 'Principal',
            'wrapper' => [
                'class' => 'form-group col-md-6',
            ],
        ]);

        $this->crud->addField([
            'label' => 'Featured',
            'hint' => 'Por exemplo, "landing"',
            'name' => 'featured',
            'tab' => 'Principal',
            'type' => 'text',
            'wrapper' => [
                'class' => 'form-group col-md-6',
            ],
        ]);

        $this->crud->addField([
            'label' => 'Weekly Featured Start Date',
            'hint' => 'A data em que o desafio irá entrar na lista de "Weekly Featured". Se não houver horário, pode considerar 00:00:00',
            'name' => 'weekly_featured_start_date',
            'type' => 'datetime',
            'tab' => 'Principal',
            'wrapper' => [
                'class' => 'form-group col-md-6',
            ],
        ]);

        $this->crud->addField([
            'label' => 'Solution Publish Date',
            'hint' => 'A data em que o desafio será resolvido (ou sua resolução disponibilizada). Se não houver horário, pode considerar 00:00:00',
            'name' => 'solution_publish_date',
            'type' => 'datetime',
            'tab' => 'Principal',
            'wrapper' => [
                'class' => 'form-group col-md-6',
            ],
        ]);

        CRUD::field('repository_name')
            ->type('text')
            ->hint('Nome do repositório no GitHub')
            ->tab('Principal');

        $this->crud->addField([
            // Table
            'name' => 'resources',
            'label' => 'Recursos',
            'tab' => 'Principal',
            'type' => 'table',
            'entity_singular' => 'resource', // used on the "Add X" button
            'columns' => [
                'name' => 'Nome do Recurso',
                'type' => "Tipo: 'file' | 'figma' | 'stackblitz-embed'",
                'url' => 'Resource URL',
            ],
            'max' => 10, // maximum rows allowed in the table
            'min' => 0, // minimum rows allowed in the table
            'hint' => 'O stackblitz-embed vai fazer override do url do github da solução oficial. Use apenas se quiser substituir o link da solução oficial. Coloque o link de embed sem nenhum query parameter.',
        ]);

        $this->crud->addField([
            'name' => 'main_technology_id',
            'label' => 'Tecnologia Principal',
            'type' => 'select',
            'tab' => 'Principal',
            'model' => "App\Models\Tag",
            'entity' => 'mainTechnology',
            'attribute' => 'name',
        ]);

        $this->crud->addField([
            'name' => 'tags',
            'type' => 'relationship',
            'tab' => 'Principal',
        ]);

        $this->crud->addField([
            'name' => 'position',
            'type' => 'number',
            'hint' => 'Posição do desafio na lista',
            'default' => 1,
            'tab' => 'Principal',
            'attributes' => ['step' => 'any'],
        ]);

        $this->crud->addField([
            'name' => 'notifica',
            'type' => 'notify-discord',
            'tab' => 'Ações',
            'data' => [
                'title' => 'Discord: MP Lançado',
                'notification-url' => '/admin/challenge-notification/discord-launched-mp/',
            ],
        ]);

        $this->crud->addField([
            'name' => 'notifica1',
            'type' => 'notify-discord',
            'tab' => 'Ações',
            'data' => [
                'title' => 'Discord: MP Resolução Disponível',
                'notification-url' => '/admin/challenge-notification/discord-launched-solution/',
            ],
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

    protected function notifyDiscordChallengeLaunched($challengeId)
    {
        $challenge = Challenge::findOrFail($challengeId);
        $message = "Fala pessoal (@here)! Acabamos de lançar mais um Mini Projeto no Codante:\n ​ \n**{$challenge->name}!** 🚀\n ​ \nAcesse o link abaixo para acessar o Mini-Projeto e para participar! 👇 \n ​ \n";
        Discord::sendMessage($message);
    }

    protected function notifyDiscordChallengeSolutionLaunched($challengeId)
    {
        $challenge = Challenge::findOrFail($challengeId);
        $message = "Fala pessoal (@here)! Acabamos de disponibilizar no Codante:\n ​ \nResolução do Mini Projeto: **{$challenge->name}!**\n ​ \nNo link abaixo você encontra tanto a resolução em vídeo como o código da resolução! 👇 \n ​ \n";
        Discord::sendMessage($message);
    }
}
