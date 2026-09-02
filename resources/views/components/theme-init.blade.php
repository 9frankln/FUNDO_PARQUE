<script>
    (() => {
        const root = document.documentElement;
        
        const getSavedTheme = () => {
            const saved = localStorage.getItem('theme');
            if (saved === 'dark' || saved === 'light') {
                return saved;
            }
            return 'light'; // Default to light mode
        };

        const applyTheme = (theme, persist = false) => {
            const isDark = theme === 'dark';
            // Desactiva transiciones para que el cambio de tema se aplique de golpe,
            // nunca como colores escalonados por componente.
            root.classList.add('theme-switching');
            root.classList.toggle('dark', isDark);
            if (persist) {
                localStorage.setItem('theme', theme);
            }
            // Reactiva transiciones en el siguiente frame (doble rAF garantiza estilo aplicado).
            requestAnimationFrame(() => {
                requestAnimationFrame(() => root.classList.remove('theme-switching'));
            });
        };

        // Apply immediately before render
        applyTheme(getSavedTheme());

        window.getTheme = () => getSavedTheme();

        window.setTheme = (theme) => {
            applyTheme(theme, true);
        };

        // Enforce theme on Livewire SPA navigation
        document.addEventListener('livewire:navigated', () => {
            applyTheme(getSavedTheme());
        });
    })();
</script>
