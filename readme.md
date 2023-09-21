# 🧭 Columbus 

Columbus is a lightweight composer package that allows you to quickly and easily generate sitemaps for your Laravel application. 

## Installation

You can install the package via composer:
    
```
composer require dgtlss/columbus
```

Once installed you can publish the config file and generate the middleware required for Columbus to work. The middleware tells Columbus which routes should be added to the sitemap, and which should be ignored.

```
php artisan columbus:init
```

Once you have initialised Columbus you will need to add the middleware to your `app/Http/Kernel.php` file. You can do this by adding the following line to the `$routeMiddleware` array:

```
'Mappable' => \App\Http\Middleware\mappable::class,
```

Now that the middleware has been added to your laravel application you can generate your sitemap by running the following command:

```
php artisan columbus:map
```

This will generate a `sitemap.xml` file in your public directory. This will now be available by going to `yourdomain.test/sitemap`

## To Do

- [ ] Add support for multiple sitemaps
- [ ] Add support for sitemap index files
- [ ] Add support for sitemap caching
- [ ] Add support for dynamic routes (e.g. blog posts)