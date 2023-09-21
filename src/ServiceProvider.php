<?php
    namespace Dgtlss\Columbus;

    require_once __DIR__.'/helpers.php';


    class ServiceProvider extends \Illuminate\Support\ServiceProvider {



        public function boot()
        {
            $this->setupConfig(); // Load config
            if ($this->app->runningInConsole()) {
                $this->commands([
                    Commands\Map::class,
                    Commands\Init::class,
                ]);
            }
        }

        public function register()
        {            
            // Add the mappable middleware to the global middleware stack
            $this->app['router']->aliasMiddleware('mappable', \Dgtlss\Columbus\Middleware\Mappable::class);

            // Register a sitemap route if one doesn't already exist
            if(!$this->app['router']->has('sitemap')){
                $this->app['router']->get('sitemap', function(){
                    return response()->file(public_path('sitemap.xml'), [
                        'Content-Type' => 'application/xml'
                    ]);
                })->name('sitemap');
            }

        }

        protected function setupConfig(){

            $configPath = __DIR__ . '/../config/columbus.php';
            $this->publishes([$configPath => $this->getConfigPath()], 'config');
    
        }

        protected function getConfigPath()
        {
            return config_path('columbus.php');
        }

        protected function publishConfig($configPath)
        {
            $this->publishes([$configPath => config_path('columbus.php')], 'config');
        }


    }
