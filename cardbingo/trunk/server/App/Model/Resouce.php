<?php

namespace App\Model;

/**
 * 所有的资源管理
 * 统一的ID规划
 * 通过ItemId区分类型
 */
class Resouce extends Model {
    
    public static function isCoin($coin) {
        $shard = \App\Model\Shard::getInstance();
        $coinType = $shard->gameConfig('resource')['coin'];
        return $coin == $coinType;
    }
    
    public static function isPower($power) {
        $shard = \App\Model\Shard::getInstance();
        $powerType = $shard->gameConfig('resource')['power'];
        return $power == $powerType;
    }

    public static function isDiamond($diamond) {
        $shard = \App\Model\Shard::getInstance();
        $diamondType = $shard->gameConfig('resource')['diamond'];
        return $diamond == $diamondType;
    }

    /**
     * 检查是否当前资源是否满足
     * @param int $roleId
     * @param int $resourceId
     * @param int $num
     * @param mixed $extParams
     */
    public static function check($roleId, $resource) {
        
        foreach ($resource as $rs) {
            $rid = $rs[0];
            $rnum = $rs[1];
            if ($rnum <= 0) {
                return false;
            }
            switch (true) {
                case self::isPower($rid)://体力
//                    $mRoleData = \App\Model\Role::getInstance($roleId)->get();
//                    if ($mRoleData['coin'] < $rnum) {
//                        return false;
//                    }
                    break;
                case self::isCoin($rid)://金币
                    $mRoleData = \App\Model\Role::getInstance($roleId)->get();
                    if ($mRoleData['coin'] < $rnum) {
                        return false;
                    }
                    break;
                case self::isDiamond($rid)://钻石
                    $mRoleData = \App\Model\Role::getInstance($roleId)->get();
                    if ($mRoleData['diamond'] < $rnum) {
                        return false;
                    }
                    break;
                default :
                    $mItemData = \App\Model\Item::getInstance($roleId)->get($rid);
                    $itemNum = $mItemData["item_num"];
                    if ($itemNum < $rnum) {
                        return false;
                    }
                    break;
            }
        }
        
        
        
        return true;
    }
    
    public static function giveReward($res,$roleId,$rewardid) {
        if(!$rewardid){
            $res->setError(10009, "reward id is error");
            return false;
        }
        $shard = \App\Model\Shard::getInstance();
        $rewardtplt = $shard->get_tplt_by_id("reward_tplt",$rewardid);
        if(!$rewardtplt){
            $res->setError(10009, "reward id is error");
            return false;
        }
        $fixed_reward = $rewardtplt['fixed_reward'];
        $random_time = $rewardtplt['random_time'];
        if(!$random_time){
            $rewardList = $fixed_reward;
        }
        else{
            $rewardArray = array();
            for ($index1 = 0; $index1 < $random_time; $index1++) {
                $random_reward = $rewardtplt['random_reward'];
                $data = array();
                foreach ($random_reward as $key=>$value) {
                    $data[$key]= $value[4];
                }
                $index = randByWeights($data);
                if($index==FALSE){
                    $res->setError(10011, "reward_tplt is error");
                    return false;
                }
                $reward = $random_reward[$index];
                $rewardArray[$index1] = [$reward[0],$reward[1],rand($reward[2],$reward[3])];           
            }
            $rewardList = array_merge($fixed_reward, $rewardArray);     
        }
        $rewardData=array();
        foreach ($rewardList as $rewards) {
            $newkey = $rewards[0].$rewards[1];
            if($rewardData[$newkey]){
                $rewardData[$newkey] = [$rewards[0],$rewards[1],$rewardData[$newkey][2]+$rewards[2]];
            }
            $rewardData[$newkey]=[$rewards[0],$rewards[1],$rewards[2]];
        }
        $rewardData = array_values($rewardData);
        //dump($rewardData);
        foreach ($rewardData as $rewardDatas) {
            $first = intval($rewardDatas[0]);
            $second = intval($rewardDatas[1]);
            $value = intval($rewardDatas[2]);
            if(!$first||!$second){
                continue;
            }
            //dump("first",$first);
            //dump("resource",intval($shard->gameConfig('reward')['resource']));
            //dump("item",intval($shard->gameConfig('reward')['item']));
            Resouce::giveResouce($res,$roleId, [$first,$second,$value]);
        }
        //dump($rewardData);
        $rewardDatass=array();
        //for ($index2 = 0; $index2 < count($rewardData); $index2++) {
        foreach ($rewardData as $key => $value) {
            $rewardDatass[$key]=[intval($value[0]),intval($value[1]),intval($value[2])];
        }
            
        //}  
        //dump($rewardDatass);
        return $rewardDatass;
    }
    
    public static function giveResouce($res,$roleId, $resource) {
        $rtype = intval($resource[0]);
        $rid = intval($resource[1]);
        $rnum = intval($resource[2]);
//        dump($rtype);
//        dump($rid);
//        dump($rnum);
        $shard = \App\Model\Shard::getInstance();
        switch ($rtype) {           
            case $shard->gameConfig('reward')['resource']:
                switch ($rid) {
                    case self::isPower($rid)://体力
                        $mRole = \App\Model\Role::getInstance($roleId);
                        return $mRole->addPower($res,$rnum);
                        break;
                    case self::isCoin($rid)://金币
                        $mRole = \App\Model\Role::getInstance($roleId);
                        return $mRole->addCoin($res,$rnum);
                        break;
                    case self::isDiamond($rid)://钻石
                        $mRole = \App\Model\Role::getInstance($roleId);
                        return $mRole->addDiamond($res,$rnum);
                        break;
                    default :
                        $res->setError(10011, "reward id is error");
                        return false;
                        break;
                }        
                break;
            case $shard->gameConfig('reward')['item']:
                $mItem = \App\Model\Item::getInstance($roleId);
                return $mItem->add($res,$rid,$rnum);
                break;
            default:
                $res->setError(10011, "reward type is error");
                return false;
                break;
        }
    }
    
    public static function useResouce($res,$roleId, $resource) {
        $rid = $resource[0];
        $rnum = intval($resource[1]);
        switch (true) {
            case self::isPower($rid)://体力
                return true;
            case self::isCoin($rid)://金币
                $mRole = \App\Model\Role::getInstance($roleId);
                return $mRole->subCoin($res,$rnum);
            case self::isDiamond($rid)://钻石
                $mRole = \App\Model\Role::getInstance($roleId);
                return $mRole->subDiamond($res,$rnum);
            default :
                $mItem = \App\Model\Item::getInstance($roleId);
                return $mItem->sub($res,$rid,$rnum);
        }
    }
    
    
    

    
    

}
