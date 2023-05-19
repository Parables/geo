<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('geonames', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('name');
            $table->string('ascii_name');
            $table->longText('alternate_names');
            $table->string('latitude');
            $table->string('longitude');
            $table->string('feature_code');
            $table->string('feature_class');
            $table->string('country_code');
            $table->string('cc2')->nullable();
            $table->string('admin1_code')->nullable();
            $table->string('admin2_code')->nullable();
            $table->string('admin3_code')->nullable();
            $table->string('admin4_code')->nullable();
            $table->string('population')->nullable();
            $table->string('elevation')->nullable();
            $table->string('dem')->nullable();
            $table->string('timezone')->nullable();
            $table->string('modification_date')->nullable();
            $table->timestamps();
            $table->nestedSet();
        });
    }
    public function down()
    {
        Schema::table('table', function (Blueprint $table) {
            $table->dropNestedSet();
        });
    }
};
