<?php
/**
 * 短信
 * @author bxl@gmail.com
 * @date 2018-06-13
 *
 */
class SmsModel
{
	/**
	 * @desc 发送又拍云短信
	 * @param $phone
	 * @param $vars
	 * @author bxl@gmail.com
	 * @time 2018/06/13
	 */
	public static function sendByUpyun($phone, $vars)
	{		
		$url = 'https://sms-api.upyun.com/api/messages';
		$token  = 'sWlYEWNkuj7hytQ6K8HZ6V91Ov0OBE';
		# 构造msg
		$msg = array(
			'mobile' => $phone,
			'template_id' => 1742,
			'vars' => $vars,
		);
		
		$options = array(
				'http' => array(
						'header'  => "Content-type: application/x-www-form-urlencoded\r\nAuthorization: $token",
						'method'  => 'POST',
						'content' => http_build_query($msg)
				)
		);
		$context  = stream_context_create($options);
		$result = file_get_contents($url, false, $context);
	}
}
