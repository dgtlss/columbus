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
        $variableRoutes = 0;
        $routesTable = [];
        foreach($routes as $route){
            $middleware = $route->middleware();
            if(in_array('Mappable', $middleware)){
                $routesTable[] = [
                    'uri' => $route->uri(),
                    'name' => $route->getName(),
                    'methods' => implode(', ', $route->methods()),
                    'actions' => $route->getActionName(),
                    'middleware' => implode(', ', $route->middleware()),
                ];
                $mappableRoutes++;
                // check if the route has any variables in it
                if(strpos($route->uri(), '{') !== false){
                    $variableRoutes++;
                }
            }
        }

        // if we haven't found any mappable routes, then we can't continue. End the command here.
        if($mappableRoutes == 0){
            $this->error('🚫 No routes found, please check your routes for the "Mappable" middleware and try again');
            return;
        }

        $this->info('📝 Found '.$mappableRoutes.' eligible routes');

        if($variableRoutes != 0){
            $this->info('📝 Found '.$variableRoutes.' dynamic routes with variables');
        }

        // Show a table of all the mapped routes
        $this->table([
            'URI',
            'Name',
            'Methods',
            'Actions',
            'Middleware',
        ], $routesTable);

        $this->info('📝 Generating sitemap...');

        $sitemap = '<?xml version="1.0" encoding="UTF-8"?>'.PHP_EOL;
        $sitemap .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'.PHP_EOL;

        $removedLinks = 0;
        foreach($routes as $route){
            if(in_array('Mappable', $route->middleware())){
                $allowedMethods = array_intersect(config('columbus.allowed_methods'), $route->methods());
                if($allowedMethods != []){
                    if($route->getName() && $route->getName() != 'columbus.map'){
                        $sitemap .= '    <url>'.PHP_EOL;
                        $sitemap .= '        <loc>'.url($route->uri()).'</loc>'.PHP_EOL;
                        $sitemap .= '        <lastmod>'.Carbon::now()->toAtomString().'</lastmod>'.PHP_EOL;
                        $sitemap .= '        <changefreq>daily</changefreq>'.PHP_EOL;
                        $sitemap .= '        <priority>0.5</priority>'.PHP_EOL;
                        $sitemap .= '    </url>'.PHP_EOL;
                    }
                }else{
                    $removedLinks++;
                }
            }
        }

        $sitemap .= '</urlset>';

        $totalMappedRoutes = $mappableRoutes - $removedLinks;

        $this->info('📝 Sitemap generated with '.$totalMappedRoutes. ' routes');
        $removedLinks != 0 ? $this->info('📝 Removed '.$removedLinks. ' routes because of method restrictions') : '';

        $this->info('💾 Saving sitemap...');

        file_put_contents(public_path('sitemap.xml'), $sitemap);

        $this->info('💾 Sitemap saved');

        $this->info('👍 Done in '.round(microtime(true) - $start, 2).'s');

        if(config('columbus.notifications')){
            $this->notify('💚 Columbus Finished Successfully! ' ,'Completed in: '.round(microtime(true) - $start, 2).'s',__DIR__.'/../../columbus.png');
        }
    }
}