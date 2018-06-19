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
}
