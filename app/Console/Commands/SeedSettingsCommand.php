<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Actions\Settings\SeedPredefinedSettings;
use Illuminate\Console\Command;

use function Laravel\Prompts\info;
use function Laravel\Prompts\spin;
use function Laravel\Prompts\table;

final class SeedSettingsCommand extends Command
{
    protected $signature = 'settings:seed
                            {--force : Overwrite existing settings with default values}';

    protected $description = 'Seed predefined application settings';

    public function handle(SeedPredefinedSettings $action): int
    {
        $force = (bool) $this->option('force');

        if ($force) {
            $this->warn('⚠️  Force mode enabled - existing settings will be overwritten');
            $this->newLine();
        }

        /** @var array{created: int, skipped: int, updated: int} $result */
        $result = spin(
            callback: fn (): array => $action->handle($force),
            message: 'Seeding predefined settings...'
        );

        if ($result['created'] === 0 && $result['updated'] === 0 && $result['skipped'] > 0) {
            info('All settings already exist. Use --force to overwrite.');

            return self::SUCCESS;
        }

        if ($result['created'] > 0 || $result['updated'] > 0) {
            info('Settings seeded successfully!');
        }

        $this->newLine();

        table(
            headers: ['Action', 'Count'],
            rows: [
                ['Created', (string) $result['created']],
                ['Skipped', (string) $result['skipped']],
                ['Updated', (string) $result['updated']],
            ]
        );

        return self::SUCCESS;
    }
}
