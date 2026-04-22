<?php

use App\Domain\Model\Auth\Role;
use App\Domain\Model\Auth\User;
use App\Domain\Model\Sponsor\Project;
use App\Domain\Model\Sponsor\Projectlist;
use App\Domain\Model\Sponsor\RunParticipation;
use App\Domain\Model\Sponsor\SponsoredRun;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * Erstellt einen umfangreichen Datensatz für Migrationstests.
 *
 * Ausführen mit:
 *   php artisan db:seed --class=FakeLegacyDataSeeder
 *
 * Erstellt:
 *   - 1 Admin-Nutzer (admin@example.com / password)
 *   - ~800 Nutzer (Faker DE)
 *   - 5 Sponsorenläufe (verschiedene Zeiträume, 1 geschlossen)
 *   - 90–200 Läufer pro Lauf (mit Überlappungen)
 *   - 1–50 Sponsoren pro Läufer (~18.000 Sponsoren gesamt)
 *   - Projekte + Projektlisten (reale Daten aus ProjectsTableSeeder)
 */
class FakeLegacyDataSeeder extends Seeder
{
    private \Faker\Generator $faker;
    private array $projectIds = [];

    public function run(): void
    {
        $this->faker = \Faker\Factory::create('de_DE');

        $this->command->info('Seeder gestartet — bitte warten (kann 1–2 Min. dauern) …');

        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        DB::table('sponsors')->truncate();
        DB::table('run_participations')->truncate();
        DB::table('projectlist_sponsored_run')->truncate();
        DB::table('project_projectlist')->truncate();
        DB::table('projectlists')->truncate();
        DB::table('sponsored_runs')->truncate();
        DB::table('role_user')->truncate();
        DB::table('roles')->truncate();
        DB::table('projects')->truncate();
        DB::table('users')->truncate();

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $this->seedRolesAndAdmin();
        $this->seedProjects();
        $this->seedProjectlists();
        $users = $this->seedUsers(800);
        $runs  = $this->seedRuns();
        $this->seedParticipationsAndSponsors($users, $runs);

        $this->command->info('Fertig!');
    }

    // ── Rollen & Admin ────────────────────────────────────────────────────────

    private function seedRolesAndAdmin(): void
    {
        $this->command->info('Erstelle Admin-Rolle und -Nutzer …');

        $adminRole = Role::create([
            'name'         => 'admin',
            'display_name' => 'Administrator',
            'description'  => 'Superuser mit allen Rechten',
        ]);

        $admin = User::create([
            'firstname'        => 'Admin',
            'lastname'         => 'User',
            'email'            => 'admin@example.com',
            'password'         => Hash::make('password'),
            'phone'            => '0221 1234567',
            'birthday'         => '1980-01-01',
            'street'           => 'Musterstraße',
            'housenumber'      => '1',
            'postcode'         => '12345',
            'city'             => 'Musterstadt',
            'gender'           => 'm',
            'confirmed'        => 1,
            'wants_newsletter' => false,
        ]);

        $admin->attachRole($adminRole);
    }

    // ── Projekte ──────────────────────────────────────────────────────────────

    private function seedProjects(): void
    {
        $this->command->info('Erstelle Projekte …');

        $projects = [
            // Personen
            [0,       'Allgemeine Mission',               'project'],
            [10100,   '101-00 Vogel, Eduard',             'person'],
            [10200,   '102-00 Nestmann, Thomas',          'person'],
            [10400,   '104-00 Harder, Waldemar',          'person'],
            [10500,   '105-00 Tissen, Jakob',             'person'],
            [10600,   '106-00 Wiebe, Peter',              'person'],
            [20500,   '205-00 Warkentin, Peter & Antonia -Brasilien-', 'person'],
            [20600,   '206-00 Dyck, Eduard & Maria -Haiti-',           'person'],
            [22100,   '221-00 Löwen, Waldemar & Helene -Malawi-',      'person'],
            [22600,   '226-00 Klassen, Anika -Malawi-',               'person'],
            [29300,   '293-00 Anderson, Tesni -Kenia-',               'person'],
            [30000,   '300-00 Giesbrecht, Karoline -Mosambik-',       'person'],
            // Projekte
            [2002100, '20-021-00 Flüchtlingsarbeit Deutschland',      'project'],
            [2020100, '20-201-00 Gemeindegründung',                   'project'],
            [2050100, '20-501-00 Jugendmission "Send Me"',            'project'],
            [4200000, '42-000-00 Haiti allgemein',                    'project'],
            [5000000, '50-000-00 Brasilien allgemein',                'project'],
            [6010000, '60-100-00 Kinderdörfer Malawi/Mosambik',       'project'],
            [6010100, '60-101-00 Chiole Kinderdorf',                  'project'],
            [6010200, '60-102-00 Mosambik Kinderdorf',                'project'],
            [7000000, '70-000-00 Thailand Allgemein',                 'project'],
            [9901000, '99-010-00 Missionare in Not',                  'project'],
            [9910000, '99-100-00 Kinderhilfe Global',                 'project'],
        ];

        foreach ($projects as [$id, $name, $scope]) {
            Project::create(['id' => $id, 'name' => $name, 'scope' => $scope]);
        }

        $this->projectIds = array_column($projects, 0);
    }

