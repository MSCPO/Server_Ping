<?php

namespace Kers;

class Utils
{
	public function getRealIp()
	{
		$ip = false;
		if (!empty($_SERVER["HTTP_CLIENT_IP"])) {
			$ip = $_SERVER["HTTP_CLIENT_IP"];
		}
		if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
			$ips = explode(", ", $_SERVER['HTTP_X_FORWARDED_FOR']);
			if ($ip) {
				array_unshift($ips, $ip);
				$ip = FALSE;
			}
			for ($i = 0; $i < count($ips); $i++) {
				if (!preg_match("/^(10|172\\.16|192\\.168)\\./", $ips[$i])) {
					$ip = $ips[$i];
					break;
				}
			}
		}
		return ($ip ? $ip : $_SERVER['REMOTE_ADDR']);
	}

	public function hasEmpty(...$a)
	{
		foreach ($a as $key => $val) {
			if (empty($val) || $val == null || $val == []) return true;
		}
		return false;
	}

	public function getLocation($ip = false, $maxRetries = 3)
	{
		$ip = !$ip ? $this->getRealIp() : $ip;
		$retries = 0;
		$s = false;
	
		while ($retries < $maxRetries) {
			try {
				$s = file_get_contents("http://whois.pconline.com.cn/ip.jsp?ip={$ip}", true);
				if ($s !== false) {
					break; // 如果请求成功，则退出循环
				}
			} catch (Exception $e) {
				// 忽略异常，继续重试
			}
	
			$retries++;
			sleep(1); // 暂停1秒再重试
		}
	
		if ($s === false) {
			// 如果在最大重试次数后仍然失败，可以选择抛出异常或返回默认值
			throw new Exception('Failed to get location after ' . $maxRetries . ' attempts.');
		}
	
		$encode = mb_detect_encoding($s, array("ASCII", 'UTF-8', "GB2312", "GBK", 'BIG5'));
		$s = mb_convert_encoding($s, 'UTF-8', $encode);
		$s = str_replace(PHP_EOL, '', $s);
		$s = str_replace("\\r", '', $s);
		return $s;
	}
}
