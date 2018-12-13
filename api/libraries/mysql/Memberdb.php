<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * @use 会员相关
 * @author sky
 * @date 2018-10-29 17:00
 */
class Memberdb {
    public $_table = 'member';
    public $_mysqllib;

    public function __construct()
    {
        $this->_mysqllib = new mysql_lib(['table' => $this->_table]);
    }

    /**
     * 根据条件获取到一条数据
     */
    public function getInfo($where, $order = array(), $field = null)
    {
        return $this->_mysqllib->getInfo($where, $order, $field);
    }

    /**
     * 根据条件获取到条数
     */
    public function countResult($where)
    {
        return $this->_mysqllib->countResult($where);
    }

    /**
     * @use 更新用户信息
     */
    public function updateInfo($data, $where)
    {
        return $this->_mysqllib->updateInfo($data, $where);
    }

    /**
     * @use 添加一条会员信息
     */
    public function addInfo($data)
    {
        return $this->_mysqllib->addInfo($data);
    }

    /**
     * @use 更新用户信息(更新eb_member和eb_member_info表)
     */
    public function updateMemberInfo($data, $additionData, $member_id)
    {
        $this->_mysqllib->trans_begin();

        $this->_mysqllib->updateInfo($data, ['id'=>$member_id], 'member');
        $this->_mysqllib->updateInfo($additionData, ['member_id'=>$member_id], 'member_info');

        if($this->_mysqllib->trans_status() === FALSE)
        {
            $this->_mysqllib->trans_rollback();
        }else
        {
            $this->_mysqllib->trans_commit();
        }

        return $this->_mysqllib->trans_status();
    }

    /**
     * 根据条件分页获取多条数据
     */
    public function getList($offset = 0, $limit = 20, $where = array(), $field = '*',$order = array(), $group = array())
    {
        return $this->_mysqllib->getPage($offset, $limit, $where, $order, $field, $group);
    }

}