    // ── Projektlisten ─────────────────────────────────────────────────────────

    private function seedProjectlists(): void
    {
        $this->command->info('Erstelle Projektlisten …');

        $lists = [
            'Afrika-Projekte'    => [6010000, 6010100, 6010200, 22100, 22600, 29300, 30000],
            'Asien & Pazifik'    => [7000000, 20600, 4200000],
            'Lateinamerika'      => [5000000, 20500, 2020100],
            'Deutschland & EU'   => [2002100, 2050100, 2020100],
            'Kinder & Jugend'    => [9910000, 6010100, 6010200, 7000000, 2050100],
            'Alle Projekte'      => [0, 9901000, 9910000, 2002100, 6010000, 7000000, 5000000, 4200000],
        ];

        foreach ($lists as $listName => $projectIds) {
            $list = Projectlist::create(['name' => $listName]);
            $list->projects()->attach(array_filter($projectIds, fn ($id) => in_array($id, $this->projectIds)));
        }
    }

    // ── Nutzer ────────────────────────────────────────────────────────────────

    private function seedUsers(int $count): array
    {
        $this->command->info("Erstelle {$count} Nutzer …");

        $now   = now()->toDateTimeString();
        $batch = [];

        for ($i = 0; $i < $count; $i++) {
            $gender   = $this->faker->randomElement(['m', 'f']);
            $birthday = $this->faker->dateTimeBetween('-70 years', '-8 years')->format('Y-m-d');
            $postcode = str_pad((string) $this->faker->numberBetween(10000, 99999), 5, '0', STR_PAD_LEFT);

            $batch[] = [
                'ext_personnel_no' => $this->faker->optional(0.3)->numberBetween(10000, 99999),
                'firstname'        => $this->faker->firstName($gender === 'm' ? 'male' : 'female'),
                'lastname'         => $this->faker->lastName(),
                'email'            => $this->faker->unique()->safeEmail(),
                'phone'            => $this->faker->optional(0.7)->phoneNumber(),
                'birthday'         => $birthday,
                'street'           => $this->faker->streetName(),
                'housenumber'      => $this->faker->buildingNumber(),
                'postcode'         => substr($postcode, 0, 5),
                'city'             => $this->faker->city(),
                'gender'           => $gender,
                'password'         => Hash::make('password'),
                'confirmed'        => 1,
                'wants_newsletter' => $this->faker->optional(0.6)->boolean(),
                'remember_token'   => \Illuminate\Support\Str::random(60),
                'created_at'       => $now,
                'updated_at'       => $now,
            ];

            if (count($batch) >= 100) {
                DB::table('users')->insert($batch);
                $batch = [];
            }
        }

        if ($batch) {
            DB::table('users')->insert($batch);
        }

        // skip id=1 (admin), return the rest
        return DB::table('users')->where('id', '>', 1)->pluck('id')->all();
    }

    // ── Sponsorenläufe ────────────────────────────────────────────────────────

