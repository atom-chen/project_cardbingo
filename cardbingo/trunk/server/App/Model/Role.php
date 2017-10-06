<?php

namespace App\Model;

class Role extends Model {

    protected $_name = "role";
    protected $_cacheKeyPre = "role";
    protected $_cacheKey = '';
    protected $_roleId = 0;
    protected $_serverId = 0;
    protected $_updateParams = [];
    
    public function __construct ($roleId = 0) {
        $this->_roleId = $roleId;
        $mAccount = \App\Model\Account::getInstance();
        $accountData = $mAccount->getAccount($this->_roleId);
        $this->_serverId = $accountData['server_id'];
        $this->_cacheKey = "{$this->_cacheKeyPre}:{$this->_serverId}:{$this->_roleId}";
        if (!$this->_roleId) {
            throw new \Core\Exception(__METHOD__ . " Must set roleID", 30002);
        }
        \Core\Controller::setAfterAction([$this, "__afterAction"], [], __CLASS__ . $this->_roleId);
    }
    
    public function _saveRedis($params = []) {
        if (!empty($this->_updateParams)) {
            $params = array_merge($this->_updateParams, $params);
        }

        if (empty($params)) {
            return true;
        }
        
        $role = $this->get();
        
        if (!$role) {
            return false;
        }
            
        $data = array_merge($role, $params);
        $this->_setCache($data);
        return true;
     
    }
    
    public function __afterAction() {
        $this->_update();
    }

    private function _update($params = []) {
        if (!empty($this->_updateParams)) {
            $params = array_merge($this->_updateParams, $params);
        }

        if (empty($params)) {
            return true;
        }
        
        $role = $this->get();
        
        if (!$role) {
            return false;
        }
        
        if (\App\PO\Role::getInstance()->shardDb($this->_serverId)->updateById($this->_roleId,$params)) {
            $this->_updateParams = [];
            return true;
        }
        return false;
    }
    
    public function updateCoin($coinArray) {
        $coin = $coinArray[0];
        if ($coin) {
            $this->_updateParams['coin'] = $coin;
            $this->_saveRedis();
        }
    }
    
    public function updateDiamond($diamondArray) {
        $diamond = $diamondArray[0];
        if ($diamond) {
            $this->_updateParams['diamond'] = $diamond;
            $this->_saveRedis();
        }
    }
    
    public function updatePower($powerArray) {
        $power = $powerArray[0];
        if ($power) {
            $this->_updateParams['power'] = $power;
            $cacheKey = "{$this->_cacheKeyPre}:{$this->_serverId}:{$this->_roleId}:power";
            $newdata=[
                    'power' => $power,
                    'time' => NOW,
                    'remain' => $powerArray[1]       
                ];
            $this->redis()->multi()
                ->hMSet($cacheKey, $newdata)
                ->exec();   
        }
    }
    
    public function getPower() {
        $cacheKey = "{$this->_cacheKeyPre}:{$this->_serverId}:{$this->_roleId}:power";
        $powerdata = $this->redis()->hGetAll($cacheKey);
        if(!$powerdata){
            return [];
        }
        return $powerdata;
    }
    
    public function addCoin($res,$num = 0) {
        $role = $this->get();
        if ($num) {
            $this->_updateParams['coin'] = $role['coin'] + $num;
            $this->_saveRedis();
            return true;
        }
        return false;
    }
    
    public function addPower($res,$num = 0) {
        $role = $this->get();
        if ($num) {
            $this->_updateParams['power'] = $role['power'] + $num;
            $this->_saveRedis();
            return true;
        }
        return false;
    }

    public function addDiamond($res,$num = 0) {
        $role = $this->get();
        if ($num) {
            $this->_updateParams['diamond'] = $role['diamond'] + $num;
            $this->_saveRedis();
            return true;
        }
        return false;
    }
    
    public function subCoin($res,$subNum = 0) {
        $role = $this->get();
        $num = $role['coin'];
        
        if ($subNum &&(!$num ||$num < $subNum)) {
            $res->setError(10000, "sub coin num is error");
            return false;
        }
        
        $num -= $subNum;
        
        if ($num<0) {
            $res->setError(10000, "sub coin num is error");
            return false;
        }
        
        $this->_updateParams['coin'] = $num;
        $this->_saveRedis();
        return true;
        
    }
    
    public function subDiamond($res,$subNum = 0) {
        $role = $this->get();
        $num = $role['diamond'];
        
        if ($subNum &&(!$num ||$num < $subNum)) {
            $res->setError(10001, "sub diamond num is error");
            return false;
        }
        
        $num -= $subNum;
        
        if ($num<0) {
            $res->setError(10001, "sub diamond num is error");
            return false;
        }
        $this->_updateParams['diamond'] = $num;
        $this->_saveRedis();
        return true;
        
    }
    
    public function subPower($res,$subNum = 0) {
        $role = $this->get();
        $num = $role['power'];
        
        if ($subNum &&(!$num ||$num < $subNum)) {
            $res->setError(10000, "sub power num is error");
            return false;
        }
        
        $num -= $subNum;
        
        if ($num<0) {
            $res->setError(10000, "sub power num is error");
            return false;
        }
        
        $this->_updateParams['power'] = $num;
        $this->_saveRedis();
        return true;
        
    }
    

    public function setLoginTime($loginTime) {
        $this->_updateParams['login_time'] = $loginTime;
        $this->_saveRedis();
    }
    
    public function setNickName($nickname) {
        $this->_updateParams['nickname'] = $nickname;
        $this->_saveRedis();
    }
    
    
    public function addVersion() {
        $role = $this->get();
        $roleArchive = $role['archive_version'];
        $newArchive = $roleArchive + 1;
        if ($roleArchive>0&&$newArchive>0) {
            $this->_updateParams['archive_version'] = $newArchive;
        }
        else {
            $this->_updateParams['archive_version'] = 0;
        }
        $this->_saveRedis();
    }
    
    public function changeVersion($gv,$cid) {
        $this->_updateParams['game_version'] = $gv;
        $this->_updateParams['channel_id'] = $cid;
        
        $this->_saveRedis();
    }

    /**
     *  创建用户
     * @param type $sid
     */
//    public function create() {
    public function create($register_time,$gv,$cid) {
        if (!$this->_serverId){
            $this->setError(1008, "server_id error");
            return false;
        }
        $shard = \App\Model\Shard::getInstance();
        $data = [
            'role_id' => $this->_roleId,
            'nickname' => $shard->gameConfig('role')['nickname'],
            "coin" => 0,
            "diamond" => 0,
            'login_time' => NOW,
            'register_time' => $register_time,
            'archive_version' => 1,
            'game_version' => $gv,
            'channel_id' => $cid
        ];
        \App\PO\Role::getInstance()->shardDb($this->_serverId)->insert($data,FALSE);
        $this->_setCache($data);
        return $data;
    }
   

    private function _setCache($data) {
        $this->redis()->pipeline()
                ->hMSet($this->_cacheKey, $data)
                ->exec();
        $this->cache()->set($this->_cacheKey, $data);
    }

    public function get() {
        $data = $this->cache()->get($this->_cacheKey);
        if (!$data) {
            //内存中没有值
            $data = $this->redis()->hGetAll($this->_cacheKey);
            if (!$data) {
                $data = \App\PO\Role::getInstance()->shardDb($this->_serverId)->get();
                //设置到Redis
                if ($data) {
                    $this->_setCache($data);
                }
            }
            $this->cache()->set($this->_cacheKey, $data);
        }

        if (!empty($this->_updateParams)) {
            return array_merge($data, $this->_updateParams);
        }

        return $data;
    }


}
