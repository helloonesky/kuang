<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * @use mysql封装基类
 * @author sky
 * @date 2018-10-17 15:00
 */
class Mysql {
}

class Mysql_lib {
    public $_CI;
    public $_db;
    public $_table;

    public function __construct($param = [])
    {
        $this->_CI =& get_instance();

        if(isset($param['table']) && $param['table'])
        {
            $this->_table = $param['table'];
        }
    }

    /**
     * 连接数据库(主从)
     *
     * 默认是主库，如果$slaveflag是true则是从库
     */
    public function connectDB($slaveflag = false)
    {
        if($slaveflag)
        {
            //根据权重连接从库
            $this->_CI->load->database();
            $this->_db = $this->_CI->db;
        }else
        {
            //连接主库
            $this->_CI->load->database();
            $this->_db = $this->_CI->db;
        }
    }

    /**
     * where条件 支持LIKE IN
     */
    private function formatSqlWhere($where = array())
    {
        foreach($where AS $k => $v)
        {
            if(strstr($k, " LIKE"))
            {
                $k = rtrim($k, " LIKE");
                $this->_db->like($k, $v);
            }else if(strstr($k, " IN"))
            {
                $k = rtrim($k, " IN");
                $this->_db->where_in($k, $v);
            }else if(strstr($k, " NOT_IN"))
            {
                $k = rtrim($k, " NOT_IN");
                $this->_db->where_not_in($k, $v);
            }else
            {
                $this->_db->where($k, $v);
            }
        }
    }

    /**
     * 增加一条数据,返回自增id
     */
    public function addInfo($data, $table = '')
    {
        if($table)
        {
            $this->_table = $table;
        }

        if(!$this->_table || empty($data) || !is_array($data))
        {
            return false;
        }

        //主库
        $this->connectDB();

        $status = $this->_db->insert($this->_table, $data);

        return $status ? $this->_db->insert_id() : 0;
    }

    /**
     * 批量增加多条数据,返回执行的行数
     */
    public function batchAddInfo($data, $table = '')
    {
        if($table)
        {
            $this->_table = $table;
        }

        if(!$this->_table || empty($data) || !is_array($data))
        {
            return false;
        }

        //主库
        $this->connectDB();

        $affected_rows = $this->_db->insert_batch($this->_table, $data);

        return $affected_rows ? $affected_rows : 0;
    }

    /**
     * 修改数据,返回执行的行数
     */
    public function updateInfo($data, $where, $table = '')
    {
        if($table)
        {
            $this->_table = $table;
        }

        if(!$this->_table || empty($data) || !is_array($data) || empty($where) || !is_array($where))
        {
            return false;
        }

        //主库
        $this->connectDB();

        $status = $this->_db->update($this->_table, $data, $where);

        return $status ? $this->_db->affected_rows() : 0;
    }

    /**
     * 根据条件删除数据,返回执行的行数(硬删除，不建议使用)
     */
    public function deleteInfo($where, $table = '')
    {
        if($table)
        {
            $this->_table = $table;
        }

        if(!$this->_table || empty($where) || !is_array($where))
        {
            return false;
        }

        //主库
        $this->connectDB();

        $status = $this->_db->delete($this->_table, $where);

        return $status ? $this->_db->affected_rows() : 0;
    }

    /**
     * 根据条件COUNT
     */
    public function countResult($where = array(), $table = '')
    {
        if($table)
        {
            $this->_table = $table;
        }

        if(!$this->_table)
        {
            return false;
        }

        //从库
        $this->connectDB(true);

        if($where)
        {
            $this->formatSqlWhere($where);
        }

        return $this->_db->count_all_results($this->_table);
    }

    /**
     * 根据条件获取到一条数据
     *
     * $this->_db->last_query();
     */
    public function getInfo($where, $order = array(), $field = null, $group = array(), $table = '')
    {
        if($table)
        {
            $this->_table = $table;
        }

        if(!$this->_table || empty($where) || !is_array($where))
        {
            return false;
        }

        //从库
        $this->connectDB(true);

        if($where)
        {
            $this->formatSqlWhere($where);
        }

        if($field)
        {
            $this->_db->select($field);
        }else
        {
            $this->_db->select('*');
        }

        if($group)
        {
            $this->_db->group_by($group);
        }

        if($order)
        {
            foreach($order as $k => $v)
            {
                $this->_db->order_by($k, $v);
            }
        }

        $this->_db->limit(1);

        $result = $this->_db->get($this->_table)->row_array();

        return $result;
    }

