<?php

namespace Core;

/**
 * 数据对象实例
 */
class PO {

    use \ServiceSingleton;

    protected static function _serviceInit() {
        $class = get_called_class();
        return new $class();
    }

    /**
     * 数据对象的数据类型
     * 
     * @var type 
     */
    protected $_dataType = 'db';
    protected $_name = '';
    protected $_priKey = '';
    protected $_fields = [];
    //dbkey
    protected $_db = "default";
    protected $_queryWhere = [];
    protected $_queryFields = [];
    protected $_queryLimit = "";
    protected $_queryOrder = '';

    public function getDataType() {
        return $this->_dataType;
    }

    public function __construct() {
        if (!$this->_name || empty($this->_fields)) {
            throw new \Core\Exception(get_called_class() . " name fields undefinded", "9998");
        }
        $this->_reset();
    }

    private function _reset() {
        $this->_queryWhere = [];
        $this->_queryLimit = "";
        $this->_queryOrder = '';
        $this->_queryFields = $this->_fields;
    }

    public function getById($id) {
        $where[$this->_priKey] = $id;
        $this->where($where);
        return $this->get();
    }

    public function getAllById($id) {
        $this->where([$this->_priKey => $id]);
        return $this->getAll();
    }

    public function get() {
        $data = \Core\PDO::getInstance($this->_db)->getOne(
                $this->_name
                , implode(",", $this->_queryFields)
                , $this->_queryWhere
                , $this->_queryOrder
        );
        $this->_reset();
        return $data;
    }

    public function getAll() {
        $data = \Core\PDO::getInstance($this->_db)->getAll(
                $this->_name
                , implode(",", $this->_queryFields)
                , $this->_queryWhere
                , FALSE
                , $this->_queryOrder
                , $this->_queryLimit
        );
        $this->_reset();
        return $data;
    }

    public function insert($data = [], $getLastID = true) {
        if ($this->_checkData($data)) {
            throw new \Core\Exception("VO.insert \n fields " . json_encode($this->_fields) . "\n data:" . json_encode($data), 9997);
        }
        return \Core\PDO::getInstance($this->_db)->insert(
                        $this->_name
                        , $data
                        , $getLastID
        );
    }

    private function _checkData($data = []) {
        if (is_array($data)) {
            foreach ($data as $field => $val) {
                if ($field !== $this->_priKey || !in_array($field, $this->_fields)) {
                    return FALSE;
                }
            }
            return true;
        }
        return FALSE;
    }

    public function updateById($id,$data) {
        $where[$this->_priKey] = $id;
        $this->where($where);
        if (empty($this->_queryWhere)) {
            throw new \Core\Exception("VO.update update where not set exec \$vo->while())", 9996);
        }
        if ($this->_checkData($data)) {
            throw new \Core\Exception("VO.update field error \n fields " . json_encode($this->_fields) . "\n data:" . json_encode($data), 9997);
        }
        return \Core\PDO::getInstance($this->_db)->update(
                        $this->_name
                        , $data
                        , $this->_queryWhere
                        , $this->_queryLimit
        );
    }
    
    public function update($data) {
        //dump("update",$data);
        if (empty($this->_queryWhere)) {
            throw new \Core\Exception("VO.update update where not set exec \$vo->while())", 9996);
        }
        if ($this->_checkData($data)) {
            throw new \Core\Exception("VO.update field error \n fields " . json_encode($this->_fields) . "\n data:" . json_encode($data), 9997);
        }
        return \Core\PDO::getInstance($this->_db)->update(
                        $this->_name
                        , $data
                        , $this->_queryWhere
                        , $this->_queryLimit
        );
    }

    public function delete() {
        return \Core\PDO::getInstance($this->_db)->delete(
                        $this->_name
                        , $this->_queryWhere
        );
    }

    public function field($fields = []) {
        $this->_queryFields = $fields;
        return $this;
    }

    public function where($where = []) {
        $this->_queryWhere = $where;
        return $this;
    }

    public function table($table) {
        $this->_name = $table;
        return $this;
    }

    public function db() {
        $this->_db = "default";
        return $this;
    }
    
    public function shardDb($sid = 0) {
        if(!$sid){
            throw new \Core\Exception("拆分的数据库必须传入sid", 9999);
        }
        $this->_db = "shard_{$sid}";
        return $this;
    }

    public function limit($start = "", $limit = 0) {
        if (!$limit) {
            $this->_queryLimit = $start;
        } else {
            $this->_queryLimit = "{$start},{$limit}";
        }
        return $this;
    }

    public function order($order) {
        $this->_queryOrder = $order;
        return $this;
    }

    public function __toString() {
        return json_decode($this->_data);
    }

}
