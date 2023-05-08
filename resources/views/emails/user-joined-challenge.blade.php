<x-mail::message>
  # Você está participando de um novo Mini Projeto!

  Maravilha, {{ $user->name }}! Você está participando do Mini Projeto: **{{ $challenge->name }}**.

  Agora é mão na massa! Acesse o Mini Projeto e comece a codar! Qualquer dúvida, estamos à disposição!

  <x-mail::button :url="'https://codante.io/mini-projetos/' . $challenge->slug">
    Ver Mini Projeto
  </x-mail::button>

  A gente se vê,<br>
  Equipe Codante
</x-mail::message>