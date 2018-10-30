<?php
set_time_limit(0);

function getProb($startTime,$endTime, $randTime)
{
 
    $curTime = msectime() + $randTime;
 
    if($curTime>$endTime || $curTime<$startTime){
        return 0;
    }
 
    $redis      = new Redis();
    $redis->connect('127.0.0.1');
    $memKey     = 'prob_prevTime';
    $prevTime   = $redis->get($memKey);//  -  1000;//mt_rand(1000, 30000);
    $redis->setex($memKey,3600*24*20,$curTime);

    // echo $prevTime . '~~~~~' . date('Y-m-d H:i:s', $prevTime);
 
    if(empty($prevTime) || $prevTime<$startTime) $prevTime = $startTime;
 
    $prob = round(($curTime - $prevTime)/($endTime - $prevTime) * 1000, 3);
    
    // echo '<br />' . $prob;
    return $prob;
}
 

$redis  = new Redis();
$redis->connect('127.0.0.1');
 
$num = $redis->get('gift_number');//剩余库存
 
// $startTime  = strtotime(date('Y-m-d 00:00:00'));
// $endTime    = strtotime(date('Y-m-d 23:59:59'));
// 
$startTime     = time();
$endTime       = strtotime('+1 day');
 
for ($i=0; true; $i++) { 
    # code...
    $prob = getProb($startTime * 1000,$endTime * 1000, $i * 1000);
     
    $prob *= 1000;
    $num = $redis->get('gift_number');
    $prob *= $num;
     
    // echo '<br />' . $prob;
    // sleep(1000);

    if(rand(0,1000000) <= $prob)
    {
        
        $res    = $redis->decr('gift_number');
        if ($res >= 0) {
            echo '<br />' . $prob . '~~~~~ok';
            echo '<br />' . $i; 
            if ($res <= 0) {
                echo '<br /> 第' . $i . '次奖品全部抽完'; 
                exit();
            }
        } else {
            // echo '未中奖';
        }

    } else {
        // echo '<br /> 未中奖'; 
    }
}
    

function  msectime() {
    list($msec, $sec) = explode(' ', microtime());
    $msectime =  (float)sprintf('%.0f', (floatval($msec) + floatval($sec)) * 1000);
    return $msectime;
}

// echo msectime();