<?php
/**
 * redis操作
 * @author bxl@gmail.com
 * @date 2017-12-25
 *
 */
class RedisModel
{
	public static $redis = null;
	
	public function __construct()
	{
	}
	
	public static function getInstance()
	{
		if (self::$redis == null) {
			$redis = new Redis();
			$cfg = Config::redis('queue');
			$re = $redis->connect($cfg[0], $cfg[1]);
			if (!$re) {
				throw new Exception('redis连接失败');
			}
			self::$redis = $redis;
		}
		return self::$redis;
	}
	
	/**
	 * @desc 获取锁
	 * @param $lockKey
	 * @param $timeOut
	 * @return bool
	 * @author bxl
	 * @date 2018/07/26
	 */
	public function getLock($lockKey, $timeOut = 5)
	{
		# lockKey判空
		if(empty($lockKey)) {
			return FALSE;
		}
		
		$redis = self::getInstance();
		$nowTime = time();
		$setStatus = $redis->setnx($lockKey, $nowTime);
		
		if(! $setStatus)
		{
			$lockTime = $redis->get($lockKey);
			# 判断死锁
			if(intval($lockTime + $timeOut) < $nowTime)
			{
				$redis->del($lockKey);
				$lock = $redis->setnx($lockKey, $nowTime);
			}
		}
		
		return $lock ? TRUE : FALSE;
	}
	
	/**
	 * @desc 释放锁
	 * @param $lockKey
	 * @return bool
	 * @author bxl
	 * @date 2018/07/26
	 */
	public function unLock($lockKey)
	{
		if(empty($lockKey)) {
			return FALSE;
		}
		
		$redis = self::getInstance();
		
		return $redis->del($lockKey);
	}
	
	/**
	 * @desc 获取有效key
	 * @param $key
	 * @param $params
	 * @return string
	 */
	public function getRealKey($key, $params = array())
	{
		$s = vsprintf($key, $params);
		
		return $s;
	}	
}
