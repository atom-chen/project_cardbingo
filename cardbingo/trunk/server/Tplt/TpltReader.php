<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Tplt;




class TpltReader {   
    
    function searchDir($path,&$data){
        if(is_dir($path)){
            $dp=dir($path);
            while($file=$dp->read()){
                if($file!='.'&& $file!='..'){
                    self::searchDir($path.'/'.$file,$data);
                }
            }
            $dp->close();
        }
        
        if(is_file($path)){
            $data[]=$path;
         }
    }

    function getDir($dir){
        $data=array();
        self::searchDir($dir,$data);
        return   $data;
    }
    
    function readTplt(){
        //echo phpversion();
        //echo phpinfo();
        $redis = \Core\Redis::getInstance();
        $redis->set("1","2");
        $tpltPath = self::getDir(DIR . "Tplt/Templates");
        //dump($tpltPath);
        foreach ($tpltPath as $xml) {
            $filename = basename($xml);
            //dump($filename);
            tplt($filename);
            //dump($content);
        };
        foreach ($tpltPath as $xml) {
            $filename = basename($xml);
            //dump($filename);
            $content = self::getAll($filename);
            //dump($content);
            foreach ($content as $key => $value) {
                $contents = self::getByID($filename,$key);
                //dump($contents);
            }
            
        };
    }
        
    function getAll($file){
        return tplt($file);
    }
        
    function getByID($file,$id){
        $contents = tplt($file);
        return $contents[$id];
    }
    
    function getByKey($file,$id,$key){
        $content = tplt($file);
        $contents = $content[$id];
        return $contents[$key];
    }
    
}  
    
    


	




    
