## Server_Ping

通过`UDP/TCP`协议获取MC的服务器状态，支持Vercel Serverless部署。

请求方式：`GET/POST`

### 请求参数 ###

参数|示例|描述
-|-|-
ip|aomemsc.giize.com|服务器IP地址
port|25565|服务器端口
java|null|获取基岩服信息

### 返回参数 ###

参数|示例|描述
-|-|-
code|200|接口响应状态码
api_version|v0.5.7|API版本
status|online|服务器状态
ip|example.com|返回查询IP
port|25565|返回查询端口
location|北京省 联通|线路所在位置
motd|Minecraft服务器集体宣传组织（MSCPO）|服务器广播内容Motd
agreement|389|协议版本
version|1.14.30|客户端版本
online|3|服务器在线人数
max|10|服务器人数上限
gamemode|Survival|游戏模式（仅限基岩版）
delay|64|连接服务器延迟(ms)
client|N/A|服务器类型（BE/JE）

JAVA服务器请求示例`index.php?ip=example.com&port=25565`
基岩服务器请求示例`index.php?ip=example.com&port=19132&java=null`

### 错误码 ###

错误码|描述
-|-
200|正常
201|无法与输入地址握手
202|无法建立UDP连接（基岩版）
203|无法解析Hex（基岩版）

项目基于 [PHP-Minecraft-Query](https://github.com/xPaw/PHP-Minecraft-Query) 
借鉴了[motd-for-minecraft](https://github.com/PluginsKers/motd-for-minecraft/)
经过了完整的重写和逻辑替换，如有错误请提出Issure和Pull

本项目由[Minecraft服务器集体宣传组织（MSCPO）维护](http://mscpo.netlify.app/)
