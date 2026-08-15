# -*- coding: utf-8 -*-
import os

ROOT = os.path.dirname(os.path.abspath(__file__))

NAV_ITEMS = [
    ("index.html", "Acasă"),
    ("devino-curier.html", "Devino Curier"),
    ("despre-noi.html", "Despre Noi"),
    ("orase.html", "Orașe"),
    ("intrebari-frecvente.html", "FAQ"),
    ("contact.html", "Contact"),
    ("blog.html", "Blog"),
    ("cariere.html", "Cariere"),
]

def header(active, depth=""):
    links = []
    for href, label in NAV_ITEMS:
        cls = ' class="active"' if href == active else ''
        links.append(f'<li><a href="{depth}{href}"{cls}>{label}</a></li>')
    links_html = "\n          ".join(links)
    return f"""  <header class="site-header" id="siteHeader">
    <div class="container">
      <a href="{depth}index.html" class="brand">
        <img src="{depth}assets/images/logo-icon.png" alt="GoDeliver logo">
        <span>GoDeliver</span>
      </a>
      <nav class="nav-main" id="navMain">
        <ul style="display:flex;flex-direction:inherit;gap:inherit;align-items:inherit;list-style:none;padding:0;margin:0;">
          {links_html}
        </ul>
        <div class="header-actions" style="margin-top:26px;display:flex;">
          <a href="{depth}devino-curier.html" class="btn btn-primary btn-sm">Aplică Acum</a>
        </div>
      </nav>
      <div class="header-actions">
        <div class="lang-switch">
          <button class="active" type="button" data-lang="ro">RO</button>
          <button type="button" data-lang="en">EN</button>
        </div>
        <a href="{depth}devino-curier.html" class="btn btn-primary btn-sm" style="display:none" id="headerCta">Aplică Acum</a>
        <button class="nav-toggle" id="navToggle" aria-label="Deschide meniul">
          <span></span><span></span><span></span>
        </button>
      </div>
    </div>
  </header>
"""

def footer(depth=""):
    return f"""  <footer class="site-footer">
    <div class="container">
      <div class="footer-grid">
        <div class="footer-brand">
          <a href="{depth}index.html" class="brand" style="margin-bottom:16px;">
            <img src="{depth}assets/images/logo-icon.png" alt="GoDeliver logo">
            <span>GoDeliver</span>
          </a>
          <p>Platforma prin care curierii din România livrează pentru Glovo, Bolt Food și Wolt — plăți săptămânale, comision minim, suport real 24/7.</p>
          <div class="footer-social">
            <a href="https://facebook.com" target="_blank" rel="noopener" aria-label="Facebook">f</a>
            <a href="https://instagram.com" target="_blank" rel="noopener" aria-label="Instagram">ig</a>
            <a href="https://wa.me/40700000000" target="_blank" rel="noopener" aria-label="WhatsApp">wa</a>
            <a href="https://linkedin.com" target="_blank" rel="noopener" aria-label="LinkedIn">in</a>
          </div>
        </div>
        <div class="footer-col">
          <h4>Companie</h4>
          <a href="{depth}despre-noi.html">Despre Noi</a>
          <a href="{depth}orase.html">Orașe</a>
          <a href="{depth}blog.html">Blog</a>
          <a href="{depth}cariere.html">Cariere</a>
        </div>
        <div class="footer-col">
          <h4>Curieri</h4>
          <a href="{depth}devino-curier.html">Devino Curier</a>
          <a href="{depth}intrebari-frecvente.html">Întrebări Frecvente</a>
          <a href="{depth}portal/login.php">Portal Curier</a>
          <a href="{depth}contact.html">Suport</a>
        </div>
        <div class="footer-col">
          <h4>Legal</h4>
          <a href="{depth}confidentialitate.html">Confidențialitate</a>
          <a href="{depth}termeni.html">Termeni și Condiții</a>
          <a href="{depth}gdpr.html">GDPR</a>
          <a href="{depth}cookies.html">Cookies</a>
        </div>
        <div class="footer-col">
          <h4>Contact</h4>
          <a href="tel:+40700000000">+40 700 000 000</a>
          <a href="mailto:contact@godeliver.ro">contact@godeliver.ro</a>
          <span style="display:block;font-size:13px;color:var(--text-muted);line-height:1.5;">CIORGOVEAN LIVIU FLORIN PFA<br>CUI 48540021 · Colaborare 100% remote</span>
        </div>
      </div>
      <div class="footer-bottom">
        <p>© <span data-year></span> GoDeliver. Toate drepturile rezervate.</p>
        <div class="footer-legal">
          <a href="{depth}confidentialitate.html">Confidențialitate</a>
          <a href="{depth}termeni.html">Termeni</a>
          <a href="{depth}gdpr.html">GDPR</a>
          <a href="{depth}cookies.html">Cookies</a>
        </div>
      </div>
    </div>
  </footer>

  <div class="cookie-banner">
    <p>Folosim cookie-uri pentru a-ți oferi cea mai bună experiență pe site. Poți afla mai multe în <a href="{depth}cookies.html" style="color:var(--gold)">Politica de Cookies</a>.</p>
    <div class="cookie-actions">
      <button class="btn btn-ghost btn-sm" type="button" data-cookie="essential">Doar Esențiale</button>
      <button class="btn btn-primary btn-sm" type="button" data-cookie="all">Accept Tot</button>
    </div>
  </div>
  <button class="back-to-top" aria-label="Înapoi sus">↑</button>
"""

def page(active, title, description, body, depth="", extra_head="", extra_scripts=""):
    return f"""<!DOCTYPE html>
<html lang="ro">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>{title}</title>
<meta name="description" content="{description}">
<meta name="robots" content="index, follow">
<link rel="canonical" href="https://godeliver.ro/{active if active != 'index.html' else ''}">
<meta property="og:title" content="{title}">
<meta property="og:description" content="{description}">
<meta property="og:type" content="website">
<meta property="og:image" content="{depth}assets/images/logo-full.png">
<meta name="twitter:card" content="summary_large_image">
<link rel="icon" type="image/png" href="{depth}assets/images/logo-icon.png">
<link rel="stylesheet" href="{depth}assets/css/style.css">
{extra_head}
</head>
<body class="is-loading">
{preloader()}
{header(active, depth)}
{body}
{footer(depth)}
<script src="{depth}assets/js/main.js"></script>
{extra_scripts}
</body>
</html>
"""

