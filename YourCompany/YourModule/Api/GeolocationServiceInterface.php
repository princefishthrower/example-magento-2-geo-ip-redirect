<?php

namespace YourCompany\YourModule\Api;

interface GeolocationServiceInterface
{
    public function getCountryCodeByIp(): string;
}