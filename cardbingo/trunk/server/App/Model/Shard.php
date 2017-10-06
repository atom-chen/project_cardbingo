<?php

namespace App\Model;

class Shard extends Model {
    
    function doMysql() {
        $this->_updateAllToDb();
        $this->_insertAllToDb();
    }
    
    private function _updateAllToDb() {
        $count = \Core\CacheRedis::getInstance()->llen("updateDb");      
        if ($count<=0){
            return FALSE;
        }
//        wlog("_updateAllToDb");
//        wlog($count);
        for ($x=0; $x<=$count; $x++) {
            $returnValue = \Core\CacheRedis::getInstance()->lpopArray("updateDb");
            $tablename = $returnValue["table"];
            $values = String2Array($returnValue["value"]);
            $serverId = $returnValue["server"];
            $obj = "App\\PO\\".$tablename;
            $t = call_user_func(array($obj, 'getInstance'), '');
            foreach ($values as $Id => $value) {
                $t->shardDb($serverId)->where($value["where"])->update($value["value"]);
            }
        } 
        
    }
    
    private function _insertAllToDb() {
        $count = \Core\CacheRedis::getInstance()->llen("insertDb");      
        if ($count<=0){
            return FALSE;
        }
//        wlog("_insertAllToDb");
//        wlog($count);
        for ($x=0; $x<=$count; $x++) {
            $returnValue = \Core\CacheRedis::getInstance()->lpopArray("insertDb");
            $tablename = $returnValue["table"];
            $values = String2Array($returnValue["value"]);
            $serverId = $returnValue["server"];
            $obj = "App\\PO\\".$tablename;
            $t = call_user_func(array($obj, 'getInstance'), '');
            //$t = call_user_func(array($obj, 'getInstance'), '');
            foreach ($values as $Id => $value) {
                $t->shardDb($serverId)->insert($value);
            }
        }
    }
    
    
    /**
     * 通过权重获取随机ID
     */
    function getActiveSid() {
        $shardingConfig = config('Db')['sharding_config'];
        $activeSid = randByWeights($shardingConfig);
        if ($activeSid === FALSE) {
            return 0;
        }
        return $activeSid;
    }
    
    function filter0($array) {
        $arr=array();
        foreach($array as $i=>$d){
            if($d!=0){
                $arr[]=$d;
            }
        }
        return $arr;
    }
    
