<?php
/**
 * Standalone Legacy-Datenseeder für Migrationstests
 * ===================================================
 * Erstellt das komplette Legacy-Schema + umfangreiche Fake-Daten.
 * Kein Framework erforderlich — läuft mit PHP ≥ 7.4.
 *
 * VERWENDUNG:
 *   php seed-legacy-data.php [--sqlite=pfad/zur/datei.sqlite]
 *                            [--mysql="host=127.0.0.1;dbname=sponrun;user=root;pass=secret"]
 *                            [--fresh]   Alle bestehenden Tabellen vorher leeren
 *
 * Standard: SQLite-Datei "legacy_test.sqlite" im aktuellen Verzeichnis
 *
 * MIGRATIONSTESTS danach:
 *   1. Laravel 5.5 Legacy-App:  php artisan db:seed --class=FakeLegacyDataSeeder
 *      (oder diese Datei direkt: php seed-legacy-data.php)
 *   2. Neue Webapp (webapp/):
 *      php artisan sponrun:migrate-from-legacy
 *      php artisan migrate
 *      php artisan db:seed --class=Database\Seeders\DatabaseSeeder (optional)
 *
 * ERSTELLT:
 *   - 1 Admin + ~800 Nutzer
 *   - 5 Sponsorenläufe (2022–2025, 4 geschlossen / 1 offen)
 *   - 90–200 Läufer pro Lauf mit Überschneidungen
 *   - 1–50 Sponsoren pro Läufer (~18.000 Sponsoren gesamt)
 *   - 23 Projekte + 6 Projektlisten
 *   - Admin-Rolle (Entrust) → Nutzerzuweisung
 */

declare(strict_types=1);

// ── CLI-Argumente ─────────────────────────────────────────────────────────────

$opts   = getopt('', ['sqlite::', 'mysql::', 'fresh']);
$fresh  = isset($opts['fresh']);
$sqlite = $opts['sqlite'] ?? 'legacy_test.sqlite';
$mysql  = $opts['mysql'] ?? null;

// ── Datenbank-Verbindung ──────────────────────────────────────────────────────

