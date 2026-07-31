<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Conoce <?php echo e($publicFundoName); ?>: ganadería, equinos, infraestructura y procesos de trabajo rural.">
    <meta name="theme-color" content="#071a13">
    <title><?php echo e($publicFundoName); ?> | <?php echo e($branding->name); ?></title>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($heroItems->isNotEmpty()): ?>
        <link rel="preload" as="image" href="<?php echo e($heroItems->first()['full']); ?>" fetchpriority="high">
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    <?php echo app('Illuminate\Foundation\Vite')('resources/css/app.css'); ?>
    <?php echo \Livewire\Mechanisms\FrontendAssets\FrontendAssets::styles(); ?>

    <?php if (isset($component)) { $__componentOriginal6328591391b02ca7d72027c0d6027f6b = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6328591391b02ca7d72027c0d6027f6b = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.brand-theme','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('brand-theme'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal6328591391b02ca7d72027c0d6027f6b)): ?>
<?php $attributes = $__attributesOriginal6328591391b02ca7d72027c0d6027f6b; ?>
<?php unset($__attributesOriginal6328591391b02ca7d72027c0d6027f6b); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal6328591391b02ca7d72027c0d6027f6b)): ?>
<?php $component = $__componentOriginal6328591391b02ca7d72027c0d6027f6b; ?>
<?php unset($__componentOriginal6328591391b02ca7d72027c0d6027f6b); ?>
<?php endif; ?>
    <script>
        (() => {
            const saved = localStorage.getItem('theme');
            document.documentElement.classList.toggle('dark', saved ? saved === 'dark' : true);
        })();
    </script>

    <style>
        .landing-page {
            --landing-bg: #f1f5f1;
            --landing-surface: #ffffff;
            --landing-surface-soft: #e8f0e9;
            --landing-ink: #10241b;
            --landing-muted: #5b7064;
            --landing-line: rgba(17, 54, 37, .13);
            --landing-accent: rgb(var(--brand-600));
            --landing-accent-soft: rgb(var(--brand-100));
            min-width: 320px;
            overflow-x: hidden;
            color: var(--landing-ink);
            background:
                radial-gradient(circle at 10% 5%, rgb(var(--brand-400) / .11), transparent 25rem),
                linear-gradient(180deg, var(--landing-bg), color-mix(in srgb, var(--landing-bg) 92%, #fff));
            font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
        }
        .dark .landing-page {
            --landing-bg: #06140f;
            --landing-surface: #0b2018;
            --landing-surface-soft: #10291f;
            --landing-ink: #f1faf4;
            --landing-muted: #9fb7aa;
            --landing-line: rgba(167, 243, 208, .13);
            --landing-accent: rgb(var(--brand-400));
            --landing-accent-soft: rgb(var(--brand-400) / .12);
            background:
                radial-gradient(circle at 86% 8%, rgb(var(--brand-500) / .13), transparent 30rem),
                radial-gradient(circle at 5% 46%, rgb(var(--brand-700) / .13), transparent 26rem),
                var(--landing-bg);
        }
        .landing-container { width: min(100% - 2rem, 92rem); margin-inline: auto; }
        .landing-header { position: fixed; inset: 0 0 auto; z-index: 60; border-bottom: 1px solid transparent; transition: background .2s, border-color .2s, box-shadow .2s; }
        .landing-header.is-scrolled, .landing-header:focus-within { border-color: var(--landing-line); background: color-mix(in srgb, var(--landing-bg) 88%, transparent); box-shadow: 0 14px 40px rgba(1, 12, 7, .11); backdrop-filter: blur(18px); }
        .landing-navbar { display: flex; min-height: 5.25rem; align-items: center; gap: 1rem; }
        .landing-brand { display: inline-flex; min-width: 0; align-items: center; gap: .8rem; text-decoration: none; }
        .landing-brand-mark { display: grid; width: 2.75rem; height: 2.75rem; flex: 0 0 auto; place-items: center; border-radius: 1rem; color: #fff; background: linear-gradient(145deg, rgb(var(--brand-500)), rgb(var(--brand-700))); box-shadow: 0 10px 24px rgb(var(--brand-700) / .28); }
        .landing-brand-mark svg, .landing-brand-mark img { width: 1.45rem; height: 1.45rem; }
        .landing-brand-copy { min-width: 0; line-height: 1.1; }
        .landing-brand-copy strong { display: block; color: var(--landing-ink); font-size: clamp(1.05rem, 2vw, 1.45rem); font-weight: 900; letter-spacing: -.04em; white-space: nowrap; }
        .landing-brand-copy small { display: block; margin-top: .35rem; color: var(--landing-accent); font-size: .58rem; font-weight: 800; letter-spacing: .14em; text-transform: uppercase; white-space: nowrap; }
        .landing-nav-links { display: flex; margin-left: auto; align-items: center; gap: .2rem; padding: .28rem; border: 1px solid var(--landing-line); border-radius: 1rem; background: color-mix(in srgb, var(--landing-surface) 72%, transparent); }
        .landing-nav-links a { border-radius: .75rem; padding: .72rem .9rem; color: var(--landing-muted); font-size: .72rem; font-weight: 800; text-decoration: none; transition: color .18s, background .18s; }
        .landing-nav-links a:hover, .landing-nav-links a:focus-visible { color: var(--landing-ink); background: var(--landing-surface-soft); }
        .landing-nav-links a:last-child { color: var(--landing-accent); background: var(--landing-accent-soft); }
        .landing-nav-actions { display: flex; align-items: center; gap: .55rem; }
        .landing-icon-button { display: grid; width: 2.6rem; height: 2.6rem; place-items: center; border: 1px solid var(--landing-line); border-radius: .85rem; color: var(--landing-muted); background: color-mix(in srgb, var(--landing-surface) 70%, transparent); transition: color .18s, background .18s; }
        .landing-icon-button:hover { color: var(--landing-ink); background: var(--landing-surface-soft); }
        .landing-icon-button svg { width: 1.15rem; height: 1.15rem; }
        .landing-access { display: inline-flex; min-height: 2.65rem; align-items: center; justify-content: center; border-radius: .9rem; padding: .65rem 1.1rem; color: #fff; background: rgb(var(--brand-600)); box-shadow: 0 10px 25px rgb(var(--brand-700) / .22); font-size: .7rem; font-weight: 900; letter-spacing: .05em; text-decoration: none; text-transform: uppercase; transition: transform .18s, background .18s; }
        .landing-access:hover { transform: translateY(-1px); background: rgb(var(--brand-700)); }
        .landing-mobile-menu { border-top: 1px solid var(--landing-line); background: color-mix(in srgb, var(--landing-bg) 96%, transparent); backdrop-filter: blur(18px); }
        .landing-mobile-menu nav { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: .55rem; padding-block: 1rem; }
        .landing-mobile-menu nav a { border: 1px solid var(--landing-line); border-radius: .85rem; padding: .8rem; color: var(--landing-muted); background: var(--landing-surface); font-size: .72rem; font-weight: 800; text-align: center; text-decoration: none; }
        .landing-main { padding-top: 5.25rem; }
        .landing-hero { display: grid; min-height: calc(100svh - 5.25rem); align-items: center; gap: clamp(2rem, 5vw, 5rem); padding-block: clamp(3.5rem, 7vw, 7rem); }
        .landing-kicker { display: inline-flex; align-items: center; gap: .55rem; margin: 0 0 .9rem; color: var(--landing-accent); font-size: .65rem; font-weight: 900; letter-spacing: .18em; text-transform: uppercase; }
        .landing-kicker::before { width: 1.6rem; height: 1px; content: ""; background: currentColor; }
        .landing-hero-copy h1 { max-width: 44rem; margin: 0; font-size: clamp(2.9rem, 6vw, 6rem); font-weight: 900; letter-spacing: -.065em; line-height: .96; text-wrap: balance; }
        .landing-hero-fundo { display: block; margin-top: .65rem; color: var(--landing-accent); font-size: clamp(1rem, 2vw, 1.35rem); font-weight: 800; letter-spacing: -.02em; }
        .landing-hero-copy > p { max-width: 40rem; margin: 1.4rem 0 0; color: var(--landing-muted); font-size: clamp(1rem, 1.45vw, 1.18rem); line-height: 1.75; }
        .landing-hero-actions { display: flex; flex-wrap: wrap; gap: .75rem; margin-top: 2rem; }
        .landing-primary, .landing-secondary { display: inline-flex; min-height: 3.15rem; align-items: center; justify-content: center; gap: .55rem; border-radius: 1rem; padding: .75rem 1.2rem; font-size: .78rem; font-weight: 900; text-decoration: none; transition: transform .18s, border-color .18s, background .18s; }
        .landing-primary { color: #fff; background: rgb(var(--brand-600)); box-shadow: 0 16px 30px rgb(var(--brand-700) / .22); }
        .landing-primary:hover { transform: translateY(-2px); background: rgb(var(--brand-700)); }
        .landing-secondary { border: 1px solid var(--landing-line); color: var(--landing-ink); background: var(--landing-surface); }
        .landing-secondary:hover { transform: translateY(-2px); border-color: rgb(var(--brand-500) / .45); }
        .landing-primary svg, .landing-secondary svg { width: 1rem; height: 1rem; }
        .landing-hero-facts { display: grid; max-width: 44rem; grid-template-columns: minmax(4.5rem, .55fr) minmax(6.5rem, .65fr) minmax(15rem, 1.8fr); gap: clamp(.75rem, 2vw, 1.35rem); margin-top: 2.2rem; }
        .landing-hero-facts.is-compact { max-width: 24rem; grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .landing-hero-facts div { min-width: 0; padding: .85rem 0; border-top: 1px solid var(--landing-line); }
        .landing-hero-facts strong { display: block; font-size: 1rem; font-weight: 900; }
        .landing-hero-facts span { display: block; margin-top: .3rem; color: var(--landing-muted); font-size: .65rem; line-height: 1.35; }
        .landing-hero-location strong { overflow-wrap: anywhere; line-height: 1.35; text-wrap: balance; }
        .landing-visual { position: relative; min-width: 0; }
        .landing-visual-frame { position: relative; overflow: hidden; min-height: clamp(29rem, 62vw, 44rem); border: 1px solid var(--landing-line); border-radius: clamp(1.5rem, 3vw, 2.5rem); background: var(--landing-surface-soft); box-shadow: 0 32px 80px rgba(1, 13, 8, .23); }
        .landing-visual-frame > img { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; }
        .landing-visual-overlay { position: absolute; inset: 0; background: linear-gradient(180deg, rgba(1, 12, 7, .02) 35%, rgba(1, 12, 7, .82)); pointer-events: none; }
        .landing-visual-info { position: absolute; right: 1rem; bottom: 1rem; left: 1rem; display: flex; align-items: end; justify-content: space-between; gap: 1rem; color: #fff; }
        .landing-visual-info small { display: block; color: rgba(255,255,255,.68); font-size: .6rem; font-weight: 800; letter-spacing: .15em; text-transform: uppercase; }
        .landing-visual-info strong { display: block; max-width: 25rem; margin-top: .35rem; font-size: clamp(1rem, 2vw, 1.35rem); line-height: 1.15; }
        .landing-slide-controls { display: flex; gap: .4rem; }
        .landing-slide-controls button { display: grid; width: 2.5rem; height: 2.5rem; place-items: center; border: 1px solid rgba(255,255,255,.3); border-radius: .8rem; color: #fff; background: rgba(1,12,7,.35); backdrop-filter: blur(10px); }
        .landing-slide-controls svg { width: 1rem; height: 1rem; }
        .landing-visual-thumbs { display: flex; position: absolute; right: 1rem; top: 1rem; gap: .4rem; }
        .landing-visual-thumbs button { overflow: hidden; width: 2.8rem; height: 2.8rem; border: 2px solid transparent; border-radius: .75rem; opacity: .65; transition: opacity .18s, border-color .18s, transform .18s; }
        .landing-visual-thumbs button.is-active { border-color: rgb(var(--brand-400)); opacity: 1; transform: translateY(2px); }
        .landing-visual-thumbs img { width: 100%; height: 100%; object-fit: cover; }
        .landing-framed-image { object-position: var(--media-focus-x, 50%) var(--media-focus-y, 50%); transform: scale(var(--media-zoom, 1)); transform-origin: var(--media-focus-x, 50%) var(--media-focus-y, 50%); }
        .landing-areas, .landing-section { width: min(100% - 2rem, 92rem); margin-inline: auto; padding-block: clamp(3.5rem, 7vw, 7rem); }
        .landing-section-heading { display: flex; align-items: end; justify-content: space-between; gap: 2rem; margin-bottom: 2rem; }
        .landing-section-heading h2 { max-width: 48rem; margin: 0; font-size: clamp(2rem, 4vw, 4.2rem); font-weight: 900; letter-spacing: -.055em; line-height: 1; text-wrap: balance; }
        .landing-section-heading p:not(.landing-kicker) { max-width: 42rem; margin: 1rem 0 0; color: var(--landing-muted); font-size: .96rem; line-height: 1.7; }
        .landing-area-grid { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: .8rem; }
        .landing-area-card { position: relative; overflow: hidden; min-height: 17rem; border: 1px solid var(--landing-line); border-radius: 1.4rem; color: #fff; background: var(--landing-surface-soft); text-decoration: none; isolation: isolate; }
        .landing-area-card img { position: absolute; inset: 0; z-index: -2; width: 100%; height: 100%; object-fit: cover; transition: transform .55s, filter .35s; }
        .landing-area-card::before { position: absolute; inset: 0; z-index: -1; content: ""; background: linear-gradient(180deg, rgba(2, 16, 10, .08), rgba(2, 16, 10, .88)); }
        .landing-area-card:hover img { filter: saturate(1.08) contrast(1.02); }
        .landing-area-card > span { display: flex; height: 100%; flex-direction: column; justify-content: space-between; padding: 1.1rem; }
        .landing-area-card small { align-self: flex-start; border: 1px solid rgba(255,255,255,.24); border-radius: 999px; padding: .35rem .55rem; background: rgba(0,0,0,.18); font-size: .55rem; font-weight: 900; letter-spacing: .12em; text-transform: uppercase; backdrop-filter: blur(8px); }
        .landing-area-card strong { display: block; font-size: 1.1rem; font-weight: 900; line-height: 1.15; }
        .landing-area-card em { display: block; margin-top: .4rem; color: rgba(255,255,255,.72); font-size: .7rem; font-style: normal; }
        .landing-story { border-top: 1px solid var(--landing-line); }
        .landing-story-grid { display: grid; align-items: stretch; gap: clamp(1.5rem, 4vw, 4rem); }
        .landing-story-media { display: grid; min-height: clamp(28rem, 55vw, 43rem); grid-template-columns: 1fr .48fr; grid-template-rows: 1fr 1fr; gap: .65rem; }
        .landing-story-media figure { position: relative; overflow: hidden; margin: 0; border: 1px solid var(--landing-line); border-radius: 1.3rem; background: var(--landing-surface-soft); }
        .landing-story-media figure:first-child { grid-row: 1 / 3; }
        .landing-story-media img { width: 100%; height: 100%; object-fit: cover; transition: transform .55s, filter .35s; }
        .landing-story-media figure:hover img { filter: saturate(1.06) contrast(1.02); }
        .landing-story-media button { position: absolute; inset: 0; width: 100%; cursor: zoom-in; }
        .landing-story-media-placeholder { display: grid; height: 100%; place-items: center; color: var(--landing-muted); background: linear-gradient(145deg, var(--landing-surface-soft), var(--landing-surface)); }
        .landing-story-media-placeholder svg { width: 4rem; height: 4rem; opacity: .4; }
        .landing-story-copy { width: 100%; max-width: 30rem; align-self: center; padding-block: 1rem; }
        .landing-story-number { display: block; margin-bottom: 1.3rem; color: color-mix(in srgb, var(--landing-accent) 32%, transparent); font-size: 4.4rem; font-weight: 900; letter-spacing: -.08em; line-height: .8; }
        .landing-story-copy h2 { margin: 0; font-size: clamp(2.1rem, 4vw, 4rem); font-weight: 900; letter-spacing: -.055em; line-height: 1; text-wrap: balance; }
        .landing-story-copy > p:not(.landing-kicker) { margin: 1.25rem 0 0; color: var(--landing-muted); font-size: 1rem; line-height: 1.8; white-space: pre-line; }
        .landing-feature-list { display: grid; gap: .65rem; margin-top: 1.7rem; }
        .landing-feature-list span { display: flex; align-items: center; gap: .7rem; border-top: 1px solid var(--landing-line); padding-top: .65rem; color: var(--landing-ink); font-size: .78rem; font-weight: 800; }
        .landing-feature-list span::before { width: .45rem; height: .45rem; flex: 0 0 auto; border-radius: 50%; content: ""; background: var(--landing-accent); box-shadow: 0 0 0 .3rem var(--landing-accent-soft); }
        .landing-story-link { display: inline-flex; align-items: center; gap: .5rem; margin-top: 1.8rem; color: var(--landing-accent); font-size: .74rem; font-weight: 900; text-decoration: none; }
        .landing-story-link svg { width: 1rem; height: 1rem; }
        .landing-gallery { padding: clamp(1rem, 2vw, 1.5rem); border: 1px solid var(--landing-line); border-radius: clamp(1.5rem, 3vw, 2.4rem); background: var(--landing-surface); box-shadow: 0 28px 70px rgba(1, 13, 8, .12); }
        .landing-gallery .landing-section-heading { padding: clamp(.5rem, 2vw, 1.5rem); }
        .landing-gallery-count { flex: 0 0 auto; text-align: right; }
        .landing-gallery-count strong { display: block; color: var(--landing-accent); font-size: 2.5rem; font-weight: 900; line-height: .8; }
        .landing-gallery-count span { color: var(--landing-muted); font-size: .65rem; font-weight: 800; text-transform: uppercase; }
        .landing-gallery-filters { display: flex; gap: .45rem; overflow-x: auto; padding: .2rem clamp(.5rem, 2vw, 1.5rem) 1rem; scrollbar-width: none; }
        .landing-gallery-filters::-webkit-scrollbar, .landing-gallery-rail::-webkit-scrollbar { display: none; }
        .landing-gallery-filters button { flex: 0 0 auto; border: 1px solid var(--landing-line); border-radius: 999px; padding: .55rem .85rem; color: var(--landing-muted); background: var(--landing-surface-soft); font-size: .65rem; font-weight: 850; }
        .landing-gallery-filters button.is-active { border-color: transparent; color: #fff; background: rgb(var(--brand-600)); }
        .landing-gallery-stage { display: grid; grid-template-columns: minmax(0, 1fr) 14.5rem; gap: .7rem; }
        .landing-gallery-main { position: relative; overflow: hidden; min-height: clamp(24rem, 57vw, 42rem); border-radius: 1.5rem; color: #fff; background: var(--landing-surface-soft); text-align: left; }
        .landing-gallery-main img { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; will-change: opacity, transform; }
        .landing-gallery-shade { position: absolute; inset: 0; background: linear-gradient(180deg, transparent 42%, rgba(2, 13, 8, .8)); }
        .landing-gallery-caption { position: absolute; right: 1.25rem; bottom: 1.25rem; left: 1.25rem; }
        .landing-gallery-caption small { display: block; color: rgb(var(--brand-300)); font-size: .6rem; font-weight: 900; letter-spacing: .14em; text-transform: uppercase; }
        .landing-gallery-caption strong { display: block; margin-top: .35rem; font-size: clamp(1.05rem, 2vw, 1.45rem); font-weight: 850; }
        .landing-gallery-expand { display: grid; position: absolute; right: 1rem; top: 1rem; width: 2.7rem; height: 2.7rem; place-items: center; border: 1px solid rgba(255,255,255,.3); border-radius: .85rem; background: rgba(2, 13, 8, .28); backdrop-filter: blur(10px); }
        .landing-gallery-expand svg { width: 1.2rem; height: 1.2rem; }
        .landing-gallery-aside { display: flex; flex-direction: column; justify-content: space-between; border-radius: 1.5rem; padding: 1.1rem; color: #ecfdf5; background: linear-gradient(145deg, #0d3426, #071b14); }
        .landing-gallery-copy span { color: rgb(var(--brand-300)); font-size: .58rem; font-weight: 900; letter-spacing: .14em; text-transform: uppercase; }
        .landing-gallery-copy strong { display: block; margin-top: .65rem; font-size: 1.12rem; font-weight: 900; line-height: 1.15; }
        .landing-gallery-copy p { margin: .8rem 0 0; color: #9fbaab; font-size: .69rem; line-height: 1.55; }
        .landing-gallery-controls { display: flex; gap: .45rem; }
        .landing-gallery-controls button { display: grid; width: 2.8rem; height: 2.8rem; place-items: center; border: 1px solid rgba(255,255,255,.16); border-radius: .85rem; color: #fff; background: rgba(255,255,255,.06); }
        .landing-gallery-controls svg { width: 1.1rem; height: 1.1rem; }
        .landing-gallery-rail { display: flex; gap: .55rem; overflow-x: auto; padding-top: .7rem; scrollbar-width: none; scroll-snap-type: x proximity; }
        .landing-gallery-rail button { position: relative; overflow: hidden; width: 9rem; height: 6.4rem; flex: 0 0 auto; border: 2px solid transparent; border-radius: 1rem; opacity: .66; scroll-snap-align: start; transition: opacity .18s, border-color .18s, transform .18s; }
        .landing-gallery-rail button.is-active { border-color: rgb(var(--brand-500)); opacity: 1; transform: translateY(-2px); }
        .landing-gallery-rail img { width: 100%; height: 100%; object-fit: cover; }
        .landing-gallery-rail span { position: absolute; right: .35rem; bottom: .35rem; left: .35rem; overflow: hidden; border-radius: .4rem; padding: .25rem .4rem; color: #fff; background: rgba(2, 13, 8, .58); font-size: .5rem; font-weight: 800; text-overflow: ellipsis; white-space: nowrap; backdrop-filter: blur(6px); }
        .landing-footer { margin-top: clamp(2rem, 5vw, 5rem); border-top: 1px solid var(--landing-line); background: color-mix(in srgb, var(--landing-surface) 58%, transparent); }
        .landing-footer-grid { display: grid; grid-template-columns: 1.3fr .7fr .8fr; gap: 3rem; padding-block: 3.5rem; }
        .landing-footer-grid.is-compact { grid-template-columns: 1.3fr .7fr; }
        .landing-footer h3 { margin: 0; font-size: 1.3rem; font-weight: 900; letter-spacing: -.03em; }
        .landing-footer p { max-width: 30rem; margin: .8rem 0 0; color: var(--landing-muted); font-size: .78rem; line-height: 1.7; }
        .landing-footer h4 { margin: 0 0 .8rem; color: var(--landing-muted); font-size: .58rem; font-weight: 900; letter-spacing: .14em; text-transform: uppercase; }
        .landing-footer nav { display: grid; gap: .55rem; }
        .landing-footer nav a { color: var(--landing-ink); font-size: .75rem; font-weight: 750; text-decoration: none; }
        .landing-footer-bottom { display: flex; align-items: center; justify-content: space-between; gap: 1rem; border-top: 1px solid var(--landing-line); padding-block: 1rem; color: var(--landing-muted); font-size: .64rem; }
        .landing-floating-actions { display: flex; position: fixed; right: 1rem; bottom: 1rem; z-index: 45; flex-direction: column; align-items: flex-end; gap: .65rem; }
        .landing-back-top { display: grid; width: 2.9rem; height: 2.9rem; place-items: center; border: 1px solid rgb(var(--brand-400) / .3); border-radius: 1rem; color: #fff; background: rgb(var(--brand-700)); box-shadow: 0 14px 30px rgba(1, 13, 8, .3); }
        .landing-back-top svg { width: 1.15rem; height: 1.15rem; }
        .landing-whatsapp { display: inline-flex; min-height: 3.25rem; align-items: center; gap: .65rem; border: 1px solid rgba(255,255,255,.22); border-radius: 999px; padding: .7rem 1rem; color: #fff; background: #168c4f; box-shadow: 0 16px 35px rgba(2, 45, 23, .34); font-size: .72rem; font-weight: 900; text-decoration: none; transition: transform .18s, background .18s; }
        .landing-whatsapp:hover { transform: translateY(-2px); background: #117741; }
        .landing-whatsapp svg { width: 1.35rem; height: 1.35rem; flex: 0 0 auto; }
        .landing-lightbox { position: fixed; inset: 0; z-index: 100; display: grid; place-items: center; padding: 4.5rem 1rem 1rem; color: #fff; background: rgba(1, 8, 5, .94); backdrop-filter: blur(14px); }
        .landing-lightbox img { max-width: min(92vw, 92rem); max-height: calc(100svh - 7rem); border-radius: 1rem; object-fit: contain; box-shadow: 0 30px 90px #000; }
        .landing-lightbox-close { display: grid; position: absolute; right: 1rem; top: 1rem; width: 2.8rem; height: 2.8rem; place-items: center; border: 1px solid rgba(255,255,255,.18); border-radius: .9rem; background: rgba(255,255,255,.08); }
        .landing-lightbox-nav { display: grid; position: absolute; top: 50%; width: 3rem; height: 3rem; place-items: center; border: 1px solid rgba(255,255,255,.18); border-radius: 1rem; background: rgba(255,255,255,.08); transform: translateY(-50%); }
        .landing-lightbox-nav.is-prev { left: 1rem; }
        .landing-lightbox-nav.is-next { right: 1rem; }
        .landing-lightbox svg { width: 1.25rem; height: 1.25rem; }
        .landing-lightbox-caption { position: absolute; bottom: 1.2rem; right: 5rem; left: 5rem; color: rgba(255,255,255,.75); font-size: .74rem; font-weight: 750; text-align: center; }
        .landing-login { width: min(100%, 28rem); max-height: calc(100svh - 2rem); overflow-y: auto; border: 1px solid var(--landing-line); border-radius: 1.5rem; padding: clamp(1.25rem, 4vw, 2rem); color: var(--landing-ink); background: var(--landing-surface); box-shadow: 0 35px 90px rgba(0,0,0,.35); }
        @media (min-width: 1024px) {
            .landing-hero { grid-template-columns: minmax(0, .88fr) minmax(30rem, 1.12fr); }
            .landing-story-grid { grid-template-columns: minmax(0, 1.55fr) minmax(18rem, .65fr); }
            .landing-story.is-reversed .landing-story-grid { grid-template-columns: minmax(18rem, .65fr) minmax(0, 1.55fr); }
            .landing-story.is-reversed .landing-story-media { order: 2; }
        }
        @media (max-width: 1279px) {
            .landing-nav-links { display: none; }
            .landing-nav-actions { margin-left: auto; }
        }
        @media (max-width: 1180px) {
            .landing-area-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        }
        @media (max-width: 767px) {
            .landing-container, .landing-areas, .landing-section { width: min(100% - 1.25rem, 92rem); }
            .landing-navbar { min-height: 4.6rem; }
            .landing-main { padding-top: 4.6rem; }
            .landing-nav-actions .landing-access, .landing-nav-actions .landing-theme { display: none; }
            .landing-hero { min-height: auto; padding-block: 3rem 4.5rem; }
            .landing-hero-copy h1 { font-size: clamp(2.65rem, 14vw, 4rem); }
            .landing-hero-facts { grid-template-columns: 1fr; }
            .landing-hero-facts div { display: flex; align-items: baseline; justify-content: space-between; gap: 1rem; padding: .6rem 0; }
            .landing-visual-frame { min-height: 30rem; }
            .landing-visual-thumbs { display: none; }
            .landing-section-heading { display: block; }
            .landing-gallery-count { margin-top: 1rem; text-align: left; }
            .landing-area-grid { grid-template-columns: 1fr 1fr; }
            .landing-area-card { min-height: 13rem; }
            .landing-story-media { min-height: 25rem; grid-template-columns: 1fr 1fr; grid-template-rows: 1fr .42fr; }
            .landing-story-media figure:first-child { grid-column: 1 / 3; grid-row: auto; }
            .landing-gallery-stage { grid-template-columns: 1fr; }
            .landing-gallery-main { min-height: 28rem; }
            .landing-gallery-aside { min-height: 0; flex-direction: row; align-items: end; gap: 1rem; }
            .landing-gallery-copy p { display: none; }
            .landing-footer-grid, .landing-footer-grid.is-compact { grid-template-columns: 1fr; gap: 2rem; }
            .landing-footer-bottom { align-items: start; flex-direction: column; }
            .landing-lightbox-nav { top: auto; bottom: 1rem; transform: none; }
            .landing-lightbox-caption { right: 4.5rem; bottom: 1.8rem; left: 4.5rem; }
        }
        @media (max-width: 420px) {
            .landing-area-grid { grid-template-columns: 1fr; }
            .landing-area-card { min-height: 12rem; }
            .landing-gallery-main { min-height: 23rem; }
            .landing-whatsapp { width: 3.25rem; padding-inline: 0; justify-content: center; }
            .landing-whatsapp span { display: none; }
        }
        @media (prefers-reduced-motion: reduce) {
            html { scroll-behavior: auto !important; }
            .landing-page *, .landing-page *::before, .landing-page *::after { scroll-behavior: auto !important; transition-duration: .01ms !important; animation-duration: .01ms !important; }
        }
    </style>
</head>

<?php
    $heroDefaults = \App\Models\LandingBlock::defaultContent('hero');
    $heroTitle = filled($hero?->title) ? $hero->title : $heroDefaults['title'];
    $heroContent = filled($hero?->content) ? $hero->content : $heroDefaults['content'];
    $showFundoName = (bool) ($heroSettings['show_fundo_name'] ?? true);
    $showLocation = (bool) ($heroSettings['show_location'] ?? false);
    $customLocation = trim((string) ($heroSettings['custom_location'] ?? ''));
    $showAddress = (bool) ($heroSettings['show_address'] ?? false);
    $customAddress = trim((string) ($heroSettings['custom_address'] ?? ''));
    $publicLocation = $showLocation && filled($customLocation) ? $customLocation : null;
    $publicAddress = $showAddress && filled($customAddress) ? $customAddress : null;
    $showLocationFact = filled($publicLocation) || filled($publicAddress);
    $locationFactTitle = $publicLocation ?: $publicAddress;
    $locationFactDetail = $publicLocation && $publicAddress ? $publicAddress : null;
    $showWhatsApp = (bool) ($heroSettings['show_whatsapp'] ?? false);
    $whatsAppDigits = preg_replace('/\D+/', '', (string) ($heroSettings['whatsapp_number'] ?? ''));
    $whatsAppMessage = trim((string) ($heroSettings['whatsapp_message'] ?? ''));
    $whatsAppUrl = $showWhatsApp && preg_match('/^\d{8,15}$/', $whatsAppDigits)
        ? 'https://wa.me/'.$whatsAppDigits.(filled($whatsAppMessage) ? '?text='.rawurlencode($whatsAppMessage) : '')
        : null;
    $primaryTarget = $contentBlocks->isNotEmpty() ? '#areas' : ($galleryItems->isNotEmpty() ? '#galeria' : '#pie');
?>

<body
    class="landing-page antialiased"
    x-data="{
        loginOpen: <?php echo e(request()->boolean('login') ? 'true' : 'false'); ?>,
        mobileNavOpen: false,
        darkMode: document.documentElement.classList.contains('dark'),
        atTop: true,
        activeLightbox: null,
        openLogin() { this.loginOpen = true; this.mobileNavOpen = false; this.$nextTick(() => document.getElementById('modal-email')?.focus()) },
        toggleTheme() { this.darkMode = !this.darkMode; document.documentElement.classList.toggle('dark', this.darkMode); localStorage.setItem('theme', this.darkMode ? 'dark' : 'light') },
        closeLightbox() { this.activeLightbox = null },
        stepLightbox(offset) {
            if (!this.activeLightbox?.images?.length) return;
            this.activeLightbox.current = (this.activeLightbox.current + offset + this.activeLightbox.images.length) % this.activeLightbox.images.length;
        }
    }"
    @scroll.window="atTop = window.scrollY < 24"
    @open-login.window="openLogin()"
    @open-lightbox.window="activeLightbox = $event.detail"
    @keydown.escape.window="loginOpen = false; mobileNavOpen = false; closeLightbox()"
    @keydown.left.window="if (activeLightbox) stepLightbox(-1)"
    @keydown.right.window="if (activeLightbox) stepLightbox(1)"
    :class="{ 'overflow-hidden': loginOpen || activeLightbox }"
>
    <header class="landing-header" :class="!atTop && 'is-scrolled'">
        <div class="landing-container landing-navbar">
            <a href="<?php echo e(route('home')); ?>#inicio" class="landing-brand" aria-label="Ir al inicio de la página">
                <span class="landing-brand-mark"><?php if (isset($component)) { $__componentOriginal8741a05e11b0c77d19ec61b6b35b26b3 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8741a05e11b0c77d19ec61b6b35b26b3 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.brand-logo','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('brand-logo'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal8741a05e11b0c77d19ec61b6b35b26b3)): ?>
<?php $attributes = $__attributesOriginal8741a05e11b0c77d19ec61b6b35b26b3; ?>
<?php unset($__attributesOriginal8741a05e11b0c77d19ec61b6b35b26b3); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal8741a05e11b0c77d19ec61b6b35b26b3)): ?>
<?php $component = $__componentOriginal8741a05e11b0c77d19ec61b6b35b26b3; ?>
<?php unset($__componentOriginal8741a05e11b0c77d19ec61b6b35b26b3); ?>
<?php endif; ?></span>
                <span class="landing-brand-copy">
                    <strong><?php echo e($branding->name); ?></strong>
                    <small><?php echo e($branding->tagline ?: $branding->name); ?></small>
                </span>
            </a>

            <nav class="landing-nav-links" aria-label="Navegación principal">
                <a href="#inicio">Inicio</a>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $contentBlocks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $section => $block): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <a href="#<?php echo e($section); ?>"><?php echo e(\App\Models\LandingBlock::sectionDefinitions()[$section]['label']); ?></a>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($galleryItems->isNotEmpty()): ?><a href="#galeria">Galería</a><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </nav>

            <div class="landing-nav-actions">
                <button type="button" class="landing-icon-button landing-theme" @click="toggleTheme()" aria-label="Cambiar tema">
                    <svg x-show="!darkMode" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-width="1.8" d="M20.5 15.8A8.5 8.5 0 0 1 8.2 3.5 8.5 8.5 0 1 0 20.5 15.8Z"/></svg>
                    <svg x-cloak x-show="darkMode" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-width="1.8" d="M12 3v2m0 14v2m9-9h-2M5 12H3m15.4-6.4-1.4 1.4M7 17l-1.4 1.4m0-12.8L7 7m10 10 1.4 1.4M16 12a4 4 0 1 1-8 0 4 4 0 0 1 8 0Z"/></svg>
                </button>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->guard()->check()): ?>
                    <a href="<?php echo e(route('dashboard')); ?>" class="landing-access">Dashboard</a>
                <?php else: ?>
                    <button type="button" class="landing-access" @click="openLogin()">Acceder</button>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <button type="button" class="landing-icon-button xl:hidden" @click="mobileNavOpen = !mobileNavOpen" :aria-expanded="mobileNavOpen" aria-label="Abrir navegación">
                    <svg x-show="!mobileNavOpen" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-width="2" d="M4 7h16M4 12h16M4 17h16"/></svg>
                    <svg x-cloak x-show="mobileNavOpen" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-width="2" d="m6 6 12 12M18 6 6 18"/></svg>
                </button>
            </div>
        </div>

        <div x-cloak x-show="mobileNavOpen" x-transition class="landing-mobile-menu xl:hidden">
            <div class="landing-container">
                <nav aria-label="Navegación móvil">
                    <a href="#inicio" @click="mobileNavOpen = false">Inicio</a>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $contentBlocks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $section => $block): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <a href="#<?php echo e($section); ?>" @click="mobileNavOpen = false"><?php echo e(\App\Models\LandingBlock::sectionDefinitions()[$section]['label']); ?></a>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($galleryItems->isNotEmpty()): ?><a href="#galeria" @click="mobileNavOpen = false">Galería</a><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <button type="button" class="landing-icon-button" @click="toggleTheme()" aria-label="Cambiar tema">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-width="1.8" d="M12 3v2m0 14v2m9-9h-2M5 12H3m15.4-6.4-1.4 1.4M7 17l-1.4 1.4m0-12.8L7 7m10 10 1.4 1.4M16 12a4 4 0 1 1-8 0 4 4 0 0 1 8 0Z"/></svg>
                    </button>
                </nav>
                <div style="padding-bottom: 1rem">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->guard()->check()): ?>
                        <a href="<?php echo e(route('dashboard')); ?>" class="landing-access" style="width: 100%">Abrir dashboard</a>
                    <?php else: ?>
                        <button type="button" class="landing-access" style="width: 100%" @click="openLogin()">Acceder al sistema</button>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </div>
        </div>
    </header>

    <main id="inicio" class="landing-main scroll-mt-24">
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($heroVisible): ?>
        <section class="landing-container landing-hero">
            <div class="landing-hero-copy">
                <?php
                    $showOwner = (bool) ($heroSettings['show_owner'] ?? true);
                    $ownerName = trim((string) ($heroSettings['owner_name'] ?? 'Familia Choquenaira'));
                ?>

                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showOwner && filled($ownerName)): ?>
                <div style="display: inline-flex; align-items: center; gap: .5rem; padding: .35rem .9rem; border: 1px solid rgb(var(--brand-400) / .3); border-radius: 999px; margin-bottom: .8rem; color: var(--landing-ink); background: color-mix(in srgb, var(--brand-500) 12%, transparent); font-size: .75rem; font-weight: 900; letter-spacing: .08em; text-transform: uppercase; box-shadow: 0 4px 12px rgba(0,0,0,.05);">
                    <svg style="width: .9rem; height: .9rem; color: rgb(var(--brand-700));" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 0 0-2-2H7a2 2 0 0 0-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5m0 0v-5a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2v5m-4 0h4"/></svg>
                    <span><?php echo e($ownerName); ?></span>
                </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                <p class="landing-kicker"><?php echo e($heroSettings['eyebrow'] ?: 'Producción rural conectada'); ?></p>
                <h1><?php echo e($heroTitle); ?></h1>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showFundoName && filled($branding->tagline)): ?><span class="landing-hero-fundo"><?php echo e($branding->tagline); ?></span><?php elseif($showFundoName): ?><span class="landing-hero-fundo"><?php echo e($publicFundoName); ?></span><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <p><?php echo e($heroContent); ?></p>

                <div class="landing-hero-actions">
                    <a href="<?php echo e($primaryTarget); ?>" class="landing-primary"><?php echo e($heroSettings['cta_label'] ?: 'Conocer el fundo'); ?><svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-width="2" d="m9 18 6-6-6-6"/></svg></a>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($galleryItems->isNotEmpty()): ?><a href="#galeria" class="landing-secondary">Ver galería<svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-width="1.8" d="M4 7h16v12H4V7Zm3-3h10v3H7V4Zm1 8 2.5 2.5L14 11l3 4"/></svg></a><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>

                <div class="landing-hero-facts <?php echo e($showLocationFact ? '' : 'is-compact'); ?>" aria-label="Resumen público">
                    <div><strong><?php echo e($contentBlocks->count()); ?></strong><span>áreas principales</span></div>
                    <div><strong><?php echo e($galleryItems->count()); ?></strong><span>registros visuales</span></div>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showLocationFact): ?><div class="landing-hero-location"><strong><?php echo e($locationFactTitle); ?></strong><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($locationFactDetail): ?><span><?php echo e($locationFactDetail); ?></span><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?></div><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </div>

            <div class="landing-visual">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($heroItems->isNotEmpty()): ?>
                    <div
                        class="landing-visual-frame"
                        x-data="{
                            slides: <?php echo \Illuminate\Support\Js::from($heroItems)->toHtml() ?>,
                            active: 0,
                            timer: null,
                            start() { if (this.slides.length > 1 && !window.matchMedia('(prefers-reduced-motion: reduce)').matches) this.timer = setInterval(() => this.next(), 5200) },
                            stop() { if (this.timer) clearInterval(this.timer) },
                            next() { this.active = (this.active + 1) % this.slides.length },
                            prev() { this.active = (this.active - 1 + this.slides.length) % this.slides.length }
                        }"
                        x-init="start()"
                        @mouseenter="stop()"
                        @mouseleave="stop(); start()"
                    >
                        <img class="landing-framed-image" :src="slides[active].full" :alt="slides[active].caption" :style="`--media-focus-x: ${slides[active].focus_x}%; --media-focus-y: ${slides[active].focus_y}%; --media-zoom: ${slides[active].zoom};`" fetchpriority="high" decoding="async">
                        <span class="landing-visual-overlay"></span>
                        <div class="landing-visual-thumbs" x-show="slides.length > 1">
                            <template x-for="(slide, index) in slides" :key="slide.id"><button type="button" @click="active = index" :class="active === index && 'is-active'" :aria-label="`Ver imagen ${index + 1}`"><img class="landing-framed-image" :src="slide.thumb" :style="`--media-focus-x: ${slide.focus_x}%; --media-focus-y: ${slide.focus_y}%; --media-zoom: ${slide.zoom};`" alt=""></button></template>
                        </div>
                        <div class="landing-visual-info">
                            <span><small x-text="slides[active].category_label"></small><strong x-text="slides[active].caption"></strong></span>
                            <div class="landing-slide-controls" x-show="slides.length > 1"><button type="button" @click="prev()" aria-label="Imagen anterior"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-width="2" d="m15 18-6-6 6-6"/></svg></button><button type="button" @click="next()" aria-label="Imagen siguiente"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-width="2" d="m9 6 6 6-6 6"/></svg></button></div>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="landing-visual-frame landing-story-media-placeholder"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-width="1.5" d="M4 19V8l8-4 8 4v11H4Zm4 0v-6h8v6M7 9h.01M17 9h.01"/></svg></div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </section>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($contentBlocks->isNotEmpty()): ?>
        <section id="areas" class="landing-areas scroll-mt-24" aria-labelledby="areas-title">
            <div class="landing-section-heading">
                <div><p class="landing-kicker">Conoce nuestra operación</p><h2 id="areas-title">Trabajo de campo, organizado por áreas</h2><p>Información clara y una mirada real a cada parte del fundo.</p></div>
            </div>
            <div class="landing-area-grid">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $contentBlocks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $section => $block): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php
                        $defaults = \App\Models\LandingBlock::defaultContent($section);
                        $cover = $block->media->first();
                        $coverFrame = $cover ? \App\Models\LandingBlock::mediaFrame($cover) : null;
                    ?>
                    <a href="#<?php echo e($section); ?>" class="landing-area-card">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($cover): ?><img class="landing-framed-image" style="--media-focus-x: <?php echo e($coverFrame['focus_x']); ?>%; --media-focus-y: <?php echo e($coverFrame['focus_y']); ?>%; --media-zoom: <?php echo e($coverFrame['zoom']); ?>" src="<?php echo e($cover->hasGeneratedConversion('thumb') ? $cover->getUrl('thumb') : $cover->getUrl()); ?>" alt="" loading="lazy" decoding="async"><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <span><small><?php echo e(str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT)); ?></small><span><strong><?php echo e(filled($block->title) ? $block->title : $defaults['title']); ?></strong><em>Explorar área</em></span></span>
                    </a>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </section>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $contentBlocks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $section => $block): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php
                $defaults = \App\Models\LandingBlock::defaultContent($section);
                $settings = array_replace(\App\Models\LandingBlock::defaultSettings($section), $block->settings ?? []);
                $media = $block->media->take(3)->values();
                $sectionImages = $block->media->map(fn ($item) => $item->hasGeneratedConversion('optimized') ? $item->getUrl('optimized') : $item->getUrl())->values();
                $features = collect(range(1, 3))->map(fn ($feature) => $settings['feature_'.$feature] ?? null)->filter(fn ($feature) => filled($feature));
            ?>
            <section id="<?php echo e($section); ?>" class="landing-section landing-story <?php echo e($loop->even ? 'is-reversed' : ''); ?> scroll-mt-24" aria-labelledby="<?php echo e($section); ?>-title">
                <div class="landing-story-grid">
                    <div class="landing-story-media" x-data="{ images: <?php echo \Illuminate\Support\Js::from($sectionImages)->toHtml() ?>, open(index) { $dispatch('open-lightbox', { images: this.images, captions: this.images.map(() => <?php echo \Illuminate\Support\Js::from(filled($block->title) ? $block->title : $defaults['title'])->toHtml() ?>), current: index }) } }">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $media; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $image): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <?php
                                $imageFrame = \App\Models\LandingBlock::mediaFrame($image);
                            ?>
                            <figure><img class="landing-framed-image" style="--media-focus-x: <?php echo e($imageFrame['focus_x']); ?>%; --media-focus-y: <?php echo e($imageFrame['focus_y']); ?>%; --media-zoom: <?php echo e($imageFrame['zoom']); ?>" src="<?php echo e($image->hasGeneratedConversion($loop->first ? 'optimized' : 'thumb') ? $image->getUrl($loop->first ? 'optimized' : 'thumb') : $image->getUrl()); ?>" alt="<?php echo e($image->getCustomProperty('caption') ?: (filled($block->title) ? $block->title : $defaults['title'])); ?>" loading="lazy" decoding="async"><button type="button" @click="open(<?php echo e($loop->index); ?>)" aria-label="Ampliar imagen"></button></figure>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <div class="landing-story-media-placeholder" style="grid-column: 1 / 3; grid-row: 1 / 3"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-width="1.5" d="M4 19V8l8-4 8 4v11H4Zm4 0v-6h8v6M7 9h.01M17 9h.01"/></svg></div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                    <div class="landing-story-copy">
                        <span class="landing-story-number"><?php echo e(str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT)); ?></span>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(filled($settings['eyebrow'] ?? null)): ?><p class="landing-kicker"><?php echo e($settings['eyebrow']); ?></p><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <h2 id="<?php echo e($section); ?>-title"><?php echo e(filled($block->title) ? $block->title : $defaults['title']); ?></h2>
                        <p><?php echo e(filled($block->content) ? $block->content : $defaults['content']); ?></p>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($features->isNotEmpty()): ?><div class="landing-feature-list"><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $features; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $feature): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><span><?php echo e($feature); ?></span><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?></div><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($galleryItems->isNotEmpty()): ?><a href="#galeria" class="landing-story-link">Ver registro visual<svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-width="2" d="m9 18 6-6-6-6"/></svg></a><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                </div>
            </section>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <?php
            $galleryDefaults = \App\Models\LandingBlock::defaultContent('galeria');
            $gallerySettings = array_replace(\App\Models\LandingBlock::defaultSettings('galeria'), $galleryBlock?->settings ?? []);
        ?>
        <?php if (isset($component)) { $__componentOriginal95dbe0677c992f5a27f7be25f2eb556a = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal95dbe0677c992f5a27f7be25f2eb556a = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.gallery','data' => ['items' => $galleryItems,'title' => filled($galleryBlock?->title) ? $galleryBlock->title : $galleryDefaults['title'],'content' => filled($galleryBlock?->content) ? $galleryBlock->content : $galleryDefaults['content'],'eyebrow' => $gallerySettings['eyebrow']]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('gallery'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['items' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($galleryItems),'title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(filled($galleryBlock?->title) ? $galleryBlock->title : $galleryDefaults['title']),'content' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(filled($galleryBlock?->content) ? $galleryBlock->content : $galleryDefaults['content']),'eyebrow' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($gallerySettings['eyebrow'])]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal95dbe0677c992f5a27f7be25f2eb556a)): ?>
<?php $attributes = $__attributesOriginal95dbe0677c992f5a27f7be25f2eb556a; ?>
<?php unset($__attributesOriginal95dbe0677c992f5a27f7be25f2eb556a); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal95dbe0677c992f5a27f7be25f2eb556a)): ?>
<?php $component = $__componentOriginal95dbe0677c992f5a27f7be25f2eb556a; ?>
<?php unset($__componentOriginal95dbe0677c992f5a27f7be25f2eb556a); ?>
<?php endif; ?>
    </main>

    <footer id="pie" class="landing-footer scroll-mt-24">
        <div class="landing-container landing-footer-grid <?php echo e($showLocationFact ? '' : 'is-compact'); ?>">
            <div><h3><?php echo e($publicFundoName); ?></h3><p><?php echo e($heroContent); ?></p></div>
            <div><h4>Explorar</h4><nav><a href="#inicio">Inicio</a><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $contentBlocks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $section => $block): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><a href="#<?php echo e($section); ?>"><?php echo e(\App\Models\LandingBlock::sectionDefinitions()[$section]['label']); ?></a><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?><?php if($galleryItems->isNotEmpty()): ?><a href="#galeria">Galería</a><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?></nav></div>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showLocationFact): ?><div><h4>Ubicación</h4><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($publicLocation): ?><p><?php echo e($publicLocation); ?></p><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?><?php if($publicAddress): ?><p><?php echo e($publicAddress); ?></p><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?></div><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
        <div class="landing-container landing-footer-bottom"><span>&copy; <?php echo e(date('Y')); ?> <?php echo e($publicFundoName); ?>. Información pública institucional.</span><span>Gestionado con <?php echo e($branding->name); ?></span></div>
    </footer>

    <div class="landing-floating-actions">
        <a x-cloak x-show="!atTop" x-transition href="#inicio" class="landing-back-top" aria-label="Volver al inicio"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-width="2" d="m6 15 6-6 6 6"/></svg></a>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($whatsAppUrl): ?>
            <a href="<?php echo e($whatsAppUrl); ?>" target="_blank" rel="noopener noreferrer" class="landing-whatsapp" aria-label="Contactar por WhatsApp">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M20 11.5a8 8 0 0 1-11.9 7L4 19.5l1.1-3.8A8 8 0 1 1 20 11.5Z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8.7 8.2c.2-.5.5-.5.8-.5h.4c.2 0 .4.1.5.5l.7 1.7c.1.3 0 .5-.2.7l-.6.7c-.2.2-.1.4 0 .6.6 1.1 1.5 2 2.6 2.6.2.1.4.2.6 0l.8-1c.2-.2.4-.3.7-.2l1.8.9c.3.1.4.3.4.5 0 .4-.2 1.2-.8 1.7-.5.5-1.3.8-2.1.6-1.1-.3-2.7-.9-4.5-2.5-1.4-1.3-2.4-2.8-2.7-4-.3-.9.1-1.8.6-2.3Z"/></svg>
                <span>WhatsApp</span>
            </a>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->guard()->guest()): ?>
        <div x-cloak x-show="loginOpen" class="fixed inset-0 z-[90] grid place-items-center overflow-y-auto bg-black/75 p-4 backdrop-blur-md" role="dialog" aria-modal="true" aria-labelledby="login-title" @click.self="loginOpen = false">
            <section x-show="loginOpen" x-transition class="landing-login">
                <div class="mb-6 flex items-start justify-between gap-4"><div><p class="landing-kicker">Área privada</p><h2 id="login-title" class="m-0 text-2xl font-black tracking-tight">Acceso al sistema</h2><p class="mt-2 text-sm text-zinc-500">Ingresa credenciales autorizadas.</p></div><button type="button" class="landing-icon-button" @click="loginOpen = false" aria-label="Cerrar"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-width="2" d="m6 6 12 12M18 6 6 18"/></svg></button></div>
                <?php