def preloader():
    return """  <div class="preloader" id="preloader" aria-hidden="true">
    <div class="preloader-inner">
      <div class="preloader-road">
        <svg class="preloader-scooter" viewBox="0 0 240 140" xmlns="http://www.w3.org/2000/svg">
          <g class="speed-trails">
            <rect x="0" y="46" width="46" height="4" rx="2" fill="#FFC400" opacity="0.85"/>
            <rect x="0" y="60" width="30" height="4" rx="2" fill="#FFC400" opacity="0.55"/>
            <rect x="0" y="74" width="18" height="4" rx="2" fill="#FFC400" opacity="0.3"/>
          </g>
          <g transform="translate(50,0)">
            <path d="M60 96 C58 80, 70 70, 86 70 L118 70 C126 70, 130 64, 138 64 L150 64" fill="none" stroke="#EDEDF0" stroke-width="6" stroke-linecap="round"/>
            <path d="M150 64 L158 46" stroke="#EDEDF0" stroke-width="6" stroke-linecap="round"/>
            <rect x="86" y="52" width="34" height="16" rx="8" fill="#FFC400"/>
            <circle cx="112" cy="46" r="10" fill="#EDEDF0"/>
            <path d="M108 40 Q112 30 120 32" stroke="#EDEDF0" stroke-width="5" stroke-linecap="round" fill="none"/>
            <g class="wheel" style="transform-origin:64px 96px;"><circle cx="64" cy="96" r="18" fill="none" stroke="#EDEDF0" stroke-width="5"/><circle cx="64" cy="96" r="3" fill="#EDEDF0"/><line x1="64" y1="80" x2="64" y2="112" stroke="#EDEDF0" stroke-width="2"/><line x1="48" y1="96" x2="80" y2="96" stroke="#EDEDF0" stroke-width="2"/></g>
            <g class="wheel" style="transform-origin:150px 96px;"><circle cx="150" cy="96" r="18" fill="none" stroke="#EDEDF0" stroke-width="5"/><circle cx="150" cy="96" r="3" fill="#EDEDF0"/><line x1="150" y1="80" x2="150" y2="112" stroke="#EDEDF0" stroke-width="2"/><line x1="134" y1="96" x2="166" y2="96" stroke="#EDEDF0" stroke-width="2"/></g>
          </g>
        </svg>
      </div>
      <div class="preloader-brand">
        <span>Go</span><span class="accent">Deliver</span>
      </div>
    </div>
  </div>
"""

def write(filename, html):
    path = os.path.join(ROOT, filename)
    os.makedirs(os.path.dirname(path), exist_ok=True)
    with open(path, "w", encoding="utf-8") as f:
        f.write(html)
    print("written:", filename)

# =========================================================
# HOME PAGE
# =========================================================
home_body = """
<main>
  <section class="hero">
    <div class="container hero-grid">
      <div class="hero-copy reveal in">
        <div class="eyebrow">Parteneri oficiali Glovo · Bolt Food · Wolt</div>
        <h1>Devino curier<br><span class="accent">GoDeliver</span> azi.</h1>
        <p>Câștigă bani livrând pentru Glovo, Bolt Food și Wolt. Program flexibil, plăți săptămânale și suport profesional, în peste 50 de orașe din România.</p>
        <div class="hero-actions">
          <a href="devino-curier.html" class="btn btn-primary">Aplică Acum</a>
          <a href="contact.html" class="btn btn-ghost">Contactează-ne</a>
        </div>
        <div class="hero-stats">
          <div class="stat"><b data-count="100" data-suffix="+">0</b><span>Curieri Activi</span></div>
          <div class="stat"><b data-count="50" data-suffix="+">0</b><span>Orașe</span></div>
          <div class="stat"><b data-count="24" data-suffix="/7">0</b><span>Suport</span></div>
          <div class="stat"><b data-count="99" data-suffix="%">0</b><span>Plăți la Timp</span></div>
        </div>
      </div>
      <div class="hero-visual reveal in">
        <div class="hero-art">
          <svg viewBox="0 0 500 620" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="xMidYMid slice">
            <defs>
              <linearGradient id="glow" x1="0" y1="0" x2="1" y2="1">
                <stop offset="0%" stop-color="#FFC400" stop-opacity="0.35"/>
                <stop offset="100%" stop-color="#FFC400" stop-opacity="0"/>
              </linearGradient>
              <linearGradient id="routeGrad" x1="0" y1="0" x2="1" y2="0">
                <stop offset="0%" stop-color="#FFC400" stop-opacity="0"/>
                <stop offset="100%" stop-color="#FFC400" stop-opacity="1"/>
              </linearGradient>
            </defs>
            <circle cx="380" cy="120" r="220" fill="url(#glow)"/>
            <g stroke="#26262c" stroke-width="1.2" opacity="0.6">
              <path d="M0 90 H500 M0 190 H500 M0 290 H500 M0 390 H500 M0 490 H500" />
              <path d="M70 0 V620 M180 0 V620 M290 0 V620 M400 0 V620" />
            </g>
            <path id="routePath" d="M40 520 C 120 480, 100 360, 200 330 S 340 250, 320 160 S 420 90, 460 60"
                  fill="none" stroke="#3a3a42" stroke-width="4" stroke-linecap="round"/>
            <path d="M40 520 C 120 480, 100 360, 200 330 S 340 250, 320 160 S 420 90, 460 60"
                  fill="none" stroke="url(#routeGrad)" stroke-width="4" stroke-linecap="round"
                  stroke-dasharray="700" stroke-dashoffset="700">
              <animate attributeName="stroke-dashoffset" from="700" to="0" dur="2.6s" fill="freeze" begin="0.2s"/>
            </path>
            <circle r="9" fill="#FFC400">
              <animateMotion dur="2.6s" begin="0.2s" fill="freeze" path="M40 520 C 120 480, 100 360, 200 330 S 340 250, 320 160 S 420 90, 460 60"/>
            </circle>
            <circle cx="40" cy="520" r="7" fill="#0A0A0B" stroke="#FFC400" stroke-width="3"/>
            <circle cx="460" cy="60" r="7" fill="#FFC400"/>
            <g transform="translate(150,420)" opacity="0.9">
              <rect x="-46" y="-24" width="92" height="48" rx="14" fill="#131316" stroke="#2c2c33"/>
              <text x="0" y="6" text-anchor="middle" fill="#EDEDF0" font-family="Inter" font-weight="700" font-size="13">12 min</text>
            </g>
          </svg>
        </div>
        <div class="hero-badge">
          <span class="dot"></span>
          <div>
            <strong>Comandă livrată</strong>
            <span>Curier activ · Cluj-Napoca</span>
          </div>
        </div>
      </div>
    </div>
  </section>

  <section class="partners-strip">
    <div class="container">
      <span class="label">Livrează pentru</span>
      <div class="partner-logos">
        <span>Glovo</span><span>Bolt Food</span><span>Wolt</span>
      </div>
    </div>
  </section>

  <section class="section" id="why">
    <div class="container">
      <div class="section-head center reveal">
        <div class="eyebrow" style="justify-content:center">De ce GoDeliver</div>
        <h2>Tot ce ai nevoie ca să livrezi în siguranță</h2>
        <p>Ne ocupăm de administrativ, contracte și suport — tu te concentrezi doar pe livrări.</p>
      </div>
      <div class="grid-4">
        <div class="card reveal"><div class="icon">💳</div><h3>Plăți Săptămânale</h3><p>Banii intră în cont în fiecare săptămână, fără întârzieri.</p></div>
        <div class="card reveal"><div class="icon">📉</div><h3>Comision Minim</h3><p>Cel mai mic comision din piață, ca să rămâi cu mai mult din ce câștigi.</p></div>
        <div class="card reveal"><div class="icon">🎧</div><h3>Suport Complet</h3><p>Echipă dedicată, disponibilă non-stop pentru orice problemă.</p></div>
        <div class="card reveal"><div class="icon">⏱️</div><h3>Program Liber</h3><p>Lucrezi când vrei tu — full-time, part-time sau ocazional.</p></div>
        <div class="card reveal"><div class="icon">🎁</div><h3>Bonusuri</h3><p>Bonusuri de performanță și campanii sezoniere pentru curierii activi.</p></div>
        <div class="card reveal"><div class="icon">🔗</div><h3>Program Referral</h3><p>Recomandă un prieten și primești bonus pentru fiecare curier activat.</p></div>
        <div class="card reveal"><div class="icon">🛡️</div><h3>Asigurare</h3><p>Opțiuni de asigurare medicală și pentru echipament, la preț preferențial.</p></div>
        <div class="card reveal"><div class="icon">🚐</div><h3>Fleet Management</h3><p>Administrare completă a flotei: vehicule, mentenanță și echipament.</p></div>
        <div class="card reveal"><div class="icon">🎓</div><h3>Training</h3><p>Instruire inițială pentru siguranță rutieră și utilizarea aplicațiilor.</p></div>
        <div class="card reveal"><div class="icon">🎒</div><h3>Echipament</h3><p>Genți termice, echipament reflectorizant și accesorii incluse.</p></div>
      </div>
    </div>
  </section>

  <section class="section section-tight" style="background:var(--surface);border-top:1px solid var(--border);border-bottom:1px solid var(--border);">
    <div class="container">
      <div class="section-head center reveal">
        <div class="eyebrow" style="justify-content:center">Proces</div>
        <h2>Cum funcționează</h2>
        <p>De la aplicație la prima livrare, în mai puțin de 48 de ore.</p>
      </div>
      <div class="steps">
        <div class="step reveal"><div class="step-connector"></div><div class="num">1</div><h3>Aplici</h3><p>Completezi formularul online în câteva minute.</p></div>
        <div class="step reveal"><div class="step-connector"></div><div class="num">2</div><h3>Încarci Documente</h3><p>Act de identitate, permis și acte vehicul.</p></div>
        <div class="step reveal"><div class="step-connector"></div><div class="num">3</div><h3>Semnezi Contractul</h3><p>Contract generat automat, semnătură electronică.</p></div>
        <div class="step reveal"><div class="step-connector"></div><div class="num">4</div><h3>Activare Cont</h3><p>Verificăm și activăm contul tău de curier.</p></div>
        <div class="step reveal"><div class="num">5</div><h3>Începi să Livrezi</h3><p>Primești prima comandă și primul bonus.</p></div>
      </div>
    </div>
  </section>

  <section class="section" id="calculator">
    <div class="container">
      <div class="section-head center reveal">
        <div class="eyebrow" style="justify-content:center">Calculator Câștiguri</div>
        <h2>Cât poți câștiga pe lună?</h2>
        <p>Estimare orientativă în funcție de oraș, platformă, vehicul și program.</p>
      </div>
      <div class="calculator reveal">
        <div>
          <div class="calc-field">
            <label>Oraș</label>
            <select id="calc-city">
              <option value="bucuresti">București</option>
              <option value="cluj">Cluj-Napoca</option>
              <option value="timisoara">Timișoara</option>
              <option value="iasi">Iași</option>
              <option value="constanta">Constanța</option>
            </select>
          </div>
          <div class="calc-field">
            <label>Platformă</label>
            <select id="calc-platform">
              <option value="glovo">Glovo</option>
              <option value="bolt">Bolt Food</option>
              <option value="wolt">Wolt</option>
            </select>
          </div>
          <div class="calc-field">
            <label>Vehicul</label>
            <select id="calc-vehicle">
              <option value="bike">Bicicletă</option>
              <option value="scooter" selected>Scuter/Moped</option>
              <option value="car">Mașină</option>
            </select>
          </div>
          <div class="calc-field">
            <label>Ore pe zi: <span id="calc-hours-val">6 h/zi</span></label>
            <input type="range" id="calc-hours" min="2" max="12" value="6">
          </div>
          <div class="calc-field">
            <label>Zile pe săptămână: <span id="calc-days-val">5 zile/săpt.</span></label>
            <input type="range" id="calc-days" min="1" max="7" value="5">
          </div>
        </div>
        <div class="calc-result">
          <div class="eyebrow" style="justify-content:center">Câștig Estimat</div>
          <div class="amount">0 RON</div>
          <div class="range">pe lună</div>
          <a href="devino-curier.html" class="btn btn-primary btn-block" style="margin-top:22px;">Aplică Acum</a>
        </div>
      </div>
    </div>
  </section>

  <section class="section">
    <div class="container">
      <div class="cta-banner reveal">
        <div class="eyebrow" style="justify-content:center">Alătură-te flotei</div>
        <h2>Gata să începi să livrezi?</h2>
        <p>Completează aplicația în 5 minute și primești răspuns în maximum 48 de ore.</p>
        <div class="cta-actions">
          <a href="devino-curier.html" class="btn btn-primary">Aplică Acum</a>
          <a href="contact.html" class="btn btn-ghost">Vorbește cu un consultant</a>
        </div>
      </div>
    </div>
  </section>
</main>
"""

