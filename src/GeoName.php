<?php

declare(strict_types=1);

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
        private string $modification_date,
        private int|string $lft,
        private int|string $rgt,
        private int|string $depth,
        private string $parent_id,
    ) {
    }



    /**
     * integer id of record in geonames database
     * */
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

    private function lft(): int

    {
        return intval($this->lft);
    }

    private function rgt(): int
    {
        return intval($this->rgt);
    }

    private function depth(): int
    {
        return intval($this->depth);
    }

    private function parentId(): string
    {
        return $this->parent_id;
    }

    public function isEarth(): bool
    {
        return $this->id === "6295630" || $this->name === 'Earth';
    }

    public function isContinent(): bool
    {
        return $this->feature_class === 'L' && $this->feature_code === 'CONT';
    }

    public function isCountry(): bool
    {
        return $this->feature_class === 'A' && str_starts_with($this->feature_code, 'PCL');
    }

    public function isStateRegion(): bool
    {
        return $this->feature_class === 'A' && $this->feature_code === 'ADM1';
    }

    public function belongsToADM1(): bool
    {
        return !empty($this->admin1_code) && (empty($this->admin2_code) && empty($this->admin3_code) && empty($this->admin4_code));
    }

    public function isADM2(): bool
    {
        return $this->feature_class === 'A' && $this->feature_code === 'ADM2';
    }

    public function belongsToADM2(): bool
    {
        return (!empty($this->admin1_code) && !empty($this->admin2_code)) && (empty($this->admin3_code) && empty($this->admin4_code));
    }

    public function isADM3(): bool
    {
        return $this->feature_class === 'A' && $this->feature_code === 'ADM3';
    }

    public function belongsToADM3(): bool
    {
        return  !empty($this->admin3_code);
    }

    public function isADM4(): bool
    {
        return $this->feature_class === 'A' && $this->feature_code === 'ADM4';
    }

    public function belongsToADM4(): bool
    {
        return !empty($this->admin4_code);
    }


    public function isADM5(): bool
    {
        return $this->feature_class === 'A' && $this->feature_code === 'ADM5';
    }

    public  function isCityTown(): bool
    {
        return $this->feature_class === 'P' && str_starts_with($this->feature_code, 'PPL');
    }


    public static function fromString(string $line): GeoName
    {
        $payload = explode("\t", $line);
        return self::fromPayload($payload);
    }

    /**
     * @param array<string,mixed> $payload
     */
    public function nodeFromPayload(array $payload): void
    {
        $this->lft = $payload['lft'];
        $this->rgt = $payload['rgt'];
        $this->depth = $payload['depth'];
        $this->parent_id = $payload['parent_id'];
    }

    /**
     * @return array<string,mixed>
     */
    public function node(): array
    {
        return [
            'id' => $this->id,
            'lft' => $this->lft,
            'rgt' => $this->rgt,
            'depth' => $this->depth,
            'parent_id' => $this->parent_id,
        ];
    }

    /**
     * @return array<int,mixed>
     */
    public static function newNode(): array
    {
        return [
            0,
            0,
            0,
            null,
        ];
    }

    /**
     * @param array<int,mixed> $payload
     */
    public static function fromPayload(array $payload): GeoName
    {
        $count = count($payload);

        if ($count < 19) {
            throw new DomainException("Some parts are missing from the line..." . json_encode($payload, JSON_PRETTY_PRINT));
        } else if ($count === 19) {
            $payload = [...$payload, ...self::newNode()];
        } else {
            $payload = array_slice($payload, 0, 23);
        }

        $payload = array_map('trim', $payload);

        return new GeoName(...$payload);
    }

    /**
     * @return array<string,string>
     */
    public function toPayload(): array
    {
        return [
            "id" => $this->id(),
            "name" => $this->name(),
            "ascii_name" => $this->asciiName(),
            "alternate_names" => $this->alternateNames(),
            "latitude" => $this->latitude(),
            "longitude" => $this->longitude(),
            "feature_class" => $this->featureClass(),
            "feature_code" => $this->featureCode(),
            "country_code" => $this->countryCode(),
            "cc2" => $this->cc2(),
            "admin1_code" => $this->admin1Code(),
            "admin2_code" => $this->admin2Code(),
            "admin3_code" => $this->admin3Code(),
            "admin4_code" => $this->admin4Code(),
            "population" => $this->population(),
            "elevation" => $this->elevation(),
            "dem" => $this->dem(),
            "timezone" => $this->timezone(),
            "modification_date" => $this->modificationDate(),
            "lft" => $this->lft(),
            "rgt" => $this->rgt(),
            "depth" => $this->depth(),
            "parent_id" => $this->parentId(),
        ];
    }
}


/*
 The main 'geoname' table has the following fields :
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
