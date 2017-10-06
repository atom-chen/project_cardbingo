<?php

namespace Core;

class PDO {

    private $_db;
    private $_stmt;
    private $_sql;
    use \ServiceSingleton;
    /**
     *  用于数据库 service 初始化
     * @param type $key
     * @return \Core\PDO
     * @throws \Core\Exception
     */
    public static function _serviceInit($key){
        $config = config('Db')[$key];
        if (!$config) {
            throw new \Core\Excdefaulteption("PDO Config :{$key} not found ", 203);
        }
        try {
            $pdo = new \Core\PDO($config['dsn'], $config['user'], $config['password']);
        } catch (\PDOException $e) {
            throw new \Core\Exception($e->getMessage(), 204);
        }
        return $pdo;
    }
    
    public function __construct($dsn, $username, $password, $options = []) {
        $this->_db = new \PDO($dsn, $username, $password, $options);
        //默认把结果序列化成stdClass
        $this->_db->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);
        //自己写代码捕获Exception
        $this->_db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_SILENT);
    }

    public function disConnect() {
        $this->_db = null;
        $this->_stmt = null;
    }

    public function exec($statement) {
        $this->_sql = $statement;
        if (!$this->_db->exec($statement)) {
            $this->_errMsg();
        }
        return true;
    }

    public function query($statement) {
        if ($res = $this->_db->query($statement)) {
            $this->_stmt = $res;
            $this->_sql = $statement;
            return $this;
        }
        $this->_errMsg();
        return false;
    }

    private function _errMsg($errmsg = "") {
        $errorInfo = ($this->_stmt && $this->_stmt->errorCode()) ? $this->_stmt->errorInfo() : $this->_db->errorInfo();
        $errmsg = $errmsg ? $errmsg : "sql:" . $this->_sql . " error :" . $errorInfo[2];
        throw new \Core\Exception($errmsg, 103);
    }

    public function fetch() {
        return $this->_stmt->fetch();
    }

    public function fetchAll() {
        return $this->_stmt->fetchAll();
    }

    public function lastInsertId() {
        return $this->_db->lastInsertId();
    }

    public function rowCount() {
        return $this->_stmt->rowCount();
    }

    public function prepare($statement) {
        $res = $this->_db->prepare($statement);
        $this->_sql = $statement;
        if ($res) {
            $this->_stmt = $res;
            return $this;
        }
        $this->_errMsg();
        return false;
    }

    public function bindArray($array) {
        foreach ($array as $k => $v) {
            if (is_array($v)) {
                //array的有效结构 array('value'=>xxx,'type'=>PDO::PARAM_XXX)
                $this->_stmt->bindValue($k + 1, $v['value'], $v['type']);
            } else {
                $this->_stmt->bindValue($k + 1, $v, \PDO::PARAM_STR);
            }
        }
        return $this;
    }

    public function bindValue($parameter, $value, $data_type = \PDO::PARAM_STR) {
        return $this->_stmt->bindValue($parameter, $value, $data_type);
    }

    public function bindParam($parameter, &$variable, $data_type = \PDO::PARAM_STR, $length = null, $driver_options = null) {
        return $this->_stmt->bindParam($parameter, $variable, $data_type, $length, $driver_options);
    }

    public function bindColumn($column, &$param, $type = null, $maxlen = null, $driverdata = null) {
        return $this->_stmt->bindColumn($column, $param, $type, $maxlen, $driverdata);
    }

    public function execute() {
        if ($this->_stmt->execute()) {
            return true;
        }
        $this->_errMsg();
        return false;
    }

    public function insert($table, $data, $getLastID = true) {
        $filedsStr1 = $filedsStr2 = "";
        foreach ($data as $field => $value) {
            $filedsStr1 .= ",{$field}";
            $filedsStr2 .= ",:{$field}";
        }
        $filedsStr1 = substr($filedsStr1, 1);
        $filedsStr2 = substr($filedsStr2, 1);
        $sql = <<<SQL
insert into %s (%s) values(%s);
SQL;
        $sql = sprintf($sql, $table, $filedsStr1, $filedsStr2);
        $this->prepare($sql);
        foreach ($data as $field => $value) {
            $this->bindValue(":{$field}", $value);
        }
        if (!$this->execute()) {
            return false;
        }
        return $getLastID ? $this->lastInsertId(): TRUE;
    }

    public function update($table, $data, $cond = [], $limit = " 1") {
        $condStr = "";
        $filedsStr = $this->_parseUpdateField($data);

        if ($cond) {
            $condStr = $this->_parseCond($cond);
            $condStr = "where " . $condStr;
        }

        if ($limit) {
            $limit = "limit $limit";
        }

        $sql = <<<SQL
update %s set %s %s %s;
SQL;

        $sql = sprintf($sql, $table, $filedsStr, $condStr, $limit);
        $this->prepare($sql);
        $this->_bindArray($data);
        $this->_bindArray($cond);
        return $this->execute();
    }

    public function delete($table, $cond = [], $limit = "1") {
        //delete from account where username=:username limit 1
        $condStr = "";
        if ($cond) {
            $condStr = $this->_parseCond($cond);
            $condStr = "where " . $condStr;
        }

        if ($limit) {
            $limit = "limit $limit";
        }

        $sql = <<<SQL
delete from %s %s %s;
SQL;

        $sql = sprintf($sql, $table, $condStr, $limit);
        $this->prepare($sql);

        $this->_bindArray($cond);

        return $this->execute();
    }
    
    function getOne($table, $fields = "*", $cond = [], $order = "") {
        $condStr = "";
        if ($cond) {
            $condStr = $this->_parseCond($cond);
            $condStr = "where " . $condStr;
        }
        $orderStr = "";
        if ($order) {
            $orderStr = " order by {$order} ";
        }

        $sql = <<<SQL
select {$fields} from {$table} {$condStr} {$orderStr} limit 1;
SQL;
        $this->prepare($sql);
        $this->_bindArray($cond);
        $this->execute();
        $ret = $this->fetch();
        if (!$ret || !is_array($ret)) {
            return [];
        }
        return $ret;
    }

    function getAll($table, $fields = "*", $cond = [], $isGroup = false, $order = "", $limit = "") {
        $condStr = "";
        if ($cond) {
            $condStr = $this->_parseCond($cond);
            $condStr = "where " . $condStr;
        }

        if ($order) {
            $order = " order by {$order} ";
        }

        if ($limit) {
            $limit = "limit $limit";
        }

        $sql = <<<SQL
select {$fields} from {$table} {$condStr} {$order} {$limit};
SQL;
        $this->prepare($sql);
        if($cond){
            $this->_bindArray($cond);
        }
        $this->execute();
        return $isGroup ? $this->_stmt->fetchAll(\PDO::FETCH_GROUP | \PDO::FETCH_UNIQUE) : $this->_stmt->fetchAll();
    }

    private function _bindArray($data) {
        foreach ($data as $field => $value) {
            $this->bindValue(":{$field}", $value);
        }
    }

    private function _parseUpdateField($data) {
        $str = "";
        foreach ($data as $field => $value) {
            $str .= ",{$field}=:{$field}";
        }
        $str = substr($str, 1);
        return $str;
    }

    private function _parseCond($cond) {
        $str = "";
        foreach ($cond as $field => $value) {
            $str .= " and {$field}=:{$field}";
        }
        $str = substr($str, 4);
        return $str;
    }

}
