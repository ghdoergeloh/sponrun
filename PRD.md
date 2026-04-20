# Product Requirements Document – SponRun (Neuentwicklung)

**Version:** 1.0  
**Datum:** 2026-04-20  
**Basis:** Analyse der bestehenden Laravel-5.5-Anwendung  
**Zielstack:** Laravel (Backend/API) + React (Frontend)

---

## 1. Projektziel

SponRun ist eine Webanwendung zur digitalen Verwaltung von **Sponsorenläufen**. Zielgruppe sind NGOs, Vereine und andere Organisationen, die Spendenläufe veranstalten und bisher die Daten ihrer Läufer und Sponsoren manuell von Laufzetteln abtippen müssen.

Die Anwendung ermöglicht:
- Läufern, sich online für einen Lauf anzumelden und ihre Sponsoren digital zu erfassen
- Sponsoren, ihre Zusage direkt über einen geteilten Link einzutragen
- Administratoren, alle Daten einzusehen, zu pflegen und als Excel/CSV zu exportieren

---

## 2. Rollen & Berechtigungen

Die Anwendung kennt drei Rollen:

| Rolle | Beschreibung |
|---|---|
| **Admin** | Verwaltet Sponsorenläufe, Projekte, Projektlisten; sieht und bearbeitet alle Teilnehmer und Sponsoren; exportiert Auswertungen |
| **Läufer** | Registriert sich selbst; tritt einem Lauf bei; pflegt seine eigenen Sponsoren; generiert und teilt seinen persönlichen Sponsoren-Link |
| **Sponsor (Gast)** | Kein Account erforderlich; trägt seine Zusage über den geteilten Link des Läufers ein |

### 2.1 Berechtigungsmatrix

| Funktion | Admin | Läufer | Sponsor (Gast) |
|---|:---:|:---:|:---:|
| Sponsorenlauf erstellen/bearbeiten/löschen | ✓ | – | – |
| Sponsorenlauf schließen/öffnen | ✓ | – | – |
| Alle Teilnahmen einsehen | ✓ | – | – |
| Teilnahme-Rundenzahl eintragen (alle) | ✓ | – | – |
| Alle Sponsoren einsehen/bearbeiten | ✓ | – | – |
| Auswertung exportieren (Excel/CSV) | ✓ | – | – |
| Projekte & Projektlisten verwalten | ✓ | – | – |
| Eigenen Account anlegen | ✓ | ✓ | – |
| Einem Lauf beitreten | ✓ | ✓ | – |
| Eigene Sponsoren eintragen | ✓ | ✓ | – |
| Eigene Rundenzahl eintragen | ✓ | ✓ | – |
| Eigenen Sponsoren-Link teilen | – | ✓ | – |
| Sponsorzusage über Link eintragen | – | – | ✓ |

---

## 3. Fachliche Anforderungen

### 3.1 Benutzerverwaltung

#### 3.1.1 Registrierung (Läufer)
- Registrierungsformular mit Pflichtfeldern:
  - Vorname, Nachname
  - E-Mail-Adresse (eindeutig)
  - Passwort (mit Bestätigung)
  - Geburtsdatum
  - Straße, Hausnummer, PLZ (5-stellig), Ort
  - Geschlecht (m/w)
  - Telefon (optional)
  - Newsletter-Opt-in (optional, konfigurierbar ob Pflichtfeld)
- Nach Registrierung: E-Mail-Bestätigung mit Verifikationslink
- Konto wird erst nach Bestätigung aktiviert

#### 3.1.2 Login / Logout
- E-Mail + Passwort
- "Angemeldet bleiben"-Option (Remember Token)
- Passwort vergessen / Passwort zurücksetzen per E-Mail

#### 3.1.3 Kontopflege
- Eigene Profildaten bearbeiten (alle Felder außer E-Mail)
- Passwort ändern

#### 3.1.4 Admin-Verwaltung
- Erster Nutzer (Seed) erhält automatisch die Admin-Rolle
- Rollenverwaltung über Datenbank (Entrust-kompatibles System)

---

### 3.2 Sponsorenläufe

