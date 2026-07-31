<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'name',
    'show' => false,
    'maxWidth' => '2xl',
    'title' => null,
]));

foreach ($attributes->all() as $__key => $__value) {
    if (in_array($__key, $__propNames)) {
        $$__key = $$__key ?? $__value;
    } else {
        $__newAttributes[$__key] = $__value;
    }
}

$attributes = new \Illuminate\View\ComponentAttributeBag($__newAttributes);

unset($__propNames);
unset($__newAttributes);

foreach (array_filter(([
    'name',
    'show' => false,
    'maxWidth' => '2xl',
    'title' => null,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
$maxWidth = [
    'sm' => 'agro-dialog--xs',
    'md' => 'agro-dialog--sm',
    'lg' => 'agro-dialog--compact',
    'xl' => 'agro-dialog--md',
    '2xl' => 'agro-dialog--md',
    'full' => 'agro-dialog--full',
][$maxWidth] ?? 'agro-dialog--lg';

$accessibleTitle = $title ?: Illuminate\Support\Str::headline($name);
?>

<div
    x-data="{
        show: <?php echo \Illuminate\Support\Js::from($show)->toHtml() ?>,
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
    x-on:open-modal.window="$event.detail === '<?php echo e($name); ?>' && open()"
    x-on:close-modal.window="$event.detail === '<?php echo e($name); ?>' && close()"
    x-on:close.stop="close()"
    x-on:keydown.escape.window="close()"
    x-on:keydown.tab="trap($event)"
    x-on:click.self="close()"
    x-show="show"
    x-transition.opacity.duration.150ms
    class="agro-dialog-overlay"
    style="display: <?php echo e($show ? 'flex' : 'none'); ?>;"
>
    <div
        x-ref="panel"
        x-show="show"
        @click.stop
        role="dialog"
        aria-modal="true"
        aria-label="<?php echo e($accessibleTitle); ?>"
        tabindex="-1"
        class="agro-dialog <?php echo e($maxWidth); ?>"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 translate-y-3 scale-95"
        x-transition:enter-end="opacity-100 translate-y-0 scale-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 translate-y-0 scale-100"
        x-transition:leave-end="opacity-0 translate-y-3 scale-95"
    >
        <?php echo e($slot); ?>

    </div>
</div>
<?php /**PATH G:\PROYECTOS\FUNDO_PARQUE v04\resources\views/components/modal.blade.php ENDPATH**/ ?>