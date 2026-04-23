<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

class MigrateFromLegacy extends Command
{
    protected $signature = 'sponrun:migrate-from-legacy {--dry-run : Show SQL without executing}';
    protected $description = 'Migrate a legacy SponRun (Laravel 5.5 / Entrust) database to the new schema';

    private bool $dryRun;

    public function handle(): int
    {
        $this->dryRun = (bool) $this->option('dry-run');

        if ($this->dryRun) {
            $this->warn('DRY RUN — no changes will be made.');
        }

        $this->step0SyncMigrationsTable();
        $this->step1EmailVerifiedAt();
        $this->step2PasswordResetTokens();
        $this->step3SpatiePermissions();
        $this->step4WantsNewsletterSponsor();

        $this->info('');
        $this->info('Migration complete. Run `php artisan migrate` to apply remaining schema changes.');

        return Command::SUCCESS;
    }

    /**
     * Pre-populate the migrations table so `php artisan migrate` does not attempt to
     * re-create tables that already exist in the legacy database.
     *
     * Laravel tracks executed migrations by filename. The legacy app used different
     * filenames (e.g. 2016_06_01_000000_create_users_table) than the new consolidated
     * migrations (0001_01_01_000000_create_users_table). Without this step, running
     * `php artisan migrate` would fail with "table already exists" for every table.
     *
     * The Spatie permission migration is also marked here because the legacy Entrust
     * setup already created `roles` and `permissions`. The missing Spatie-specific
     * pivot tables (model_has_roles etc.) are created directly in step 3.
     */
    private function step0SyncMigrationsTable(): void
    {
        $this->info('Step 0: Sync migrations table for legacy schema compatibility ...');

        if (!Schema::hasTable('migrations')) {
            $this->warn('  No migrations table found — assuming fresh install, skipping.');
            return;
        }

        // Maps new migration filename → the primary table it creates.
        // Only entries where the table is expected to already exist in the legacy DB.
        $tableMap = [
            '0001_01_01_000000_create_users_table'                     => 'users',
            '2024_01_01_000010_create_sponsored_runs_table'            => 'sponsored_runs',
            '2024_01_01_000020_create_projects_table'                  => 'projects',
            '2024_01_01_000030_create_projectlists_table'              => 'projectlists',
            '2024_01_01_000040_create_project_projectlist_table'       => 'project_projectlist',
            '2024_01_01_000050_create_projectlist_sponsored_run_table' => 'projectlist_sponsored_run',
            '2024_01_01_000060_create_run_participations_table'        => 'run_participations',
            '2024_01_01_000070_create_sponsors_table'                  => 'sponsors',
            // Spatie migration creates roles+permissions (exist via Entrust) plus the
            // new pivot tables. We mark it as done here; pivot tables are created in step 3.
            '2024_01_01_000080_create_permission_tables'               => 'roles',
        ];

        $batch = DB::table('migrations')->max('batch') ?? 0;
        $marked = 0;

        foreach ($tableMap as $migration => $table) {
            if (DB::table('migrations')->where('migration', $migration)->exists()) {
                $this->line("  $migration — already recorded, skipping.");
                continue;
            }

            if (!Schema::hasTable($table)) {
                $this->line("  $migration — table '$table' not found, will run via migrate.");
                continue;
            }

            $this->line("  $migration — marking as pre-existing (table '$table' exists).");
            $this->exec(
                "INSERT INTO migrations (migration, batch) VALUES ('$migration', $batch)",
                function () use ($migration, $batch): void {
                    DB::table('migrations')->insert(['migration' => $migration, 'batch' => $batch]);
                }
            );
            $marked++;
        }

        $this->line("  Done: $marked migration(s) marked as pre-existing.");
    }

    private function step1EmailVerifiedAt(): void
    {
        $this->info('Step 1: Add email_verified_at and backfill from confirmed=1 ...');

        if (!Schema::hasColumn('users', 'email_verified_at')) {
            $this->exec(
                'ALTER TABLE users ADD COLUMN email_verified_at TIMESTAMP NULL AFTER email',
                fn () => Schema::table('users', fn (Blueprint $t) => $t->timestamp('email_verified_at')->nullable()->after('email'))
            );
        } else {
            $this->line('  email_verified_at already exists — skipping.');
        }

        $count = DB::table('users')->where('confirmed', 1)->whereNull('email_verified_at')->count();
        $this->line("  Backfilling $count verified users ...");
        $this->exec(
            "UPDATE users SET email_verified_at = updated_at WHERE confirmed = 1 AND email_verified_at IS NULL",
            fn () => DB::table('users')
                ->where('confirmed', 1)
                ->whereNull('email_verified_at')
                ->update(['email_verified_at' => DB::raw('updated_at')])
        );
    }

    private function step2PasswordResetTokens(): void
    {
        $this->info('Step 2: Rename password_resets → password_reset_tokens if needed ...');

        if (Schema::hasTable('password_resets') && !Schema::hasTable('password_reset_tokens')) {
            $this->exec(
                'RENAME TABLE password_resets TO password_reset_tokens',
                fn () => Schema::rename('password_resets', 'password_reset_tokens')
            );

            // Mark the Breeze password_reset_tokens migration as done too.
            if (!DB::table('migrations')->where('migration', '0001_01_01_000001_create_password_reset_tokens_table')->exists()) {
                $batch = DB::table('migrations')->max('batch') ?? 0;
                $this->exec(
                    "INSERT INTO migrations (migration, batch) VALUES ('0001_01_01_000001_create_password_reset_tokens_table', $batch)",
                    fn () => DB::table('migrations')->insert([
                        'migration' => '0001_01_01_000001_create_password_reset_tokens_table',
                        'batch'     => $batch,
                    ])
                );
            }
        } else {
            $this->line('  Already using password_reset_tokens or no password_resets table — skipping.');
        }
    }