#### 3.2.1 Erstellen (Admin)
Pflichtfelder:
- Name des Laufs
- Beginn (Datum + Uhrzeit)

Optionale Felder:
- Ende (Datum + Uhrzeit)
- Veranstaltungsort: Straße, Hausnummer, PLZ, Ort
- Beschreibung (Freitext)
- T-Shirt-Bestellung aktivieren (ja/nein)
- Projektlisten zuweisen (eine oder mehrere)

#### 3.2.2 Bearbeiten (Admin)
- Alle Felder nachträglich editierbar
- Projektlisten hinzufügen/entfernen
- Lauf schließen: Verhindert neue Teilnahmen und neue Sponsoreintragungen
- Lauf wieder öffnen möglich

#### 3.2.3 Anzeige / Dashboard
- Öffentliche Übersicht offener Läufe für eingeloggte Nutzer
- Detailseite eines Laufs mit Beschreibung, Datum, Ort

---

### 3.3 Teilnahme (Läufer)

#### 3.3.1 Einem Lauf beitreten
- Läufer wählt einen offenen Lauf aus
- Optional: Projekt auswählen (aus der dem Lauf zugewiesenen Projektliste)
- Optional: T-Shirt-Größe wählen (XS / S / M / L / XL / XXL), wenn Lauf das anbietet
- Eine Teilnahme pro Läufer und Lauf

#### 3.3.2 Rundenzahl eintragen
- Läufer kann nach dem Lauf seine gelaufenen Runden eintragen
- Admin kann die Rundenzahl für beliebige Teilnehmer eintragen/korrigieren

#### 3.3.3 Spendensumme berechnen
- Das System berechnet automatisch die zu leistende Spendensumme aller Sponsoren basierend auf den gelaufenen Runden

#### 3.3.4 Sponsoren-Link teilen
- Jede Teilnahme hat einen eindeutigen Hash-basierten Link
- Läufer kann diesen Link kopieren und z. B. per WhatsApp teilen
- Über den Link können Sponsoren ihre Zusage eintragen (ohne Account)

---

### 3.4 Sponsoren

#### 3.4.1 Sponsor-Eintragung durch den Läufer
- Läufer kann Sponsoren direkt in seinem Konto eintragen
- Felder (Sponsor):
  - Vorname, Nachname (Pflicht)
  - E-Mail (optional)
  - Telefon (optional)
  - Straße, Hausnummer, PLZ, Ort (Pflicht)
  - Externe Personalnummer (optional, für Organisationen mit Mitarbeiternummern)
  - Betrag pro Runde (decimal, Pflicht)
  - Maximalbetrag / Fixbetrag (decimal, optional – bei 0 kein Maximum)
  - Newsletter-Opt-in (optional)

**Spendenlogik:**
- `Spendensumme = MIN(laps × donation_per_lap, donation_static_max)` wenn `donation_static_max > 0`
- `Spendensumme = laps × donation_per_lap` wenn `donation_static_max = 0`

#### 3.4.2 Sponsor-Eintragung über geteilten Link (Gast)
- Kein Login erforderlich
- Formular mit denselben Feldern wie 3.4.1
- Nach dem Speichern: Bestätigungsseite
- Optional: E-Mail-Benachrichtigung an den Läufer, dass ein neuer Sponsor eingetragen wurde

#### 3.4.3 Sponsor-Verwaltung durch Admin
- Alle Sponsoren eines Laufs einsehen
- Einzelne Sponsoren bearbeiten oder löschen
- Sponsoren für beliebige Teilnahmen manuell eintragen

---

### 3.5 Projekte & Projektlisten

#### 3.5.1 Projekte (Admin)
- Projekte haben eine manuelle ID (keine Auto-Increment), einen Namen und einen Scope (`person` oder `project`)
- Projekte können erstellt, bearbeitet und gelöscht werden

#### 3.5.2 Projektlisten (Admin)
- Eine Projektliste ist eine benannte Sammlung von Projekten
- Projektlisten können erstellt, bearbeitet und gelöscht werden
- Projekte können einer Projektliste hinzugefügt oder entfernt werden
- Eine Projektliste kann einem oder mehreren Sponsorenläufen zugewiesen werden
- Läufer sehen beim Beitritt zu einem Lauf die Projekte der zugewiesenen Projektlisten

