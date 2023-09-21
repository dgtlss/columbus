<?php

namespace Dgtlss\Columbus\Commands;

use Illuminate\Console\Command;

use Carbon\Carbon;
use Route;
use Cache;

class Map extends Command
{
    protected $signature = 'columbus:map';
    protected $description = 'Search your application for routes to generate a sitemap';

    protected $version;

    protected $allowedMethods;

    public function __construct()
    {
        parent::__construct();
		$this->version = json_decode(file_get_contents(__DIR__.'/../../composer.json'))->version;		
        $this->allowedMethods = config('columbus.allowed_methods');
    }

    public function handle()
    {
        $start = microtime(true); // Start a timer
        $this->info('🧭 Columbus Starting...');

        $this->info('🔍 Searching for routes...');

        // Get all of the routes from the project
        $routes = Route::getRoutes();
        
        // Loop through the routes and workout which ones are mappable
        $mappableRoutes = 0;
        $routesTable = [];
        foreach($routes as $route){
            $middleware = $route->middleware();
            if(in_array('Mappable', $middleware)){
                $routesTable[] = [
                    'uri' => $route->uri(),
                    'name' => $route->getName(),
                    'methods' => implode(', ', $route->methods()),
                    'middleware' => implode(', ', $route->middleware()),
                ];
                $mappableRoutes++;
            }
        }

        // if we haven't found any mappable routes, then we can't continue. End the command here.
        if($mappableRoutes == 0){
            $this->error('🚫 No routes found, please check your routes for the "mappable" middleware and try again');
            return;
        }

        $this->info('📝 Found '.$mappableRoutes.' eligible routes');

        // Show a table of all the mapped routes
        $this->table([
            'URI',
            'Name',
            'Methods',
            'Middleware',
        ], $routesTable);

        $this->info('📝 Generating sitemap...');

        $sitemap = '<?xml version="1.0" encoding="UTF-8"?>'.PHP_EOL;
        $sitemap .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'.PHP_EOL;

        foreach($routes as $route){
            if(in_array('Mappable', $route->middleware())){
                if(in_array(config('columbus.allowed_methods'), $route->methods())){
                    if($route->getName() && $route->getName() != 'columbus.map'){
                        $sitemap .= '    <url>'.PHP_EOL;
                        $sitemap .= '        <loc>'.url($route->uri()).'</loc>'.PHP_EOL;
                        $sitemap .= '        <lastmod>'.Carbon::now()->toAtomString().'</lastmod>'.PHP_EOL;
                        $sitemap .= '        <changefreq>daily</changefreq>'.PHP_EOL;
                        $sitemap .= '        <priority>0.5</priority>'.PHP_EOL;
                        $sitemap .= '    </url>'.PHP_EOL;
                    }
                }
            }
        }

        $sitemap .= '</urlset>';

        $this->info('📝 Sitemap generated with '.$mappableRoutes. ' routes');

        $this->info('💾 Saving sitemap...');

        file_put_contents(public_path('sitemap.xml'), $sitemap);

        $this->info('💾 Sitemap saved');

        $this->info('👍 Done in '.round(microtime(true) - $start, 2).'s');

        if(config('columbus.notifications')){
            $this->notify('💚 Columbus Finished Successfully! ' ,'Completed in: '.round(microtime(true) - $start, 2).'s',__DIR__.'/../../columbus.png');
        }

    }
}