    /**
     * 通过权重获取随机档次ID
     */
    function getRandomCard($card1,$card2,$card3,$card4,$card5,$card6) {
        $bonus = $this->get_tplt("bonus_tplt");
        ;
        $bonus1=[];
        $bonus2=[];
        $bonus3=[];
        foreach ($bonus as $key => $value) {
            if($this->gameConfig('account')['mod1'][0]<$key&&$key<$this->gameConfig('account')['mod1'][1]){
                $bonus1[$key]=$value;
            }
            elseif ($this->gameConfig('account')['mod2'][0]<$key&&$key<$this->gameConfig('account')['mod2'][0]) {
                $bonus2[$key]=$value;
            }
            elseif ($this->gameConfig('account')['mod3'][0]<$key&&$key<$this->gameConfig('account')['mod3'][0]) {
                $bonus3[$key]=$value;
            }
        }
        
        $bonusList = array();
        foreach ($bonus as $key => $value) {
            foreach ($value as $keys => $values) {
                if($keys=="rate"){
                    $bonusList[$key] = intval($values);
                }
            }       
        }
        
        $randomBid = randByWeights($bonusList);
        if ($randomBid === FALSE) {
            $this->setError(10012, "random bonus_id is error");
            return false;
        }
        $bonus_data = $this->get_tplt_by_id("bonus_tplt",$randomBid);
        if(!$bonus_data){
            $this->setError(10012, "random bonus_id is error");
            return false;
        }
        $effect_min = $bonus_data["effect_min"];
        $effect_max = $bonus_data["effect_max"];
        $multiple = $bonus_data["multiple"];
        $randnum = rand($effect_min, $effect_max)-$this->gameConfig('account')['each_card_num'];
        $cardid = rand(1,$this->gameConfig('account')['max_card_num']);     
        $cardArray=[];
        $cardRemianArray =[];
        switch ($cardid) {
            case 1:
                $cardArray = $this->filter0($card1);
                $cardRemianArray = $this->filter0(array_merge($card2,$card3,$card4,$card5,$card6));
                break;
            case 2:
                $cardArray = $this->filter0($card2);
                $cardRemianArray = $this->filter0(array_merge($card1,$card3,$card4,$card5,$card6));
                break;
            case 3:
                $cardArray = $this->filter0($card3);
                $cardRemianArray = $this->filter0(array_merge($card2,$card1,$card4,$card5,$card6));
                break;
            case 4:
                $cardArray = $this->filter0($card4);
                $cardRemianArray = $this->filter0(array_merge($card2,$card3,$card1,$card5,$card6));
                break;
            case 5:
                $cardArray = $this->filter0($card5);
                $cardRemianArray = $this->filter0(array_merge($card2,$card3,$card4,$card1,$card6));
                break;
            case 6:
                $cardArray = $this->filter0($card6);
                $cardRemianArray = $this->filter0(array_merge($card2,$card3,$card4,$card5,$card1));
                break;
            default:
                break;
        }
        
        
        $lastkey = array_rand($cardArray,1);
        $lastNum = $cardArray[$lastkey];
        if ($key !== false){
            array_splice($cardArray, $lastkey, 1);
        }
        
        $newArray=[];
        $lastkeylist = array_rand($cardRemianArray,$randnum);
        foreach ($lastkeylist as $key) {
            $newArray[]=$cardRemianArray[$key];
        }
        
        $newArray = array_merge($newArray, $cardArray);
        shuffle($newArray);
        array_push($newArray, $lastNum);
        wlog($randnum+15);
        wlog("randnum");
        wlog($lastNum);
        wlog("lastnum");

        foreach ($newArray as $key => $value) {
            wlog($value);
        }
                
        return $newArray;
    }
    
    function set_channelid($id) {
        $this->redis()->set("channel_id",$id);
    }
    
    function get_channelid() {
        return $this->redis()->get("channel_id");
    }
    
    /**
     * 通过UID简单Hash规则获取SID
     */
    function getHashSid($uid) {
        return $uid % 10;
    }
    
    function gameConfig($function){
        $key = "gameconfig:{$function}";
        $content = $this->redis()->get($key);
        if (empty($content)) {
            $content = require(DIR . "Config/GameConfig.php" );
            $content = $content[$function];
            $content = Array2String($content);
            $this->redis()->set($key,$content);
        }   
        return String2Array($content);
    }
    
    function read_tplt($file){
        $key = "tplt:{$file}";
        $content = $this->redis()->get($key);
        if (empty($content)) {
            $content = require(DIR . "Tplt/Templates/{$file}.php" );
            $content = Array2String($content);
            $this->redis()->set($key,$content);
        }   
        return String2Array($content);
    }
    
    
    function get_tplt_by_id($file,$id){
        $content = $this->read_tplt($file);
        return $content[$id];
    }
    
    function get_tplt($file){
        $content = $this->read_tplt($file);
        return $content;
    }
    
   
    
    function _getVersion($file){
        $version = $this->redis()->get($file);
        //static $versions = array();
        if (empty($version)) {
            $myfile = fopen(DIR . "Public/{$file}.txt", "r");
            $fv = fgets($myfile);
            list($versionkey, $versionvalue) = explode(":",$fv);
            fclose($myfile);
            unset($versionkey);
            $this->redis()->set($file,$versionvalue);
            $version = $versionvalue;
        }
        return $version;
    
    }
    
    function chkPV($pv){
//        $server_pv = $this->_getVersion("protocolv");
//        if (!$pv || intval($pv)!=intval($server_pv)){
//            $this->setError(1000, "protocol version error");
//            return false;
//        }
        return true;
    }
    