---

### 3.6 Auswertung & Export (Admin)

#### 3.6.1 Excel-Export
- Pro Sponsorenlauf exportierbar
- Dateiname: `Auswertung {Laufname}.xlsx`
- Spalten im Export:
  - **Läufer:** ID, Projekt, T-Shirt-Größe, Vorname, Nachname, Straße, Hausnummer, PLZ, Ort, E-Mail, Telefon, Newsletter-Opt-in
  - **Sponsor:** ID, Vorname, Nachname, Straße, Hausnummer, PLZ, Ort, E-Mail, Telefon, Newsletter-Opt-in
  - **Spenddaten:** Betrag/Runde, Maximalbetrag, gelaufene Runden, Spendensumme
  - **Zahlungserfassung:** Eingangsdatum, Eingangsbetrag (für spätere Nutzung)

#### 3.6.2 Statistik-Übersicht (Admin, in der App)
- Läufer mit den meisten Runden
- Läufer mit den meisten Sponsoren
- Läufer mit der höchsten Spendensumme
- Ältester / jüngster Läufer
- Gesamtzahl Runden, Gesamtspendensumme

---

### 3.7 E-Mail-Benachrichtigungen

| Trigger | Empfänger | Inhalt |
|---|---|---|
| Registrierung | Läufer | Bestätigungslink zur E-Mail-Verifizierung |
| Passwort vergessen | Läufer | Reset-Link |
| Neuer Sponsor eingetragen (über Link) | Läufer | Name des Sponsors, Zusagebetrag |

---

## 4. Datenbankschema

Das Schema der Bestandsanwendung wird vollständig übernommen, um eine Migration zu ermöglichen.

### 4.1 Tabelle `users`

```sql
CREATE TABLE users (
    id               INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    ext_personnel_no INT NULL,
    firstname        VARCHAR(255) NOT NULL,
    lastname         VARCHAR(255) NOT NULL,
    email            VARCHAR(255) NOT NULL UNIQUE,
    phone            VARCHAR(255) NULL,
    birthday         DATE NOT NULL,
    street           VARCHAR(255) NOT NULL,
    housenumber      VARCHAR(31) NOT NULL,
    postcode         CHAR(5) NOT NULL,
    city             VARCHAR(255) NOT NULL,
    gender           ENUM('m', 'f') NOT NULL,
    password         VARCHAR(255) NOT NULL,
    confirmed        TINYINT(1) NOT NULL DEFAULT 0,
    confirmation_code VARCHAR(255) NULL,
    wants_newsletter TINYINT(1) NULL,
    remember_token   VARCHAR(100) NULL,
    created_at       TIMESTAMP NULL,
    updated_at       TIMESTAMP NULL
);
```

### 4.2 Tabelle `sponsored_runs`

```sql
CREATE TABLE sponsored_runs (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(255) NOT NULL,
    begin       DATETIME NOT NULL,
    end         DATETIME NULL,
    closed      TINYINT(1) NOT NULL DEFAULT 0,
    with_tshirt TINYINT(1) NOT NULL DEFAULT 0,
    street      VARCHAR(255) NULL,
    housenumber VARCHAR(255) NULL,
    postcode    VARCHAR(255) NULL,
    city        VARCHAR(255) NULL,
    description TEXT NULL,
    created_at  TIMESTAMP NULL,
    updated_at  TIMESTAMP NULL
);
```

### 4.3 Tabelle `projects`

```sql
CREATE TABLE projects (
    id         INT UNSIGNED PRIMARY KEY,  -- manuelle ID, kein Auto-Increment
    name       VARCHAR(255) NOT NULL,
    scope      ENUM('person', 'project') NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);
```

### 4.4 Tabelle `run_participations`

