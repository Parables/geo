<?php

namespace Parables\Geo;

trait GeoNameTrait
{
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

    public function isADM2(): bool
    {
        return $this->feature_class === 'A' && $this->feature_code === 'ADM2';
    }

    public function isADM3(): bool
    {
        return $this->feature_class === 'A' && $this->feature_code === 'ADM3';
    }

    public function isADM4(): bool
    {
        return $this->feature_class === 'A' && $this->feature_code === 'ADM4';
    }

    public function isADM5(): bool
    {
        return $this->feature_class === 'A' && $this->feature_code === 'ADM5';
    }

    public  function isCityTown(): bool
    {
        return $this->feature_class === 'P' && str_starts_with($this->feature_code, 'PPL');
    }
}
