<x-mail::message>
  # Seja muito bem vindo ao Codante!

  Olá {{ $user->name }}, seja bem vindo ao codante!

  Fique à vontade para explorar nossos workshops e mini-projetos. Qualquer dúvida, estamos à disposição!

  <x-mail::button :url="'https://codante.io'">
    Explorar a plataforma
  </x-mail::button>

  Até mais,<br>
  {{ config('app.name') }}
</x-mail::message>