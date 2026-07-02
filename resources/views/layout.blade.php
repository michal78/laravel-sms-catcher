<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>SMS Catcher</title>
    <style>
        :root {
            color-scheme: light;
            --bg: #f4f4f8;
            --bg-accent: radial-gradient(circle at 15% -10%, rgba(129, 140, 248, 0.16), transparent 45%),
                radial-gradient(circle at 85% 0%, rgba(56, 189, 248, 0.14), transparent 40%);
            --panel: #ffffff;
            --border: #e5e7eb;
            --accent: #6366f1;
            --accent-2: #38bdf8;
            --accent-contrast: #ffffff;
            --text: #14161f;
            --muted: #6b7280;
            --hover: rgba(99, 102, 241, 0.06);
            --danger: #ef4444;
            --shadow: rgba(20, 22, 31, 0.08);
        }

        @media (prefers-color-scheme: dark) {
            :root {
                color-scheme: dark;
                --bg: #0a0b10;
                --bg-accent: radial-gradient(circle at 15% -10%, rgba(99, 102, 241, 0.22), transparent 45%),
                    radial-gradient(circle at 85% 0%, rgba(56, 189, 248, 0.16), transparent 40%);
                --panel: #13141c;
                --border: #23252f;
                --accent: #818cf8;
                --accent-2: #38bdf8;
                --accent-contrast: #0a0b10;
                --text: #e7e8ee;
                --muted: #93949f;
                --hover: rgba(129, 140, 248, 0.1);
                --danger: #f87171;
                --shadow: rgba(0, 0, 0, 0.35);
            }
        }

        html[data-theme="light"] {
            color-scheme: light;
            --bg: #f4f4f8;
            --bg-accent: radial-gradient(circle at 15% -10%, rgba(129, 140, 248, 0.16), transparent 45%),
                radial-gradient(circle at 85% 0%, rgba(56, 189, 248, 0.14), transparent 40%);
            --panel: #ffffff;
            --border: #e5e7eb;
            --accent: #6366f1;
            --accent-2: #38bdf8;
            --accent-contrast: #ffffff;
            --text: #14161f;
            --muted: #6b7280;
            --hover: rgba(99, 102, 241, 0.06);
            --danger: #ef4444;
            --shadow: rgba(20, 22, 31, 0.08);
        }

        html[data-theme="dark"] {
            color-scheme: dark;
            --bg: #0a0b10;
            --bg-accent: radial-gradient(circle at 15% -10%, rgba(99, 102, 241, 0.22), transparent 45%),
                radial-gradient(circle at 85% 0%, rgba(56, 189, 248, 0.16), transparent 40%);
            --panel: #13141c;
            --border: #23252f;
            --accent: #818cf8;
            --accent-2: #38bdf8;
            --accent-contrast: #0a0b10;
            --text: #e7e8ee;
            --muted: #93949f;
            --hover: rgba(129, 140, 248, 0.1);
            --danger: #f87171;
            --shadow: rgba(0, 0, 0, 0.35);
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: ui-sans-serif, system-ui, -apple-system, "Segoe UI", Inter, sans-serif;
            background-color: var(--bg);
            background-image: var(--bg-accent);
            background-attachment: fixed;
            color: var(--text);
            -webkit-font-smoothing: antialiased;
        }

        .topbar {
            position: sticky;
            top: 0;
            z-index: 10;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 1.25rem 2rem;
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            background: color-mix(in srgb, var(--bg) 78%, transparent);
            border-bottom: 1px solid transparent;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 0.65rem;
            font-weight: 700;
            font-size: 1.15rem;
            letter-spacing: -0.01em;
        }

        .brand-mark {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 2rem;
            height: 2rem;
            border-radius: 0.65rem;
            background: linear-gradient(135deg, var(--accent), var(--accent-2));
            box-shadow: 0 8px 20px -6px var(--accent);
            color: #fff;
        }

        .brand-badge {
            background: var(--hover);
            color: var(--accent);
            padding: 0.2rem 0.55rem;
            border-radius: 999px;
            font-size: 0.7rem;
            font-weight: 600;
            letter-spacing: 0.04em;
            text-transform: uppercase;
        }

        .theme-toggle {
            width: 2.5rem;
            height: 2.5rem;
            border-radius: 999px;
            border: 1px solid var(--border);
            background: var(--panel);
            color: var(--text);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
            cursor: pointer;
            transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease;
            box-shadow: 0 6px 16px var(--shadow);
        }

        .theme-toggle:hover {
            transform: translateY(-1px);
            border-color: var(--accent);
        }

        .theme-toggle:focus-visible {
            outline: 2px solid var(--accent);
            outline-offset: 3px;
        }

        .container {
            max-width: 1180px;
            margin: 0 auto;
            padding: 0.5rem 2rem 3rem;
        }

        .grid {
            display: grid;
            grid-template-columns: minmax(0, 1fr) minmax(0, 1fr);
            gap: 1.5rem;
        }

        .panel {
            background: var(--panel);
            border-radius: 1.1rem;
            border: 1px solid var(--border);
            box-shadow: 0 20px 40px -24px var(--shadow);
            overflow: hidden;
        }

        .panel-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            padding: 1.1rem 1.5rem;
            border-bottom: 1px solid var(--border);
            flex-wrap: wrap;
        }

        .message-list {
            max-height: 560px;
            overflow-y: auto;
        }

        .message {
            display: flex;
            flex-direction: column;
            gap: 0.3rem;
            padding: 1rem 1.5rem;
            border-bottom: 1px solid var(--border);
            text-decoration: none;
            color: inherit;
            transition: background 0.15s ease;
            word-break: break-word;
            overflow-wrap: break-word;
        }

        .message:hover {
            background: var(--hover);
        }

        .message:last-child {
            border-bottom: none;
        }

        .message small {
            color: var(--muted);
        }

        .message-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.5rem;
            flex-wrap: wrap;
        }

        .message-title {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .unread-dot {
            width: 0.5rem;
            height: 0.5rem;
            border-radius: 999px;
            background: linear-gradient(135deg, var(--accent), var(--accent-2));
            display: inline-block;
            box-shadow: 0 0 0 3px var(--hover);
            flex-shrink: 0;
        }

        .message-from {
            color: var(--muted);
            font-size: 0.85rem;
        }

        .phone-shell {
            width: 280px;
            margin: 2rem auto;
            border: 12px solid #111827;
            border-radius: 36px;
            padding: 1.5rem 1rem;
            background: linear-gradient(145deg, #0f172a, #1e293b);
            box-shadow: 0 20px 45px rgba(15, 23, 42, 0.4);
        }

        .phone-screen {
            background: #0f172a;
            border-radius: 24px;
            padding: 1.5rem 1rem;
            min-height: 360px;
            display: flex;
            flex-direction: column;
            gap: 1rem;
            color: #e2e8f0;
        }

        .bubble {
            max-width: 80%;
            padding: 0.75rem 1rem;
            border-radius: 18px;
            background: rgba(148, 163, 184, 0.25);
            align-self: flex-start;
            line-height: 1.4;
            word-wrap: break-word;
            overflow-wrap: break-word;
            white-space: pre-wrap;
        }

        .bubble a {
            color: #38bdf8;
            text-decoration: underline;
            word-break: break-all;
        }

        .bubble a:hover {
            color: #0ea5e9;
        }

        .meta {
            color: var(--muted);
            font-size: 0.85rem;
        }

        .actions {
            display: flex;
            gap: 0.6rem;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.4rem;
            border: 1px solid var(--border);
            background: var(--panel);
            color: inherit;
            padding: 0.5rem 0.9rem;
            border-radius: 0.6rem;
            cursor: pointer;
            transition: background 0.2s ease, transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease;
            font-size: 0.85rem;
            text-decoration: none;
            font-weight: 500;
            appearance: none;
            -webkit-appearance: none;
            -moz-appearance: none;
        }

        .btn:hover {
            border-color: var(--accent);
            transform: translateY(-1px);
            box-shadow: 0 10px 20px var(--shadow);
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--accent), var(--accent-2));
            color: var(--accent-contrast);
            border-color: transparent;
            box-shadow: 0 10px 20px -8px var(--accent);
        }

        .btn-danger {
            background: transparent;
            color: var(--danger);
            border-color: color-mix(in srgb, var(--danger) 40%, transparent);
        }

        .btn-danger:hover {
            background: color-mix(in srgb, var(--danger) 10%, transparent);
            border-color: var(--danger);
        }

        .empty {
            padding: 3rem 2rem;
            text-align: center;
            color: var(--muted);
        }

        .empty-icon {
            width: 3rem;
            height: 3rem;
            margin: 0 auto 1rem;
            border-radius: 999px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--hover);
            color: var(--accent);
        }

        @media (max-width: 960px) {
            .topbar, .container {
                padding-left: 1.25rem;
                padding-right: 1.25rem;
            }

            .grid {
                grid-template-columns: 1fr;
            }

            .phone-shell {
                width: 220px;
                margin: 1.5rem auto;
            }
        }
    </style>
    @stack('head')