write("index.html", page(
    "index.html",
    "GoDeliver — Devino Curier Glovo, Bolt Food și Wolt în România",
    "Alătură-te flotei GoDeliver și câștigă bani livrând pentru Glovo, Bolt Food și Wolt. Plăți săptămânale, comision minim, suport 24/7, în peste 50 de orașe.",
    home_body
))

# =========================================================
# ABOUT PAGE
# =========================================================
about_body = """
<main>
  <section class="page-hero">
    <div class="container">
      <div class="eyebrow">Despre GoDeliver</div>
      <h1>Construim viitorul livrărilor din România</h1>
      <p>Din 2021, conectăm mii de curieri cu Glovo, Bolt Food și Wolt, oferind infrastructura, contractele și suportul de care au nevoie ca să lucreze corect și în siguranță.</p>
    </div>
  </section>

  <section class="section-tight">
    <div class="container grid-2" style="align-items:center;">
      <div class="reveal">
        <div class="eyebrow">Povestea Noastră</div>
        <h2>De la o idee simplă, la cea mai mare flotă independentă</h2>
        <p>GoDeliver a pornit din nevoia curierilor de a avea un partener administrativ de încredere: contracte corecte, plăți la timp și suport real, nu doar o aplicație.</p>
        <p>Astăzi coordonăm peste 100 de curieri activi în 50+ orașe, cu o echipă dedicată de manageri de flotă și suport tehnic — totul organizat 100% remote.</p>
      </div>
      <div class="card reveal" style="padding:40px;">
        <div class="grid-2" style="gap:28px;">
          <div><b style="font-family:var(--display);font-size:2rem;display:block;color:var(--gold);">2021</b><span style="font-size:13px;color:var(--text-muted);">An înființare</span></div>
          <div><b style="font-family:var(--display);font-size:2rem;display:block;color:var(--gold);">100+</b><span style="font-size:13px;color:var(--text-muted);">Curieri activi</span></div>
          <div><b style="font-family:var(--display);font-size:2rem;display:block;color:var(--gold);">50+</b><span style="font-size:13px;color:var(--text-muted);">Orașe acoperite</span></div>
          <div><b style="font-family:var(--display);font-size:2rem;display:block;color:var(--gold);">3</b><span style="font-size:13px;color:var(--text-muted);">Platforme partenere</span></div>
        </div>
      </div>
    </div>
  </section>

  <section class="section">
    <div class="container">
      <div class="section-head center reveal">
        <div class="eyebrow" style="justify-content:center">Valori</div>
        <h2>Ce ne ghidează în fiecare zi</h2>
      </div>
      <div class="grid-3">
        <div class="card reveal"><div class="icon">🤝</div><h3>Transparență</h3><p>Contracte clare, comisioane afișate deschis, fără costuri ascunse.</p></div>
        <div class="card reveal"><div class="icon">⚡</div><h3>Viteză</h3><p>Activare rapidă și răspunsuri prompte la orice solicitare.</p></div>
        <div class="card reveal"><div class="icon">🛡️</div><h3>Siguranță</h3><p>Training și echipament pentru livrări în siguranță pe orice vreme.</p></div>
      </div>
    </div>
  </section>

  <section class="section section-tight" style="background:var(--surface);border-top:1px solid var(--border);">
    <div class="container">
      <div class="cta-banner reveal">
        <h2>Vrei să faci parte din echipă?</h2>
        <p>Aplică acum ca și curier sau vezi joburile disponibile la birou.</p>
        <div class="cta-actions">
          <a href="devino-curier.html" class="btn btn-primary">Devino Curier</a>
          <a href="cariere.html" class="btn btn-ghost">Vezi Cariere</a>
        </div>
      </div>
    </div>
  </section>
</main>
"""
write("despre-noi.html", page("despre-noi.html", "Despre Noi — GoDeliver",
    "Află povestea GoDeliver: cum conectăm mii de curieri cu Glovo, Bolt Food și Wolt în peste 50 de orașe din România.", about_body))

