<?php

namespace jakubiak\stra\licenseHandlers;

/**
 * Interface for all license Handlers
 *
 * Check method is crucial one because it checks if we can let user do anything or he doesn't have license to use product
 * after... methods perform additional actions when handler needs to do something on specified events
 *
 * Interface LicenseHandlerInterface
 * @package app\helpers\auth\licenseHandlers
 */
interface LicenseHandlerInterface
{
    /**
     * Sets user so it can be used in other methods
     *
     * @param $user
     * @return mixed
     */
    public function setUser($user);

    /**
     * Main method that check is license limits has been reached
     * If so it should throw exception
     *
     * @return mixed
     */
    public function check();

    /**
     * Method called after user login
     *
     * Can perform additional actions when handler needs to do something on this event
     *
     * @param int $sessionLength Session length in seconds
     * @return mixed
     */
    public function afterLogin($sessionLength);

    /**
     * Method called on each request when user is retrieved from cache
     *
     * Can perform additional actions when handler needs to do something on this event
     *
     * @param int $sessionLength Session length in seconds
     * @return mixed
     */
    public function afterGet($sessionLength);

    /**
     * Method called after logout
     *
     * Can perform additional actions when handler needs to do something on this event
     *
     * @return mixed
     */
    public function afterLogout();

}