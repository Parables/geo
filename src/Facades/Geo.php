<?php

namespace Parables\Geo\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Parables\Geo\Geo
 */
class Geo extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \Parables\Geo\Geo::class;
    }
}
