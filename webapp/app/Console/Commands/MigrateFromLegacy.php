<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

class MigrateFromLegacy extends Command
{
    protected $signature = 'sponrun:migrate-from-legacy {--dry-run : Show SQL without executing}';
    protected $description = 'Migrate a legacy SponRun (Laravel 5.5 / Entrust) database to the new schema. Run `php artisan migrate` first.';

    private bool $dryRun;

    public function handle(): int
    {
        $this->dryRun = (bool) $this->option('dry-run');

        if ($this->dryRun) {
            $this->warn('DRY RUN — no changes will be made.');
        }

        $this->info('Recommended workflow:');
        $this->line('  1. php artisan migrate                      (schema: new tables + Spatie pivots)');
        $this->line('  2. php artisan sponrun:migrate-from-legacy  (data: backfill + Entrust → Spatie)');
        $this->info('');

        $this->step1EmailVerifiedAt();
        $this->step2PasswordResetTokens();
        $this->step3SpatiePermissions();
        $this->step4WantsNewsletterSponsor();

        $this->info('');
        $this->info('Migration complete.');

        return Command::SUCCESS;
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
            $this->line('  email_verified_at already exists — skipping add.');
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
        } else {
            $this->line('  Already using password_reset_tokens or no password_resets table — skipping.');
        }
    }

    private function step3SpatiePermissions(): void
    {
        $this->info('Step 3: Transform Entrust → Spatie (data only — schema was handled by migrate) ...');

        if (!Schema::hasTable('model_has_roles')) {
            $this->error('  model_has_roles missing — run `php artisan migrate` first, then re-run this command.');
            return;
        }

        // 3a. Ensure roles.guard_name is populated (migration may have added the column already)
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

        // 3b. Ensure permissions.guard_name is populated
        if (Schema::hasTable('permissions') && !Schema::hasColumn('permissions', 'guard_name')) {
            $this->exec(
                "ALTER TABLE permissions ADD COLUMN guard_name VARCHAR(255) NOT NULL DEFAULT 'web' AFTER name",
                fn () => Schema::table('permissions', fn (Blueprint $t) => $t->string('guard_name')->default('web')->after('name'))
            );
        }

        // 3c. Copy role_user → model_has_roles
        if (Schema::hasTable('role_user')) {
            $rows = DB::table('role_user')->get();
            $this->line("  Migrating {$rows->count()} role assignments from role_user ...");
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

        // 3d. Copy permission_role → role_has_permissions
        if (Schema::hasTable('permission_role') && Schema::hasTable('role_has_permissions')) {
            $rows = DB::table('permission_role')->get();
            $this->line("  Migrating {$rows->count()} role-permission assignments from permission_role ...");
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

    private function exec(string $description, \Closure $action): void
    {
        if ($this->dryRun) {
            $this->line("  [DRY] $description");
        } else {
            $action();
        }
    }
}
