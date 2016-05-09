<?php

namespace jakubiak\stra\licenseHandlers;

/**
 * Implements simple SeUser method and can be base for other LicenseHandlers
 * @package app\helpers\auth\licenseHandlers
 */
class BaseLicenseHandler
{
    protected $_user;

    public function setUser($user)
    {
        $this->_user = $user;
    }
}