    function chkDV($dv){
        $server_dv = $this->_getVersion("protocolv");
        if (!$pv || intval($pv)!=intval($server_pv)){
            $this->setError(1000, "protocol version error");
            return false;
        }
        return true;
    }
    
    function chkRV($rv){
        $server_rv = $this->_getVersion("resourcev");
        if (!$rv || intval($rv)!=intval($server_rv)){
            $this->setError(1001, "resource version error");
            return false;
        }
        return true;
    }
    
    function chkAV($userid,$av){
        if ($av<0){
            $this->setError(1011, "archive version error");
            return false;
        }     
        $mRole = \App\Model\Role::getInstance($userid);
        $role = $mRole->get();
        if($role['archive_version']==$av){
            return true;
        }
        else{
            $this->setError(1011, "archive version error");
            return false;
        }
        
    }
    
    function chkInitData($datals){
        //$this->setError(1002, "chk initdata error");
        return true;
    }
    
    function chkData($dv,$datals){
        //$this->setError(1003, "chk data error");
        return true;
    }
    
    function chkRegister($username,$password){
        $name_min = $this->gameConfig('account')['user_name_min'];
        $name_max = $this->gameConfig('account')['user_name_max'];
        $pass_min = $this->gameConfig('account')['user_password_min'];
        $pass_max = $this->gameConfig('account')['user_password_max'];
//        (!preg_match('/^[a-zA-Z]$/i',$username[0]))
        if (!$username || !$password || (!preg_match("/^[0-9a-zA-Z]{{$name_min},{$name_max}}$/i",$username)) || (!preg_match("/^[0-9a-zA-Z]{{$pass_min},{$pass_max}}$/i",$password))) {
            $this->setError(1004, "register check error");
            return false; //用户名不符合要求
	} else {
            return true; //用户名符合要求
	}
		
    }
    
    public function chkToken($username,$password,$initdata,$token){
        $accessToken = \App\Model\AccessToken::getInstance();       
        if (!$accessToken->check($username,$password,$token)){
            $this->setError($accessToken->errCode(),$accessToken->errMsg());
            return false;
        }
        return true;
		
    }
    
    function chkVersion($pv,$rv){
        //协议号校验
        if (!$this->chkPV($pv)){
            return false;
        }
        //资源号校验
        if (!$this->chkRV($rv)){
            return false;
        }
        
        return true;
    }
    
    function generelChk($pv, $rv, $token, $username, $password, $dv, $data, $initdata){
        //dump(11);
        if(!$this->chkVersion($pv, $rv)){
            return false;
        }
        
        //dump(12);
        if(!$this->chkData($dv,$data)){
            return false;
        }
        //dump(13);
        if(!$this->chkRegister($username,$password)){
            return false;
        }
        //dump(14);
        if(!$this->chkToken($username,$password,$initdata,$token)){
            return false;
        }
        //dump(15);
        return true;
    }
    
    function commonInitReturn($token,$isbind,$timestamp,$mRole,$mItem,$mHero,$mCopy,$bussinessData){
        $role = $mRole->get();
        $item = $mItem->get();
        $hero = $mHero->get();
        $copy = $mCopy->get();
//        dump($item);
        $heroData = array();
        foreach ($hero as $key => $value) {
            $heroData[$value['hero_id']] = intval($value['is_lock']);
        }
        $copyData = array();
        foreach ($copy as $key => $value) {
            $copyData[$key] = array(intval($value['copy_type']),intval($value['copy_score']),intval($value['copy_star']),intval($value['copy_lock']));
        }
        $itemData = array();
        foreach ($item as $key => $value) {
            $itemData[$value['item_id']] = intval($value['item_num']);
        }
        $mpower = $mRole->getPower();
        return ["common"=>["av"=>intval($role['archive_version']),"token"=>$token,"timestamp"=>intval($timestamp),"isbind"=>intval($isbind)],"init"=>["coin"=>intval($role['coin']),"diamond"=>intval($role['diamond']),"power"=>[intval($mpower['power']),intval($mpower['time']),intval($mpower['remain'])],"item"=>$itemData,"hero"=>$heroData,"copy"=>$copyData],"business"=>$bussinessData];
    }
    
