<?php

namespace Oneofftech\Identities\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;
use SplFileInfo;

class ScaffoldAuthenticationControllers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ui:identities';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Scaffold login and registration via social/oauth identities routes, controllers and views';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {

        // scaffold controllers

        if (! is_dir($directory = app_path('Http/Controllers/Identities/Auth'))) {
            mkdir($directory, 0755, true);
        }

        $filesystem = new Filesystem;

        collect($filesystem->allFiles(__DIR__.'/../../../stubs/Identities/Auth'))
            ->each(function (SplFileInfo $file) use ($filesystem) {
                $filesystem->copy(
                    $file->getPathname(),
                    app_path('Http/Controllers/Identities/Auth/'.Str::replaceLast('.stub', '.php', $file->getFilename()))
                );
            });

        $this->info('Authentication scaffolding generated successfully.');

        if (! is_dir($directory = app_path('Identities'))) {
            mkdir($directory, 0755, true);
        }

        collect($filesystem->allFiles(__DIR__.'/../../../stubs/Identities/Models'))
            ->each(function (SplFileInfo $file) use ($filesystem) {
                $filesystem->copy(
                    $file->getPathname(),
                    app_path(Str::replaceLast('.stub', '.php', $file->getFilename()))
                );
            });

        $this->info('Model scaffolding generated successfully.');

        // add routes registration

        file_put_contents(
            base_path('routes/web.php'),
            file_get_contents(__DIR__.'/../../../stubs/routes.stub'),
            FILE_APPEND
        );

        $this->info('Routes appended to web.php.');

        // scaffold migration

        copy(
            __DIR__.'/../../../stubs/migrations/2020_08_09_115707_create_identities_table.php',
            base_path('database/migrations/2020_08_09_115707_create_identities_table.php')
        );

        $this->info('Added identities migration.');

        return 0;
    }
}