if ($mysql) {
    [$host, $dbname, $user, $pass] = array_pad(explode(';', $mysql, 4), 4, '');
    $pdo = new PDO("mysql:host={$host};dbname={$dbname};charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);
    $driver = 'mysql';
} else {
    $pdo = new PDO("sqlite:{$sqlite}", null, null, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    $pdo->exec("PRAGMA journal_mode=WAL; PRAGMA synchronous=NORMAL; PRAGMA foreign_keys=OFF;");
    $driver = 'sqlite';
}

out("Verbunden mit {$driver} (" . ($mysql ? $mysql : $sqlite) . ")");

// ── Schema erstellen ──────────────────────────────────────────────────────────

createSchema($pdo, $driver, $fresh);

// ── Daten seeden ──────────────────────────────────────────────────────────────

$faker = new Faker($driver);

out('');
out('Seeding läuft — das kann 1–2 Minuten dauern …');

$adminId    = seedAdmin($pdo, $driver);
$projectIds = seedProjects($pdo);
$listIds    = seedProjectlists($pdo, $projectIds);
$runIds     = seedRuns($pdo, $driver, $listIds);
$userIds    = seedUsers($pdo, $driver, $faker, 800, $adminId);
seedParticipationsAndSponsors($pdo, $faker, $userIds, $runIds, $projectIds);

out('');
out('Fertig!');
out("Admin-Login: admin@example.com / password");
out('');

// Statistik ausgeben
$stats = [
    'Nutzer'      => $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn(),
    'Läufe'       => $pdo->query("SELECT COUNT(*) FROM sponsored_runs")->fetchColumn(),
    'Teilnahmen'  => $pdo->query("SELECT COUNT(*) FROM run_participations")->fetchColumn(),
    'Sponsoren'   => $pdo->query("SELECT COUNT(*) FROM sponsors")->fetchColumn(),
    'Projekte'    => $pdo->query("SELECT COUNT(*) FROM projects")->fetchColumn(),
    'Projektlisten' => $pdo->query("SELECT COUNT(*) FROM projectlists")->fetchColumn(),
];
foreach ($stats as $label => $count) {
    out("  {$label}: {$count}");
}

// ═══════════════════════════════════════════════════════════════════════════════
// HILFSFUNKTIONEN
// ═══════════════════════════════════════════════════════════════════════════════

function createSchema(PDO $pdo, string $driver, bool $fresh): void
{
    out('Erstelle Schema …');

    $ai = $driver === 'mysql' ? 'AUTO_INCREMENT' : 'AUTOINCREMENT';
    $bi = $driver === 'mysql' ? 'TINYINT(1)'     : 'INTEGER';

    if ($fresh) {
        $tables = ['sponsors','run_participations','projectlist_sponsored_run',
                   'project_projectlist','projectlists','role_user',
                   'permission_role','permissions','roles',
                   'sponsored_runs','projects','password_resets','users'];
        foreach ($tables as $t) {
            $pdo->exec("DROP TABLE IF EXISTS {$t}");
        }
    }

    $ddl = [
        "CREATE TABLE IF NOT EXISTS users (
            id               INTEGER PRIMARY KEY {$ai},
            ext_personnel_no INTEGER  NULL,
            firstname        VARCHAR(255) NOT NULL,
            lastname         VARCHAR(255) NOT NULL,
            email            VARCHAR(255) NOT NULL UNIQUE,
            phone            VARCHAR(255) NULL,
            birthday         DATE         NOT NULL,
            street           VARCHAR(255) NOT NULL,
            housenumber      VARCHAR(31)  NOT NULL,
            postcode         VARCHAR(5)   NOT NULL,
            city             VARCHAR(255) NOT NULL,
            gender           VARCHAR(1)   NOT NULL,
            confirmed        {$bi} NOT NULL DEFAULT 0,
            confirmation_code VARCHAR(255) NULL,
            wants_newsletter {$bi} NULL,
            password         VARCHAR(255) NOT NULL,
            remember_token   VARCHAR(100) NULL,
            created_at       DATETIME NULL,
            updated_at       DATETIME NULL
        )",
        "CREATE TABLE IF NOT EXISTS password_resets (
            email      VARCHAR(255) NOT NULL,
            token      VARCHAR(255) NOT NULL,
            created_at DATETIME NULL
        )",
        "CREATE TABLE IF NOT EXISTS projects (
            id         INTEGER PRIMARY KEY,
            name       VARCHAR(255) NOT NULL,
            scope      VARCHAR(10)  NOT NULL DEFAULT 'project',
            created_at DATETIME NULL,
            updated_at DATETIME NULL
        )",
        "CREATE TABLE IF NOT EXISTS sponsored_runs (
            id           INTEGER PRIMARY KEY {$ai},
            name         VARCHAR(255) NOT NULL,
            begin        DATETIME     NOT NULL,
            end          DATETIME     NOT NULL,
            with_tshirt  {$bi} NOT NULL DEFAULT 0,
            closed       {$bi} NOT NULL DEFAULT 0,
            street       VARCHAR(255) NULL,
            housenumber  VARCHAR(31)  NULL,
            postcode     VARCHAR(5)   NULL,
            city         VARCHAR(255) NULL,
            description  VARCHAR(255) NULL,
            created_at   DATETIME NULL,
            updated_at   DATETIME NULL
        )",
        "CREATE TABLE IF NOT EXISTS projectlists (
            id         INTEGER PRIMARY KEY {$ai},
            name       VARCHAR(255) NOT NULL,
            created_at DATETIME NULL,
            updated_at DATETIME NULL
        )",
        "CREATE TABLE IF NOT EXISTS project_projectlist (
            project_id     INTEGER NOT NULL,
            projectlist_id INTEGER NOT NULL,
            created_at DATETIME NULL,
            updated_at DATETIME NULL,
            PRIMARY KEY (project_id, projectlist_id)
        )",
        "CREATE TABLE IF NOT EXISTS projectlist_sponsored_run (
            projectlist_id  INTEGER NOT NULL,
            sponsored_run_id INTEGER NOT NULL,
            created_at DATETIME NULL,
            updated_at DATETIME NULL,
            PRIMARY KEY (projectlist_id, sponsored_run_id)
        )",
        "CREATE TABLE IF NOT EXISTS run_participations (
            id               INTEGER PRIMARY KEY {$ai},
            user_id          INTEGER NOT NULL,
            sponsored_run_id INTEGER NOT NULL,
            project_id       INTEGER NULL,
            laps             INTEGER NOT NULL DEFAULT 0,
            hash             VARCHAR(255) NOT NULL UNIQUE,
            tshirt_size      VARCHAR(5) NULL,
            created_at DATETIME NULL,
            updated_at DATETIME NULL
        )",
        "CREATE TABLE IF NOT EXISTS sponsors (
            id                   INTEGER PRIMARY KEY {$ai},
            run_participation_id INTEGER NOT NULL,
            user_id              INTEGER NOT NULL,
            firstname            VARCHAR(255) NOT NULL,
            lastname             VARCHAR(255) NOT NULL,
            street               VARCHAR(255) NOT NULL,
            housenumber          VARCHAR(31)  NOT NULL,
            postcode             VARCHAR(5)   NOT NULL,
            city                 VARCHAR(255) NOT NULL,
            phone                VARCHAR(255) NULL,
            email                VARCHAR(255) NULL,
            donation_per_lap     DECIMAL(10,2) NOT NULL DEFAULT 0,
            donation_static_max  DECIMAL(10,2) NOT NULL DEFAULT 0,
            wants_newsletter     {$bi} NULL,
            ext_personnel_no     INTEGER NULL,
            created_at DATETIME NULL,
            updated_at DATETIME NULL
        )",
        "CREATE TABLE IF NOT EXISTS roles (
            id           INTEGER PRIMARY KEY {$ai},
            name         VARCHAR(255) NOT NULL UNIQUE,
            display_name VARCHAR(255) NULL,
            description  VARCHAR(255) NULL,
            created_at DATETIME NULL,
            updated_at DATETIME NULL
        )",
        "CREATE TABLE IF NOT EXISTS role_user (
            user_id INTEGER NOT NULL,
            role_id INTEGER NOT NULL,
            PRIMARY KEY (user_id, role_id)
        )",
    ];

    foreach ($ddl as $sql) {
        $pdo->exec($sql);
    }
}

