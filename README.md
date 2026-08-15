# GoDeliver — Platformă Curieri (Glovo · Bolt Food · Wolt)

Platformă completă front-end (HTML/CSS/JS) + back-end (PHP/MySQL) pentru
GoDeliver: site public, panou de administrare și portal pentru curieri.
Operator: **CIORGOVEAN LIVIU FLORIN PFA · CUI 48540021** · activitate 100% remote.

## Structură

```
/
├── index.html, devino-curier.html, despre-noi.html, orase.html,
│   intrebari-frecvente.html, contact.html, blog.html, cariere.html,
│   confidentialitate.html, termeni.html, gdpr.html, cookies.html
│
├── assets/
│   ├── css/style.css              Design system complet (tokens + componente)
│   ├── js/main.js                 Comportament comun (meniu, preloader, calculator, FAQ)
│   ├── js/application-form.js     Formular aplicație curier (5 pași + semnătură)
│   ├── js/contact.js              Formular de contact
│   ├── js/blog.js, js/careers.js  Randare dinamică blog/joburi din baza de date
│   └── images/                    Logo GoDeliver
│
├── backend/
│   ├── config.php                 Configurare DB / email / WhatsApp
│   ├── submit_application.php     Endpoint aplicații curieri
│   ├── submit_contact.php         Endpoint formular contact
│   ├── api_blog.php               Feed public JSON — articole publicate
│   ├── api_jobs.php                Feed public JSON — joburi active
│   ├── create_admin.php           Script CLI — creează/resetează un cont admin
│   ├── lib/simple_pdf.php         Generator PDF propriu, fără dependențe externe
│   └── uploads/                   Fișiere încărcate (protejat, .htaccess)
│
├── admin/                         Panou de administrare (vezi mai jos)
├── portal/                        Portal Curier (vezi mai jos)
│
├── database/
│   ├── schema.sql                 Schema principală MySQL
│   └── migration_2_portal.sql     Tabele suplimentare (portal, concedii, echipament)
│
├── robots.txt, sitemap.xml
└── build.py                       Script Python folosit DOAR de mine ca generator de
                                     pagini (header/footer comune) — NU e necesar pe
                                     server, nu trebuie urcat sau rulat de tine.
```

## Instalare pe hosting (cPanel / VPS cu Apache + PHP 8.1+ + MySQL)

1. **Urcă toate fișierele** din acest folder în `public_html` (sau rădăcina domeniului).
2. **Baza de date**: folosește baza deja creată de hosting-ul tău (ex: `godelive_adm`).
   Nu rula `CREATE DATABASE` din schema — majoritatea găzduirilor shared nu permit asta
   contului tău de DB; selectează baza existentă și importă direct în ea.
3. **Importă schema**, în ordine, prin phpMyAdmin (Import) sau linie de comandă:
   ```
   mysql -u USER -p NUME_BAZA_TA < database/schema.sql
   mysql -u USER -p NUME_BAZA_TA < database/migration_2_portal.sql
   ```
4. **Configurează conexiunea** în `backend/config.php` — sunt deja completate cu
   `godelive_adm` / `godelive_adm` / host `localhost`. Dacă hosting-ul tău folosește
   alt host de MySQL (verifică în cPanel → MySQL Databases), schimbă `DB_HOST`.
   Alternativ, poți seta variabilele de mediu `GODELIVER_DB_HOST`, `GODELIVER_DB_NAME`,
   `GODELIVER_DB_USER`, `GODELIVER_DB_PASS` direct din panoul de hosting, fără să
   editezi codul.
5. **Permisiuni**: `backend/uploads/` (și subfolderele lui) trebuie să fie writable (755/775).
6. **Creează primul cont de admin** din SSH:
   ```
   php backend/create_admin.php "Nume Complet" email@godeliver.ro ParolaSigura123 super_admin
   ```
7. Testează: aplică pe `/devino-curier.html`, aprobă din `/admin/`, autentifică-te în
   `/portal/` cu contul de curier generat.

## Panoul de Admin — `/admin/`

