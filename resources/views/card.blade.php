<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#185FA5">

    <title>Centresidence | Smarter Property Management. Stronger Communities.</title>
    <meta name="description" content="Centresidence — Infrastructure & Finance OS for smarter property management and stronger communities.">

    <meta property="og:title" content="Centresidence — Infrastructure & Finance OS">
    <meta property="og:description" content="Smarter property management. Stronger communities.">
    <meta property="og:url" content="https://centresidence.com/card">
    <meta property="og:type" content="website">

    <style>
        :root {
            --blue: #185FA5;
            --blue-hover: #0F4A84;
            --blue-light: #E6F1FB;
            --blue-border: #B5D4F4;
            --blue-ghost: #185ea51c;
            --gray-900: #111827;
            --gray-700: #374151;
            --gray-500: #6b7280;
            --gray-400: #9ca3af;
            --gray-200: #e5e7eb;
            --gray-50: #fafafa;
            --white: #ffffff;
            --green: #1D9E75;
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            min-height: 100vh;
            background: #f8fafc;
            color: var(--gray-700);
            font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            -webkit-font-smoothing: antialiased;
        }

        a { color: inherit; text-decoration: none; }

        .page {
            width: 100%;
            max-width: 760px;
            margin: 0 auto;
            padding: 24px 16px 36px;
        }

        .card {
            background: var(--white);
            border: .5px solid rgba(24,95,165,.43);
            border-radius: 12px;
            overflow: hidden;
            box-shadow:
                0 4px 12px rgba(0,0,0,.04),
                0 0 0 1px rgba(24,95,165,.05),
                0 6px 18px rgba(24,95,165,.06);
        }

        .hero {
            position: relative;
            overflow: hidden;
            padding: 28px 24px 26px;
            color: white;
            background:
                radial-gradient(circle at 85% 10%, rgba(24,95,165,.32), transparent 32%),
                linear-gradient(135deg, #061321 0%, #081b30 55%, #07111e 100%);
        }

        .hero::after {
            content: "";
            position: absolute;
            width: 180px;
            height: 180px;
            right: -80px;
            bottom: -100px;
            border-radius: 50%;
            border: 1px solid rgba(24,95,165,.35);
            box-shadow: 0 0 0 28px rgba(24,95,165,.05), 0 0 0 56px rgba(24,95,165,.035);
        }

        .brand {
            position: relative;
            z-index: 1;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .brand-mark {
            width: 48px;
            height: 38px;
            display: grid;
            place-items: center;
            color: #fff;
            font-size: 22px;
            font-weight: 800;
            letter-spacing: -2px;
            border-radius: 9px;
            background: linear-gradient(135deg, #1687ff, #185FA5);
            box-shadow: 0 0 22px rgba(24,95,165,.35);
        }

        .brand-name {
            font-size: 25px;
            line-height: 1;
            font-weight: 400;
            letter-spacing: -1px;
        }

        .tagline {
            margin-top: 5px;
            color: #00aeea;
            font-size: 9px;
            font-weight: 700;
            letter-spacing: .18em;
        }

        .eyebrow {
            margin-top: 25px;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            color: #00aeea;
            font-size: 10px;
            font-weight: 600;
            letter-spacing: .22em;
            text-transform: uppercase;
        }

        .eyebrow::after {
            content: "";
            width: 58px;
            height: 1px;
            background: rgba(24,95,165,.65);
        }

        .hero h1 {
            position: relative;
            z-index: 1;
            margin: 17px 0 7px;
            max-width: 560px;
            font-size: clamp(25px, 5vw, 38px);
            line-height: 1.08;
            font-weight: 500;
            letter-spacing: -.04em;
        }

        .hero h1 span { color: #0aaef0; }

        .hero-copy {
            position: relative;
            z-index: 1;
            margin: 0;
            max-width: 570px;
            color: rgba(255,255,255,.72);
            font-size: 13px;
            line-height: 1.65;
        }

        .actions {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px;
            padding: 16px;
            border-bottom: .5px solid var(--gray-200);
            background: var(--gray-50);
        }

        .btn {
            min-height: 42px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 7px;
            padding: 8px 13px;
            border-radius: 7px;
            border: .5px solid transparent;
            font-size: 12px;
            font-weight: 500;
            transition: all .15s ease;
        }

        .btn:active { transform: translateY(1px); }

        .btn-primary {
            color: white;
            background: var(--blue);
        }

        .btn-primary:hover { background: var(--blue-hover); transform: translateY(-1px); }

        .btn-ghost {
            color: var(--gray-700);
            background: #fff;
            border-color: var(--gray-200);
        }

        .btn-ghost:hover {
            color: var(--blue);
            border-color: var(--blue-border);
            background: var(--blue-light);
        }

        .section {
            padding: 22px 20px;
        }

        .section + .section {
            border-top: .5px solid var(--gray-200);
        }

        .section-label {
            margin-bottom: 13px;
            color: var(--gray-400);
            font-size: 10px;
            font-weight: 500;
            letter-spacing: .07em;
            text-transform: uppercase;
        }

        .services {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px;
        }

        .service {
            display: flex;
            align-items: center;
            gap: 10px;
            min-height: 58px;
            padding: 10px;
            border: .5px solid var(--gray-200);
            border-radius: 10px;
            background: #fff;
            transition: all .25s ease;
        }

        .service:hover {
            border-color: var(--blue);
            transform: translateY(-3px);
            box-shadow:
                0 10px 25px rgba(0,0,0,.06),
                0 0 0 1px rgba(24,95,165,.12),
                0 12px 30px rgba(24,95,165,.12);
        }

        .service-icon {
            flex: 0 0 31px;
            width: 31px;
            height: 31px;
            display: grid;
            place-items: center;
            border-radius: 50%;
            color: var(--blue);
            background: var(--blue-light);
            border: .5px solid var(--blue-border);
        }

        .service-icon svg {
            width: 16px;
            height: 16px;
            stroke: currentColor;
            stroke-width: 1.8;
            fill: none;
            stroke-linecap: round;
            stroke-linejoin: round;
        }

        .service-name {
            font-size: 12px;
            line-height: 1.35;
            font-weight: 500;
            color: var(--gray-700);
        }

        .contact-grid {
            display: grid;
            gap: 0;
        }

        .contact-row {
            display: flex;
            align-items: center;
            gap: 11px;
            padding: 11px 0;
            border-bottom: .5px solid #f3f4f6;
        }

        .contact-row:last-child { border-bottom: 0; }

        .contact-icon {
            flex: 0 0 30px;
            width: 30px;
            height: 30px;
            display: grid;
            place-items: center;
            border-radius: 8px;
            color: var(--blue);
            background: var(--blue-ghost);
        }

        .contact-icon svg {
            width: 15px;
            height: 15px;
            stroke: currentColor;
            stroke-width: 1.8;
            fill: none;
            stroke-linecap: round;
            stroke-linejoin: round;
        }

        .contact-meta {
            min-width: 0;
        }

        .contact-label {
            color: var(--gray-400);
            font-size: 10px;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: .07em;
        }

        .contact-value {
            margin-top: 2px;
            color: var(--gray-700);
            font-size: 13px;
            font-weight: 500;
            overflow-wrap: anywhere;
        }

        .contact-value:hover { color: var(--blue); }

        .cta {
            margin-top: 2px;
            padding: 18px 20px 20px;
            background: linear-gradient(180deg, #fafafa, #fff);
            border-top: .5px solid var(--gray-200);
        }

        .cta-inner {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
        }

        .cta-title {
            color: var(--gray-900);
            font-size: 14px;
            font-weight: 600;
        }

        .cta-copy {
            margin-top: 3px;
            color: var(--gray-500);
            font-size: 12px;
            line-height: 1.45;
        }

        .footer {
            padding: 16px 20px 20px;
            text-align: center;
            color: var(--gray-400);
            font-size: 10px;
            letter-spacing: .02em;
        }

        .qr-fallback {
            display: none;
        }

        @media (max-width: 540px) {
            .page { padding: 0 0 24px; }
            .card { border-radius: 0 0 12px 12px; }
            .hero { padding: 25px 18px 23px; }
            .actions { padding: 12px; }
            .section { padding: 20px 16px; }
            .services { grid-template-columns: 1fr; }
            .cta-inner { align-items: stretch; flex-direction: column; }
            .cta .btn { width: 100%; }
        }

        @media (min-width: 541px) {
            .actions { grid-template-columns: repeat(4, 1fr); }
        }
    </style>
</head>

<body>
<div class="page">
    <main class="card">

        <header class="hero">
            <div class="brand">
                <div class="brand-mark">CS</div>
                <div>
                    <div class="brand-name">centresidence</div>
                    <div class="tagline">REAL ESTATE. SIMPLIFIED. CONNECTED.</div>
                </div>
            </div>

            <div class="eyebrow">Infrastructure &amp; Finance OS</div>

            <h1>Smarter Property.<br><span>Stronger Communities.</span></h1>

            <p class="hero-copy">
                Centresidence connects property management, payments, services,
                utilities, finance and tenant experiences in one intelligent platform.
            </p>
        </header>

        <nav class="actions" aria-label="Centresidence actions">
            <a class="btn btn-primary" href="{{ url('/card/contact') }}">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2v20M2 12h20"/></svg>
                Save Contact
            </a>

            <a class="btn btn-ghost" href="https://centresidence.com/" target="_blank" rel="noopener">
                Visit Website
            </a>

            <a class="btn btn-ghost" href="https://wa.me/254714389005?text=Hi%20Centresidence%2C%20I%20met%20your%20team%20and%20would%20like%20to%20learn%20more." target="_blank" rel="noopener">
                WhatsApp Us
            </a>

            <a class="btn btn-ghost" href="mailto:info@centresidence.com?subject=Centresidence%20Enquiry">
                Email Us
            </a>
        </nav>

        <section class="section">
            <div class="section-label">What we do</div>

            <div class="services">
                <div class="service">
                    <div class="service-icon">
                        <svg viewBox="0 0 24 24"><path d="M3 21h18M5 21V7l7-4 7 4v14M9 21v-7h6v7"/></svg>
                    </div>
                    <div class="service-name">Property &amp; Tenant Management</div>
                </div>

                <div class="service">
                    <div class="service-icon">
                        <svg viewBox="0 0 24 24"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="M3 10h18M7 15h3"/></svg>
                    </div>
                    <div class="service-name">Rent Collection &amp; Payments</div>
                </div>

                <div class="service">
                    <div class="service-icon">
                        <svg viewBox="0 0 24 24"><path d="m14.7 6.3 3-3a2.1 2.1 0 0 1 3 3l-3 3M13 8l3 3M3 21l6.5-2.2L19 9.3l-4.3-4.3L5.2 14.5 3 21Z"/></svg>
                    </div>
                    <div class="service-name">Maintenance &amp; Service Requests</div>
                </div>

                <div class="service">
                    <div class="service-icon">
                        <svg viewBox="0 0 24 24"><path d="M8 3h8v18H8zM11 7h2M10 11h4M10 15h4M10 19h4"/></svg>
                    </div>
                    <div class="service-name">Prepaid Utilities &amp; Smart Metering</div>
                </div>

                <div class="service">
                    <div class="service-icon">
                        <svg viewBox="0 0 24 24"><path d="M4 19V5M4 19h17M7 16l4-5 3 2 5-7"/></svg>
                    </div>
                    <div class="service-name">Property Finance &amp; Growth Capital</div>
                </div>

                <div class="service">
                    <div class="service-icon">
                        <svg viewBox="0 0 24 24"><path d="M4 10h16v10H4zM2 10l2-5h16l2 5M8 10v10M16 10v10"/></svg>
                    </div>
                    <div class="service-name">Marketplace &amp; Tenant Services</div>
                </div>
            </div>
        </section>

        <section class="section">
            <div class="section-label">Connect with us</div>

            <div class="contact-grid">
                <div class="contact-row">
                    <div class="contact-icon">
                        <svg viewBox="0 0 24 24"><path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3.1 19.5 19.5 0 0 1-6-6A19.8 19.8 0 0 1 2.1 4.2 2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7c.1 1 .4 2 .7 2.9a2 2 0 0 1-.5 2.1L8 10a16 16 0 0 0 6 6l1.3-1.3a2 2 0 0 1 2.1-.5c.9.3 1.9.6 2.9.7A2 2 0 0 1 22 16.9Z"/></svg>
                    </div>
                    <div class="contact-meta">
                        <div class="contact-label">Phone / WhatsApp</div>
                        <a class="contact-value" href="tel:+254714389005">+254 714 389 005</a>
                    </div>
                </div>

                <div class="contact-row">
                    <div class="contact-icon">
                        <svg viewBox="0 0 24 24"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/></svg>
                    </div>
                    <div class="contact-meta">
                        <div class="contact-label">Email</div>
                        <a class="contact-value" href="mailto:info@centresidence.com">info@centresidence.com</a>
                    </div>
                </div>

                <div class="contact-row">
                    <div class="contact-icon">
                        <svg viewBox="0 0 24 24"><path d="M12 21s7-6.1 7-12a7 7 0 1 0-14 0c0 5.9 7 12 7 12Z"/><circle cx="12" cy="9" r="2.2"/></svg>
                    </div>
                    <div class="contact-meta">
                        <div class="contact-label">Location</div>
                        <div class="contact-value">Nairobi, Kenya</div>
                    </div>
                </div>
            </div>
        </section>

        <section class="cta">
            <div class="cta-inner">
                <div>
                    <div class="cta-title">Ready to explore Centresidence?</div>
                    <div class="cta-copy">Let's talk about your property, portfolio or community.</div>
                </div>

                <a class="btn btn-primary" href="https://wa.me/254714389005?text=Hi%20Centresidence%2C%20I%20met%20your%20team%20at%20the%20trade%20fair%20and%20would%20like%20to%20learn%20more." target="_blank" rel="noopener">
                    Book a Demo
                </a>
            </div>
        </section>

        <footer class="footer">
            centresidence.com &nbsp;•&nbsp; Real Estate. Simplified. Connected.
        </footer>
    </main>
</div>
</body>
</html>