    /**
     * 根据条件获取多条数据
     *
     * $this->_db->last_query();
     */
    public function getAll($where = array(), $order = array(), $field = null, $limit = 20, $group = array(), $table = '')
    {
        if($table)
        {
            $this->_table = $table;
        }

        if(!$this->_table)
        {
            return false;
        }

        //从库
        $this->connectDB(true);

        if($where)
        {
            $this->formatSqlWhere($where);
        }

        if($field)
        {
            $this->_db->select($field);
        }else
        {
            $this->_db->select('*');
        }

        if($group)
        {
            $this->_db->group_by($group);
        }

        if($order)
        {
            foreach($order as $k => $v)
            {
                $this->_db->order_by($k, $v);
            }
        }

        $this->_db->limit($limit);

        $result = $this->_db->get($this->_table)->result_array();

        return $result;
    }

    /**
     * 根据条件分页获取多条数据
     *
     * $this->_db->last_query();
     */
    public function getPage($offset = 0, $limit = 20, $where = array(), $order = array(), $field = null, $group = array(), $table = '')
    {
        if($table)
        {
            $this->_table = $table;
        }

        if(!$this->_table)
        {
            return false;
        }

        //从库
        $this->connectDB(true);

        //统计总数
        $total = $this->countResult($where);

        //列表
        if($where)
        {
            $this->formatSqlWhere($where);
        }

        if($field)
        {
            $this->_db->select($field);
        }else
        {
            $this->_db->select('*');
        }

        if($order)
        {
            foreach($order as $k => $v)
            {
                $this->_db->order_by($k, $v);
            }
        }

        if($group)
        {
            $this->_db->group_by($group);
        }

        $this->_db->limit($limit, $offset);
        $list = $this->_db->get($this->_table)->result_array();

        $result = [];
        $result['total'] = $total;
        $result['page']['offset'] = $offset;
        $result['page']['limit'] = $limit;
        $result['page']['total_pages'] = ceil($total / $limit);
        $result['list'] = $list;

        return $result;
    }

    /**
     * 查询sql
     *
     * 常用SELECT、COUNT、IN操作
     */
    public function query($sql = '', $params = array())
    {
        if(!$sql)
        {
            return false;
        }

        //从库
        $this->connectDB(true);

        if($params)
        {
            $result = $this->_db->query($sql, $params)->result_array();
        }else
        {
            $result = $this->_db->query($sql)->result_array();
        }

        return $result && count($result)==1 ? $result[0] : $result;
    }

    /**
     * 执行sql
     *
     * 常用UPDATE、INSERT、DELETE操作
     *
     * 返回成功/失败
     */
    public function execute($sql = '', $params = array())
    {
        if(!$sql)
        {
            return false;
        }

        //主库
        $this->connectDB();

        if($params)
        {
            $query = $this->_db->query($sql, $params);
        }else
        {
            $query = $this->_db->query($sql);
        }

        return $query;
    }

    /**
     * 事务开启
     */
    public function trans_begin()
    {
        //主库
        $this->connectDB();

        return $this->_db->trans_begin();
    }

    /**
     * 事务提交
     */
    public function trans_commit()
    {
        //主库
        $this->connectDB();

        return $this->_db->trans_commit();
    }

    /**
     * 事务回滚
     */
    public function trans_rollback()
    {
        //主库
        $this->connectDB();

        return $this->_db->trans_rollback();
    }

    /**
     * 事务状态
     */
    public function trans_status()
    {
        //主库
        $this->connectDB();

        return $this->_db->trans_status();
    }

    /**
     * 增加一条数据,返回自增id
     */
    public function insertId()
    {
        //主库
        $this->connectDB();

        return $this->_db->insert_id() ? $this->_db->insert_id() : 0;
    }
}