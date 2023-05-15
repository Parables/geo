<?php

namespace Parables\Geo\Models;

use Illuminate\Database\Eloquent\Model;
use Kalnoy\Nestedset\NodeTrait;

class GeoName extends Model
{
    use NodeTrait;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'geonames';

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];
}