```sql
CREATE TABLE run_participations (
    id               INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id          INT UNSIGNED NOT NULL,
    sponsored_run_id INT UNSIGNED NOT NULL,
    project_id       INT UNSIGNED NULL,
    laps             INT NOT NULL DEFAULT 0,
    hash             VARCHAR(255) NOT NULL UNIQUE,  -- Basis für Sponsoren-Link
    tshirt_size      ENUM('XS','S','M','L','XL','XXL') NULL,
    created_at       TIMESTAMP NULL,
    updated_at       TIMESTAMP NULL,

    FOREIGN KEY (user_id)          REFERENCES users(id)          ON DELETE CASCADE,
    FOREIGN KEY (sponsored_run_id) REFERENCES sponsored_runs(id) ON DELETE CASCADE,
    FOREIGN KEY (project_id)       REFERENCES projects(id)       ON DELETE SET NULL
);
```

### 4.5 Tabelle `sponsors`

```sql
CREATE TABLE sponsors (
    id                   INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    run_participation_id INT UNSIGNED NOT NULL,
    user_id              INT UNSIGNED NOT NULL,  -- Läufer-Referenz
    ext_personnel_no     INT NULL,
    firstname            VARCHAR(255) NOT NULL,
    lastname             VARCHAR(255) NOT NULL,
    email                VARCHAR(255) NULL,
    phone                VARCHAR(255) NULL,
    street               VARCHAR(255) NOT NULL,
    housenumber          VARCHAR(31) NOT NULL,
    postcode             CHAR(5) NOT NULL,
    city                 VARCHAR(255) NOT NULL,
    donation_per_lap     DECIMAL(10,2) NOT NULL,
    donation_static_max  DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    wants_newsletter     TINYINT(1) NULL,
    created_at           TIMESTAMP NULL,
    updated_at           TIMESTAMP NULL,

    FOREIGN KEY (run_participation_id) REFERENCES run_participations(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id)              REFERENCES users(id)              ON DELETE CASCADE
);
```

### 4.6 Tabelle `projectlists`

```sql
CREATE TABLE projectlists (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name       VARCHAR(255) NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);
```

### 4.7 Zwischentabellen (Many-to-Many)

```sql
-- Projekte ↔ Projektlisten
CREATE TABLE project_projectlist (
    project_id     INT UNSIGNED NOT NULL,
    projectlist_id INT UNSIGNED NOT NULL,
    PRIMARY KEY (project_id, projectlist_id),
    FOREIGN KEY (project_id)     REFERENCES projects(id)     ON DELETE CASCADE,
    FOREIGN KEY (projectlist_id) REFERENCES projectlists(id) ON DELETE CASCADE
);

-- Projektlisten ↔ Sponsorenläufe
CREATE TABLE projectlist_sponsored_run (
    projectlist_id   INT UNSIGNED NOT NULL,
    sponsored_run_id INT UNSIGNED NOT NULL,
    PRIMARY KEY (projectlist_id, sponsored_run_id),
    FOREIGN KEY (projectlist_id)   REFERENCES projectlists(id)   ON DELETE CASCADE,
    FOREIGN KEY (sponsored_run_id) REFERENCES sponsored_runs(id) ON DELETE CASCADE
);
```

### 4.8 Rollen & Berechtigungen (Entrust-kompatibel)

```sql
CREATE TABLE roles (
    id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name         VARCHAR(255) NOT NULL UNIQUE,
    display_name VARCHAR(255) NULL,
    description  VARCHAR(255) NULL,
    created_at   TIMESTAMP NULL,
    updated_at   TIMESTAMP NULL
);

CREATE TABLE permissions (
    id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name         VARCHAR(255) NOT NULL UNIQUE,
    display_name VARCHAR(255) NULL,
    description  VARCHAR(255) NULL,
    created_at   TIMESTAMP NULL,
    updated_at   TIMESTAMP NULL
);

CREATE TABLE role_user (
    user_id INT UNSIGNED NOT NULL,
    role_id INT UNSIGNED NOT NULL,
    PRIMARY KEY (user_id, role_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE
);

CREATE TABLE permission_role (
    permission_id INT UNSIGNED NOT NULL,
    role_id       INT UNSIGNED NOT NULL,
    PRIMARY KEY (permission_id, role_id),
    FOREIGN KEY (permission_id) REFERENCES permissions(id) ON DELETE CASCADE,
    FOREIGN KEY (role_id)       REFERENCES roles(id)       ON DELETE CASCADE
);

CREATE TABLE password_resets (
    email      VARCHAR(255) NOT NULL,
    token      VARCHAR(255) NOT NULL,
    created_at TIMESTAMP NULL,
    INDEX (email)
);
```

