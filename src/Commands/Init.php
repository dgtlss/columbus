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

        $this->info('📝 Mappable middleware added to global middleware stack!');

        $this->info('🧭 Columbus initialised successfully!');

    }
}