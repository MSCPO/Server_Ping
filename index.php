<?php

namespace Kers;

use Kers\Utils;
use xPaw\MinecraftPing;
use xPaw\MinecraftPingException;

require __DIR__ . '/utils.class.php';

$Utils = new Utils();

header("Access-Control-Allow-Origin: *");
header('Content-type: application/json');
error_reporting(0);

$api_version = 'v0.5.7';

// 获取当前运行脚本的服务器的IP地址
$api_nodeIP = $_SERVER['SERVER_ADDR'];

$array = [
	'code' => 201,
	'api_version' => $api_version,
	'status' => 'offine',
	'ip' => 'N/A',
	// 'real' => 'N/A',
	'location' => 'N/A',
	'api_node' => $Utils->getLocation($api_nodeIP),
	'port' => 'N/A',
	'motd' => 'N/A',
	'agreement' => 'N/A',
	'version' => 'N/A',
	'online' => 0,
	'max' => 0,
	'gamemode' => 'N/A',
	'delay' => 'N/A',
	'client' => 'N/A'
];

if (!$Utils->hasEmpty($_REQUEST['ip'], $_REQUEST['port'])) {
	$ip = $_REQUEST['ip'];
	$port = $_REQUEST['port'];
	if (!isset($_REQUEST['java'])) {

		// 服务器IP入口
		define('MQ_SERVER_ADDR', $_REQUEST['ip'] );
		define('MQ_SERVER_PORT', $_REQUEST['port']);
		define('MQ_TIMEOUT', 1);

		// 将所有内容显示在浏览器中，因为有些人无法查看日志以查找错误。
		error_reporting( E_ALL | E_STRICT );
		ini_set( 'display_errors', '1' );

		require __DIR__ . '/src/MinecraftPing.php';
		require __DIR__ . '/src/MinecraftPingException.php';

		$Timer = microtime( true );

		$Info = false;
		$Query = null;

		try
		{
			$Query = new MinecraftPing( MQ_SERVER_ADDR, MQ_SERVER_PORT, MQ_TIMEOUT );

			$Info = $Query->Query( );
			
			if( $Info === false )
			{
				/*
				 * 如果这个服务器版本低于1.7，我们可以尝试使用较旧的协议重新查询它
				 * 这个函数返回的数据格式可能与1.7版本的输出格式不同，如果你想匹配1.7的输出，你需要自己手动映射这些内容
				 * 如果你确定这个服务器使用的是较旧的版本，那么你可以直接调用QueryOldPre17，避免调用Query()和重新连接部分
				 */
	
				$Query->Close( );
				$Query->Connect( );
	
				$Info = $Query->QueryOldPre17( );
			}
		} catch (MinecraftPingException $e) {
			$array['code'] = 201;
			$Exception = $e;
		}

		if( $Query !== null )
		{
			$Query->Close( );
		}

		$Timer = number_format( microtime(true) - $Timer, 4, '.', '');
		
		// JAVA数据返回
		if (isset($Exception)) {
			$array = [
				'code' => 201,
				'api_version' => $api_version,
				'status' => 'offine',
				'ip' => 'N/A',
				// 'real' => 'N/A',
				'location' => 'N/A',
				'api_node' => $Utils->getLocation($api_nodeIP),
				'port' => 'N/A',
				'motd' => 'N/A',
				'agreement' => 'N/A',
				'version' => 'N/A',
				'online' => 0,
				'max' => 0,
				'gamemode' => 'N/A',
				'delay' => 'N/A',
				'client' => 'N/A'
			];
		} else {
			// 获取所有已被分割的motd的'text'键的值并将这些值拼接成一个字符串
			if (isset($Info['description']['extra']) && is_array($Info['description']['extra'])) {
				$textValues = array_column($Info['description']['extra'], 'text');
			} else {
				$textValues = $Info['description'];
			}
			$concatenatedText = implode($textValues);
			$real = gethostbyname($ip);
			$array = [
				'code' => 200,
				'api_version' => $api_version,
				'status' => 'online',
				'ip' => $ip,
				// 'real' => $real,
				'location' => $Utils->getLocation($real),
				'api_node' => $Utils->getLocation($api_nodeIP),
				'port' => $port,
				'motd' => $concatenatedText,
				'agreement' => $Info['version']['protocol'],
				'version' => $Info['version']['name'],
				'online' => $Info['players']['online'],
				'max' => $Info['players']['max'],
				'gamemode' => 'N/A',
				'delay' => round($Timer, 3) * 1000,
				'client' => 'JE'
			];
		}
	} else {
		// 基岩版查询逻辑
		$t1 = microtime(true);
		if ($handle = stream_socket_client("udp://{$ip}:{$port}", $errno, $errstr, 2)) {
			stream_set_timeout($handle, 2);
			fwrite($handle, hex2bin('0100000000240D12D300FFFF00FEFEFEFEFDFDFDFD12345678') . "\n");
			$result = strstr(fread($handle, 1024), "MCPE");
			fclose($handle);
			$data = explode(";", $result);
			$data['1'] = preg_replace("/§[a-z A-Z 0-9]{1}/s", '', $data['1']);
			if (!$Utils->hasEmpty($data, $data['1'])) {
				$t2 = microtime(true);
				$real = gethostbyname($ip);
				$array = [
					'code' => 200,
					'api_version' => $api_version,
					'status' => 'online',
					'ip' => $ip,
					// 'real' => $real,
					'location' => $Utils->getLocation($real),
					'api_node' => $Utils->getLocation($api_nodeIP),
					'port' => $port,
					'motd' => $data['1'],
					'agreement' => $data['2'],
					'version' => $data['3'],
					'online' => $data['4'],
					'max' => $data['5'],
					'gamemode' => $data['8'],
					'delay' => round($t2 - $t1, 3) * 1000,
					'client' => 'BE'
				];
			} else {
				$array['code'] = 203;
			}
		} else {
			$array['code'] = 202;
		}
	}
} else {
	$array['code'] = 201;
}
// 通过JSON格式返回
exit(json_encode($array, JSON_UNESCAPED_UNICODE));
?>
