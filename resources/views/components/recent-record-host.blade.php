@props(['active' => false])

<div {{ $attributes }}
     @if($active) x-data x-init="setTimeout(() => $wire.clearRecentRecord(), 15000)" @endif>
    {{ $slot }}
</div>
