<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Larable — Backend GUI</title>
    <script>
        (function() {
            const theme = localStorage.getItem('larable_theme') || 'light';
            document.documentElement.setAttribute('data-theme', theme);
        })();
    </script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
    @yield('styles')
</head>
<body>
    <!-- ─── Top Bar ──────────────────────────────────────────────── -->
    <header class="larable-topbar">
        <div class="topbar-left">
            <svg class="topbar-logo" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="22" height="22">
                <path d="M12 2L2 7l10 5 10-5-10-5z"/><path d="M2 17l10 5 10-5"/><path d="M2 12l10 5 10-5"/>
            </svg>
            <span class="topbar-title">Larable</span>
            <span class="topbar-badge">Backend GUI</span>
        </div>
        <div class="topbar-right">
            <button id="larable-theme-toggle" class="topbar-link" style="background: none; border: none; cursor: pointer; padding: 0.35rem 0.5rem; display: flex; align-items: center;" title="Toggle Theme">
                <span id="theme-toggle-icon-container" style="display: flex; align-items: center;"></span>
            </button>
            <a href="{{ config('app.frontend_url') }}" target="_blank" class="topbar-link">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><path d="M18 13v6a2 2 0 01-2 2H5a2 2 0 01-2-2V8a2 2 0 012-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
                Frontend (3000)
            </a>
            <a href="http://localhost:8025" target="_blank" class="topbar-link">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                Mailpit (8025)
            </a>
        </div>
    </header>

    <!-- ─── Main Layout ──────────────────────────────────────────── -->
    <div class="larable-layout">
        @yield('content')
    </div>

    @yield('scripts')

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const toggleBtn = document.getElementById('larable-theme-toggle');
            const iconContainer = document.getElementById('theme-toggle-icon-container');

            function updateIcon(theme) {
                if (theme === 'light') {
                    // Render Moon icon for switching to dark mode
                    iconContainer.innerHTML = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="16" height="16"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path></svg>';
                } else {
                    // Render Sun icon for switching to light mode
                    iconContainer.innerHTML = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="16" height="16"><circle cx="12" cy="12" r="5"></circle><line x1="12" y1="1" x2="12" y2="3"></line><line x1="12" y1="21" x2="12" y2="23"></line><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"></line><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"></line><line x1="1" y1="12" x2="3" y2="12"></line><line x1="21" y1="12" x2="23" y2="12"></line><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"></line><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"></line></svg>';
                }
            }

            const currentTheme = document.documentElement.getAttribute('data-theme') || 'light';
            updateIcon(currentTheme);

            toggleBtn.addEventListener('click', function() {
                const activeTheme = document.documentElement.getAttribute('data-theme') || 'light';
                const nextTheme = activeTheme === 'light' ? 'dark' : 'light';
                document.documentElement.setAttribute('data-theme', nextTheme);
                localStorage.setItem('larable_theme', nextTheme);
                updateIcon(nextTheme);
                window.dispatchEvent(new CustomEvent('larable-theme-changed', { detail: nextTheme }));
            });
        });
    </script>
</body>
</html>
