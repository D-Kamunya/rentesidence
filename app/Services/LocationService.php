<?php

namespace App\Services;

use App\Traits\ResponseTrait;

class LocationService
{
    use ResponseTrait;

    public function getCountry()
    {
        return $this->success([]);
    }

    public function getStateByCountryId($country_id)
    {
        return $this->success([
            'stateArr' => [],
            'states' => []
        ]);
    }

    public function getCitiesByStateId($state_id)
    {
        return $this->success([
            'cityArr' => [],
            'cities' => []
        ]);
    }
}
