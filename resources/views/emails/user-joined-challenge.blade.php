<x-mail::message>
  # Você está participando de um novo desafio!

  Maravilha, {{ $user->name }}! Você está participando do desafio **{{ $challenge->name }}**.

  Agora é mão na massa! Acesse o desafio e comece a codar! Qualquer dúvida, estamos à disposição!

  <x-mail::button :url="'https://codante.io/mini-projetos/' . $challenge->slug">
    Ver Desafio
  </x-mail::button>

  A gente se vê,<br>
  Equipe Codante
</x-mail::message>