function seedAdmin(PDO $pdo, string $driver): int
{
    out('Erstelle Admin …');

    $now = date('Y-m-d H:i:s');
    $pdo->exec("INSERT OR IGNORE INTO roles (name, display_name, description, created_at, updated_at)
                VALUES ('admin', 'Administrator', 'Superuser mit allen Rechten', '{$now}', '{$now}')");

    $stmt = $pdo->prepare("INSERT INTO users (
        firstname, lastname, email, password, phone, birthday, street,
        housenumber, postcode, city, gender, confirmed, wants_newsletter, created_at, updated_at
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1, 0, ?, ?)");
    $stmt->execute(['Admin','User','admin@example.com', password_hash('password', PASSWORD_BCRYPT),
                    '0221 1234567','1980-01-01','Musterstraße','1','12345','Musterstadt','m', $now, $now]);
    $adminId = (int) $pdo->lastInsertId();

    $roleId = (int) $pdo->query("SELECT id FROM roles WHERE name='admin'")->fetchColumn();
    $pdo->exec("INSERT OR IGNORE INTO role_user (user_id, role_id) VALUES ({$adminId}, {$roleId})");

    return $adminId;
}

function seedProjects(PDO $pdo): array
{
    out('Erstelle Projekte …');

    $projects = [
        [0,       'Allgemeine Mission',                           'project'],
        [10100,   '101-00 Vogel, Eduard',                        'person'],
        [10200,   '102-00 Nestmann, Thomas',                     'person'],
        [10400,   '104-00 Harder, Waldemar',                     'person'],
        [10500,   '105-00 Tissen, Jakob',                        'person'],
        [10600,   '106-00 Wiebe, Peter',                         'person'],
        [20500,   '205-00 Warkentin, Peter & Antonia -Brasilien-','person'],
        [20600,   '206-00 Dyck, Eduard & Maria -Haiti-',         'person'],
        [22100,   '221-00 Löwen, Waldemar & Helene -Malawi-',    'person'],
        [22600,   '226-00 Klassen, Anika -Malawi-',              'person'],
        [29300,   '293-00 Anderson, Tesni -Kenia-',              'person'],
        [30000,   '300-00 Giesbrecht, Karoline -Mosambik-',      'person'],
        [2002100, '20-021-00 Flüchtlingsarbeit Deutschland',     'project'],
        [2020100, '20-201-00 Gemeindegründung',                  'project'],
        [2050100, '20-501-00 Jugendmission "Send Me"',           'project'],
        [4200000, '42-000-00 Haiti allgemein',                   'project'],
        [5000000, '50-000-00 Brasilien allgemein',               'project'],
        [6010000, '60-100-00 Kinderdörfer Malawi/Mosambik',      'project'],
        [6010100, '60-101-00 Chiole Kinderdorf',                 'project'],
        [6010200, '60-102-00 Mosambik Kinderdorf',               'project'],
        [7000000, '70-000-00 Thailand Allgemein',                'project'],
        [9901000, '99-010-00 Missionare in Not',                 'project'],
        [9910000, '99-100-00 Kinderhilfe Global',                'project'],
    ];

    $now  = date('Y-m-d H:i:s');
    $stmt = $pdo->prepare("INSERT OR IGNORE INTO projects (id, name, scope, created_at, updated_at) VALUES (?,?,?,?,?)");
    foreach ($projects as [$id, $name, $scope]) {
        $stmt->execute([$id, $name, $scope, $now, $now]);
    }

    return array_column($projects, 0);
}

function seedProjectlists(PDO $pdo, array $projectIds): array
{
    out('Erstelle Projektlisten …');

    $now   = date('Y-m-d H:i:s');
    $lists = [
        'Afrika-Projekte'  => [6010000, 6010100, 6010200, 22100, 22600, 29300, 30000],
        'Asien & Pazifik'  => [7000000, 20600, 4200000],
        'Lateinamerika'    => [5000000, 20500, 2020100],
        'Deutschland & EU' => [2002100, 2050100, 2020100],
        'Kinder & Jugend'  => [9910000, 6010100, 6010200, 7000000, 2050100],
        'Alle Projekte'    => [0, 9901000, 9910000, 2002100, 6010000, 7000000, 5000000, 4200000],
    ];

    $listIds = [];
    foreach ($lists as $name => $pIds) {
        $pdo->prepare("INSERT INTO projectlists (name, created_at, updated_at) VALUES (?,?,?)")
            ->execute([$name, $now, $now]);
        $listId    = (int) $pdo->lastInsertId();
        $listIds[] = $listId;

        foreach ($pIds as $pId) {
            if (in_array($pId, $projectIds, true)) {
                $pdo->exec("INSERT OR IGNORE INTO project_projectlist
                    (project_id, projectlist_id, created_at, updated_at)
                    VALUES ({$pId}, {$listId}, '{$now}', '{$now}')");
            }
        }
    }

    return $listIds;
}

function seedRuns(PDO $pdo, string $driver, array $listIds): array
{
    out('Erstelle 5 Sponsorenläufe …');

    $now  = date('Y-m-d H:i:s');
    $runs = [
        ['Frühjahrs-Sponsorenlauf 2022', '2022-04-02 09:00:00', '2022-04-02 16:00:00', 1, 1,
         'Schulstraße', '5', '70173', 'Stuttgart',
         'Erster Sponsorenlauf. Gesammelte Spenden für Kinderdörfer in Malawi.',
         array_slice($listIds, 0, 2)],
        ['Herbst-Sponsorenlauf 2022',    '2022-10-15 09:00:00', '2022-10-15 17:00:00', 1, 1,
         'Kirchweg', '12', '70173', 'Stuttgart',
         'Herbstlauf mit Schwerpunkt Lateinamerika.',
         array_slice($listIds, 1, 3)],
        ['Jahres-Sponsorenlauf 2023',    '2023-06-10 09:00:00', '2023-06-10 16:00:00', 0, 1,
         'Parkstraße', '3', '70174', 'Stuttgart',
         'Großer Gemeinschaftslauf mit allen Altersgruppen.',
         array_slice($listIds, 0, 4)],
        ['Jubiläums-Sponsorenlauf 2024', '2024-05-18 09:00:00', '2024-05-18 17:00:00', 1, 1,
         'Festwiese', '1', '70199', 'Stuttgart',
         '10. Jubiläumslauf – Haiti und Kenia im Fokus.',
         $listIds],
        ['Sponsorenlauf Frühjahr 2025',  '2025-04-05 09:00:00', '2025-04-05 16:00:00', 1, 0,
         'Gemeindeweg', '7', '70178', 'Stuttgart',
         'Aktueller Lauf – Projekte weltweit unterstützen.',
         $listIds],
    ];

    $stmt = $pdo->prepare("INSERT INTO sponsored_runs
        (name, begin, end, with_tshirt, closed, street, housenumber, postcode, city, description, created_at, updated_at)
        VALUES (?,?,?,?,?,?,?,?,?,?,?,?)");

    $runIds = [];
    foreach ($runs as [$name, $begin, $end, $tshirt, $closed, $str, $hn, $pc, $city, $desc, $plistIds]) {
        $stmt->execute([$name, $begin, $end, $tshirt, $closed, $str, $hn, $pc, $city, $desc, $now, $now]);
        $runId    = (int) $pdo->lastInsertId();
        $runIds[] = ['id' => $runId, 'tshirt' => $tshirt, 'listIds' => $plistIds];

        foreach ($plistIds as $lid) {
            $pdo->exec("INSERT OR IGNORE INTO projectlist_sponsored_run
                (projectlist_id, sponsored_run_id, created_at, updated_at)
                VALUES ({$lid}, {$runId}, '{$now}', '{$now}')");
        }
    }

    return $runIds;
}

function seedUsers(PDO $pdo, string $driver, Faker $f, int $count, int $skipId): array
{
    out("Erstelle {$count} Nutzer …");

    $now  = date('Y-m-d H:i:s');
    $stmt = $pdo->prepare("INSERT INTO users
        (ext_personnel_no, firstname, lastname, email, phone, birthday,
         street, housenumber, postcode, city, gender, confirmed,
         wants_newsletter, password, remember_token, created_at, updated_at)
        VALUES (?,?,?,?,?,?,?,?,?,?,?,1,?,?,?,?,?)");

    $ids = [];
    for ($i = 0; $i < $count; $i++) {
        $gender = $f->gender();
        $stmt->execute([
            $f->optional(0.3, null)(fn () => $f->int(10000, 99999)),
            $f->firstname($gender),
            $f->lastname(),
            $f->uniqueEmail(),
            $f->optional(0.7, null)(fn () => $f->phone()),
            $f->date('-70 years', '-8 years'),
            $f->street(),
            $f->buildingNumber(),
            $f->postcode(),
            $f->city(),
            $gender,
            (int) $f->bool(60),
            password_hash('password', PASSWORD_BCRYPT),
            bin2hex(random_bytes(30)),
            $now, $now,
        ]);
        $ids[] = (int) $pdo->lastInsertId();
    }

    return $ids;
}

function seedParticipationsAndSponsors(PDO $pdo, Faker $f, array $userIds, array $runs, array $projectIds): void
{
    $now      = date('Y-m-d H:i:s');
    $tshirts  = ['XS', 'S', 'M', 'L', 'XL', 'XXL'];
    $runCount = count($runs);

    $rpStmt = $pdo->prepare("INSERT INTO run_participations
        (user_id, sponsored_run_id, project_id, laps, hash, tshirt_size, created_at, updated_at)
        VALUES (?,?,?,?,?,?,?,?)");

    $spStmt = $pdo->prepare("INSERT INTO sponsors
        (run_participation_id, user_id, firstname, lastname, street, housenumber,
         postcode, city, phone, email, donation_per_lap, donation_static_max,
         wants_newsletter, ext_personnel_no, created_at, updated_at)
        VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");

    foreach ($runs as $idx => $run) {
        $runNo       = $idx + 1;
        $runId       = $run['id'];
        $withTshirt  = (bool) $run['tshirt'];
        $runnerCount = $f->int(90, 200);
        $runUserIds  = $f->sample($userIds, $runnerCount);

        // Verfügbare Projektlisten für diesen Lauf
        $availProjIds = $pdo->query(
            "SELECT DISTINCT pp.project_id FROM project_projectlist pp
             JOIN projectlist_sponsored_run psr ON pp.projectlist_id = psr.projectlist_id
             WHERE psr.sponsored_run_id = {$runId}"
        )->fetchAll(PDO::FETCH_COLUMN);

        if (empty($availProjIds)) {
            $availProjIds = $projectIds;
        }

        out("Lauf {$runNo}/{$runCount}: {$runnerCount} Läufer …", false);

        $usedHashes    = [];
        $sponsorBuffer = [];

        foreach ($runUserIds as $userId) {
            // Hash (wie Legacy: md5(microtime()))
            do {
                $hash = md5(microtime() . $userId . $runId . random_int(0, PHP_INT_MAX));
            } while (isset($usedHashes[$hash]));
            $usedHashes[$hash] = true;

            $laps      = $f->int(0, 60);
            $projectId = $f->optional(0.8, null)(fn () => $f->pick($availProjIds));
            $tshirt    = ($withTshirt && $f->bool(70)) ? $f->pick($tshirts) : null;

            $rpStmt->execute([$userId, $runId, $projectId, $laps, $hash, $tshirt, $now, $now]);
            $rpId = (int) $pdo->lastInsertId();

            $sponsorCount = $f->int(1, 50);
            for ($s = 0; $s < $sponsorCount; $s++) {
                $type     = $f->pick(['per_lap', 'static', 'both']);
                $perLap   = 0.0;
                $staticMax = 0.0;
                if ($type === 'per_lap')   { $perLap    = round($f->float(0.25, 5.00), 2); }
                if ($type === 'static')    { $staticMax = round($f->float(5.00, 200.00), 2); }
                if ($type === 'both')      {
                    $perLap    = round($f->float(0.25, 3.00), 2);
                    $staticMax = round($f->float(10.00, 100.00), 2);
                }

                $sponsorBuffer[] = [
                    $rpId, $userId,
                    $f->firstname($f->gender()),
                    $f->lastname(),
                    $f->street(),
                    $f->buildingNumber(),
                    $f->postcode(),
                    $f->city(),
                    $f->optional(0.5, null)(fn () => $f->phone()),
                    $f->optional(0.6, null)(fn () => $f->uniqueEmail()),
                    $perLap, $staticMax,
                    $f->optional(0.5, null)(fn () => (int) $f->bool()),
                    $f->optional(0.2, null)(fn () => $f->int(10000, 99999)),
                    $now, $now,
                ];

                if (count($sponsorBuffer) >= 500) {
                    bulkInsertSponsors($pdo, $spStmt, $sponsorBuffer);
                    $sponsorBuffer = [];
                }
            }
        }

        bulkInsertSponsors($pdo, $spStmt, $sponsorBuffer);
        $sponsorBuffer = [];

        $sponCount = (int) $pdo->query(
            "SELECT COUNT(*) FROM sponsors s
             JOIN run_participations rp ON s.run_participation_id = rp.id
             WHERE rp.sponsored_run_id = {$runId}"
        )->fetchColumn();

        out(" {$runnerCount} Teilnahmen, {$sponCount} Sponsoren.", true, true);
    }
}

function bulkInsertSponsors(PDO $pdo, PDOStatement $stmt, array $buf): void
{
    if (empty($buf)) return;
    $pdo->beginTransaction();
    foreach ($buf as $row) {
        $stmt->execute($row);
    }
    $pdo->commit();
}

// ── Mini-Faker ohne externen Abhängigkeiten ───────────────────────────────────

class Faker
{
    private array $used = [];
    private string $driver;

    private array $firstnamesM  = ['Thomas','Michael','Andreas','Stefan','Klaus','Peter','Hans','Markus','Jürgen','Christian','Daniel','Felix','David','Sebastian','Tobias','Simon','Matthias','Oliver','Christoph','Patrick','Max','Jan','Lukas','Florian','Dominik'];
    private array $firstnamesF  = ['Anna','Maria','Sandra','Lisa','Julia','Sabine','Christine','Nicole','Laura','Sarah','Kathrin','Melanie','Andrea','Petra','Claudia','Monika','Franziska','Nadine','Hannah','Lena','Emma','Sophie','Marie','Jana','Lea'];
    private array $lastnames    = ['Müller','Schmidt','Schneider','Fischer','Weber','Meyer','Wagner','Becker','Schulz','Hoffmann','Koch','Richter','Bauer','Klein','Wolf','Schröder','Neumann','Schwarz','Zimmermann','Braun','Krüger','Hofmann','Hartmann','Lange','Schmitt','Werner','Schmitz','Kramer','Vogel','Friedrich'];
    private array $streets      = ['Hauptstraße','Schulstraße','Gartenweg','Kirchweg','Bergstraße','Waldweg','Ringstraße','Parkweg','Birkenweg','Lindenstraße','Rosenweg','Buchenweg','Ahornstraße','Fichtenweg','Eichenweg','Jahnstraße','Rathausplatz','Marktplatz','Bahnhofstraße','Dorfstraße'];
    private array $cities       = ['Stuttgart','München','Frankfurt','Hamburg','Berlin','Köln','Düsseldorf','Leipzig','Dresden','Hannover','Bremen','Dortmund','Essen','Nürnberg','Duisburg','Bochum','Wuppertal','Bielefeld','Bonn','Münster','Karlsruhe','Mannheim','Augsburg','Wiesbaden','Gelsenkirchen'];
    private array $domains      = ['gmail.com','web.de','gmx.de','t-online.de','freenet.de','outlook.com','yahoo.de','hotmail.de','icloud.com','mailbox.org'];

    public function __construct(string $driver) { $this->driver = $driver; }

    public function gender(): string { return $this->pick(['m', 'f']); }

    public function firstname(string $gender): string
    {
        return $this->pick($gender === 'm' ? $this->firstnamesM : $this->firstnamesF);
    }

    public function lastname(): string { return $this->pick($this->lastnames); }

    public function uniqueEmail(): string
    {
        $tries = 0;
        do {
            $local = strtolower($this->pick($this->firstnamesM)) . '.' . strtolower($this->pick($this->lastnames));
            $local = preg_replace('/[^a-z.]/', '', iconv('UTF-8', 'ASCII//TRANSLIT', $local));
            $email = $local . random_int(10, 9999) . '@' . $this->pick($this->domains);
        } while (isset($this->used[$email]) && ++$tries < 100);
        $this->used[$email] = true;
        return $email;
    }

    public function phone(): string
    {
        return sprintf('0%d %d', random_int(711, 9999), random_int(100000, 9999999));
    }

    public function street(): string { return $this->pick($this->streets); }
    public function city(): string   { return $this->pick($this->cities); }

    public function postcode(): string
    {
        return str_pad((string) random_int(10000, 99999), 5, '0', STR_PAD_LEFT);
    }

    public function buildingNumber(): string
    {
        $n = (string) random_int(1, 200);
        return random_int(0, 5) === 0 ? $n . chr(random_int(97, 101)) : $n;
    }

    public function date(string $from = '-70 years', string $to = '-8 years'): string
    {
        $fromTs = strtotime($from);
        $toTs   = strtotime($to);
        return date('Y-m-d', random_int(min($fromTs, $toTs), max($fromTs, $toTs)));
    }

    public function int(int $min, int $max): int { return random_int($min, $max); }

    public function float(float $min, float $max): float
    {
        return $min + (mt_rand() / mt_getrandmax()) * ($max - $min);
    }

    public function bool(int $percent = 50): bool { return random_int(1, 100) <= $percent; }

    public function pick(array $arr): mixed
    {
        return $arr[array_rand($arr)];
    }

    public function sample(array $arr, int $n): array
    {
        if ($n >= count($arr)) return $arr;
        $keys = array_rand($arr, $n);
        if (!is_array($keys)) $keys = [$keys];
        return array_map(fn ($k) => $arr[$k], $keys);
    }

    public function optional(float $chance, mixed $default): callable
    {
        return function (callable $fn = null) use ($chance, $default) {
            if ((mt_rand() / mt_getrandmax()) <= $chance) {
                return $fn ? $fn() : true;
            }
            return $default;
        };
    }
}

// ── Ausgabe-Helper ────────────────────────────────────────────────────────────

function out(string $msg, bool $nl = true, bool $overwrite = false): void
{
    if ($overwrite) echo "\r" . str_pad($msg, 80) . "\n";
    elseif ($nl)    echo $msg . "\n";
    else            echo $msg;
    flush();
}