</head>
<body>
<div class="topbar">
    <div class="brand">
        <span class="brand-mark" aria-hidden="true">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M4 4h16a1 1 0 0 1 1 1v10a1 1 0 0 1-1 1H8l-4 4V5a1 1 0 0 1 1-1Z" fill="currentColor"/>
            </svg>
        </span>
        SMS Catcher
        <span class="brand-badge">dev</span>
    </div>
    <button id="theme-toggle" class="theme-toggle" type="button" aria-label="Switch theme" title="Switch theme"></button>
</div>
<div class="container">
    @yield('content')
</div>
<script>
    (function () {
        const storageKey = 'theme-preference';
        const toggle = document.getElementById('theme-toggle');
        const mediaQuery = window.matchMedia('(prefers-color-scheme: dark)');

        function storedMode() {
            const value = localStorage.getItem(storageKey);
            return value === 'light' || value === 'dark' ? value : 'system';
        }

        function effectiveMode() {
            const mode = storedMode();
            if (mode === 'system') {
                return mediaQuery.matches ? 'dark' : 'light';
            }
            return mode;
        }

        function applyMode(mode) {
            if (mode === 'light') {
                document.documentElement.setAttribute('data-theme', 'light');
            } else if (mode === 'dark') {
                document.documentElement.setAttribute('data-theme', 'dark');
            } else {
                document.documentElement.removeAttribute('data-theme');
            }

            const active = mode === 'system' ? effectiveMode() : mode;
            if (mode === 'system') {
                toggle.textContent = active === 'dark' ? '☀️' : '🌙';
                toggle.setAttribute('aria-label', `Switch to ${active === 'dark' ? 'light' : 'dark'} mode`);
                toggle.setAttribute('title', 'Follow system theme');
            } else if (mode === 'dark') {
                toggle.textContent = '☀️';
                toggle.setAttribute('aria-label', 'Switch to light mode');
                toggle.setAttribute('title', 'Dark mode enabled');
            } else {
                toggle.textContent = '🌙';
                toggle.setAttribute('aria-label', 'Switch to dark mode');
                toggle.setAttribute('title', 'Light mode enabled');
            }

            toggle.dataset.mode = mode;
        }

        function nextMode(current) {
            if (current === 'system') {
                return 'dark';
            }
            if (current === 'dark') {
                return 'light';
            }
            return 'system';
        }

        toggle.addEventListener('click', function () {
            const current = storedMode();
            const mode = nextMode(current);
            if (mode === 'system') {
                localStorage.removeItem(storageKey);
            } else {
                localStorage.setItem(storageKey, mode);
            }
            applyMode(mode);
        });

        const handleChange = function () {
            if (storedMode() === 'system') {
                applyMode('system');
            }
        };

        if (typeof mediaQuery.addEventListener === 'function') {
            mediaQuery.addEventListener('change', handleChange);
        } else if (typeof mediaQuery.addListener === 'function') {
            mediaQuery.addListener(handleChange);
        }

        applyMode(storedMode());
    }());
</script>
</body>
</html>