# =========================================================
# CITIES PAGE
# =========================================================
cities = ["București","Cluj-Napoca","Timișoara","Iași","Constanța","Craiova","Brașov","Galați",
          "Ploiești","Oradea","Brăila","Arad","Pitești","Sibiu","Bacău","Târgu Mureș",
          "Baia Mare","Buzău","Botoșani","Satu Mare","Râmnicu Vâlcea","Drobeta-Turnu Severin",
          "Suceava","Piatra Neamț"]
city_cards = ""
for c in cities:
    city_cards += f'<a href="devino-curier.html" class="city-card reveal"><b>{c}</b><span>Glovo · Bolt Food · Wolt</span></a>\n'

cities_body = f"""
<main>
  <section class="page-hero">
    <div class="container">
      <div class="eyebrow">Acoperire Națională</div>
      <h1>GoDeliver este activ în peste 50 de orașe</h1>
      <p>Indiferent unde locuiești, poți aplica pentru a deveni curier GoDeliver. Iată câteva dintre orașele principale.</p>
    </div>
  </section>
  <section class="section-tight">
    <div class="container">
      <div class="city-grid">
        {city_cards}
      </div>
    </div>
  </section>
  <section class="section">
    <div class="container">
      <div class="cta-banner reveal">
        <h2>Orașul tău nu e în listă?</h2>
        <p>Extindem constant acoperirea. Aplică oricum — te contactăm imediat ce lansăm în zona ta.</p>
        <div class="cta-actions">
          <a href="devino-curier.html" class="btn btn-primary">Aplică Acum</a>
        </div>
      </div>
    </div>
  </section>
</main>
"""
write("orase.html", page("orase.html", "Orașe — GoDeliver",
    "GoDeliver este activ în peste 50 de orașe din România. Vezi acoperirea națională și aplică pentru a deveni curier.", cities_body))

# =========================================================
# FAQ PAGE
# =========================================================
faqs = [
    ("Cum devin curier GoDeliver?", "Completezi formularul de aplicație online din pagina „Devino Curier”, încarci documentele necesare, semnezi contractul electronic și, după validare, contul tău este activat în maximum 48 de ore.", "aplicare"),
    ("Ce documente am nevoie pentru aplicație?", "Ai nevoie de carte de identitate, permis de conducere (dacă livrezi cu scuter sau mașină), talon și asigurare vehicul, IBAN și un selfie cu actul de identitate.", "aplicare"),
    ("Cât timp durează procesul de aplicare?", "În medie, între 24 și 48 de ore de la trimiterea aplicației complete, cu toate documentele încărcate corect.", "aplicare"),
    ("Cât pot câștiga ca și curier GoDeliver?", "Câștigurile depind de oraș, platformă, vehicul și numărul de ore lucrate. Folosește calculatorul de pe pagina principală pentru o estimare personalizată.", "plati"),
    ("Când primesc banii?", "Plățile se fac săptămânal, direct în contul bancar declarat în aplicație. Poți urmări istoricul plăților din portalul de curier.", "plati"),
    ("Ce comision percepe GoDeliver?", "Avem cel mai mic comision din piață — detaliile exacte, în funcție de platformă și tip de contract, se regăsesc în contractul tău.", "plati"),
    ("Pot lucra part-time?", "Da. Programul este complet flexibil — alegi tu câte ore și în ce zile livrezi, fără un minim obligatoriu.", "program"),
    ("Ce tip de contract pot alege?", "Poți opta pentru contract de muncă, colaborare ca PFA, colaborare prin SRL propriu sau contract de colaborare simplă — alegi ce ți se potrivește la pasul 3 din aplicație.", "program"),
    ("Ce vehicul pot folosi?", "Bicicletă, scuter/moped sau mașină — depinde de platformă și oraș. Îți recomandăm opțiunea potrivită în funcție de zona în care livrezi.", "program"),
    ("Ce fac dacă am o problemă în timpul unei livrări?", "Contactezi echipa de suport GoDeliver prin telefon, WhatsApp sau ticket din portalul de curier — suntem disponibili 24/7.", "suport"),
    ("Cum raportez un document expirat?", "Sistemul te notifică automat cu 30 de zile înainte de expirarea unui document. Poți încărca noul document direct din portalul tău.", "suport"),
    ("Cum funcționează programul de recomandare?", "Primești un cod unic de referral. Pentru fiecare prieten pe care îl recomanzi și care devine curier activ, primești un bonus în contul tău.", "suport"),
]
cat_labels = {"all":"Toate", "aplicare":"Aplicare", "plati":"Plăți", "program":"Program", "suport":"Suport"}
faq_tabs = "".join(f'<button class="faq-tab{" active" if k=="all" else ""}" data-cat="{k}">{v}</button>' for k,v in cat_labels.items())
faq_items = ""
for q,a,cat in faqs:
    faq_items += f"""<div class="faq-item" data-cat="{cat}">
      <div class="faq-q"><span>{q}</span><span class="plus">+</span></div>
      <div class="faq-a"><p>{a}</p></div>
    </div>\n"""

faq_body = f"""
<main>
  <section class="page-hero">
    <div class="container">
      <div class="eyebrow">Suntem aici să ajutăm</div>
      <h1>Întrebări frecvente</h1>
      <p>Tot ce trebuie să știi despre aplicare, plăți, program și suport.</p>
    </div>
  </section>
  <section class="section-tight">
    <div class="container">
      <div class="faq-tabs">{faq_tabs}</div>
      <div class="faq-list">
        {faq_items}
      </div>
      <div class="cta-banner reveal" style="margin-top:70px;">
        <h2>Nu ai găsit răspunsul?</h2>
        <p>Echipa noastră de suport îți răspunde în cel mai scurt timp.</p>
        <div class="cta-actions"><a href="contact.html" class="btn btn-primary">Contactează-ne</a></div>
      </div>
    </div>
  </section>
</main>
"""
write("intrebari-frecvente.html", page("intrebari-frecvente.html", "Întrebări Frecvente — GoDeliver",
    "Găsește răspunsuri la cele mai frecvente întrebări despre aplicare, plăți, program și suport la GoDeliver.", faq_body))

