<?php

namespace Dgtlss\Columbus\Commands;

use Illuminate\Console\Command;

use Carbon\Carbon;
use Route;
use Cache;

class Init extends Command
{
    protected $signature = 'columbus:init';
    protected $description = 'Setup Columbus for use in your application';

    protected $version;

    public function __construct()
    {
        parent::__construct();
		$this->version = json_decode(file_get_contents(__DIR__.'/../../composer.json'))->version;		
    }

    public function handle()
    {

        $this->info('🧭 Columbus Initialising...');
        
        // Publish the config file
        $this->call('vendor:publish', [
            '--provider' => "Dgtlss\Columbus\ServiceProvider",
            '--tag' => "config",
        ]);

        $this->info('📝 Columbus config file published!');

        // Add the mappable middleware to the global middleware stack
        $this->call('make:middleware', [
            'name' => "Mappable",
        ]);

        // Open the web.php routes file
        $routesFile = file_get_contents(base_path('routes/web.php'));

        // Add an empty route group with a Mappable middleware to the bottom of the file
        $routesFile .= "\nRoute::middleware(['Mappable'])->group(function(){\n\t/* routes in this group will be added to the sitemap */\n});";

        // Add a line to the bottom of the file for the sitemap url
        $routesFile .= "\nRoute::get('sitemap', function(){\n\treturn response()->file(public_path('sitemap.xml'), [\n\t\t'Content-Type' => 'application/xml'\n\t]);\n})->name('sitemap');";

        // Save the file
        file_put_contents(base_path('routes/web.php'), $routesFile);

        $this->info('📝 Mappable middleware added to global middleware stack!');

        $this->info('🧭 Columbus initialised successfully!');

    }
}