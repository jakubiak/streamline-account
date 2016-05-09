<?php

namespace jakubiak\stra;

use Yii;
use app\helpers\auth\licenseHandlers\LicenseHandlerInterface;

/**
 * @inheritdoc
 *
 * In addition it has license handler attached and calls its methods to provide license checks
 *
 * @package app\helpers
 */
class LicensedRedisSession extends RedisSession
{
    //User cache identifiers
    const CACHE_PART_USER_CHECKED_PRODUCTS = 'licenseCheckedProducts';

    /**
     * Attached License handler
     *
     * @var LicenseHandlerInterface
     */
    private $_licenseHandler;

    /**
     * Sets license handler
     *
     * @param LicenseHandlerInterface $licenseHandler
     * @param null $product
     * @throws \Exception
     */
    public function __construct(LicenseHandlerInterface $licenseHandler, $product = null)
    {
        parent::__construct($product);
        $this->_licenseHandler = $licenseHandler;
    }
    /**
     * @inheritdoc
     *
     * In addition calls check method if product hasn't been checked before and afterGet method on instance handler
     */
    public function get($token, $renew = false)
    {
        $userInfo = $this->getUserInfo($token, $renew);

        $user = $userInfo[static::CACHE_PART_USER_OBJECT];
        $this->_licenseHandler->setUser($user);
        if (!in_array($this->_product, $userInfo[static::CACHE_PART_USER_CHECKED_PRODUCTS])) {
            $this->_licenseHandler->check();
        }

        parent::get($token);

        $this->_licenseHandler->afterGet($userInfo[static::CACHE_PART_USER_SESSION_LENGTH]);
        return $user;

    }

    /**
     * @inheritdoc
     *
     * In addition calls afterLogout method on instance handler
     */
    public function logout()
    {
        $user = $this->getUser(static::getToken());
        parent::logout();
        $this->_licenseHandler->setUser($user);
        $this->_licenseHandler->afterLogout();
    }


    /**
     * Gets user object from redis by token
     *
     * @param $token
     * @return \app\models\User
     */
    protected function getUser($token)
    {
        $userInfo = $this->getUserInfo($token);
        return $userInfo[static::CACHE_PART_USER_OBJECT];
    }


}