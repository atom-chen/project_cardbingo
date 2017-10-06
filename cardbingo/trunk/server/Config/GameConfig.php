<?php

return [
    //账号
    'account' => ["max_reward_rate" => 500,                   //游戏当前设定最大奖励倍率
                    "expectation_profit" => -100000,          //服务器设定期望利润
                    "max_newbie_bet" => 10000,               //新手最大投注金额
                    "max_newbie_count" => 6,                   //新手最大投注次数
                    "max_card_num" => 6,                       //最大卡牌张数
                    "each_card_num" => 15,                     //每张卡最大数字个数
                    "mod1" => [0,100],                         //营收保障模式1取值范围
                    "mod2" => [100,200],                       //新手刺激模式2取值范围
                    "mod3" => [1000,1100]]                     //正常模式3取值范围
];