### 4.9 Entity-Relationship-Übersicht

```
users ──────────────────────────────────────────── role_user ── roles ── permission_role ── permissions
  │
  │ 1:N
  ▼
run_participations ──── sponsored_runs ──── projectlist_sponsored_run ──── projectlists ──── project_projectlist ──── projects
  │                         
  │ 1:N                     
  ▼
sponsors
```

**Hinweis:** `sponsors.user_id` verweist auf den **Läufer** (Inhaber der Teilnahme), nicht auf einen User-Account des Sponsors selbst. Sponsoren haben keinen eigenen Account.

---

## 5. API-Struktur (Neue Anwendung – Laravel REST API)

Die neue Anwendung trennt Backend (Laravel JSON-API) und Frontend (React SPA) vollständig.

### 5.1 Authentifizierung

| Methode | Endpunkt | Beschreibung |
|---|---|---|
| POST | `/api/auth/register` | Registrierung |
| GET | `/api/auth/verify/{code}` | E-Mail-Bestätigung |
| POST | `/api/auth/login` | Login → gibt Token zurück |
| POST | `/api/auth/logout` | Logout |
| POST | `/api/auth/password/email` | Passwort-Reset anfordern |
| POST | `/api/auth/password/reset` | Passwort zurücksetzen |

### 5.2 Nutzer (authentifiziert)

| Methode | Endpunkt | Beschreibung |
|---|---|---|
| GET | `/api/user` | Eigenes Profil |
| PATCH | `/api/user` | Profil aktualisieren |

### 5.3 Sponsorenläufe

| Methode | Endpunkt | Beschreibung | Rolle |
|---|---|---|---|
| GET | `/api/runs` | Alle Läufe | Auth |
| POST | `/api/runs` | Lauf erstellen | Admin |
| GET | `/api/runs/{id}` | Lauf-Details | Auth |
| PATCH | `/api/runs/{id}` | Lauf bearbeiten | Admin |
| DELETE | `/api/runs/{id}` | Lauf löschen | Admin |
| POST | `/api/runs/{id}/close` | Lauf schließen | Admin |
| POST | `/api/runs/{id}/reopen` | Lauf öffnen | Admin |
| GET | `/api/runs/{id}/evaluation` | Excel-Export | Admin |
| PATCH | `/api/runs/{id}/projectlists` | Projektlisten zuweisen | Admin |

### 5.4 Teilnahmen

| Methode | Endpunkt | Beschreibung | Rolle |
|---|---|---|---|
| GET | `/api/participations` | Eigene Teilnahmen | Läufer |
| POST | `/api/participations` | Lauf beitreten | Läufer |
| GET | `/api/participations/{id}` | Teilnahme-Details | Läufer/Admin |
| PATCH | `/api/participations/{id}` | Runden/Projekt/T-Shirt | Läufer/Admin |
| DELETE | `/api/participations/{id}` | Teilnahme löschen | Admin |
| GET | `/api/participations/{id}/calculate` | Spendensumme berechnen | Läufer/Admin |
| GET | `/api/runs/{id}/participations` | Alle Teilnahmen eines Laufs | Admin |

### 5.5 Sponsoren

| Methode | Endpunkt | Beschreibung | Rolle |
|---|---|---|---|
| GET | `/api/participations/{id}/sponsors` | Sponsoren der eigenen Teilnahme | Läufer |
| POST | `/api/participations/{id}/sponsors` | Sponsor eintragen | Läufer |
| PATCH | `/api/participations/{id}/sponsors/{sid}` | Sponsor bearbeiten | Läufer/Admin |
| DELETE | `/api/participations/{id}/sponsors/{sid}` | Sponsor löschen | Läufer/Admin |
| GET | `/api/run/{hash}/sponsors` | Sponsoren-Liste (Gast, via Hash) | Gast |
| POST | `/api/run/{hash}/sponsors` | Sponsor eintragen (Gast, via Hash) | Gast |

