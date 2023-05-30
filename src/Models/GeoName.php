<?php

declare(strict_types=1);

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
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'id',
        'name',
        'ascii_name',
        'alternate_names',
        'latitude',
        'longitude',
        'feature_code',
        'feature_class',
        'country_code',
        'cc2',
        'admin1_code',
        'admin2_code',
        'admin3_code',
        'admin4_code',
        'population',
        'elevation',
        'dem',
        'timezone',
        'modification_date',
        'created_at',
        'updated_at',
        'lft',
        'rgt',
        'parent_id',
    ];
}