    private function seedRuns(): array
    {
        $this->command->info('Erstelle 5 Sponsorenläufe …');

        $allProjectlistIds = DB::table('projectlists')->pluck('id')->all();

        $runs = [
            [
                'name'        => 'Frühjahrs-Sponsorenlauf 2022',
                'begin'       => '2022-04-02 09:00:00',
                'end'         => '2022-04-02 16:00:00',
                'closed'      => true,
                'with_tshirt' => true,
                'street'      => 'Schulstraße',
                'housenumber' => '5',
                'postcode'    => '70173',
                'city'        => 'Stuttgart',
                'description' => 'Erster Sponsorenlauf unserer Gemeinde. Gesammelte Spenden gingen in die Kinderdörfer in Malawi.',
                'plists'      => array_slice($allProjectlistIds, 0, 2),
            ],
            [
                'name'        => 'Herbst-Sponsorenlauf 2022',
                'begin'       => '2022-10-15 09:00:00',
                'end'         => '2022-10-15 17:00:00',
                'closed'      => true,
                'with_tshirt' => true,
                'street'      => 'Kirchweg',
                'housenumber' => '12',
                'postcode'    => '70173',
                'city'        => 'Stuttgart',
                'description' => 'Herbstlauf mit Schwerpunkt Lateinamerika-Projekte.',
                'plists'      => array_slice($allProjectlistIds, 1, 3),
            ],
            [
                'name'        => 'Jahres-Sponsorenlauf 2023',
                'begin'       => '2023-06-10 09:00:00',
                'end'         => '2023-06-10 16:00:00',
                'closed'      => true,
                'with_tshirt' => false,
                'street'      => 'Parkstraße',
                'housenumber' => '3',
                'postcode'    => '70174',
                'city'        => 'Stuttgart',
                'description' => 'Großer Gemeinschaftslauf mit allen Altersgruppen.',
                'plists'      => array_slice($allProjectlistIds, 0, 4),
            ],
            [
                'name'        => 'Jubiläums-Sponsorenlauf 2024',
                'begin'       => '2024-05-18 09:00:00',
                'end'         => '2024-05-18 17:00:00',
                'closed'      => true,
                'with_tshirt' => true,
                'street'      => 'Festwiese',
                'housenumber' => '1',
                'postcode'    => '70199',
                'city'        => 'Stuttgart',
                'description' => '10. Jubiläumslauf – besonderes Engagement für Haiti und Kenia.',
                'plists'      => $allProjectlistIds,
            ],
            [
                'name'        => 'Sponsorenlauf Frühjahr 2025',
                'begin'       => '2025-04-05 09:00:00',
                'end'         => '2025-04-05 16:00:00',
                'closed'      => false,
                'with_tshirt' => true,
                'street'      => 'Gemeindeweg',
                'housenumber' => '7',
                'postcode'    => '70178',
                'city'        => 'Stuttgart',
                'description' => 'Unser aktueller Lauf – Projekte weltweit unterstützen.',
                'plists'      => $allProjectlistIds,
            ],
        ];

        $created = [];
        foreach ($runs as $runData) {
            $plists = $runData['plists'];
            $closed = $runData['closed'];
            unset($runData['plists'], $runData['closed']);

            $run = SponsoredRun::create($runData);
            // closed ist nicht im $fillable → direkt setzen
            $run->closed = $closed;
            $run->save();

            if ($plists) {
                $run->projectlists()->attach($plists);
            }
            $created[] = $run;
        }

        return $created;
    }

    // ── Teilnahmen & Sponsoren ────────────────────────────────────────────────