# =========================================================
# CONTACT PAGE
# =========================================================
contact_body = """
<main>
  <section class="page-hero">
    <div class="container">
      <div class="eyebrow">Contact</div>
      <h1>Hai să vorbim</h1>
      <p>Suntem disponibili pentru curieri, parteneri și presă. Alege canalul care ți se potrivește.</p>
    </div>
  </section>
  <section class="section-tight">
    <div class="container contact-grid">
      <div class="reveal">
        <div class="contact-info-item">
          <div class="icon">🌍</div>
          <div><h3 style="margin-bottom:4px;font-size:1rem;">Echipă 100% Remote</h3><p style="margin:0;font-size:14px;">Nu avem sediu fizic pentru curieri — toată colaborarea se desfășoară online, oriunde ai fi în România.</p></div>
        </div>
        <div class="contact-info-item">
          <div class="icon">🏢</div>
          <div><h3 style="margin-bottom:4px;font-size:1rem;">Entitate Juridică</h3><p style="margin:0;font-size:14px;">CIORGOVEAN LIVIU FLORIN PFA · CUI 48540021</p></div>
        </div>
        <div class="contact-info-item">
          <div class="icon">📞</div>
          <div><h3 style="margin-bottom:4px;font-size:1rem;">Telefon</h3><p style="margin:0;font-size:14px;">+40 700 000 000</p></div>
        </div>
        <div class="contact-info-item">
          <div class="icon">✉️</div>
          <div><h3 style="margin-bottom:4px;font-size:1rem;">Email</h3><p style="margin:0;font-size:14px;">contact@godeliver.ro</p></div>
        </div>
        <div class="contact-info-item">
          <div class="icon">💬</div>
          <div><h3 style="margin-bottom:4px;font-size:1rem;">WhatsApp</h3><p style="margin:0;font-size:14px;">Răspuns rapid, luni–duminică</p></div>
        </div>
        <div class="contact-info-item" style="border-bottom:none;">
          <div class="icon">🕑</div>
          <div><h3 style="margin-bottom:4px;font-size:1rem;">Program Suport</h3><p style="margin:0;font-size:14px;">24/7 pentru curieri activi</p></div>
        </div>
      </div>
      <div class="reveal">
        <div class="form-card" style="margin-bottom:24px;">
          <form id="contactForm">
            <div class="form-row">
              <div class="form-field"><label>Nume Complet</label><input class="input" name="name" required></div>
              <div class="form-field"><label>Email</label><input class="input" type="email" name="email" required></div>
            </div>
            <div class="form-row">
              <div class="form-field"><label>Telefon</label><input class="input" name="phone"></div>
              <div class="form-field"><label>Subiect</label>
                <select name="subject">
                  <option>Aplicație Curier</option>
                  <option>Suport Tehnic</option>
                  <option>Parteneriat</option>
                  <option>Altele</option>
                </select>
              </div>
            </div>
            <div class="form-field"><label>Mesaj</label><textarea class="input" rows="5" name="message" required></textarea></div>
            <button type="submit" class="btn btn-primary btn-block">Trimite Mesajul</button>
          </form>
        </div>
        <div class="card" style="text-align:center;padding:40px;">
          <div class="icon" style="margin:0 auto 16px;">🛵</div>
          <h3 style="margin-bottom:8px;">Operăm 100% remote, în toată țara</h3>
          <p style="margin:0;font-size:14px;">GoDeliver este administrat de <strong>CIORGOVEAN LIVIU FLORIN PFA</strong> (CUI 48540021). Toate aplicațiile, contractele și suportul se gestionează online — indiferent de orașul în care livrezi.</p>
        </div>
      </div>
    </div>
  </section>
</main>
"""
write("contact.html", page("contact.html", "Contact — GoDeliver",
    "Contactează echipa GoDeliver pentru aplicații, suport tehnic sau parteneriate. Telefon, email, WhatsApp și formular online.", contact_body,
    extra_scripts='<script src="assets/js/contact.js"></script>'))

# =========================================================
# BLOG PAGE
# =========================================================
posts = [
    ("Noutăți", "5 sfaturi ca să câștigi mai mult ca și curier", "Descoperă orele de vârf, zonele cele mai active și cum să îți optimizezi ruta zilnică.", "12 Iul 2026", "6 min citire", "#2a2410"),
    ("Sfaturi", "Cum alegi vehiculul potrivit pentru livrări", "Bicicletă, scuter sau mașină — comparăm costurile și câștigurile pentru fiecare opțiune.", "28 Iun 2026", "5 min citire", "#101820"),
    ("Actualizări", "GoDeliver lansează programul de asigurare pentru curieri", "De acum, curierii activi pot accesa pachete de asigurare medicală la preț preferențial.", "15 Iun 2026", "4 min citire", "#1c1010"),
    ("Sfaturi", "Siguranță rutieră: reguli esențiale pentru curieri", "Ghid practic pentru livrări în siguranță, pe orice vreme și în orice oraș.", "02 Iun 2026", "7 min citire", "#101c14"),
    ("Noutăți", "GoDeliver ajunge la 100 de curieri activi", "O retrospectivă asupra creșterii flotei remote și a planurilor de extindere pentru anul următor.", "20 Mai 2026", "3 min citire", "#1a1420"),
    ("Actualizări", "Plăți mai rapide: ce se schimbă în portalul curierului", "Am optimizat procesul de plată săptămânală — iată ce e nou.", "05 Mai 2026", "4 min citire", "#201810"),
]
blog_cards = ""
for tag, title, excerpt, date, read, color in posts:
    blog_cards += f"""<article class="blog-card reveal">
      <div class="blog-thumb" style="background:linear-gradient(135deg,{color},#0A0A0B);"></div>
      <div class="blog-body">
        <span class="blog-tag">{tag}</span>
        <h3>{title}</h3>
        <p style="font-size:13.5px;">{excerpt}</p>
        <div class="blog-meta">{date} · {read}</div>
      </div>
    </article>\n"""

blog_body = f"""
<main>
  <section class="page-hero">
    <div class="container">
      <div class="eyebrow">Blog GoDeliver</div>
      <h1>Noutăți, sfaturi și povești din flotă</h1>
      <p>Resurse utile pentru curieri și noutăți despre platforma GoDeliver.</p>
    </div>
  </section>
  <section class="section-tight">
    <div class="container">
      <div class="grid-3" id="blogGrid" data-fallback="true">
        {blog_cards}
      </div>
    </div>
  </section>
</main>
"""
write("blog.html", page("blog.html", "Blog — GoDeliver",
    "Noutăți, sfaturi și ghiduri pentru curierii GoDeliver care livrează pentru Glovo, Bolt Food și Wolt.", blog_body,
    extra_scripts='<script src="assets/js/blog.js"></script>'))

