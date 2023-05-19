<?php

namespace Parables\Geo\Models;

use Illuminate\Database\Eloquent\Model;
use Kalnoy\Nestedset\NodeTrait;
use Parables\Geo\GeoNameTrait;

class GeoName extends Model
{
    use NodeTrait;
    use GeoNameTrait;

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
