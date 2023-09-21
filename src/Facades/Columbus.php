<?php

declare(strict_types=1);
namespace Dgtlss\Columbus\Facades;
use Illuminate\Support\Facades\Facade;

class Columbus extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'Columbus';
    }
}
