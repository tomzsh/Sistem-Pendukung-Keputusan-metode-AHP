<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>SPK AHP — Sistem Pendukung Keputusan</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">

    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --bg: #ffffff;
            --bg-elevated: #f8f9fb;
            --bg-card: #ffffff;
            --bg-hover: rgba(0,0,0,0.03);
            --text-primary: #0f172a;
            --text-secondary: #475569;
            --text-muted: #94a3b8;
            --accent: #0ea5e9;
            --accent-light: #38bdf8;
            --accent-soft: rgba(14,165,233,0.08);
            --accent-border: rgba(14,165,233,0.2);
            --border: #e2e8f0;
            --border-light: #f1f5f9;
            --shadow-sm: 0 1px 2px rgba(0,0,0,0.04);
            --shadow: 0 4px 20px rgba(0,0,0,0.06);
            --shadow-lg: 0 12px 40px rgba(0,0,0,0.08);
            --radius: 12px;
            --radius-sm: 8px;
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        [data-theme="dark"] {
            --bg: #0b0f19;
            --bg-elevated: #111827;
            --bg-card: #151c2c;
            --bg-hover: rgba(255,255,255,0.03);
            --text-primary: #f1f5f9;
            --text-secondary: #94a3b8;
            --text-muted: #64748b;
            --accent: #38bdf8;
            --accent-light: #7dd3fc;
            --accent-soft: rgba(56,189,248,0.1);
            --accent-border: rgba(56,189,248,0.2);
            --border: rgba(255,255,255,0.06);
            --border-light: rgba(255,255,255,0.04);
            --shadow-sm: 0 1px 2px rgba(0,0,0,0.2);
            --shadow: 0 4px 20px rgba(0,0,0,0.3);
            --shadow-lg: 0 12px 40px rgba(0,0,0,0.4);
        }

        html { scroll-behavior: smooth; }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--bg);
            color: var(--text-primary);
            overflow-x: hidden;
            line-height: 1.6;
            transition: background 0.4s ease, color 0.4s ease;
        }

        body::before {
            content: '';
            position: fixed;
            inset: 0;
            background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 256 256' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.9' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)' opacity='0.03'/%3E%3C/svg%3E");
            pointer-events: none;
            z-index: 0;
            opacity: 0.5;
        }

        .glow {
            position: fixed;
            border-radius: 50%;
            filter: blur(80px);
            pointer-events: none;
            z-index: 0;
            opacity: 0.4;
            transition: opacity 0.4s ease;
        }
        .glow-1 {
            top: -10vh; right: -5vw;
            width: 50vw; height: 50vw;
            background: radial-gradient(circle, var(--accent-soft) 0%, transparent 70%);
        }
        .glow-2 {
            bottom: -10vh; left: -5vw;
            width: 40vw; height: 40vw;
            background: radial-gradient(circle, rgba(99,102,241,0.06) 0%, transparent 70%);
        }

        /* ── NAV ── */
        nav {
            position: fixed;
            top: 0; left: 0; right: 0;
            z-index: 100;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 1rem 5vw;
            background: rgba(var(--bg-rgb, 255,255,255), 0.75);
            backdrop-filter: blur(20px);
            border-bottom: 1px solid var(--border);
            transition: var(--transition);
        }
        [data-theme="dark"] nav {
            background: rgba(11, 15, 25, 0.75);
        }

        .nav-brand {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            text-decoration: none;
        }
        .nav-logo {
            width: 36px; height: 36px;
            background: linear-gradient(135deg, var(--accent), var(--accent-light));
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            font-family: 'Playfair Display', serif;
            font-size: 1.1rem;
            color: white;
            font-weight: 700;
            flex-shrink: 0;
            box-shadow: 0 2px 8px var(--accent-soft);
        }
        .nav-title {
            font-weight: 700;
            font-size: 0.95rem;
            color: var(--text-primary);
            letter-spacing: -0.01em;
            transition: color 0.3s;
        }
        .nav-subtitle {
            font-size: 0.7rem;
            color: var(--text-muted);
            letter-spacing: 0.05em;
        }

        .nav-links {
            display: flex;
            gap: 2rem;
            list-style: none;
        }
        .nav-links a {
            font-size: 0.82rem;
            font-weight: 500;
            color: var(--text-secondary);
            text-decoration: none;
            transition: color 0.2s;
            position: relative;
        }
        .nav-links a::after {
            content: '';
            position: absolute;
            bottom: -4px; left: 0;
            width: 0; height: 2px;
            background: var(--accent);
            border-radius: 2px;
            transition: width 0.3s;
        }
        .nav-links a:hover { color: var(--accent); }
        .nav-links a:hover::after { width: 100%; }

        .nav-right {
            display: flex;
            gap: 1rem;
            align-items: center;
        }

        /* ── THEME TOGGLE ── */
        .theme-toggle {
            width: 44px; height: 44px;
            border-radius: 12px;
            border: 1px solid var(--border);
            background: var(--bg-elevated);
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--text-secondary);
            transition: var(--transition);
            position: relative;
            overflow: hidden;
        }
        .theme-toggle:hover {
            border-color: var(--accent-border);
            color: var(--accent);
            background: var(--accent-soft);
        }
        .theme-toggle svg {
            width: 20px; height: 20px;
            position: absolute;
            transition: transform 0.4s cubic-bezier(0.4, 0, 0.2, 1), opacity 0.3s;
        }
        .theme-toggle .sun { opacity: 0; transform: rotate(90deg) scale(0.5); }
        .theme-toggle .moon { opacity: 1; transform: rotate(0) scale(1); }
        [data-theme="dark"] .theme-toggle .sun { opacity: 1; transform: rotate(0) scale(1); }
        [data-theme="dark"] .theme-toggle .moon { opacity: 0; transform: rotate(-90deg) scale(0.5); }

        .btn-ghost {
            font-size: 0.82rem;
            font-weight: 600;
            color: var(--text-secondary);
            text-decoration: none;
            padding: 0.5rem 1.1rem;
            border: 1px solid var(--border);
            border-radius: var(--radius-sm);
            background: var(--bg-card);
            transition: var(--transition);
        }
        .btn-ghost:hover {
            color: var(--accent);
            border-color: var(--accent-border);
            background: var(--accent-soft);
        }

        .btn-primary {
            font-size: 0.82rem;
            font-weight: 600;
            color: white;
            text-decoration: none;
            padding: 0.5rem 1.3rem;
            background: linear-gradient(135deg, var(--accent), var(--accent-light));
            border-radius: var(--radius-sm);
            transition: var(--transition);
            box-shadow: 0 2px 12px var(--accent-soft);
        }
        .btn-primary:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 20px var(--accent-soft);
            opacity: 0.95;
        }

        /* ── HERO ── */
        .hero {
            position: relative;
            z-index: 1;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            padding: 8rem 5vw 5rem;
        }

        .hero-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.4rem 1rem;
            border: 1px solid var(--accent-border);
            border-radius: 999px;
            background: var(--accent-soft);
            font-size: 0.72rem;
            font-weight: 600;
            letter-spacing: 0.05em;
            text-transform: uppercase;
            color: var(--accent);
            margin-bottom: 2rem;
            animation: fadeUp 0.8s ease both;
        }
        .hero-badge span {
            width: 6px; height: 6px;
            border-radius: 50%;
            background: var(--accent);
            animation: pulse 2s infinite;
        }

        .hero h1 {
            font-family: 'Playfair Display', serif;
            font-size: clamp(2.8rem, 7vw, 5rem);
            font-weight: 700;
            line-height: 1.1;
            letter-spacing: -0.02em;
            color: var(--text-primary);
            max-width: 800px;
            margin-bottom: 1.5rem;
            animation: fadeUp 0.8s 0.1s ease both;
            transition: color 0.3s;
        }
        .hero h1 em {
            font-style: italic;
            color: var(--accent);
            position: relative;
        }

        .hero p {
            font-size: 1.05rem;
            color: var(--text-secondary);
            max-width: 560px;
            line-height: 1.75;
            margin-bottom: 2.5rem;
            animation: fadeUp 0.8s 0.2s ease both;
            transition: color 0.3s;
        }

        .hero-actions {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
            justify-content: center;
            animation: fadeUp 0.8s 0.3s ease both;
        }

        .btn-lg {
            font-size: 0.9rem;
            font-weight: 600;
            letter-spacing: -0.01em;
            padding: 0.85rem 2rem;
            border-radius: var(--radius-sm);
            text-decoration: none;
            transition: var(--transition);
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }
        .btn-lg.accent {
            background: linear-gradient(135deg, var(--accent), var(--accent-light));
            color: white;
            box-shadow: 0 4px 20px var(--accent-soft);
        }
        .btn-lg.accent:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 30px var(--accent-soft);
        }
        .btn-lg.outline {
            border: 1px solid var(--border);
            color: var(--text-primary);
            background: var(--bg-card);
        }
        .btn-lg.outline:hover {
            border-color: var(--accent-border);
            background: var(--accent-soft);
            color: var(--accent);
        }

        .hero-stats {
            display: flex;
            gap: 3rem;
            margin-top: 4rem;
            padding-top: 3rem;
            border-top: 1px solid var(--border);
            animation: fadeUp 0.8s 0.4s ease both;
            flex-wrap: wrap;
            justify-content: center;
            transition: border-color 0.3s;
        }
        .stat-item { text-align: center; }
        .stat-num {
            font-family: 'Playfair Display', serif;
            font-size: 2rem;
            font-weight: 700;
            color: var(--accent);
            line-height: 1;
            transition: color 0.3s;
        }
        .stat-label {
            font-size: 0.75rem;
            letter-spacing: 0.05em;
            text-transform: uppercase;
            color: var(--text-muted);
            margin-top: 0.4rem;
            font-weight: 500;
            transition: color 0.3s;
        }

        /* ── DIVIDER ── */
        .divider {
            position: relative;
            z-index: 1;
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 0 5vw;
            margin: 0 auto;
            max-width: 1200px;
        }
        .divider-line { flex: 1; height: 1px; background: var(--border); transition: background 0.3s; }
        .divider-icon {
            width: 32px; height: 32px;
            border: 1px solid var(--border);
            border-radius: 8px;
            display: flex; align-items: center; justify-content: center;
            color: var(--accent);
            font-size: 0.85rem;
            background: var(--bg-card);
            transition: var(--transition);
        }

        /* ── SECTIONS ── */
        section { position: relative; z-index: 1; padding: 6rem 5vw; }
        .section-inner { max-width: 1200px; margin: 0 auto; }

        .section-tag {
            font-size: 0.72rem;
            font-weight: 600;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            color: var(--accent);
            margin-bottom: 0.75rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            transition: color 0.3s;
        }
        .section-tag::before {
            content: '';
            width: 20px; height: 2px;
            background: var(--accent);
            border-radius: 2px;
            opacity: 0.5;
        }
        .section-title {
            font-family: 'Playfair Display', serif;
            font-size: clamp(1.8rem, 4vw, 2.6rem);
            font-weight: 700;
            color: var(--text-primary);
            line-height: 1.2;
            margin-bottom: 1rem;
            transition: color 0.3s;
        }
        .section-desc {
            font-size: 1rem;
            color: var(--text-secondary);
            max-width: 540px;
            line-height: 1.75;
            transition: color 0.3s;
        }

        /* ── ABOUT ── */
        .about-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 5rem;
            align-items: center;
            margin-top: 4rem;
        }
        .about-visual { position: relative; }
        .ahp-diagram {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 2rem;
            position: relative;
            box-shadow: var(--shadow);
            transition: var(--transition);
        }
        .ahp-level { margin-bottom: 1.5rem; }
        .ahp-level-label {
            font-size: 0.65rem;
            font-weight: 600;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            color: var(--text-muted);
            margin-bottom: 0.6rem;
            transition: color 0.3s;
        }
        .ahp-nodes { display: flex; gap: 0.5rem; flex-wrap: wrap; }
        .ahp-node {
            padding: 0.45rem 0.85rem;
            border-radius: 6px;
            font-size: 0.75rem;
            font-weight: 600;
            letter-spacing: 0.02em;
            transition: var(--transition);
        }
        .ahp-node.goal {
            background: var(--accent-soft);
            border: 1px solid var(--accent-border);
            color: var(--accent);
            width: 100%;
            text-align: center;
        }
        .ahp-node.criteria {
            background: rgba(99,102,241,0.08);
            border: 1px solid rgba(99,102,241,0.2);
            color: #818cf8;
        }
        .ahp-node.sub {
            background: rgba(16,185,129,0.08);
            border: 1px solid rgba(16,185,129,0.2);
            color: #34d399;
            font-size: 0.7rem;
        }
        .ahp-connector {
            display: flex;
            justify-content: center;
            margin: 0.4rem 0;
            color: var(--border);
            font-size: 0.85rem;
            transition: color 0.3s;
        }

        .about-points { margin-top: 2rem; }
        .about-point {
            display: flex;
            gap: 1rem;
            padding: 1.25rem 0;
            border-bottom: 1px solid var(--border-light);
            transition: border-color 0.3s;
        }
        .about-point:last-child { border-bottom: none; }
        .point-icon {
            width: 40px; height: 40px;
            border-radius: 10px;
            background: var(--accent-soft);
            border: 1px solid var(--accent-border);
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0;
            color: var(--accent);
            font-size: 1rem;
            transition: var(--transition);
        }
        .point-title {
            font-size: 0.95rem;
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 0.25rem;
            transition: color 0.3s;
        }
        .point-desc { font-size: 0.85rem; color: var(--text-secondary); line-height: 1.6; transition: color 0.3s; }

        /* ── FEATURES ── */
        .features-section { background: var(--bg-elevated); transition: background 0.4s; }
        .features-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            margin-bottom: 3rem;
            flex-wrap: wrap;
            gap: 1.5rem;
        }
        .features-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1.5rem;
        }
        .feature-card {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 1.75rem;
            transition: var(--transition);
            position: relative;
            overflow: hidden;
        }
        .feature-card::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 2px;
            background: linear-gradient(90deg, transparent, var(--accent), transparent);
            opacity: 0;
            transition: opacity 0.3s;
        }
        .feature-card:hover {
            border-color: var(--accent-border);
            transform: translateY(-4px);
            box-shadow: var(--shadow-lg);
        }
        .feature-card:hover::before { opacity: 1; }

        .feature-num {
            font-family: 'Playfair Display', serif;
            font-size: 2.8rem;
            font-weight: 700;
            color: var(--accent);
            opacity: 0.15;
            line-height: 1;
            margin-bottom: 0.5rem;
            letter-spacing: -0.03em;
            transition: color 0.3s;
        }
        .feature-title {
            font-size: 1rem;
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: 0.75rem;
            transition: color 0.3s;
        }
        .feature-desc { font-size: 0.85rem; color: var(--text-secondary); line-height: 1.65; transition: color 0.3s; }
        .feature-tags {
            display: flex;
            flex-wrap: wrap;
            gap: 0.4rem;
            margin-top: 1.25rem;
        }
        .tag {
            font-size: 0.65rem;
            font-weight: 600;
            letter-spacing: 0.05em;
            text-transform: uppercase;
            padding: 0.3rem 0.7rem;
            border-radius: 6px;
            background: var(--accent-soft);
            border: 1px solid var(--accent-border);
            color: var(--accent);
            transition: var(--transition);
        }

        /* ── STEPS ── */
        .steps-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 0;
            margin-top: 4rem;
            position: relative;
        }
        .steps-grid::before {
            content: '';
            position: absolute;
            top: 2rem;
            left: 12.5%;
            right: 12.5%;
            height: 1px;
            background: linear-gradient(90deg, transparent, var(--border), var(--accent) 50%, var(--border), transparent);
            z-index: 0;
            transition: background 0.3s;
        }
        .step {
            text-align: center;
            padding: 0 1.5rem;
            position: relative;
            z-index: 1;
        }
        .step-num {
            width: 40px; height: 40px;
            border-radius: 50%;
            background: var(--bg-card);
            border: 2px solid var(--border);
            display: flex; align-items: center; justify-content: center;
            font-family: 'Playfair Display', serif;
            font-size: 1rem;
            font-weight: 700;
            color: var(--accent);
            margin: 0 auto 1.25rem;
            position: relative;
            z-index: 2;
            transition: var(--transition);
        }
        .step:hover .step-num {
            background: var(--accent-soft);
            border-color: var(--accent);
            box-shadow: 0 0 20px var(--accent-soft);
        }
        .step-title {
            font-size: 0.95rem;
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: 0.6rem;
            transition: color 0.3s;
        }
        .step-desc { font-size: 0.82rem; color: var(--text-secondary); line-height: 1.65; transition: color 0.3s; }

        /* ── DEMO TABLE ── */
        .demo-section { background: var(--bg-elevated); transition: background 0.4s; }
        .demo-container {
            margin-top: 3rem;
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            overflow: hidden;
            box-shadow: var(--shadow);
            transition: var(--transition);
        }
        .demo-header {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 1rem 1.5rem;
            border-bottom: 1px solid var(--border);
            background: var(--bg-elevated);
            transition: var(--transition);
        }
        .demo-dot {
            width: 10px; height: 10px;
            border-radius: 50%;
        }
        .demo-label {
            font-size: 0.75rem;
            font-weight: 600;
            letter-spacing: 0.04em;
            color: var(--text-muted);
            margin-left: 0.5rem;
            transition: color 0.3s;
        }
        .demo-body { padding: 1.5rem; overflow-x: auto; }
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.82rem;
        }
        th {
            font-size: 0.7rem;
            font-weight: 600;
            letter-spacing: 0.05em;
            text-transform: uppercase;
            color: var(--accent);
            padding: 0.75rem 1rem;
            text-align: left;
            border-bottom: 1px solid var(--border);
            transition: color 0.3s, border-color 0.3s;
        }
        td {
            padding: 0.8rem 1rem;
            color: var(--text-secondary);
            border-bottom: 1px solid var(--border-light);
            transition: color 0.3s, border-color 0.3s, background 0.2s;
        }
        tr:last-child td { border-bottom: none; }
        tr:hover td { background: var(--bg-hover); color: var(--text-primary); }
        .td-highlight {
            font-weight: 600;
            color: var(--text-primary);
            transition: color 0.3s;
        }
        .badge-val {
            display: inline-block;
            padding: 0.25rem 0.7rem;
            border-radius: 6px;
            font-size: 0.72rem;
            font-weight: 700;
        }
        .badge-val.high { background: rgba(16,185,129,0.1); color: #10b981; }
        .badge-val.med  { background: rgba(245,158,11,0.1); color: #f59e0b; }
        .badge-val.low  { background: rgba(239,68,68,0.08); color: #ef4444; }

        /* ── FOOTER ── */
        footer {
            position: relative;
            z-index: 1;
            border-top: 1px solid var(--border);
            padding: 4rem 5vw 2rem;
            transition: border-color 0.3s;
        }
        .footer-inner { max-width: 1200px; margin: 0 auto; }
        .footer-top {
            display: grid;
            grid-template-columns: 1.5fr 1fr 1fr;
            gap: 4rem;
            margin-bottom: 3rem;
        }
        .footer-brand-title {
            font-family: 'Playfair Display', serif;
            font-size: 1.3rem;
            color: var(--text-primary);
            margin-bottom: 0.75rem;
            transition: color 0.3s;
        }
        .footer-brand-desc { font-size: 0.85rem; color: var(--text-secondary); line-height: 1.7; transition: color 0.3s; }
        .footer-col-title {
            font-size: 0.72rem;
            font-weight: 600;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            color: var(--accent);
            margin-bottom: 1.25rem;
            transition: color 0.3s;
        }
        .footer-links { list-style: none; }
        .footer-links li { margin-bottom: 0.75rem; }
        .footer-links a {
            font-size: 0.85rem;
            color: var(--text-secondary);
            text-decoration: none;
            transition: color 0.2s;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .footer-links a:hover { color: var(--accent); }
        .footer-links a svg { width: 14px; height: 14px; opacity: 0.6; }

        .footer-bottom {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding-top: 2rem;
            border-top: 1px solid var(--border);
            flex-wrap: wrap;
            gap: 1rem;
            transition: border-color 0.3s;
        }
        .footer-copy {
            font-size: 0.78rem;
            color: var(--text-muted);
            transition: color 0.3s;
        }
        .footer-copy span { color: var(--accent); transition: color 0.3s; }

        .contact-chips {
            display: flex;
            gap: 0.75rem;
            flex-wrap: wrap;
        }
        .contact-chip {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.45rem 1rem;
            border: 1px solid var(--border);
            border-radius: 999px;
            font-size: 0.75rem;
            color: var(--text-secondary);
            text-decoration: none;
            transition: var(--transition);
            background: var(--bg-card);
        }
        .contact-chip:hover {
            border-color: var(--accent-border);
            color: var(--accent);
            background: var(--accent-soft);
        }
        .contact-chip svg { width: 14px; height: 14px; }

        /* ── ANIMATIONS ── */
        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(24px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        @keyframes pulse {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.5; transform: scale(0.8); }
        }

        /* ── RESPONSIVE ── */
        @media (max-width: 900px) {
            .about-grid { grid-template-columns: 1fr; gap: 3rem; }
            .features-grid { grid-template-columns: 1fr 1fr; }
            .steps-grid { grid-template-columns: 1fr 1fr; gap: 2rem; }
            .steps-grid::before { display: none; }
            .footer-top { grid-template-columns: 1fr; gap: 2rem; }
            .nav-links { display: none; }
        }
        @media (max-width: 600px) {
            .features-grid { grid-template-columns: 1fr; }
            .steps-grid { grid-template-columns: 1fr; }
            .hero-stats { gap: 1.5rem; }
            nav { padding: 0.75rem 4vw; }
        }
    </style>
</head>
<body>

<div class="glow glow-1"></div>
<div class="glow glow-2"></div>

<!-- ══ NAV ══ -->
<nav>
    <a href="{{ url('/') }}" class="nav-brand">
        <div class="nav-logo">A</div>
        <div>
            <div class="nav-title">SPK — AHP</div>
            <div class="nav-subtitle">Decision Support System</div>
        </div>
    </a>

    <ul class="nav-links">
        <li><a href="#tentang">Tentang AHP</a></li>
        <li><a href="#fitur">Fitur</a></li>
        <li><a href="#cara-kerja">Cara Kerja</a></li>
        <li><a href="#demo">Demo</a></li>
    </ul>

    <div class="nav-right">
        <button class="theme-toggle" id="themeToggle" aria-label="Toggle theme" type="button">
            <svg class="sun" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="5"></circle>
                <line x1="12" y1="1" x2="12" y2="3"></line>
                <line x1="12" y1="21" x2="12" y2="23"></line>
                <line x1="4.22" y1="4.22" x2="5.64" y2="5.64"></line>
                <line x1="18.36" y1="18.36" x2="19.78" y2="19.78"></line>
                <line x1="1" y1="12" x2="3" y2="12"></line>
                <line x1="21" y1="12" x2="23" y2="12"></line>
                <line x1="4.22" y1="19.78" x2="5.64" y2="18.36"></line>
                <line x1="18.36" y1="5.64" x2="19.78" y2="4.22"></line>
            </svg>
            <svg class="moon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path>
            </svg>
        </button>
        @if(Route::has('login'))
            @auth
                <a href="{{ route('dashboard') }}" class="btn-primary">Dashboard →</a>
            @else
                <a href="{{ route('login') }}" class="btn-ghost">Masuk</a>
                @if(Route::has('register'))
                    <a href="{{ route('register') }}" class="btn-primary">Mulai →</a>
                @endif
            @endauth
        @endif
    </div>
</nav>

<!-- ══ HERO ══ -->
<section class="hero">
    <div class="hero-badge">
        <span></span>
        Sistem Pendukung Keputusan · Offline Ready
    </div>

    <h1>Pengambilan Keputusan<br>yang <em>Terstruktur</em> & Akurat</h1>

    <p>
        Sistem berbasis metode <strong style="color:var(--text-primary); transition: color 0.3s;">Analytic Hierarchy Process (AHP)</strong>
        untuk membantu Anda membuat keputusan objektif melalui perbandingan
        kriteria dan sub-kriteria secara sistematis.
    </p>

    <div class="hero-actions">
        @if(Route::has('login'))
            @auth
                <a href="{{ route('dashboard') }}" class="btn-lg accent">Buka Dashboard →</a>
            @else
                @if(Route::has('register'))
                    <a href="{{ route('register') }}" class="btn-lg accent">Mulai Sekarang →</a>
                @endif
                <a href="{{ route('login') }}" class="btn-lg outline">Masuk ke Sistem</a>
            @endauth
        @else
            <a href="#fitur" class="btn-lg accent">Pelajari Fitur →</a>
            <a href="#cara-kerja" class="btn-lg outline">Cara Kerja</a>
        @endif
    </div>

    <div class="hero-stats">
        <div class="stat-item">
            <div class="stat-num">AHP</div>
            <div class="stat-label">Metode Analisis</div>
        </div>
        <div class="stat-item">
            <div class="stat-num">∞</div>
            <div class="stat-label">Kriteria & Sub-Kriteria</div>
        </div>
        <div class="stat-item">
            <div class="stat-num">100%</div>
            <div class="stat-label">Offline Mode</div>
        </div>
        <div class="stat-item">
            <div class="stat-num">CR</div>
            <div class="stat-label">Consistency Ratio Check</div>
        </div>
    </div>
</section>

<!-- ══ DIVIDER ══ -->
<div class="divider">
    <div class="divider-line"></div>
    <div class="divider-icon">◆</div>
    <div class="divider-line"></div>
</div>

<!-- ══ ABOUT AHP ══ -->
<section id="tentang">
    <div class="section-inner">
        <div class="about-grid">
            <div>
                <div class="section-tag">Tentang Metode</div>
                <h2 class="section-title">Apa itu<br><em>Analytic Hierarchy Process?</em></h2>
                <p class="section-desc">
                    AHP adalah metode pengambilan keputusan multi-kriteria yang dikembangkan
                    oleh Thomas L. Saaty. Metode ini memecah masalah kompleks menjadi
                    hierarki yang terstruktur — dari tujuan utama, kriteria, sub-kriteria,
                    hingga alternatif pilihan.
                </p>

                <div class="about-points">
                    <div class="about-point">
                        <div class="point-icon">⚖</div>
                        <div>
                            <div class="point-title">Perbandingan Berpasangan</div>
                            <div class="point-desc">Setiap kriteria dan sub-kriteria dibandingkan secara berpasangan menggunakan skala Saaty 1–9 untuk menentukan bobot relatifnya.</div>
                        </div>
                    </div>
                    <div class="about-point">
                        <div class="point-icon">📐</div>
                        <div>
                            <div class="point-title">Konsistensi Terukur</div>
                            <div class="point-desc">Setiap penilaian diverifikasi dengan Consistency Ratio (CR ≤ 0.1) untuk memastikan keputusan yang logis dan konsisten.</div>
                        </div>
                    </div>
                    <div class="about-point">
                        <div class="point-icon">🏆</div>
                        <div>
                            <div class="point-title">Perankingan Alternatif</div>
                            <div class="point-desc">Hasil akhir berupa skor prioritas setiap alternatif, sehingga pilihan terbaik dapat ditentukan secara objektif dan transparan.</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="about-visual">
                <div class="ahp-diagram">
                    <div class="ahp-level">
                        <div class="ahp-level-label">Level 1 — Tujuan (Goal)</div>
                        <div class="ahp-nodes">
                            <div class="ahp-node goal">🎯 Pemilihan Keputusan Terbaik</div>
                        </div>
                    </div>
                    <div class="ahp-connector">↓ ↓ ↓</div>
                    <div class="ahp-level">
                        <div class="ahp-level-label">Level 2 — Kriteria</div>
                        <div class="ahp-nodes">
                            <div class="ahp-node criteria">C1: Kriteria A</div>
                            <div class="ahp-node criteria">C2: Kriteria B</div>
                            <div class="ahp-node criteria">C3: Kriteria C</div>
                        </div>
                    </div>
                    <div class="ahp-connector">↓ ↓ ↓ ↓ ↓ ↓</div>
                    <div class="ahp-level">
                        <div class="ahp-level-label">Level 3 — Sub-Kriteria</div>
                        <div class="ahp-nodes">
                            <div class="ahp-node sub">SC1.1</div>
                            <div class="ahp-node sub">SC1.2</div>
                            <div class="ahp-node sub">SC2.1</div>
                            <div class="ahp-node sub">SC2.2</div>
                            <div class="ahp-node sub">SC3.1</div>
                            <div class="ahp-node sub">SC3.2</div>
                        </div>
                    </div>
                    <div class="ahp-connector">↓ ↓ ↓</div>
                    <div class="ahp-level">
                        <div class="ahp-level-label">Level 4 — Alternatif</div>
                        <div class="ahp-nodes">
                            <div class="ahp-node criteria" style="background:rgba(14,165,233,0.08);border-color:rgba(14,165,233,0.2);color:var(--accent)">Alt. 1</div>
                            <div class="ahp-node criteria" style="background:rgba(14,165,233,0.08);border-color:rgba(14,165,233,0.2);color:var(--accent)">Alt. 2</div>
                            <div class="ahp-node criteria" style="background:rgba(14,165,233,0.08);border-color:rgba(14,165,233,0.2);color:var(--accent)">Alt. 3</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ══ FEATURES ══ -->
<section id="fitur" class="features-section">
    <div class="section-inner">
        <div class="features-header">
            <div>
                <div class="section-tag">Fitur Unggulan</div>
                <h2 class="section-title">Semua yang Anda<br>Butuhkan</h2>
            </div>
            <p class="section-desc" style="max-width:300px">
                Dirancang untuk kemudahan penggunaan sekaligus ketepatan analisis multi-kriteria.
            </p>
        </div>

        <div class="features-grid">
            <div class="feature-card">
                <div class="feature-num">01</div>
                <div class="feature-title">Manajemen Kriteria</div>
                <div class="feature-desc">Tambah, edit, dan kelola kriteria penilaian secara fleksibel. Setiap kriteria memiliki bobot yang dihitung otomatis dari matriks perbandingan berpasangan.</div>
                <div class="feature-tags">
                    <span class="tag">Bobot Otomatis</span>
                    <span class="tag">CRUD</span>
                </div>
            </div>

            <div class="feature-card">
                <div class="feature-num">02</div>
                <div class="feature-title">Sub-Kriteria Bertingkat</div>
                <div class="feature-desc">Setiap kriteria dapat memiliki sub-kriteria yang lebih spesifik. Sistem mendukung hierarki bertingkat untuk analisis yang lebih mendalam dan terperinci.</div>
                <div class="feature-tags">
                    <span class="tag">Hierarki</span>
                    <span class="tag">Bertingkat</span>
                </div>
            </div>

            <div class="feature-card">
                <div class="feature-num">03</div>
                <div class="feature-title">Matriks Perbandingan</div>
                <div class="feature-desc">Input matriks perbandingan berpasangan dengan panduan skala Saaty. Sistem mengisi nilai resiprokal secara otomatis untuk efisiensi pengisian data.</div>
                <div class="feature-tags">
                    <span class="tag">Skala Saaty</span>
                    <span class="tag">Auto-Fill</span>
                </div>
            </div>

            <div class="feature-card">
                <div class="feature-num">04</div>
                <div class="feature-title">Uji Konsistensi (CR)</div>
                <div class="feature-desc">Setiap matriks diverifikasi otomatis dengan Consistency Ratio. Peringatan diberikan jika CR > 0.1, memastikan kevalidan penilaian yang diberikan.</div>
                <div class="feature-tags">
                    <span class="tag">CR ≤ 0.1</span>
                    <span class="tag">Validasi</span>
                </div>
            </div>

            <div class="feature-card">
                <div class="feature-num">05</div>
                <div class="feature-title">Perankingan & Laporan</div>
                <div class="feature-desc">Hasil akhir ditampilkan dalam bentuk ranking alternatif yang jelas, dilengkapi visualisasi bobot dan laporan yang dapat dicetak atau diekspor.</div>
                <div class="feature-tags">
                    <span class="tag">Ranking</span>
                    <span class="tag">Export</span>
                </div>
            </div>

            <div class="feature-card">
                <div class="feature-num">06</div>
                <div class="feature-title">Mode Offline Penuh</div>
                <div class="feature-desc">Sistem berjalan sepenuhnya secara lokal tanpa koneksi internet. Data tersimpan aman di database lokal, cocok untuk lingkungan dengan keterbatasan jaringan.</div>
                <div class="feature-tags">
                    <span class="tag">Localhost</span>
                    <span class="tag">Database Lokal</span>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ══ HOW IT WORKS ══ -->
<section id="cara-kerja">
    <div class="section-inner">
        <div style="text-align:center; margin-bottom:1rem">
            <div class="section-tag" style="justify-content:center;display:flex">Alur Penggunaan</div>
            <h2 class="section-title">Cara Kerja Sistem</h2>
            <p class="section-desc" style="margin:0 auto">Empat langkah sederhana menuju keputusan yang objektif dan terstruktur.</p>
        </div>

        <div class="steps-grid">
            <div class="step">
                <div class="step-num">1</div>
                <div class="step-title">Tentukan Tujuan & Alternatif</div>
                <div class="step-desc">Definisikan tujuan pengambilan keputusan dan daftarkan semua alternatif yang akan dievaluasi dalam sistem.</div>
            </div>
            <div class="step">
                <div class="step-num">2</div>
                <div class="step-title">Susun Kriteria & Sub-Kriteria</div>
                <div class="step-desc">Tambahkan kriteria penilaian beserta sub-kriterianya. Setiap level hierarki dikonfigurasi secara terpisah.</div>
            </div>
            <div class="step">
                <div class="step-num">3</div>
                <div class="step-title">Isi Matriks Perbandingan</div>
                <div class="step-desc">Bandingkan setiap elemen secara berpasangan menggunakan skala Saaty. Sistem menghitung bobot dan mengecek konsistensi secara real-time.</div>
            </div>
            <div class="step">
                <div class="step-num">4</div>
                <div class="step-title">Lihat Hasil & Ranking</div>
                <div class="step-desc">Dapatkan skor akhir dan ranking seluruh alternatif berdasarkan perhitungan AHP yang akurat dan transparan.</div>
            </div>
        </div>
    </div>
</section>

<!-- ══ DEMO TABLE ══ -->
{{-- <section id="demo" class="demo-section">
    <div class="section-inner">
        <div class="section-tag">Contoh Output</div>
        <h2 class="section-title">Hasil Perbandingan Kriteria</h2>
        <p class="section-desc">Contoh output matriks perbandingan berpasangan dan bobot prioritas kriteria.</p>

        <div class="demo-container">
            <div class="demo-header">
                <div class="demo-dot" style="background:#ef4444"></div>
                <div class="demo-dot" style="background:#f59e0b"></div>
                <div class="demo-dot" style="background:#22c55e"></div>
                <div class="demo-label">Matriks Perbandingan — Kriteria Utama · CR = 0.042 ✓</div>
            </div>
            <div class="demo-body">
                <table>
                    <thead>
                        <tr>
                            <th>Kriteria</th>
                            <th>C1: Kualitas</th>
                            <th>C2: Harga</th>
                            <th>C3: Waktu</th>
                            <th>C4: Layanan</th>
                            <th>Bobot Prioritas</th>
                            <th>Rank</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="td-highlight">C1: Kualitas</td>
                            <td>1.000</td>
                            <td>3.000</td>
                            <td>5.000</td>
                            <td>2.000</td>
                            <td><span class="badge-val high">0.4742</span></td>
                            <td class="td-highlight">#1</td>
                        </tr>
                        <tr>
                            <td class="td-highlight">C2: Harga</td>
                            <td>0.333</td>
                            <td>1.000</td>
                            <td>3.000</td>
                            <td>2.000</td>
                            <td><span class="badge-val med">0.2651</span></td>
                            <td class="td-highlight">#2</td>
                        </tr>
                        <tr>
                            <td class="td-highlight">C3: Waktu</td>
                            <td>0.200</td>
                            <td>0.333</td>
                            <td>1.000</td>
                            <td>0.500</td>
                            <td><span class="badge-val low">0.0923</span></td>
                            <td class="td-highlight">#4</td>
                        </tr>
                        <tr>
                            <td class="td-highlight">C4: Layanan</td>
                            <td>0.500</td>
                            <td>0.500</td>
                            <td>2.000</td>
                            <td>1.000</td>
                            <td><span class="badge-val med">0.1684</span></td>
                            <td class="td-highlight">#3</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</section> --}}

<!-- ══ FOOTER ══ -->
<footer>
    <div class="footer-inner">
        <div class="footer-top">
            <div>
                <div class="nav-logo" style="margin-bottom:1rem; width:44px;height:44px;font-size:1.3rem">A</div>
                <div class="footer-brand-title">SPK — Analytic Hierarchy Process</div>
                <p class="footer-brand-desc" style="margin-top:0.5rem">
                    Sistem Pendukung Keputusan berbasis metode AHP yang dirancang
                    untuk penggunaan offline, membantu organisasi dalam mengambil
                    keputusan multi-kriteria secara sistematis dan objektif.
                </p>
            </div>

            <div>
                <div class="footer-col-title">Navigasi</div>
                <ul class="footer-links">
                    <li><a href="#tentang">Tentang AHP</a></li>
                    <li><a href="#fitur">Fitur Sistem</a></li>
                    <li><a href="#cara-kerja">Cara Kerja</a></li>
                    {{-- <li><a href="#demo">Demo Output</a></li> --}}
                    @if(Route::has('login'))
                        @guest
                            <li><a href="{{ route('login') }}">Masuk</a></li>
                            @if(Route::has('register'))
                                <li><a href="{{ route('register') }}">Daftar</a></li>
                            @endif
                        @endguest
                    @endif
                </ul>
            </div>

            <div>
                <div class="footer-col-title">Kontak Developer</div>
                <ul class="footer-links">
                    <li>
                        <a href="https://github.com/tomzsh" target="_blank" rel="noopener">
                            <svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 0C5.374 0 0 5.373 0 12c0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23A11.509 11.509 0 0112 5.803c1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576C20.566 21.797 24 17.3 24 12c0-6.627-5.373-12-12-12z"/></svg>
                            github.com/tomzsh
                        </a>
                    </li>
                    <li>
                        <a href="mailto:gus.tom.zsh@gmail.com">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75"/></svg>
                            gus.tom.zsh@gmail.com
                        </a>
                    </li>
                </ul>
            </div>
        </div>

        <div class="footer-bottom">
            <div class="footer-copy">
                © {{ date('Y') }} <span>SPK AHP</span> · Dibuat dengan ♥ menggunakan Laravel & Livewire
            </div>
            <div class="contact-chips">
                <a href="https://github.com/tomzsh" target="_blank" rel="noopener" class="contact-chip">
                    <svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 0C5.374 0 0 5.373 0 12c0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23A11.509 11.509 0 0112 5.803c1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576C20.566 21.797 24 17.3 24 12c0-6.627-5.373-12-12-12z"/></svg>
                    @tomzsh
                </a>
                <a href="mailto:gus.tom.zsh@gmail.com" class="contact-chip">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75"/></svg>
                    gus.tom.zsh@gmail.com
                </a>
            </div>
        </div>
    </div>
</footer>

<script>
    (function() {
        const themeToggle = document.getElementById('themeToggle');
        const html = document.documentElement;

        const savedTheme = localStorage.getItem('theme');
        if (savedTheme) {
            html.setAttribute('data-theme', savedTheme);
        } else if (window.matchMedia('(prefers-color-scheme: dark)').matches) {
            html.setAttribute('data-theme', 'dark');
        }

        themeToggle.addEventListener('click', function() {
            const current = html.getAttribute('data-theme');
            const next = current === 'dark' ? 'light' : 'dark';
            html.setAttribute('data-theme', next);
            localStorage.setItem('theme', next);
        });

        document.querySelectorAll('a[href^="#"]').forEach(function(anchor) {
            anchor.addEventListener('click', function(e) {
                const href = this.getAttribute('href');
                if (href !== '#') {
                    e.preventDefault();
                    const target = document.querySelector(href);
                    if (target) {
                        target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    }
                }
            });
        });
    })();
</script>

</body>
</html>