    function commonInitAvReturn($token,$isbind,$timestamp,$mRole,$mItem,$mHero,$mCopy,$mTask,$mGiftMall,$mItemMall){
        $role = $mRole->get();
        $item = $mItem->get();
        $hero = $mHero->get();
        $copy = $mCopy->get();
        
        $msignin = $mRole->get_signin();
        $signindata = ["signin"=>[intval($msignin['day']),intval($msignin['normal']),intval($msignin['replenish']),intval($msignin['time'])]];
        $msignin_reward = $mRole->get_signin_reward();
        
        $mpower = $mRole->getPower();
        
        $taskstatus = $mTask->get_taskstatus();
        $taskstatusData = ["task"=>$taskstatus];
        
        $giftbuycount = $mGiftMall->get_buycount();
        $giftbuycountData = ["giftmall"=>$giftbuycount];
        
        $itembuycount = $mItemMall->get_buycount();
        $itembuycountData = ["itemmall"=>$itembuycount];
        
        $heroData = array();
        foreach ($hero as $key => $value) {
            $heroData[$value['hero_id']] = intval($value['is_lock']);
        }
        $copyData = array();
        foreach ($copy as $key => $value) {
            $copyData[$key] = array(intval($value['copy_type']),intval($value['copy_score']),intval($value['copy_star']),intval($value['copy_lock']));
        }
        $itemData = array();
        foreach ($item as $key => $value) {
            $itemData[$value['item_id']] = intval($value['item_num']);
        }
        $bussinessData1 = array_merge($signindata,$taskstatusData);     
        $bussinessData2 = array_merge($giftbuycountData,$itembuycountData);
        $bussinessData3 = array_merge($msignin_reward,$bussinessData2);
        $bussinessData = array_merge($bussinessData1,$bussinessData3);
        return ["common"=>["av"=>intval($role['archive_version']),"token"=>$token,"timestamp"=>intval($timestamp),"isbind"=>intval($isbind)],"init"=>["coin"=>intval($role['coin']),"diamond"=>intval($role['diamond']),"power"=>[intval($mpower['power']),intval($mpower['time']),intval($mpower['remain'])],"item"=>$itemData,"hero"=>$heroData,"copy"=>$copyData],"business"=>$bussinessData];
    }
    
    function commonBusinessParmReturn($mAccount,$userid) {
        $account = $mAccount->getAccount($userid);
        $isbind = intval($account['isbind']);
        $mAccessToken = \App\Model\AccessToken::getInstance();
        $ret = $mAccessToken->get($userid);
        $newtoken = $ret['accessToken'];
        $timestamp = $ret['timestamp'];
        //dump($timestamp);
        $mRole = \App\Model\Role::getInstance($userid);
        $mItem = \App\Model\Item::getInstance($userid);
        $mHero = \App\Model\Hero::getInstance($userid);
        $mCopy = \App\Model\Copy::getInstance($userid); 
        $mTask = \App\Model\Task::getInstance($userid);
        $mGiftMall = \App\Model\GiftMall::getInstance($userid);
        $mItemMall = \App\Model\ItemMall::getInstance($userid);
        return [$isbind,$newtoken,$timestamp,$mRole,$mItem,$mHero,$mCopy,$mTask,$mGiftMall,$mItemMall];
    }
    
    function commonInit($userid,$initData,$gv,$cid){
        
        $coinArray = $initData->coin;
        $diamondArray = $initData->diamond;
        $powerArray = $initData->power;
        $heroArray = $initData->hero;
        $itemArray = $initData->item;
        $copyArray = $initData->copy;
        
        $mRole = \App\Model\Role::getInstance($userid);
        $mRole->updateCoin($coinArray);
        $mRole->updateDiamond($diamondArray);
        $mRole->updatePower($powerArray);
        $mRole->changeVersion($gv,$cid);
        
        $mHero = \App\Model\Hero::getInstance($userid);
        $mHero->updateHero($heroArray,$gv,$cid);
        
        $mItem = \App\Model\Item::getInstance($userid);
        $mItem->updateItem($itemArray,$gv,$cid);
        
        $mCopy = \App\Model\Copy::getInstance($userid);
        $mCopy->updateCopy($copyArray,$gv,$cid);
 
    }
    