### 5.6 Projekte & Projektlisten

| Methode | Endpunkt | Beschreibung | Rolle |
|---|---|---|---|
| GET | `/api/projects` | Alle Projekte | Admin |
| POST | `/api/projects` | Projekt erstellen | Admin |
| PATCH | `/api/projects/{id}` | Projekt bearbeiten | Admin |
| DELETE | `/api/projects/{id}` | Projekt löschen | Admin |
| GET | `/api/projectlists` | Alle Projektlisten | Admin |
| POST | `/api/projectlists` | Projektliste erstellen | Admin |
| PATCH | `/api/projectlists/{id}` | Projektliste bearbeiten | Admin |
| DELETE | `/api/projectlists/{id}` | Projektliste löschen | Admin |
| PATCH | `/api/projectlists/{id}/projects` | Projekte hinzufügen/entfernen | Admin |

---

## 6. Frontend-Struktur (React SPA)

### 6.1 Seiten / Routen

| Pfad | Komponente | Beschreibung | Zugang |
|---|---|---|---|
| `/login` | LoginPage | Login-Formular | Gast |
| `/register` | RegisterPage | Registrierungsformular | Gast |
| `/verify/:code` | VerifyEmailPage | E-Mail-Bestätigung | Gast |
| `/password/reset` | PasswordResetPage | Passwort zurücksetzen | Gast |
| `/` | Dashboard | Offene Läufe | Auth |
| `/account` | AccountPage | Profil bearbeiten | Auth |
| `/runs/:id` | RunDetailPage | Lauf-Details | Auth |
| `/participations` | MyParticipationsPage | Eigene Teilnahmen | Läufer |
| `/participations/:id` | ParticipationDetailPage | Eigene Sponsoren | Läufer |
| `/run/:hash` | PublicSponsorPage | Sponsoren-Eintragung via Link | Gast |
| `/admin/runs` | AdminRunsPage | Läufe verwalten | Admin |
| `/admin/runs/:id` | AdminRunDetailPage | Teilnehmer & Auswertung | Admin |
| `/admin/runs/:id/participants/:pid` | AdminParticipantPage | Sponsoren eines Läufers | Admin |
| `/admin/projects` | AdminProjectsPage | Projekte verwalten | Admin |
| `/admin/projectlists` | AdminProjectlistsPage | Projektlisten verwalten | Admin |

### 6.2 Technologie-Empfehlungen (Frontend)

- **React 18+** mit TypeScript
- **React Router v6** für Routing
- **TanStack Query (React Query)** für API-Datenverwaltung und Caching
- **React Hook Form + Zod** für Formulare und Validierung
- **Tailwind CSS** oder **shadcn/ui** für UI-Komponenten
- **Axios** für HTTP-Requests
- **Zustand** für globales Auth-State-Management

---

## 7. Technische Anforderungen (Backend)

### 7.1 Stack

- **PHP 8.2+**
- **Laravel 11+**
- **MySQL 8+ / MariaDB 10.6+**
- **Laravel Sanctum** für API-Token-Authentifizierung (SPA-kompatibel)
- **Maatwebsite/Laravel-Excel** für Excel-Export
- **Spatie/Laravel-Permission** als Entrust-Nachfolger für Rollen/Berechtigungen

### 7.2 Konfiguration

| Parameter | Beschreibung |
|---|---|
| `NEWSLETTER_OPTIONAL` | Newsletter-Feld als optional (true) oder Pflichtfeld (false) |
| `APP_LOGO` | Pfad zum benutzerdefinierten Logo |
| `MAIL_*` | SMTP-Konfiguration für Benachrichtigungs-E-Mails |
| `FRONTEND_URL` | URL des React-Frontends (für CORS und E-Mail-Links) |

### 7.3 Seeding (Initialdaten)

