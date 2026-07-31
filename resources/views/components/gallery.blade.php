@props([
    'items' => collect(),
    'title' => 'El fundo, visto desde adentro',
    'content' => 'Personas, animales, infraestructura y jornadas que construyen nuestra historia.',
    'eyebrow' => 'Registro visual',
])

@php
    $galleryItems = collect($items)->values();
    $categories = $galleryItems
        ->unique('category')
        ->mapWithKeys(fn (array $item) => [$item['category'] => $item['category_label']]);
@endphp

@if($galleryItems->isNotEmpty())
<section id="galeria" class="landing-section scroll-mt-28" aria-labelledby="gallery-title">
    <div
        class="landing-gallery"
        x-data="{
            items: @js($galleryItems),
            category: 'all',
            activeId: @js($galleryItems->first()['id']),
            timer: null,
            get filtered() { return this.category === 'all' ? this.items : this.items.filter((item) => item.category === this.category) },
            get current() { return this.filtered.find((item) => item.id === this.activeId) || this.filtered[0] || null },
            start() {
                this.stop();
                if (this.filtered.length > 1 && !window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
                    this.timer = setInterval(() => this.step(1), 5200);
                }
            },
            stop() { if (this.timer) clearInterval(this.timer); this.timer = null },
            selectCategory(category) { this.category = category; this.activeId = this.filtered[0]?.id || null; this.start() },
            select(item) { this.activeId = item.id; this.start() },
            step(offset) {
                if (!this.filtered.length) return;
                const index = Math.max(0, this.filtered.findIndex((item) => item.id === this.current?.id));
                this.activeId = this.filtered[(index + offset + this.filtered.length) % this.filtered.length]?.id || null;
            },
            open() {
                const current = Math.max(0, this.filtered.findIndex((item) => item.id === this.current?.id));
                $dispatch('open-lightbox', { images: this.filtered.map((item) => item.full), captions: this.filtered.map((item) => item.caption), current });
            }
        }"
        x-init="start()"
        @mouseenter="stop()"
        @mouseleave="start()"
        @focusin="stop()"
        @focusout="if (!$el.contains($event.relatedTarget)) start()"
        @touchstart.passive="stop()"
        @touchend.passive="start()"
        @visibilitychange.window="document.hidden ? stop() : start()"
        @keydown.left.window="if (activeLightbox === null) { step(-1); start() }"
        @keydown.right.window="if (activeLightbox === null) { step(1); start() }"
    >
        <div class="landing-section-heading">
            <div>
                <p class="landing-kicker">{{ $eyebrow }}</p>
                <h2 id="gallery-title">{{ $title }}</h2>
                <p>{{ $content }}</p>
            </div>
            <div class="landing-gallery-count"><strong x-text="filtered.length"></strong><span>fotografías</span></div>
        </div>

        <div class="landing-gallery-filters" aria-label="Filtrar galería">
            <button type="button" @click="selectCategory('all')" :class="category === 'all' && 'is-active'">Todo</button>
            @foreach($categories as $category => $label)
                <button type="button" @click="selectCategory('{{ $category }}')" :class="category === '{{ $category }}' && 'is-active'">{{ $label }}</button>
            @endforeach
        </div>

        <div class="landing-gallery-stage">
            <button type="button" class="landing-gallery-main" @click="open()" aria-label="Ampliar fotografía seleccionada">
                <template x-if="current">
                    <img class="landing-framed-image" :src="current.full" :alt="current.caption" :style="`--media-focus-x: ${current.focus_x}%; --media-focus-y: ${current.focus_y}%; --media-zoom: ${current.zoom};`" loading="lazy" decoding="async" x-effect="current?.id; const zoom = Number(current?.zoom || 1); if ($el.animate && !window.matchMedia('(prefers-reduced-motion: reduce)').matches) $el.animate([{ opacity: .35, transform: `scale(${zoom * 1.012})` }, { opacity: 1, transform: `scale(${zoom})` }], { duration: 480, easing: 'ease-out' })">
                </template>
                <span class="landing-gallery-shade"></span>
                <span class="landing-gallery-caption"><small x-text="current?.category_label"></small><strong x-text="current?.caption"></strong></span>
                <span class="landing-gallery-expand"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="m15 15 5 5m-3-10a7 7 0 1 1-14 0 7 7 0 0 1 14 0Zm-7-3v6m-3-3h6"/></svg></span>
            </button>

            <div class="landing-gallery-aside">
                <div class="landing-gallery-copy">
                    <span>Selección actual</span>
                    <strong x-text="current?.caption"></strong>
                    <p>Explora cada área del fundo. Usa filtros, miniaturas o flechas del teclado.</p>
                </div>
                <div class="landing-gallery-controls">
                    <button type="button" @click="step(-1)" aria-label="Fotografía anterior"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m15 18-6-6 6-6"/></svg></button>
                    <button type="button" @click="step(1)" aria-label="Fotografía siguiente"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m9 6 6 6-6 6"/></svg></button>
                </div>
            </div>
        </div>

        <div class="landing-gallery-rail" role="list" aria-label="Fotografías disponibles">
            <template x-for="item in filtered" :key="item.id">
                <button type="button" role="listitem" @click="select(item)" :class="current?.id === item.id && 'is-active'" :aria-label="`Ver ${item.caption}`">
                    <img class="landing-framed-image" :src="item.thumb" :alt="item.caption" :style="`--media-focus-x: ${item.focus_x}%; --media-focus-y: ${item.focus_y}%; --media-zoom: ${item.zoom};`" loading="lazy" decoding="async">
                    <span x-text="item.category_label"></span>
                </button>
            </template>
        </div>
    </div>
</section>
@endif
