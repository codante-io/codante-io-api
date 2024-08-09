<x-mail::message>
  # Você se inscreveu em um novo {{$workshop->is_standalone ? "Workshop" : "Tutorial"}}!

  Maravilha, {{ $user->name }}! Você está participando do {{$workshop->is_standalone ? "Workshop" : "Tutorial"}}: **{{
  $workshop->name }}**.

  Agora é mão na massa!

  <x-mail::button :url="'https://codante.io/workshops/' . $workshop->slug">
    Ir para Workshop
  </x-mail::button>

  A gente se vê,<br>
  Equipe Codante
</x-mail::message>