- **Dashboard** — statistici rapide (aplicații noi, curieri activi, articole, joburi)
- **Aplicații** — listă filtrabilă, detalii complete + documente + semnătură,
  **aprobare cu un click → creează automat cont de curier** (parolă temporară afișată
  o singură dată; comunic-o curierului printr-un canal sigur, nu se trimite automat)
- **Curieri** — CRUD complet, schimbare status (activ/suspendat/concediu/încetat)
- **Plăți** — adaugi plăți săptămânale (brut/comision/bonus → net automat), marchezi
  ca plătite, export CSV
- **Contracte** — generare PDF automată per curier, cu semnătura din aplicație inclusă
- **Tickete Suport** — vezi și rezolvi ticketele trimise de curieri din portal
- **Blog** — creezi/editezi/publici articole → apar live pe `/blog.html`
- **Cariere** — adaugi/dezactivezi joburi → apar live pe `/cariere.html`
- **Rapoarte** — distribuție curieri (oraș/platformă), aplicații pe status, plăți
  lunare, exporturi CSV
- **Setări** — utilizatori admin + roluri (`super_admin`, `manager`, `recruiter`,
  `support`, `finance` — doar `super_admin` gestionează alți admini) + jurnal de
  activitate (cine a făcut ce, când)

Protecție: token CSRF pe toate formularele, parole hash-uite cu `password_hash()`
(bcrypt), niciodată în clar.

## Portalul Curierului — `/portal/`

Fiecare curier se autentifică cu email + parola primită la aprobarea aplicației
(din admin) și poate: vedea dashboard-ul (încasări, sume în așteptare, recomandări,
tickete), edita telefon/oraș/IBAN, schimba parola, încărca documente noi, vedea
istoricul de plăți, descărca contractele semnate (PDF), vedea codul de recomandare
și trimite recomandări, cere concediu/echipament, deschide tickete de suport (apar
direct în admin).

## Generarea Contractelor PDF

`backend/lib/simple_pdf.php` e un generator PDF scris de la zero — **fără nicio
librărie externă, fără Composer** — ca să funcționeze pe orice hosting shared cu
PHP simplu. Include automat datele companiei, ale curierului, și semnătura digitală
capturată la aplicare (embedată ca JPEG — necesită extensia `gd`, prezentă pe
majoritatea găzduirilor).

**Notă tehnică**: fonturile standard PDF nu suportă diacriticele românești, așa că
textul din contract e convertit automat fără diacritice (ex: "ÎNTRE" → "INTRE") —
garantează afișare corectă în orice cititor PDF, fără complexitatea unui font Unicode
embedat.

## Email și WhatsApp

`mail()` din PHP e doar un fallback de test — multe găzduiri îl blochează sau îl
trimit la Spam. Pentru producție, înlocuiește blocul de trimitere din
`backend/submit_application.php` / `submit_contact.php` cu SMTP real
([PHPMailer](https://github.com/PHPMailer/PHPMailer)) sau un API (Resend/SendGrid).

Pentru WhatsApp, `sendWhatsAppNotification()` din `submit_application.php` e gata
pregătită pentru WhatsApp Business Cloud API — se activează automat dacă completezi
`GODELIVER_WA_TOKEN` și `GODELIVER_WA_PHONE_ID`; altfel rămâne inactivă, fără erori.

## Ce ține de business, nu de cod

Câteva lucruri intenționat lăsate simple, care depind de decizii ale tale, nu de
capabilități tehnice:
- **Aprobarea concediilor/echipamentului** cerute din portal se salvează în baza de
  date (`vacation_requests`, `equipment_requests`), dar procesarea e manuală
  (direct în DB sau printr-un mic panou admin, dacă vrei să-l adaug)
- **Traducerea în engleză** — comutatorul RO/EN e pregătit vizual, conținutul e
  doar în română (dublarea conținutului e un proiect separat, la cerere)
- **SMS-uri reale** — nu există provider conectat; structura de notificări e gata,
  doar cheile API lipsesc