- Rolle `admin` anlegen
- Berechtigung `manage-runs`, `manage-projects`, `manage-users`, `export` anlegen
- Ersten Nutzer (konfigurierbar via `.env`) anlegen und Admin-Rolle zuweisen

---

## 8. Migrationskonzept (Alt → Neu)

Da das Datenbankschema erhalten bleibt, ist eine Migration unkompliziert:

### 8.1 Datenbankschema

Das neue System verwendet **dieselben Tabellen und Spaltennamen** wie das Altsystem. Die einzigen Änderungen betreffen das Auth-System:

| Änderung | Grund |
|---|---|
| `password_resets` → `password_reset_tokens` | Laravel 11 Standard |
| Entrust-Tabellen bleiben erhalten | Spatie/Permission ist kompatibel konfigurierbar |

### 8.2 Migrationsschritte

1. **Neues System aufsetzen** (Datenbank leer)
2. **Laravel-Migrationen ausführen** – erzeugt die Tabellen im neuen Schema
3. **Datenbankdump der alten Instanz** erstellen (`mysqldump --no-create-info`)
4. **Dump importieren** in die neue Datenbank (nur Daten, keine Schema-DDL)
5. **Passwörter** sind bcrypt-kompatibel → werden unverändert übernommen
6. **Rollen-Migration:** `role_user` und `permission_role` Einträge manuell prüfen/anpassen falls Spatie/Permission andere Tabellennamen verwendet
7. **Testen:** Admin-Login, Sponsoren-Link-Funktion via Hash, Excel-Export

### 8.3 Inkompatibilitäten

| Problem | Lösung |
|---|---|
| Entrust vs. Spatie/Permission (unterschiedliche Tabellennamen) | Spatie/Permission kann mit Custom Table Names konfiguriert werden, oder: Migrationsscript, das Daten von `role_user` → `model_has_roles` kopiert |
| `confirmation_code` → Laravel Sanctum / Email Verify | Alten `confirmation_code`-Wert als verifiziert markieren (alle `confirmed = 1` übernehmen) |
| `wants_newsletter` als nullable boolean | Bleibt unverändert |

---

## 9. Offene Punkte / Entscheidungsbedarf

| # | Thema | Optionen |
|---|---|---|
| 1 | **Authentifizierungsmethode** | Laravel Sanctum (SPA-Cookies) vs. Token-basiert (Bearer Token) |
| 2 | **Newsletter-Pflichtfeld** | Per Konfiguration oder immer optional? |
| 3 | **Geschlecht** | Nur m/w oder weitere Optionen (divers)? |
| 4 | **PLZ** | Nur 5-stellig (Deutschland) oder international? |
| 5 | **CSV-Export** | Zusätzlich zu Excel oder nur Excel? |
| 6 | **Sponsoren-E-Mail-Benachrichtigung** | Immer senden oder optional pro Lauf konfigurierbar? |
| 7 | **Mehrsprachigkeit** | Nur Deutsch oder i18n-fähig? |
| 8 | **Multi-Tenancy** | Eine Installation = eine Organisation, oder mehrere Organisationen je Instanz? |

---

## 10. Glossar

| Begriff | Bedeutung |
|---|---|
| **Sponsorenlauf** | Benefiz-Laufveranstaltung, bei der Läufer Sponsoren gewinnen |
| **Läufer** | Teilnehmer eines Sponsorenlaufs mit eigenem Account |
| **Sponsor** | Person, die einem Läufer eine Spende pro Runde oder einen Fixbetrag zusagt |
| **Teilnahme (run_participation)** | Verbindung zwischen Läufer und Lauf inkl. Rundenzahl und T-Shirt-Größe |
| **Hash** | Eindeutiger URL-sicherer Zufallsstring, der den Sponsoren-Link identifiziert |
| **Projekt** | Förderzweck, dem ein Läufer seine Spendengelder widmet |
| **Projektliste** | Gruppe von Projekten, die einem Lauf zugewiesen wird |
| **Auswertung** | Excel-Export mit allen Teilnahmen und Sponsoren eines Laufs |