# =========================================================
# CAREERS PAGE
# =========================================================
jobs = [
    ("Recrutor Curieri", "Resurse Umane", "Remote", "Full-time"),
    ("Manager Regional Flotă", "Operațiuni", "Remote", "Full-time"),
    ("Specialist Suport Curieri", "Suport Clienți", "Remote", "Full-time"),
    ("HR Business Partner", "Resurse Umane", "Remote", "Full-time"),
    ("Contabil Plăți Curieri", "Financiar", "Remote", "Full-time"),
]
job_cards = ""
for title, dept, city, ftype in jobs:
    job_cards += f"""<div class="job-card reveal">
      <div>
        <h3>{title}</h3>
        <div class="job-meta"><span>🏢 {dept}</span><span>📍 {city}</span><span>🕑 {ftype}</span></div>
      </div>
      <a href="contact.html" class="btn btn-ghost btn-sm">Aplică</a>
    </div>\n"""

careers_body = f"""
<main>
  <section class="page-hero">
    <div class="container">
      <div class="eyebrow">Cariere</div>
      <h1>Construiește viitorul livrărilor alături de noi</h1>
      <p>Căutăm oameni pasionați pentru echipa de birou: recrutare, operațiuni, suport și financiar.</p>
    </div>
  </section>
  <section class="section-tight">
    <div class="container">
      <div class="section-head reveal">
        <h2>Poziții Deschise</h2>
      </div>
      <div id="jobsList" data-fallback="true">
      {job_cards}
      </div>
    </div>
  </section>
  <section class="section">
    <div class="container">
      <div class="section-head center reveal">
        <div class="eyebrow" style="justify-content:center">Beneficii</div>
        <h2>De ce să lucrezi la GoDeliver</h2>
      </div>
      <div class="grid-3">
        <div class="card reveal"><div class="icon">🏡</div><h3>100% Remote</h3><p>Toată echipa de birou lucrează remote — fără deplasări, fără sediu fix.</p></div>
        <div class="card reveal"><div class="icon">📈</div><h3>Creștere Rapidă</h3><p>Suntem o companie în expansiune, cu oportunități reale de avansare.</p></div>
        <div class="card reveal"><div class="icon">🩺</div><h3>Pachet Medical</h3><p>Asigurare medicală privată pentru toți angajații.</p></div>
      </div>
    </div>
  </section>
</main>
"""
write("cariere.html", page("cariere.html", "Cariere — GoDeliver",
    "Alătură-te echipei de birou GoDeliver: recrutare, operațiuni, suport și financiar. Vezi pozițiile deschise.", careers_body,
    extra_scripts='<script src="assets/js/careers.js"></script>'))

# =========================================================
# LEGAL PAGES
# =========================================================
def legal_page(active, title_h1, eyebrow, description, sections):
    body_sections = ""
    for h, content in sections:
        body_sections += f"<h2>{h}</h2>\n{content}\n"
    body = f"""
<main>
  <section class="page-hero" style="padding:150px 0 60px;">
    <div class="container">
      <div class="eyebrow">{eyebrow}</div>
      <h1>{title_h1}</h1>
    </div>
  </section>
  <section class="section-tight">
    <div class="container legal-content reveal">
      <p class="legal-updated">Ultima actualizare: 1 August 2026</p>
      {body_sections}
    </div>
  </section>
</main>
"""
    write(active, page(active, f"{title_h1} — GoDeliver", description, body))

LEGAL_ENTITY_NOTICE = '<p style="background:var(--surface-glass);border:1px solid var(--border);border-radius:var(--radius-sm);padding:16px 18px;font-size:14px;"><strong>Operator:</strong> CIORGOVEAN LIVIU FLORIN PFA · CUI 48540021 · Activitate 100% remote, fără sediu destinat curierilor.</p>'

legal_page("confidentialitate.html", "Politica de Confidențialitate", "Legal",
    "Politica de confidențialitate GoDeliver: ce date colectăm, cum le folosim și ce drepturi ai.",
    [
        ("Operator de date", LEGAL_ENTITY_NOTICE),
        ("1. Ce date colectăm", "<p>Colectăm datele pe care ni le oferi la aplicare: nume, date de contact, CNP, documente de identitate, date despre vehicul și cont bancar, precum și date tehnice despre utilizarea platformei (adresă IP, tip dispozitiv, cookie-uri).</p>"),
        ("2. Cum folosim datele", "<ul><li>Pentru procesarea aplicației și activarea contului de curier</li><li>Pentru generarea contractelor și gestionarea plăților</li><li>Pentru comunicări legate de activitatea de curier (notificări, suport)</li><li>Pentru respectarea obligațiilor legale și fiscale</li></ul>"),
        ("3. Cu cine partajăm datele", "<p>Partajăm date strict necesare cu platformele partenere (Glovo, Bolt Food, Wolt), furnizori de servicii de plată și autorități, atunci când legea o impune. Nu vindem date către terți în scopuri de marketing.</p>"),
        ("4. Securitatea datelor", "<p>Folosim criptare, control al accesului bazat pe roluri și audit al activității pentru a proteja datele tale personale.</p>"),
        ("5. Drepturile tale", "<ul><li>Dreptul de acces la datele tale</li><li>Dreptul de rectificare</li><li>Dreptul de ștergere („dreptul de a fi uitat”)</li><li>Dreptul la portabilitatea datelor</li><li>Dreptul de a te opune prelucrării</li></ul><p>Pentru exercitarea acestor drepturi, contactează-ne la contact@godeliver.ro.</p>"),
    ])

legal_page("termeni.html", "Termeni și Condiții", "Legal",
    "Termenii și condițiile de utilizare a platformei GoDeliver pentru curieri și vizitatori.",
    [
        ("Operator", LEGAL_ENTITY_NOTICE),
        ("1. Acceptarea termenilor", "<p>Prin utilizarea platformei GoDeliver și prin depunerea unei aplicații, confirmi că ai citit, înțeles și acceptat acești termeni și condiții.</p>"),
        ("2. Eligibilitate", "<p>Pentru a deveni curier GoDeliver trebuie să ai minimum 18 ani, documente de identitate valide și, după caz, permis de conducere și documente ale vehiculului în regulă.</p>"),
        ("3. Contractul de colaborare", "<p>Relația dintre curier și GoDeliver este reglementată printr-un contract distinct (muncă, PFA, SRL sau colaborare), generat și semnat electronic în procesul de aplicare.</p>"),
        ("4. Obligațiile curierului", "<ul><li>Respectarea regulilor de circulație și siguranță rutieră</li><li>Menținerea documentelor actualizate</li><li>Comportament profesional față de clienți și restaurante partenere</li></ul>"),
        ("5. Plăți și comisioane", "<p>Plățile se efectuează săptămânal, conform structurii de comision specificate în contractul individual.</p>"),
        ("6. Încetarea colaborării", "<p>Oricare parte poate înceta colaborarea cu o notificare prealabilă, conform termenilor specificați în contract.</p>"),
        ("7. Limitarea răspunderii", "<p>GoDeliver acționează ca intermediar administrativ între curieri și platformele de livrare. Răspunderea pentru livrarea efectivă revine curierului, conform contractului semnat.</p>"),
    ])

