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
    const DEFAULT_NAMESPACE = 'App\\';

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ui:identities {--force : Overwrite existing files}';

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
     * Models namespace
     *
     * @var string
     */
    protected $modelNamespace = null;

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

        $this->modelNamespace = self::DEFAULT_NAMESPACE;

        $this->filesystem = new Filesystem;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
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

            $this->modelNamespace = is_dir(app_path('Models')) ? $this->namespace.'\\Models' : $this->namespace;
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
                $controllerName = Str::replaceLast('.stub', '.php', $file->getFilename());
                $controller = app_path("Http/Controllers/Identities/Auth/$controllerName");

                if (file_exists($controller) && ! $this->option('force')) {
                    if ($this->confirm("The [$controllerName] file already exists. Do you want to replace it?")) {
                        $this->filesystem->put(
                            $controller,
                            $this->compileControllerStub($file->getPathname())
                        );
                    }
                } else {
                    $this->filesystem->put(
                        $controller,
                        $this->compileControllerStub($file->getPathname())
                    );
                }
            });
    }

    protected function scaffoldModels()
    {
        if (! is_dir($directory = app_path('Identities'))) {
            mkdir($directory, 0755, true);
        }

        $useModelsDirectory = is_dir(app_path('Models'));

        collect($this->filesystem->allFiles(__DIR__.'/../../../stubs/Identities/Models'))
            ->each(function (SplFileInfo $file) use ($useModelsDirectory) {
                $modelName = Str::replaceLast('.stub', '.php', $file->getFilename());
                $model = $useModelsDirectory ? app_path('Models/'.$modelName) : app_path($modelName);

                if (file_exists($model) && ! $this->option('force')) {
                    if ($this->confirm("The [$modelName] file already exists. Do you want to replace it?")) {
                        $this->filesystem->put(
                            $model,
                            $this->compileModelStub($file->getPathname())
                        );
                    }
                } else {
                    $this->filesystem->put(
                        $model,
                        $this->compileModelStub($file->getPathname())
                    );
                }
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
        $originalNamespaceDeclaration = str_replace('\\;', ';', 'namespace '.self::DEFAULT_NAMESPACE.';');
        $newNamespaceDeclaration = str_replace('\\;', ';', "namespace $this->modelNamespace;");

        return str_replace(
            $originalNamespaceDeclaration,
            $newNamespaceDeclaration,
            file_get_contents($stub)
        );
    }
}