$__split = function ($name, $params = []) {
    return [$name, $params];
};
[$__name, $__params] = $__split('welcome.login-modal', []);

$__key = null;

$__key ??= \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::generateKey('lw-2199421171-0', $__key);

$__html = app('livewire')->mount($__name, $__params, $__key);

echo $__html;

unset($__html);
unset($__key);
unset($__name);
unset($__params);
unset($__split);
if (isset($__slots)) unset($__slots);
?>
            </section>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <div x-cloak x-show="activeLightbox" x-transition class="landing-lightbox" role="dialog" aria-modal="true" aria-label="Visor de imágenes" @click.self="closeLightbox()">
        <button type="button" class="landing-lightbox-close" @click="closeLightbox()" aria-label="Cerrar visor"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-width="2" d="m6 6 12 12M18 6 6 18"/></svg></button>
        <button type="button" class="landing-lightbox-nav is-prev" @click="stepLightbox(-1)" aria-label="Imagen anterior"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-width="2" d="m15 18-6-6 6-6"/></svg></button>
        <template x-if="activeLightbox"><img :src="activeLightbox.images[activeLightbox.current]" :alt="activeLightbox.captions?.[activeLightbox.current] || 'Fotografía del fundo'" decoding="async"></template>
        <button type="button" class="landing-lightbox-nav is-next" @click="stepLightbox(1)" aria-label="Imagen siguiente"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-width="2" d="m9 6 6 6-6 6"/></svg></button>
        <p class="landing-lightbox-caption" x-text="activeLightbox?.captions?.[activeLightbox.current] || `${(activeLightbox?.current || 0) + 1} / ${activeLightbox?.images?.length || 0}`"></p>
    </div>

    <?php echo \Livewire\Mechanisms\FrontendAssets\FrontendAssets::scripts(); ?>

</body>
</html>
<?php /**PATH G:\PROYECTOS\FUNDO_PARQUE v04\resources\views/welcome.blade.php ENDPATH**/ ?>