legal_page("gdpr.html", "Conformitate GDPR", "Legal · Protecția Datelor",
    "Cum respectă GoDeliver Regulamentul General privind Protecția Datelor (GDPR) în relația cu curierii și vizitatorii.",
    [
        ("Operator de date", LEGAL_ENTITY_NOTICE),
        ("1. Angajamentul nostru", "<p>GoDeliver respectă Regulamentul (UE) 2016/679 (GDPR) în toate procesele de colectare, stocare și prelucrare a datelor cu caracter personal.</p>"),
        ("2. Temeiul legal al prelucrării", "<ul><li>Executarea contractului de colaborare cu curierul</li><li>Consimțământul explicit, acolo unde este necesar (ex. cookie-uri neesențiale)</li><li>Obligații legale (fiscale, contabile)</li><li>Interesul legitim (ex. prevenirea fraudei)</li></ul>"),
        ("3. Perioada de stocare", "<p>Datele sunt păstrate pe durata colaborării și ulterior, pentru perioada impusă de legislația fiscală și contabilă din România.</p>"),
        ("4. Responsabilul cu protecția datelor", "<p>Pentru orice solicitare legată de protecția datelor, ne poți contacta la dpo@godeliver.ro.</p>"),
        ("5. Dreptul de a depune plângere", "<p>Ai dreptul de a depune o plângere la Autoritatea Națională de Supraveghere a Prelucrării Datelor cu Caracter Personal (ANSPDCP), dacă consideri că drepturile tale au fost încălcate.</p>"),
    ])

legal_page("cookies.html", "Politica de Cookies", "Legal",
    "Află ce cookie-uri folosește GoDeliver, la ce sunt folosite și cum îți poți gestiona preferințele.",
    [
        ("1. Ce sunt cookie-urile", "<p>Cookie-urile sunt fișiere text mici, stocate în browserul tău, care ajută platforma să funcționeze corect și să îți ofere o experiență personalizată.</p>"),
        ("2. Tipuri de cookie-uri folosite", "<ul><li><strong>Esențiale</strong> — necesare pentru funcționarea site-ului (ex. sesiune, preferințe limbă)</li><li><strong>Analitice</strong> — ne ajută să înțelegem cum este folosit site-ul</li><li><strong>Funcționale</strong> — memorează preferințele tale (ex. tema afișată)</li></ul>"),
        ("3. Gestionarea preferințelor", "<p>Poți accepta sau refuza cookie-urile neesențiale din bannerul afișat la prima vizită, sau din setările browserului tău în orice moment.</p>"),
        ("4. Cookie-uri terțe", "<p>Unele funcționalități (ex. harta Google Maps) pot seta cookie-uri proprii, gestionate conform politicilor furnizorilor respectivi.</p>"),
    ])

