@props(['url'])
<tr>
  <td class="header">
    {{-- <a href="{{ $url }}" style="display: inline-block;"> --}}
      <div style="display: inline-block;font-weight: bold; font-size:25px">
        {{-- @if (trim($slot) === 'Laravel') --}}
        <img src="https://codante.s3.sa-east-1.amazonaws.com/img/logo/mail-logo.png" class="logo" alt="Codante Logo">
        {{-- @else --}}
        {{-- {{ $slot }} --}}
        {{-- @endif --}}
      </div>
  </td>
</tr>