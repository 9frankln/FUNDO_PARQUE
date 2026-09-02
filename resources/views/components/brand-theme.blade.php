<style>
    :root {
@foreach($branding->paletteRgb() as $shade => $rgb)
        --brand-{{ $shade }}: {{ $rgb }};
@endforeach
        --accent: rgb(var(--brand-700));
        --accent-strong: rgb(var(--brand-800));
        --accent-soft: rgb(var(--brand-100));
        --bg-primary: color-mix(in srgb, rgb(var(--brand-50)) 38%, #f7f8f7);
        --bg-tertiary: color-mix(in srgb, rgb(var(--brand-50)) 62%, #eef1ef);
        --bg-elevated: color-mix(in srgb, rgb(var(--brand-100)) 42%, #e5e9e6);
        --text-primary: color-mix(in srgb, rgb(var(--brand-950)) 38%, #17202a);
        --text-secondary: color-mix(in srgb, rgb(var(--brand-800)) 52%, #3d4650);
        --text-muted: color-mix(in srgb, rgb(var(--brand-700)) 40%, #49555f);
        --border-primary: color-mix(in srgb, rgb(var(--brand-300)) 48%, #b6c0b9);
        --dialog-overlay: rgb(var(--brand-950) / 0.76);
    }
    .dark {
        --accent: rgb(var(--brand-300));
        --accent-strong: rgb(var(--brand-200));
        --accent-soft: rgb(var(--brand-400) / 0.12);
        --bg-primary: color-mix(in srgb, rgb(var(--brand-950)) 58%, #0d1210);
        --bg-secondary: color-mix(in srgb, rgb(var(--brand-950)) 32%, #151a17);
        --bg-tertiary: color-mix(in srgb, rgb(var(--brand-950)) 48%, #101512);
        --bg-elevated: color-mix(in srgb, rgb(var(--brand-900)) 38%, #1b211d);
        --text-primary: color-mix(in srgb, rgb(var(--brand-50)) 86%, #ffffff);
        --text-secondary: color-mix(in srgb, rgb(var(--brand-200)) 60%, #cbd5e1);
        --text-muted: color-mix(in srgb, rgb(var(--brand-300)) 42%, #a3b4a8);
        --border-primary: color-mix(in srgb, rgb(var(--brand-700)) 52%, #3a4740);
        --dialog-overlay: rgb(var(--brand-950) / 0.86);
    }
</style>