    function  save_broadcast($content){
        $key = "broadcast";
        $this->redis()->set($key,$content);
    }
    
    function redis_reset() {
        //删除tplt
        $tpltList = $this->redis()->keys("tplt*");
        foreach ($tpltList as $tpltLists) {
            $this->redis()->del($tpltLists);
        }
        $tpltkey = "back:version:reward_index_tplt";
        $reward_index_tplt_version = $this->redis()->get($tpltkey);
        if($reward_index_tplt_version){
            $this->redis()->set($tpltkey,$reward_index_tplt_version+1);
        }else{
            $this->redis()->set($tpltkey,1);
        }
        //删除版本号
        $this->redis()->del("resourcev");
        //删除协议号
        $this->redis()->del("protocolv");
        //删除gameconfig
        $gameList = $this->redis()->keys("gameconfig*");
        foreach ($gameList as $gameLists) {
            $this->redis()->del($gameLists);
        }
        
    }
    
    function tplt_reset($file) {
        //删除tplt
        $tplt = $this->redis()->keys("tplt:{$file}");      
        $this->redis()->del($tplt);
        
    }
    
    function get_reward_index_tplt($version) {
        $key = "back:version:reward_index_tplt";
        $curVersion = intval($this->redis()->get($key));
        if($curVersion==$version){
            return [$version,[]];
        }
        $shard = \App\Model\Shard::getInstance();
        $tplt = $shard->get_tplt("reward_index_tplt");
        $tplts = array();
        foreach ($tplt as $key => $value) {
           $tplts[$key] = $value['describe'];
        }
        return [$curVersion,$tplts];
    }
    
    function get_mall_tplt() {
        $shard = \App\Model\Shard::getInstance();
        $tplt = $shard->get_tplt("mall_tplt");
        return $tplt;
    }
    
    function convert_cdkey($res,$username,$award) {
        
        foreach ($award as $awards) {
            $id = $awards->id;
            $count = $awards->count;
            $reward_index_tplt = $this->get_tplt_by_id("reward_index_tplt",$id);
            $first = intval($reward_index_tplt['type']);
            $second = intval($reward_index_tplt['link_id']);
            $value = [$first,$second,intval($count)];
            if (!Resouce::giveResouce($res,$username,$value)) {
                return false;
            }
        }
        return true;
    }
    
    function  get_broadcast(){
        $key = "broadcast";
        return $this->redis()->get($key);
    }
            
    function test() {
        $userid = "1466068337625000550";
        $mAccount = \App\Model\Account::getInstance();
        $data = $mAccount->getAccount($userid);
//        $this->redis()->keys("tplt*")
        $datas = [
            'user_id' => $data['user_id'],
            'user_name' => $data['user_name'],
            'password' => $data['password'],
            "register_time" => mktime(15,0,0,5,18,2016),
            "server_id" => $data['server_id'],
            "isbind" => $data['isbind'],
        ];
        \App\PO\Account::getInstance()->db()->where(["user_id" => $userid])->update($datas);
        
        $this->redis()->multi()
                ->hMset("account:{$userid}", $datas)
                ->exec();
//          $key = "role:2:1463471996378003759:signin";
//        $newdata=[
//                    'normal' => $sign_normal,
//                    'replenish' => $sign_replenish,
//                    "day" => $day,
//                    "time"=> $nowTime
//                ];   
        $nowTime = mktime(15,10,0,5,18,2016);
        $cacheKey = "role:1:1466068337625000550:signin";
        $newdata=[
                    'normal' => 1,
                    'replenish' => 0,
                    "day" => 30,
                    "time"=> $nowTime
                ];
        $this->redis()->multi()
                ->hMSet($cacheKey, $newdata)
                ->exec();
//        dump($this->redis()->keys("tplt*"));
    }
    

}
