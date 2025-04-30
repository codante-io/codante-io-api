<?php

namespace Tests\Feature\Services;

use App\Models\Challenge;
use App\Models\Lesson;
use App\Services\Discord;
use App\Services\OpenClosedChallengeLessonsRobot;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class OpenClosedChallengeLessonsRobotTest extends TestCase
{
    use RefreshDatabase;

    protected int $hackatonTechnologyId = 225;

    protected function setUp(): void
    {
        parent::setUp();
    }

    /** @test */
    public function it_sends_notification_when_challenge_has_no_lessons_and_is_not_hackaton()
    {
        // Arrange
        $mock = Mockery::mock(Discord::class);
        $mock->shouldReceive('notify')
            ->with('==== 🔍 Iniciando verificação de Mini Projetos e suas aulas... ====', 'notificacoes-site')
            ->once();
        $mock->shouldReceive('notify')
            ->with(Mockery::pattern('/MP publicado mas não possui aulas associadas/'), 'notificacoes-site')
            ->once();
        $mock->shouldReceive('notify')
            ->with('==== 🎉 Finalizada verificação de Mini Projetos e suas aulas. ====', 'notificacoes-site')
            ->once();

        $tag = \App\Models\Tag::factory()->create();
        $challenge = Challenge::factory()->create([
            'status' => 'published',
            'main_technology_id' => $tag->id,
        ]);

        // Act
        (new OpenClosedChallengeLessonsRobot($mock))->handle();

        // Assert
        $mock->shouldHaveReceived('notify')
            ->with('==== 🔍 Iniciando verificação de Mini Projetos e suas aulas... ====', 'notificacoes-site')
            ->once();
        $mock->shouldHaveReceived('notify')
            ->with(Mockery::pattern('/MP publicado mas não possui aulas associadas/'), 'notificacoes-site')
            ->once();
        $mock->shouldHaveReceived('notify')
            ->with('==== 🎉 Finalizada verificação de Mini Projetos e suas aulas. ====', 'notificacoes-site')
            ->once();
    }

    /** @test */
    public function it_does_not_send_notification_when_challenge_has_no_lessons_but_is_hackaton()
    {
        // Arrange
        $mock = Mockery::mock(Discord::class);
        $mock->shouldReceive('notify')
            ->with('==== 🔍 Iniciando verificação de Mini Projetos e suas aulas... ====', 'notificacoes-site')
            ->once();
        $mock->shouldReceive('notify')
            ->with('==== 🎉 Finalizada verificação de Mini Projetos e suas aulas. ====', 'notificacoes-site')
            ->once();

        $tag = \App\Models\Tag::factory()->create([
            'id' => $this->hackatonTechnologyId,
        ]);
        Challenge::factory()->create([
            'status' => 'published',
            'main_technology_id' => $this->hackatonTechnologyId,
        ]);

        // Act
        (new OpenClosedChallengeLessonsRobot($mock))->handle();

        // Assert
        $mock->shouldHaveReceived('notify')
            ->with('==== 🔍 Iniciando verificação de Mini Projetos e suas aulas... ====', 'notificacoes-site')
            ->once();
        $mock->shouldHaveReceived('notify')
            ->with('==== 🎉 Finalizada verificação de Mini Projetos e suas aulas. ====', 'notificacoes-site')
            ->once();

    }

    /** @test */
    public function it_sends_notification_when_open_challenge_has_closed_lessons()
    {
        // Arrange
        $mock = Mockery::mock(Discord::class);
        $mock->shouldReceive('notify')
            ->with('==== 🔍 Iniciando verificação de Mini Projetos e suas aulas... ====', 'notificacoes-site')
            ->once();
        $mock->shouldReceive('notify')
            ->with(Mockery::pattern('/MP aberto mas existem aulas que não estão disponíveis para todos os usuários/'), 'notificacoes-site')
            ->once();
        $mock->shouldReceive('notify')
            ->with('==== 🎉 Finalizada verificação de Mini Projetos e suas aulas. ====', 'notificacoes-site')
            ->once();

        $tag = \App\Models\Tag::factory()->create();
        $challenge = Challenge::factory()->create([
            'status' => 'published',
            'is_premium' => false,
            'main_technology_id' => $tag->id,
        ]);

        Lesson::factory()->create([
            'lessonable_type' => 'App\Models\Challenge',
            'lessonable_id' => $challenge->id,
            'available_to' => 'pro',
        ]);

        // Act
        (new OpenClosedChallengeLessonsRobot($mock))->handle();

        // Assert
        $mock->shouldHaveReceived('notify')
            ->with('==== 🔍 Iniciando verificação de Mini Projetos e suas aulas... ====', 'notificacoes-site')
            ->once();
        $mock->shouldHaveReceived('notify')
            ->with(Mockery::pattern('/MP aberto mas existem aulas que não estão disponíveis para todos os usuários/'), 'notificacoes-site')
            ->once();
        $mock->shouldHaveReceived('notify')
            ->with('==== 🎉 Finalizada verificação de Mini Projetos e suas aulas. ====', 'notificacoes-site')
            ->once();
    }

    /** @test */
    public function it_sends_notification_when_closed_challenge_has_all_lessons_open()
    {
        // Arrange
        $mock = Mockery::mock(Discord::class);
        $mock->shouldReceive('notify')
            ->with('==== 🔍 Iniciando verificação de Mini Projetos e suas aulas... ====', 'notificacoes-site')
            ->once();
        $mock->shouldReceive('notify')
            ->with(Mockery::pattern('/MP fechado mas todas as aulas estão abertas/'), 'notificacoes-site')
            ->once();
        $mock->shouldReceive('notify')
            ->with('==== 🎉 Finalizada verificação de Mini Projetos e suas aulas. ====', 'notificacoes-site')
            ->once();

        $tag = \App\Models\Tag::factory()->create();
        $challenge = Challenge::factory()->create([
            'status' => 'published',
            'is_premium' => true,
            'main_technology_id' => $tag->id,
        ]);

        Lesson::factory()->create([
            'lessonable_type' => 'App\Models\Challenge',
            'lessonable_id' => $challenge->id,
            'available_to' => 'all',
        ]);

        // Act
        (new OpenClosedChallengeLessonsRobot($mock))->handle();

        // Assert
        $mock->shouldHaveReceived('notify')
            ->with('==== 🔍 Iniciando verificação de Mini Projetos e suas aulas... ====', 'notificacoes-site')
            ->once();
        $mock->shouldHaveReceived('notify')
            ->with(Mockery::pattern('/MP fechado mas todas as aulas estão abertas/'), 'notificacoes-site')
            ->once();
        $mock->shouldHaveReceived('notify')
            ->with('==== 🎉 Finalizada verificação de Mini Projetos e suas aulas. ====', 'notificacoes-site')
            ->once();
    }

    /** @test */
    public function it_does_not_send_notification_when_open_challenge_has_all_lessons_open()
    {
        // Arrange
        $mock = Mockery::mock(Discord::class);
        $mock->shouldReceive('notify')
            ->with('==== 🔍 Iniciando verificação de Mini Projetos e suas aulas... ====', 'notificacoes-site')
            ->once();
        $mock->shouldReceive('notify')
            ->with('==== 🎉 Finalizada verificação de Mini Projetos e suas aulas. ====', 'notificacoes-site')
            ->once();

        $tag = \App\Models\Tag::factory()->create();
        $challenge = Challenge::factory()->create([
            'status' => 'published',
            'is_premium' => false,
            'main_technology_id' => $tag->id,
        ]);

        Lesson::factory()->create([
            'lessonable_type' => 'App\Models\Challenge',
            'lessonable_id' => $challenge->id,
            'available_to' => 'all',
        ]);

        // Act
        (new OpenClosedChallengeLessonsRobot($mock))->handle();

        // Assert
        $mock->shouldHaveReceived('notify')
            ->with('==== 🔍 Iniciando verificação de Mini Projetos e suas aulas... ====', 'notificacoes-site')
            ->once();
        $mock->shouldHaveReceived('notify')
            ->with('==== 🎉 Finalizada verificação de Mini Projetos e suas aulas. ====', 'notificacoes-site')
            ->once();
    }

    /** @test */
    public function it_does_not_send_notification_when_closed_challenge_has_closed_lessons()
    {
        // Arrange
        $mock = Mockery::mock(Discord::class);
        $mock->shouldReceive('notify')
            ->with('==== 🔍 Iniciando verificação de Mini Projetos e suas aulas... ====', 'notificacoes-site')
            ->once();
        $mock->shouldReceive('notify')
            ->with('==== 🎉 Finalizada verificação de Mini Projetos e suas aulas. ====', 'notificacoes-site')
            ->once();

        $tag = \App\Models\Tag::factory()->create();
        $challenge = Challenge::factory()->create([
            'status' => 'published',
            'is_premium' => true,
            'main_technology_id' => $tag->id,
        ]);

        Lesson::factory()->create([
            'lessonable_type' => 'App\Models\Challenge',
            'lessonable_id' => $challenge->id,
            'available_to' => 'pro',
        ]);

        // Act
        (new OpenClosedChallengeLessonsRobot($mock))->handle();

        // Assert
        $mock->shouldHaveReceived('notify')
            ->with('==== 🔍 Iniciando verificação de Mini Projetos e suas aulas... ====', 'notificacoes-site')
            ->once();
        $mock->shouldHaveReceived('notify')
            ->with('==== 🎉 Finalizada verificação de Mini Projetos e suas aulas. ====', 'notificacoes-site')
            ->once();
    }

    /** @test */
    public function it_sends_start_and_end_messages_when_no_published_challenges()
    {
        // Arrange
        $mock = Mockery::mock(Discord::class);
        $mock->shouldReceive('notify')
            ->with('==== 🔍 Iniciando verificação de Mini Projetos e suas aulas... ====', 'notificacoes-site')
            ->once();
        $mock->shouldReceive('notify')
            ->with('==== 🎉 Finalizada verificação de Mini Projetos e suas aulas. ====', 'notificacoes-site')
            ->once();

        // Act
        (new OpenClosedChallengeLessonsRobot($mock))->handle();

        // Assert
        $mock->shouldHaveReceived('notify')
            ->with('==== 🔍 Iniciando verificação de Mini Projetos e suas aulas... ====', 'notificacoes-site')
            ->once();
        $mock->shouldHaveReceived('notify')
            ->with('==== 🎉 Finalizada verificação de Mini Projetos e suas aulas. ====', 'notificacoes-site')
            ->once();
    }
}
