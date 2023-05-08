<x-mail::message>
  # Seja muito bem vindo ao Codante!

  Olá {{ $user->name }}, agora você faz parte do Codante.io!

  Fique à vontade para explorar nossos [Workshops](https://codante.io/workshops) e [Mini
  Projetos](https://codante.io/mini-projetos). Qualquer dúvida, estamos à disposição!

  Agora, uma pergunta: Você já faz parte da nossa comunidade no Discord? Se não, corre lá e vem bater um papo com a
  gente!


  <x-mail::button :url="'https://discord.gg/PqgmPNS4vg'">
    Entrar no Discord
  </x-mail::button>

  E claro, qualquer dúvida, chama a gente, ok?

  Até mais,<br>
  Equipe Codante
</x-mail::message>