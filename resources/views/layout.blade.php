<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>SMS Catcher</title>
    <style>
        :root {
            color-scheme: light dark;
            --bg: #f7fafc;
            --panel: #ffffff;
            --border: #e2e8f0;
            --accent: #38bdf8;
            --text: #1a202c;
            --muted: #4a5568;
        }

        body {
            margin: 0;
            font-family: 'Inter', system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: var(--bg);
            color: var(--text);
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
        }

        h1 {
            font-size: 1.75rem;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        h1 span {
            background: var(--accent);
            color: white;
            padding: 0.25rem 0.5rem;
            border-radius: 0.5rem;
            font-size: 0.9rem;
        }

        .grid {
            display: grid;
            grid-template-columns: minmax(0, 1fr) minmax(0, 1fr);
            gap: 1.5rem;
        }

        .panel {
            background: var(--panel);
            border-radius: 1rem;
            border: 1px solid var(--border);
            box-shadow: 0 10px 25px rgba(15, 23, 42, 0.08);
            overflow: hidden;
        }

        .panel-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 1rem 1.5rem;
            border-bottom: 1px solid var(--border);
        }

        .message-list {
            max-height: 540px;
            overflow-y: auto;
        }

        .message {
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
            padding: 1rem 1.5rem;
            border-bottom: 1px solid var(--border);
            text-decoration: none;
            color: inherit;
            transition: background 0.15s ease;
        }

        .message:hover {
            background: rgba(56, 189, 248, 0.08);
        }

        .message:last-child {
            border-bottom: none;
        }

        .message small {
            color: var(--muted);
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
        }

        .meta {
            color: var(--muted);
            font-size: 0.85rem;
        }

        .actions {
            display: flex;
            gap: 0.75rem;
        }

        .btn {
            border: none;
            background: rgba(148, 163, 184, 0.25);
            color: inherit;
            padding: 0.5rem 0.85rem;
            border-radius: 0.5rem;
            cursor: pointer;
            transition: background 0.2s ease, transform 0.2s ease;
            font-size: 0.9rem;
        }

        .btn:hover {
            background: rgba(56, 189, 248, 0.25);
            transform: translateY(-1px);
        }

        .empty {
            padding: 2rem;
            text-align: center;
            color: var(--muted);
        }

        @media (max-width: 960px) {
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
<div class="container">
    <h1>
        SMS Catcher
        <span>dev</span>
    </h1>

    @yield('content')
</div>
</body>
</html>