    private function step3SpatiePermissions(): void
    {
        $this->info('Step 3: Transform Entrust → Spatie permission tables ...');

        // 3a. Ensure roles.guard_name exists
        if (!Schema::hasColumn('roles', 'guard_name')) {
            $this->exec(
                "ALTER TABLE roles ADD COLUMN guard_name VARCHAR(255) NOT NULL DEFAULT 'web' AFTER name",
                fn () => Schema::table('roles', fn (Blueprint $t) => $t->string('guard_name')->default('web')->after('name'))
            );
        }
        $this->exec(
            "UPDATE roles SET guard_name = 'web' WHERE guard_name IS NULL OR guard_name = ''",
            fn () => DB::table('roles')
                ->where(fn ($q) => $q->whereNull('guard_name')->orWhere('guard_name', ''))
                ->update(['guard_name' => 'web'])
        );

        // 3b. Ensure permissions.guard_name exists
        if (Schema::hasTable('permissions') && !Schema::hasColumn('permissions', 'guard_name')) {
            $this->exec(
                "ALTER TABLE permissions ADD COLUMN guard_name VARCHAR(255) NOT NULL DEFAULT 'web' AFTER name",
                fn () => Schema::table('permissions', fn (Blueprint $t) => $t->string('guard_name')->default('web')->after('name'))
            );
        }

        // 3c. Create missing Spatie pivot tables directly (they are not in the legacy Entrust schema).
        //     We cannot rely on `php artisan migrate` for these because the Spatie migration
        //     also tries to CREATE TABLE roles/permissions — which already exist.
        $this->createSpatieTableIfMissing(
            'model_has_roles',
            function (Blueprint $table): void {
                $table->unsignedBigInteger('role_id');
                $table->string('model_type');
                $table->unsignedBigInteger('model_id');
                $table->index(['model_id', 'model_type'], 'model_has_roles_model_id_model_type_index');
                $table->foreign('role_id')->references('id')->on('roles')->onDelete('cascade');
                $table->primary(['role_id', 'model_id', 'model_type']);
            }
        );

        $this->createSpatieTableIfMissing(
            'model_has_permissions',
            function (Blueprint $table): void {
                $table->unsignedBigInteger('permission_id');
                $table->string('model_type');
                $table->unsignedBigInteger('model_id');
                $table->index(['model_id', 'model_type'], 'model_has_permissions_model_id_model_type_index');
                $table->foreign('permission_id')->references('id')->on('permissions')->onDelete('cascade');
                $table->primary(['permission_id', 'model_id', 'model_type']);
            }
        );

        $this->createSpatieTableIfMissing(
            'role_has_permissions',
            function (Blueprint $table): void {
                $table->unsignedBigInteger('permission_id');
                $table->unsignedBigInteger('role_id');
                $table->foreign('permission_id')->references('id')->on('permissions')->onDelete('cascade');
                $table->foreign('role_id')->references('id')->on('roles')->onDelete('cascade');
                $table->primary(['permission_id', 'role_id']);
            }
        );

        // 3d. Copy role_user → model_has_roles
        if (Schema::hasTable('role_user')) {
            $rows = DB::table('role_user')->get();
            $this->line("  Migrating {$rows->count()} role assignments ...");
            foreach ($rows as $row) {
                $this->exec(
                    "INSERT IGNORE INTO model_has_roles (role_id, model_type, model_id) VALUES ({$row->role_id}, 'App\\\\Models\\\\User', {$row->user_id})",
                    fn () => DB::table('model_has_roles')->insertOrIgnore([
                        'role_id'    => $row->role_id,
                        'model_type' => 'App\\Models\\User',
                        'model_id'   => $row->user_id,
                    ])
                );
            }
        } else {
            $this->line('  No role_user table found — skipping.');
        }

        // 3e. Copy permission_role → role_has_permissions
        if (Schema::hasTable('permission_role') && Schema::hasTable('role_has_permissions')) {
            $rows = DB::table('permission_role')->get();
            $this->line("  Migrating {$rows->count()} role-permission assignments ...");
            foreach ($rows as $row) {
                $this->exec(
                    "INSERT IGNORE INTO role_has_permissions (permission_id, role_id) VALUES ({$row->permission_id}, {$row->role_id})",
                    fn () => DB::table('role_has_permissions')->insertOrIgnore([
                        'permission_id' => $row->permission_id,
                        'role_id'       => $row->role_id,
                    ])
                );
            }
        }
    }

    private function step4WantsNewsletterSponsor(): void
    {
        $this->info('Step 4: Ensure wants_newsletter column exists on sponsors ...');

        if (!Schema::hasColumn('sponsors', 'wants_newsletter')) {
            $this->exec(
                'ALTER TABLE sponsors ADD COLUMN wants_newsletter TINYINT(1) NULL',
                fn () => Schema::table('sponsors', fn (Blueprint $t) => $t->boolean('wants_newsletter')->nullable())
            );
        } else {
            $this->line('  Already present — skipping.');
        }
    }

    private function createSpatieTableIfMissing(string $tableName, \Closure $blueprint): void
    {
        if (Schema::hasTable($tableName)) {
            $this->line("  $tableName already exists — skipping.");
            return;
        }

        $this->line("  Creating $tableName ...");
        $this->exec(
            "CREATE TABLE $tableName (...)",
            fn () => Schema::create($tableName, $blueprint)
        );
    }

    private function exec(string $description, \Closure $action): void
    {
        if ($this->dryRun) {
            $this->line("  [DRY] $description");
        } else {
            $action();
        }
    }
}
