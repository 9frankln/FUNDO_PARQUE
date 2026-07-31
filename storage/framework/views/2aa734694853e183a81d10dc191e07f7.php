<script>
    (() => {
        const root = document.documentElement;
        let cleanupFrame = null;
        const applyTheme = (theme, persist = false) => {
            if (cleanupFrame) {
                cancelAnimationFrame(cleanupFrame);
            }

            root.classList.add('theme-switching');
            root.classList.toggle('dark', theme === 'dark');
            if (persist) {
                localStorage.setItem('theme', theme);
            }
            cleanupFrame = requestAnimationFrame(() => root.classList.remove('theme-switching'));
        };
        const savedTheme = localStorage.getItem('theme');
        const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
        applyTheme(savedTheme ?? (prefersDark ? 'dark' : 'light'));

        window.setTheme = (theme) => {
            const switchTheme = () => applyTheme(theme, true);
            if (!document.startViewTransition || window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
                switchTheme();
                return;
            }

            document.startViewTransition(switchTheme);
        };
    })();
</script>
<?php /**PATH G:\PROYECTOS\FUNDO_PARQUE v04\resources\views/components/theme-init.blade.php ENDPATH**/ ?>