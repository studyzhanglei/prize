<?php
	echo '<pre>';
	$redis 	= new Redis();
	$redis->connect('127.0.0.1');


	/**
	 * 经典的概率算法，
	 * $proArr是一个预先设置的数组，
	 * 假设数组为：array(100,200,300，400)，
	 * 开始是从1,1000 这个概率范围内筛选第一个数是否在他的出现概率范围之内， 
	 * 如果不在，则将概率空间，也就是k的值减去刚刚的那个数字的概率空间，
	 * 在本例当中就是减去100，也就是说第二个数是在1，900这个范围内筛选的。
	 * 这样 筛选到最终，总会有一个数满足要求。
	 * 就相当于去一个箱子里摸东西，
	 * 第一个不是，第二个不是，第三个还不是，那最后一个一定是。
	 * 这个算法简单，而且效率非常高，
	 * 关键是这个算法已在我们以前的项目中有应用，尤其是大数据量的项目中效率非常棒。
	 */
	
	//原先的抽奖算法，经过验证。还是比较精准的
	//新的抽奖算法
	//例: 一等奖概率为 1/1000 1份 概率为 1/1000
	//二等奖概率为 2/1000 2份 中奖概率为  4/1000
	//三等奖中奖率为20/1000 10份 中奖概率为 200/1000
	//那么未中奖的几率为 795/1000
	
	//按抽奖人次算的

	function getPrize($prizeArr, $redis)
	{	
		if (count($prizeArr) <= 0) return 0;
		$sumNumber 	= 0;
		$prizeArr 	= array_filter($prizeArr, function ($v)use(&$sumNumber, $redis){
			$amount 	= $redis->get($v);
			if ($amount <= 0) {
				return 0;
			}

			$sumNumber 	+= $amount;
			return true;
		});
// var_dump($sumNumber);
		if ($sumNumber <= 0) return 0;
		$result 	= 0;
// var_dump($prizeArr);die;
		foreach ($prizeArr as $key => $prize) {
			$amount 		= $redis->get($prize);
			if (!$amount) $amount = 0;
			$numberRand 	= mt_rand(1, $sumNumber);
// var_dump($amount);
// var_dump($percent);
// var_dump($numberRand);die;
// echo '<br />';
			if ($numberRand <= $amount) {
				$have 	= $redis->decr($prize);
				if ($have >= 0) {
					$result 	= $key;
					break;
				} 
				$sumNumber 	-= $amount;
				break;
			} else {
				$sumNumber 	-= $amount;
			}
		}
		// var_dump($result);
		return $result;

	}

	$prizeArr 		= [
		'first'		=>  'first_amount',
		'second' 	=>  'second_amount',
		'third'		=>  'third_amount',
		'none' 		=>  'none_amount',
	];

	//redis 操作
	/*
	set first_amount 1
	set second_amount 4
	set third_amount 200
	set none_amount 795
	*/
	$arr 	= [];

	for ($i = 0; $i < 1000; ++$i) {
		array_push($arr, getPrize($prizeArr, $redis));//die;
	}
	// var_dump($arr);
	var_dump(array_count_values($arr));