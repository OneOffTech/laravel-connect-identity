<?php

namespace Tests\Unit;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class ScaffoldAuthenticationControllersTest extends TestCase
{
    use DatabaseMigrations;

    public function test_scaffold()
    {
        $webRoutesFile = base_path('routes/web.php');

        if (! is_dir($routesDir = dirname($webRoutesFile))) {
            mkdir($routesDir);
        }

        file_put_contents($webRoutesFile, '<?php'.PHP_EOL);

        $exit = Artisan::call('ui:identities', ['--force' => true]);

        $this->assertEquals(0, $exit);

        $this->assertStringContainsString('\Oneofftech\Identities\Facades\Identity::routes();', file_get_contents($webRoutesFile));

        $files = [
            base_path('database/migrations/2020_08_09_115707_create_identities_table.php'),
            app_path('Models/Identity.php'),
            app_path('Http/Controllers/Identities/Auth/ConnectController.php'),
            app_path('Http/Controllers/Identities/Auth/LoginController.php'),
            app_path('Http/Controllers/Identities/Auth/RegisterController.php'),
        ];

        foreach ($files as $file) {
            $exists = File::exists($file);

            $this->assertTrue($exists, "Expected [$file] do not exists.");

            if ($exists) {
                unlink($file);
            }
        }
    }
}
