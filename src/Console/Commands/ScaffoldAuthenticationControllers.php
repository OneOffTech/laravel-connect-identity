<?php

namespace Oneofftech\Identities\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;
use RuntimeException;
use SplFileInfo;

class ScaffoldAuthenticationControllers extends Command
{

    /**
     * Default application namespace
     *
     * @var string
     */
    const DEFAULT_NAMESPACE = "App\\";

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
     * The namespace of the application
     *
     * @var string
     */
    protected $namespace = null;

    /**
     * @var Illuminate\Filesystem\Filesystem
     */
    protected $filesystem = null;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        $this->namespace = self::DEFAULT_NAMESPACE;

        $this->filesystem = new Filesystem;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        // TODO: do not replace existing files unless forced

        $this->identifyApplicationNamespace();

        $this->scaffoldAuthenticationControllers();

        $this->scaffoldModels();

        $this->appendRoutes();

        $this->scaffoldMigrations();

        $this->info('Generated identities controllers, models, migrations and routes.');

        return 0;
    }

    protected function identifyApplicationNamespace()
    {
        try {
            $this->namespace = $this->laravel->getNamespace();
            
            if ($this->namespace !== self::DEFAULT_NAMESPACE) {
                $this->comment("Using [$this->namespace] as application namespace.");
            }
        } catch (RuntimeException $ex) {
            $this->warn("Unable to identity the application namespace, assuming [$this->namespace].");
        }
    }

    protected function scaffoldAuthenticationControllers()
    {
        if (! is_dir($directory = app_path('Http/Controllers/Identities/Auth'))) {
            mkdir($directory, 0755, true);
        }

        collect($this->filesystem->allFiles(__DIR__.'/../../../stubs/Identities/Auth'))
            ->each(function (SplFileInfo $file) {
                $this->filesystem->put(
                    app_path('Http/Controllers/Identities/Auth/'.Str::replaceLast('.stub', '.php', $file->getFilename())),
                    $this->compileControllerStub($file->getPathname())
                );
            });
    }

    protected function scaffoldModels()
    {
        if (! is_dir($directory = app_path('Identities'))) {
            mkdir($directory, 0755, true);
        }

        // TODO: make it Models folder aware

        collect($this->filesystem->allFiles(__DIR__.'/../../../stubs/Identities/Models'))
            ->each(function (SplFileInfo $file) {
                $this->filesystem->put(
                    app_path(Str::replaceLast('.stub', '.php', $file->getFilename())),
                    $this->compileModelStub($file->getPathname())
                );
            });
    }

    protected function appendRoutes()
    {
        file_put_contents(
            base_path('routes/web.php'),
            file_get_contents(__DIR__.'/../../../stubs/routes.stub'),
            FILE_APPEND
        );
    }

    protected function scaffoldMigrations()
    {
        copy(
            __DIR__.'/../../../stubs/migrations/2020_08_09_115707_create_identities_table.php',
            base_path('database/migrations/2020_08_09_115707_create_identities_table.php')
        );
    }

    /**
     * Compile the controller stub to respect application namespace
     *
     * @return string
     */
    protected function compileControllerStub($stub)
    {
        return str_replace(
            self::DEFAULT_NAMESPACE,
            $this->namespace,
            file_get_contents($stub)
        );
    }

    /**
     * Compile the controller stub to respect application namespace
     *
     * @return string
     */
    protected function compileModelStub($stub)
    {
        $originalNamespaceDeclaration = str_replace('\\;', ';', "namespace ".self::DEFAULT_NAMESPACE.';');
        $newNamespaceDeclaration = str_replace('\\;', ';', "namespace $this->namespace;");
        
        return str_replace(
            $originalNamespaceDeclaration,
            $newNamespaceDeclaration,
            file_get_contents($stub)
        );
    }
}
