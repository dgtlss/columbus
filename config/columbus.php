<?php
/*
 * Columbus v1.0.0
 *
 * (c) Nathan Langer (dgtlss) <nathanlanger@googlemail.com> 2023-2024
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
*/

return[

    /*
    |--------------------------------------------------------------------------
    | Notifications
    |--------------------------------------------------------------------------
    |
    | Send a desktop notification when sitemap has been generated
    | If you don't want to use this feature, set this to false.
    | Please Note: Columbus will still feedback to the console even if this option is set to false
    |
    */

    'notifications' => true,

    /*
    |--------------------------------------------------------------------------
    | Allowed Methods
    |--------------------------------------------------------------------------
    |
    | The HTTP methods that are allowed to be mapped for use in the sitemap.
    | By default only GET requests are mapped, but you can add more here.
    | Example: ['GET', 'POST', 'PUT', 'PATCH', 'DELETE']
    */

    'allowed_methods' => [
        'GET',
    ],


];