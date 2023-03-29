@props(['url'])
<tr>
  <td class="header">
    {{-- <a href="{{ $url }}" style="display: inline-block;"> --}}
      <div style="display: inline-block;font-weight: bold; font-size:25px">
        @if (trim($slot) === 'Laravel')
        <img src="https://laravel.com/img/notification-logo.png" class="logo" alt="Laravel Logo">
        @else
        {{ $slot }}
        @endif
      </div>
  </td>
</tr>