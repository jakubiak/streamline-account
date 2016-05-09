<?php

namespace jakubiak\stra\licenseHandlers;

use yii\web\UnauthorizedHttpException;
use Yii;

/**
 * Handler that takes care of concurrent license
 *
 * Checks whether limit of concurrent users has been reached
 *
 * Class ConcurrentLicenseHandler
 * @package app\helpers\auth\licenseHandlers
 */
class ConcurrentLicenseHandler extends BaseLicenseHandler implements LicenseHandlerInterface
{
    const CACHE_PREFIX_INSTANCE_LICENSE = 'instance_users_';

    public function check()
    {
        $instance = $this->_user->instance;
        $cacheKey = static::CACHE_PREFIX_INSTANCE_LICENSE . $instance->_id;
        $loggedUsers = Yii::$app->redis->ZCOUNT($cacheKey, time(), 'inf');
        if ($loggedUsers >= $instance->license_number) {
            throw new UnauthorizedHttpException('License limit reached');
        }
    }

    public function afterLogin($sessionLength)
    {
        $sessionEndTime = time() + $sessionLength;
        $instanceCacheKey = static::CACHE_PREFIX_INSTANCE_LICENSE . $this->_user->instance->_id;
        Yii::$app->redis->ZADD($instanceCacheKey, $sessionEndTime, $this->_user->username);
    }

    public function afterGet($sessionLength)
    {
        $instanceCacheKey = static::CACHE_PREFIX_INSTANCE_LICENSE . $this->_user->instance->_id;
        $sessionEndTime = time() + $sessionLength;
        Yii::$app->redis->ZADD($instanceCacheKey, $sessionEndTime, $this->_user->username);
    }

    public function afterLogout()
    {
        $instanceCacheKey = static::CACHE_PREFIX_INSTANCE_LICENSE . $this->_user->instance->_id;
        Yii::$app->redis->ZREM($instanceCacheKey, $this->_user->username);
    }
}