    private function seedParticipationsAndSponsors(array $userIds, array $runs): void
    {
        $totalRuns     = count($runs);
        $now           = now()->toDateTimeString();

        foreach ($runs as $idx => $run) {
            $runNo        = $idx + 1;
            $runnerCount  = $this->faker->numberBetween(90, 200);
            $runUserIds   = (array) array_rand(array_flip($userIds), min($runnerCount, count($userIds)));

            // Projektauswahl für diesen Lauf (über die Projektlisten)
            $availableProjectIds = DB::table('projectlist_sponsored_run')
                ->join('project_projectlist', 'projectlist_sponsored_run.projectlist_id', '=', 'project_projectlist.projectlist_id')
                ->where('projectlist_sponsored_run.sponsored_run_id', $run->id)
                ->pluck('project_projectlist.project_id')
                ->unique()
                ->values()
                ->all();

            if (empty($availableProjectIds)) {
                $availableProjectIds = $this->projectIds;
            }

            $this->command->info("Lauf {$runNo}/{$totalRuns}: {$run->name} — {$runnerCount} Läufer …");
            $bar = $this->command->getOutput()->createProgressBar($runnerCount);

            $participationBatch = [];
            $sponsorBatch       = [];
            $usedHashes         = [];

            foreach ($runUserIds as $userId) {
                // Hash (wie im Legacy-Code: md5(microtime()))
                do {
                    $hash = md5(microtime() . $userId . $run->id . random_int(0, PHP_INT_MAX));
                } while (isset($usedHashes[$hash]));
                $usedHashes[$hash] = true;

                $laps      = $this->faker->numberBetween(0, 60);
                $projectId = $this->faker->optional(0.8)->randomElement($availableProjectIds);
                $tshirt    = $run->with_tshirt ? $this->faker->optional(0.7)->randomElement(['XS', 'S', 'M', 'L', 'XL', 'XXL']) : null;

                $participationBatch[] = [
                    'user_id'          => $userId,
                    'sponsored_run_id' => $run->id,
                    'project_id'       => $projectId,
                    'laps'             => $laps,
                    'hash'             => $hash,
                    'tshirt_size'      => $tshirt,
                    'created_at'       => $now,
                    'updated_at'       => $now,
                ];

                $bar->advance();
            }

            // Teilnahmen einfügen
            foreach (array_chunk($participationBatch, 200) as $chunk) {
                DB::table('run_participations')->insert($chunk);
            }

            // Teilnahme-IDs laden
            $runPartIds = DB::table('run_participations')
                ->where('sponsored_run_id', $run->id)
                ->get(['id', 'user_id', 'laps'])
                ->all();

            // Sponsoren generieren
            foreach ($runPartIds as $rp) {
                $sponsorCount = $this->faker->numberBetween(1, 50);
                for ($s = 0; $s < $sponsorCount; $s++) {
                    $type     = $this->faker->randomElement(['per_lap', 'static', 'both']);
                    $perLap   = 0.0;
                    $staticMax = 0.0;

                    match ($type) {
                        'per_lap' => $perLap   = round($this->faker->randomFloat(2, 0.25, 5.00), 2),
                        'static'  => $staticMax = round($this->faker->randomFloat(2, 5.00, 200.00), 2),
                        'both'    => [
                            $perLap    = round($this->faker->randomFloat(2, 0.25, 3.00), 2),
                            $staticMax = round($this->faker->randomFloat(2, 10.00, 100.00), 2),
                        ],
                    };

                    $postcode = str_pad((string) $this->faker->numberBetween(10000, 99999), 5, '0', STR_PAD_LEFT);

                    $sponsorBatch[] = [
                        'run_participation_id' => $rp->id,
                        'user_id'              => $rp->user_id,
                        'firstname'            => $this->faker->firstName(),
                        'lastname'             => $this->faker->lastName(),
                        'street'               => $this->faker->streetName(),
                        'housenumber'          => $this->faker->buildingNumber(),
                        'postcode'             => substr($postcode, 0, 5),
                        'city'                 => $this->faker->city(),
                        'phone'                => $this->faker->optional(0.5)->phoneNumber(),
                        'email'                => $this->faker->optional(0.6)->safeEmail(),
                        'donation_per_lap'     => $perLap,
                        'donation_static_max'  => $staticMax,
                        'wants_newsletter'     => $this->faker->optional(0.5)->boolean(),
                        'ext_personnel_no'     => $this->faker->optional(0.2)->numberBetween(10000, 99999),
                        'created_at'           => $now,
                        'updated_at'           => $now,
                    ];

                    if (count($sponsorBatch) >= 500) {
                        DB::table('sponsors')->insert($sponsorBatch);
                        $sponsorBatch = [];
                    }
                }
            }

            if ($sponsorBatch) {
                DB::table('sponsors')->insert($sponsorBatch);
                $sponsorBatch = [];
            }

            $bar->finish();
            $this->command->newLine();

            $rpCount  = count($runPartIds);
            $sponCount = DB::table('sponsors')
                ->join('run_participations', 'sponsors.run_participation_id', '=', 'run_participations.id')
                ->where('run_participations.sponsored_run_id', $run->id)
                ->count();

            $this->command->line("  → {$rpCount} Teilnahmen, {$sponCount} Sponsoren erstellt.");
        }
    }
}
