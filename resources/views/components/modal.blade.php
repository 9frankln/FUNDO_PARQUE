@props([
    'name',
    'show' => false,
    'maxWidth' => '2xl',
    'title' => null,
])

@php
$maxWidth = [
    'sm' => 'agro-dialog--xs',
    'md' => 'agro-dialog--sm',
    'lg' => 'agro-dialog--compact',
    'xl' => 'agro-dialog--md',
    '2xl' => 'agro-dialog--md',
    'full' => 'agro-dialog--full',
][$maxWidth] ?? 'agro-dialog--lg';

$accessibleTitle = $title ?: Illuminate\Support\Str::headline($name);
@endphp

<div
    x-data="{
        show: @js($show),
        trigger: null,
        init() {
            this.$watch('show', value => this.sync(value));
            if (this.show) this.sync(true);
        },
        destroy() {
            document.body.classList.remove('overflow-hidden');
        },
        sync(value) {
            document.body.classList.toggle('overflow-hidden', value);
            if (value) this.$nextTick(() => this.focusFirst());
        },
        open() {
            this.trigger = document.activeElement;
            this.show = true;
        },
        close() {
            if (!this.show) return;
            this.show = false;
            this.$nextTick(() => this.trigger?.isConnected && this.trigger.focus());
        },
        focusables() {
            const selector = 'a[href], button, input:not([type=\'hidden\']), textarea, select, details, [tabindex]:not([tabindex=\'-1\'])';
            return [...$el.querySelectorAll(selector)]
                .filter(element => !element.disabled && element.getClientRects().length > 0);
        },
        focusFirst() {
            (this.focusables()[0] || this.$refs.panel)?.focus();
        },
        trap(event) {
            const items = this.focusables();
            if (items.length === 0) {
                event.preventDefault();
                this.$refs.panel?.focus();
                return;
            }

            const first = items[0];
            const last = items[items.length - 1];
            if (event.shiftKey && document.activeElement === first) {
                event.preventDefault();
                last.focus();
            } else if (!event.shiftKey && document.activeElement === last) {
                event.preventDefault();
                first.focus();
            }
        },
    }"
    x-on:open-modal.window="$event.detail === '{{ $name }}' && open()"
    x-on:close-modal.window="$event.detail === '{{ $name }}' && close()"
    x-on:close.stop="close()"
    x-on:keydown.escape.window="close()"
    x-on:keydown.tab="trap($event)"
    x-on:click.self="close()"
    x-show="show"
    x-transition.opacity.duration.150ms
    class="agro-dialog-overlay"
    style="display: {{ $show ? 'flex' : 'none' }};"
>
    <div
        x-ref="panel"
        x-show="show"
        @click.stop
        role="dialog"
        aria-modal="true"
        aria-label="{{ $accessibleTitle }}"
        tabindex="-1"
        class="agro-dialog {{ $maxWidth }}"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 translate-y-3 scale-95"
        x-transition:enter-end="opacity-100 translate-y-0 scale-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 translate-y-0 scale-100"
        x-transition:leave-end="opacity-0 translate-y-3 scale-95"
    >
        {{ $slot }}
    </div>
</div>
