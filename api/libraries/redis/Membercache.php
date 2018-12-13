<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * @use 会员登录相关缓存
 * @author sky
 * @date 2018-10-29 17:00
 */
class Membercache {
    public $_redislib;
    const HASHKEY = 'membertoken';
    const HASHMKEY = 'member';

    public function __construct()
    {
        $this->_redislib = new redis_lib();
    }

    /**
     * @use 登录写入token
     */
    public function setToken($hashKey, $value)
    {
        return $this->_redislib->hSet(self::HASHKEY, $hashKey, $value);
    }

    /**
     * @use 登录写入用户信息
     */
    public function setMemberInfo($hashKey, $value)
    {
        return $this->_redislib->hSet(self::HASHMKEY, $hashKey, $value);
    }

    /**
     * @use 获取用户信息
     */
    public function getMember($hashKey)
    {
        return $this->_redislib->hGet(self::HASHMKEY, $hashKey);
    }

    /**
     * @use 判断token是否存在
     */
    public function getToken($hashKey)
    {
        return $this->_redislib->hGet(self::HASHKEY, $hashKey);
    }

    /**
     * @use 删除过期token
     */
    public function delToken($hashKey)
    {
        return $this->_redislib->hDel(self::HASHKEY, $hashKey);
    }
}