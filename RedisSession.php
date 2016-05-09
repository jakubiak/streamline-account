<?php

namespace jakubiak\stra;

use Yii;
use yii\base\ErrorException;
use yii\web\UnauthorizedHttpException;

/**
 * Writes and reads user from redis for session purposes
 *
 * @package app\helpers
 */
class RedisSession
{

    //Cache keys prefixes
    const CACHE_PREFIX_USER_INFO = 'user_';

    //User cache identifiers
    //User, it's session length and list of projects his logged in are stored as array
    const CACHE_PART_USER_OBJECT = 'user';
    const CACHE_PART_USER_SESSION_LENGTH = 'sessionLengthSeconds';

    //Session length in minutes
    protected $_sessionLength;

    protected $_product;

    protected $_userInfo;

    /**
     * Product needs to be set by extending or passing it to constructor
     *
     * @param null $product
     * @throws \Exception
     */
    public function __construct($product = null)
    {
        if ($product != null) {
//            $products = ProductList::getAll();
//            if (!in_array($product, $products)) {
//                throw new \Exception("There's no $product defined ini configuration");
//            }
            $this->_product = $product;
        } elseif ($this->_product == null) {
            throw new \Exception('Product needs to be set here');
        }
    }

    /**
     * Gets user by token, updates it's session timeout time
     *
     * @param $token
     */
    public function get($token, $renew = false)
    {
        $userInfo = $this->getUserInfo($token, $renew);
        $user = $userInfo[static::CACHE_PART_USER_OBJECT];;
        $this->checkUserProjectAccess($user);
        Yii::$app->redis->EXPIRE(static::CACHE_PREFIX_USER_INFO . $token, $userInfo[static::CACHE_PART_USER_SESSION_LENGTH]);
        return $user;
    }

    /**
     * Removes user from cache
     *
     * It could force user authentication first but this would unnecessary update session time
     */
    public function logout()
    {
        $token = static::getToken();

        Yii::$app->redis->DEL(static::CACHE_PREFIX_USER_INFO . $token);
    }


    /**
     * Gets user info array from redis by token
     *
     * @param $token
     * @return array
     * @throws UnauthorizedHttpException
     */
    protected function getUserInfo($token, $renew = false)
    {
        if (!$this->_userInfo || $renew === true) {
            $u = Yii::$app->redis->GET(static::CACHE_PREFIX_USER_INFO . $token);
            if ($u) {
                $this->_userInfo = unserialize($u);
            } else {
                throw new UnauthorizedHttpException('Token doesn\'t exist');
            }
        }

        return $this->_userInfo;
    }

    /**
     * Checks whether user has access to product
     *
     * @param \app\models\User $user
     * @throws UnauthorizedHttpException
     */
    protected function checkUserProjectAccess(\app\models\User $user)
    {
        if (!in_array($this->_product, $user->products)) {
            throw new UnauthorizedHttpException('User doesn\'t have access to this product');
        }
    }

    /**
     * Gets token form header
     *
     * Throws exception if token doesn't exist
     *
     * @param bool $silently Shouldn't it throw exception when token is not present? Set to true won't throw exception
     * @return string
     * @throws UnauthorizedHttpException
     */
    public static function getToken($silently = false)
    {
        $authHeader = Yii::$app->request->getHeaders()->get('Authorization');
        if ($authHeader !== null && preg_match("/^Bearer\\s+(.*?)$/", $authHeader, $matches)) {
            return $matches[1];
        } elseif ($silently) {
            return null;
        } else {
            throw new UnauthorizedHttpException('Incorrect or empty token');
        }
    }

}