# =========================================================
# BECOME A COURIER — MULTI-STEP APPLICATION
# =========================================================
apply_body = """
<main>
  <section class="page-hero" style="padding:150px 0 50px;">
    <div class="container">
      <div class="eyebrow">Aplicație Curier</div>
      <h1>Devino Curier GoDeliver</h1>
      <p>Completează cei 5 pași de mai jos. Durează aproximativ 5 minute.</p>
    </div>
  </section>

  <section class="section-tight">
    <div class="container form-shell">

      <div class="progress-track" id="progressTrack">
        <div class="progress-step active" data-step="1"><div class="circle">1</div><div class="label">Personal</div></div>
        <div class="progress-step" data-step="2"><div class="circle">2</div><div class="label">Muncă</div></div>
        <div class="progress-step" data-step="3"><div class="circle">3</div><div class="label">Contract</div></div>
        <div class="progress-step" data-step="4"><div class="circle">4</div><div class="label">Documente</div></div>
        <div class="progress-step" data-step="5"><div class="circle">5</div><div class="label">Verificare</div></div>
      </div>

      <div class="form-card">
        <form id="applicationForm" novalidate>

          <!-- STEP 1 — PERSONAL INFO -->
          <div class="form-panel active" data-panel="1">
            <h3>Informații Personale</h3>
            <div class="form-row">
              <div class="form-field"><label>Prenume *</label><input class="input" name="firstName" required></div>
              <div class="form-field"><label>Nume *</label><input class="input" name="lastName" required></div>
            </div>
            <div class="form-row">
              <div class="form-field"><label>Telefon *</label><input class="input" type="tel" name="phone" placeholder="07xx xxx xxx" required></div>
              <div class="form-field"><label>Email *</label><input class="input" type="email" name="email" required></div>
            </div>
            <div class="form-row">
              <div class="form-field"><label>Data Nașterii *</label><input class="input" type="date" name="birthDate" required></div>
              <div class="form-field"><label>CNP *</label><input class="input" name="cnp" maxlength="13" placeholder="13 cifre" required></div>
            </div>
            <div class="form-field"><label>Adresă *</label><input class="input" name="address" required></div>
            <div class="form-row">
              <div class="form-field"><label>Contact de Urgență — Nume *</label><input class="input" name="emergencyName" required></div>
              <div class="form-field"><label>Contact de Urgență — Telefon *</label><input class="input" type="tel" name="emergencyPhone" required></div>
            </div>
            <div class="form-nav">
              <span></span>
              <button type="button" class="btn btn-primary" data-next>Continuă →</button>
            </div>
          </div>

          <!-- STEP 2 — WORK INFO -->
          <div class="form-panel" data-panel="2">
            <h3>Informații de Muncă</h3>
            <div class="form-row">
              <div class="form-field"><label>Oraș *</label>
                <select name="city" required>
                  <option value="">Alege orașul</option>
                  <option>București</option><option>Cluj-Napoca</option><option>Timișoara</option>
                  <option>Iași</option><option>Constanța</option><option>Craiova</option>
                  <option>Brașov</option><option>Alt oraș</option>
                </select>
              </div>
              <div class="form-field"><label>Experiență Anterioară</label>
                <select name="experience">
                  <option value="none">Fără experiență</option>
                  <option value="lt1">Sub 1 an</option>
                  <option value="1-3">1–3 ani</option>
                  <option value="3+">Peste 3 ani</option>
                </select>
              </div>
            </div>
            <div class="form-field">
              <label>Platformă Preferată *</label>
              <div class="choice-grid" data-choice-group="platform">
                <label class="choice-card"><input type="radio" name="platform" value="Glovo" required><span>Glovo</span></label>
                <label class="choice-card"><input type="radio" name="platform" value="Bolt Food"><span>Bolt Food</span></label>
                <label class="choice-card"><input type="radio" name="platform" value="Wolt"><span>Wolt</span></label>
                <label class="choice-card"><input type="radio" name="platform" value="Toate"><span>Toate</span></label>
              </div>
            </div>
            <div class="form-field">
              <label>Vehicul *</label>
              <div class="choice-grid" data-choice-group="vehicle">
                <label class="choice-card"><input type="radio" name="vehicle" value="Bicicletă" required><span>🚲 Bicicletă</span></label>
                <label class="choice-card"><input type="radio" name="vehicle" value="Scuter"><span>🛵 Scuter</span></label>
                <label class="choice-card"><input type="radio" name="vehicle" value="Mașină"><span>🚗 Mașină</span></label>
              </div>
            </div>
            <div class="form-field">
              <label>Disponibilitate *</label>
              <div class="choice-grid" data-choice-group="availability">
                <label class="choice-card"><input type="radio" name="availability" value="Full-time" required><span>Full-time</span></label>
                <label class="choice-card"><input type="radio" name="availability" value="Part-time"><span>Part-time</span></label>
                <label class="choice-card"><input type="radio" name="availability" value="Weekend"><span>Weekend</span></label>
              </div>
            </div>
            <div class="form-field"><label>Limbi Vorbite</label><input class="input" name="languages" placeholder="Română, Engleză..."></div>
            <div class="form-nav">
              <button type="button" class="btn btn-ghost" data-prev>← Înapoi</button>
              <button type="button" class="btn btn-primary" data-next>Continuă →</button>
            </div>
          </div>

          <!-- STEP 3 — CONTRACT TYPE -->
          <div class="form-panel" data-panel="3">
            <h3>Tip de Contract</h3>
            <p style="margin-bottom:22px;">Alege forma de colaborare care ți se potrivește. Poți schimba tipul de contract ulterior, discutând cu echipa GoDeliver.</p>
            <div class="form-field">
              <div class="choice-grid" style="grid-template-columns:repeat(2,1fr);" data-choice-group="contract">
                <label class="choice-card" style="text-align:left;padding:20px;"><input type="radio" name="contractType" value="Angajare" required><strong>Angajare</strong><p style="margin:6px 0 0;font-size:12.5px;">Contract individual de muncă, cu toate beneficiile aferente.</p></label>
                <label class="choice-card" style="text-align:left;padding:20px;"><input type="radio" name="contractType" value="PFA"><strong>PFA</strong><p style="margin:6px 0 0;font-size:12.5px;">Colaborare ca persoană fizică autorizată.</p></label>
                <label class="choice-card" style="text-align:left;padding:20px;"><input type="radio" name="contractType" value="SRL"><strong>SRL</strong><p style="margin:6px 0 0;font-size:12.5px;">Colaborare prin firma ta (SRL propriu).</p></label>
                <label class="choice-card" style="text-align:left;padding:20px;"><input type="radio" name="contractType" value="Colaborare"><strong>Colaborare</strong><p style="margin:6px 0 0;font-size:12.5px;">Contract de colaborare simplă, flexibil.</p></label>
              </div>
            </div>
            <div class="form-nav">
              <button type="button" class="btn btn-ghost" data-prev>← Înapoi</button>
              <button type="button" class="btn btn-primary" data-next>Continuă →</button>
            </div>
          </div>

          <!-- STEP 4 — UPLOADS -->
          <div class="form-panel" data-panel="4">
            <h3>Documente</h3>
            <p style="margin-bottom:22px;">Acceptăm fișiere JPG, PNG sau PDF, sub 10MB fiecare.</p>
            <div class="upload-grid">
              <div class="form-field">
                <label>Carte de Identitate *</label>
                <div class="upload-box"><input type="file" name="docId" accept=".jpg,.jpeg,.png,.pdf" required><span>📄 Trage fișierul aici sau click pentru upload</span><div class="name"></div></div>
              </div>
              <div class="form-field">
                <label>Permis de Conducere</label>
                <div class="upload-box"><input type="file" name="docLicense" accept=".jpg,.jpeg,.png,.pdf"><span>📄 Trage fișierul aici sau click pentru upload</span><div class="name"></div></div>
              </div>
              <div class="form-field">
                <label>Talon Vehicul</label>
                <div class="upload-box"><input type="file" name="docRegistration" accept=".jpg,.jpeg,.png,.pdf"><span>📄 Trage fișierul aici sau click pentru upload</span><div class="name"></div></div>
              </div>
              <div class="form-field">
                <label>Asigurare (RCA)</label>
                <div class="upload-box"><input type="file" name="docInsurance" accept=".jpg,.jpeg,.png,.pdf"><span>📄 Trage fișierul aici sau click pentru upload</span><div class="name"></div></div>
              </div>
              <div class="form-field">
                <label>Dovadă IBAN *</label>
                <div class="upload-box"><input type="file" name="docIban" accept=".jpg,.jpeg,.png,.pdf" required><span>📄 Trage fișierul aici sau click pentru upload</span><div class="name"></div></div>
              </div>
              <div class="form-field">
                <label>Selfie cu Actul de Identitate *</label>
                <div class="upload-box"><input type="file" name="docSelfie" accept=".jpg,.jpeg,.png" required><span>🤳 Trage fișierul aici sau click pentru upload</span><div class="name"></div></div>
              </div>
            </div>
            <div class="form-field">
              <label>CV (opțional)</label>
              <div class="upload-box"><input type="file" name="docCv" accept=".pdf,.doc,.docx"><span>📄 Trage fișierul aici sau click pentru upload</span><div class="name"></div></div>
            </div>
            <div class="form-nav">
              <button type="button" class="btn btn-ghost" data-prev>← Înapoi</button>
              <button type="button" class="btn btn-primary" data-next>Continuă →</button>
            </div>
          </div>

          <!-- STEP 5 — REVIEW & SIGNATURE -->
          <div class="form-panel" data-panel="5">
            <h3>Verificare și Semnătură</h3>
            <div id="reviewContainer"></div>

            <div class="form-field">
              <label>Semnătură Digitală *</label>
              <canvas class="signature-pad" id="signaturePad" width="700" height="180"></canvas>
              <div style="display:flex;justify-content:space-between;align-items:center;margin-top:8px;">
                <span class="hint">Semnează cu mouse-ul sau degetul (pe mobil)</span>
                <button type="button" class="btn btn-ghost btn-sm" id="clearSignature">Șterge</button>
              </div>
            </div>

            <div class="checkbox-row" style="margin-top:20px;">
              <input type="checkbox" id="acceptTerms" required>
              <label for="acceptTerms">Am citit și sunt de acord cu <a href="termeni.html" style="color:var(--gold)">Termenii și Condițiile</a> și <a href="confidentialitate.html" style="color:var(--gold)">Politica de Confidențialitate</a>. *</label>
            </div>

            <div class="form-nav">
              <button type="button" class="btn btn-ghost" data-prev>← Înapoi</button>
              <button type="submit" class="btn btn-primary" id="submitBtn">Trimite Aplicația</button>
            </div>
          </div>

          <!-- SUCCESS STATE -->
          <div class="form-panel" data-panel="success">
            <div class="form-success">
              <div class="check">✓</div>
              <h3>Aplicația a fost trimisă cu succes!</h3>
              <p>Îți vom trimite un email de confirmare și te vom contacta în maximum 48 de ore.</p>
              <span class="app-id" id="appIdDisplay">GD-000000</span>
              <div style="margin-top:30px;">
                <a href="index.html" class="btn btn-ghost">Înapoi Acasă</a>
              </div>
            </div>
          </div>

        </form>
      </div>
    </div>
  </section>
</main>
"""
write("devino-curier.html", page("devino-curier.html", "Devino Curier — Aplicație GoDeliver",
    "Aplică acum pentru a deveni curier GoDeliver și livrează pentru Glovo, Bolt Food și Wolt. Formular rapid în 5 pași.",
    apply_body, extra_scripts='<script src="assets/js/application-form.js"></script>'))
