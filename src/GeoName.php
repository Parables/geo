<?php

namespace Parables\Geo;

use DomainException;

class GeoName
{
    private function __construct(
        private string $id,
        private string $name,
        private string $ascii_name,
        private string $alternate_names,
        private string $latitude,
        private string $longitude,
        private string $feature_class,
        private string $feature_code,
        private string $country_code,
        private string $cc2,
        private string $admin1_code,
        private string $admin2_code,
        private string $admin3_code,
        private string $admin4_code,
        private string $population,
        private string $elevation,
        private string $dem,
        private string $timezone,
        private string $modification_date
    ) {
    }

    public static function fromLine(string $line = null): GeoName
    {
        $data = explode("\t", $line);

        throw_if(count($data) !== 19, new DomainException('Some parts are missing from the line'));

        $data = array_map('trim', $data);

        return new  static(
            id: $data[0],
            name: $data[1],
            ascii_name: $data[2],
            alternate_names: $data[3],
            latitude: $data[4],
            longitude: $data[5],
            feature_class: $data[6],
            feature_code: $data[7],
            country_code: $data[8],
            cc2: $data[9],
            admin1_code: $data[10],
            admin2_code: $data[11],
            admin3_code: $data[12],
            admin4_code: $data[13],
            population: $data[14],
            elevation: $data[15],
            dem: $data[16],
            timezone: $data[17],
            modification_date: $data[18],
        );
    }


    public function id(): string
    {
        return $this->id;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function asciiName(): string
    {
        return $this->ascii_name;
    }

    public function alternateNames(): string
    {
        return $this->alternate_names;
    }

    public function latitude(): string
    {
        return $this->latitude;
    }

    public function longitude(): string
    {
        return $this->longitude;
    }

    public function featureClass(): string
    {
        return $this->feature_class;
    }

    public function featureCode(): string
    {
        return $this->feature_code;
    }

    public function countryCode(): string
    {
        return $this->country_code;
    }

    public function cc2(): string
    {
        return $this->cc2;
    }

    public function admin1Code(): string
    {
        return $this->admin1_code;
    }

    public function admin2Code(): string
    {
        return $this->admin2_code;
    }

    public function admin3Code(): string
    {
        return $this->admin3_code;
    }

    public function admin4Code(): string
    {
        return $this->admin4_code;
    }

    public function population(): string
    {
        return $this->population;
    }

    public function elevation(): string
    {
        return $this->elevation;
    }

    public function dem(): string
    {
        return $this->dem;
    }

    public function timezone(): string
    {
        return $this->timezone;
    }

    public function modificationDate(): string
    {
        return $this->modification_date;
    }
}


/*
 * The main 'geoname' table has the following fields :
---------------------------------------------------
geonameid         : integer id of record in geonames database
name              : name of geographical point (utf8) varchar(200)
asciiname         : name of geographical point in plain ascii characters, varchar(200)
alternatenames    : alternatenames, comma separated, ascii names automatically transliterated, convenience attribute from alternatename table, varchar(10000)
latitude          : latitude in decimal degrees (wgs84)
longitude         : longitude in decimal degrees (wgs84)
feature class     : see http://www.geonames.org/export/codes.html, char(1)
feature code      : see http://www.geonames.org/export/codes.html, varchar(10)
country code      : ISO-3166 2-letter country code, 2 characters
cc2               : alternate country codes, comma separated, ISO-3166 2-letter country code, 200 characters
admin1 code       : fipscode (subject to change to iso code), see exceptions below, see file admin1Codes.txt for display names of this code; varchar(20)
admin2 code       : code for the second administrative division, a county in the US, see file admin2Codes.txt; varchar(80)
admin3 code       : code for third level administrative division, varchar(20)
admin4 code       : code for fourth level administrative division, varchar(20)
population        : bigint (8 byte int)
elevation         : in meters, integer
dem               : digital elevation model, srtm3 or gtopo30, average elevation of 3''x3'' (ca 90mx90m) or 30''x30'' (ca 900mx900m) area in meters, integer. srtm processed by cgiar/ciat.
timezone          : the iana timezone id (see file timeZone.txt) varchar(40)
modification date : date of last modification in yyyy-MM-dd format

 */
