# 1 获取 Service Token

## 1.1 准备必要参数

登录有两种方式：扫码登录、账号密码登录

### 1.1.1 扫码登录

访问登录链接：`https://cn.account.xiaomi.com/longPolling/loginUrl?sid=micoapi`

如果访问正常，将获得类似下面的结果：

```json
{
	"loginUrl": "https://c3.account.xiaomi.com/longPolling/login?ticket=lp_5E654135a795-7e4b-4d77-bea2-01c126fb2c1d&dc=c3&sid=micoapi&ts=1675881863831",
	"qr": "https://account.xiaomi.com/pass/qr/login?ticket=lp_5E654135a795-7e4b-4d77-bea2-01c126fb2c1d&dc=c3&sid=micoapi&_qrsize=0&_hasLogo=true&ts=1675881863831",
	"lp": "https://c3.lp.account.xiaomi.com/lp/s?k=lp_5E654135a795-7e4b-4d77-bea2-01c126fb2c1d",
	"code": 0,
	"result": "ok",
	"desc": "成功",
	"description": "成功"
}
```

- `loginUrl`: 输入用户密码进行登录的页面的URL
- `qr`：二维码图片地址，使用小米APP/米家APP扫码登录
- `lp`: (Loopback?) 登录回调结果地址，在登录前访问，在登录成功后，会返回已登录的账户信息

如果扫码正常登录后，将返回类似登录结果：

```json
{
	"psecurity": "XXXX",
	"nonce": XXX,
	"ssecurity": "XXX",
	"passToken": "XXX",
	"userId": XXX,
	"cUserId": "XXX",
	"securityStatus": 0,
	"notificationUrl": "",
	"pwd": 1,
	"child": 0,
	"code": 0,
	"result": "ok",
	"desc": "成功",
	"description": "成功",
	"location": "XXX",
	"captchaUrl": null
}
```

记录以下值：

- nonce
- ssecurity
- location

### 1.1.2 账号密码登录

1、在无登录状态的情况下调用：`https://account.xiaomi.com/pass/serviceLogin?sid=micoapi&_json=true`

得到如下结果

```json
{
	"serviceParam": "{\"checkSafePhone\":false,\"checkSafeAddress\":false,\"lsrp_score\":0.0}",
	"qs": "%3Fsid%3Dmicoapi%26_json%3Dtrue",
	"code": 70016,
	"description": "登录验证失败",
	"securityStatus": 0,
	"_sign": "XXX",
	"sid": "micoapi",
	"result": "error",
	"captchaUrl": null,
	"callback": "https://api2.mina.mi.com/sts",
	"location": "XXX",
	"pwd": 0,
	"child": 0,
	"desc": "登录验证失败"
}
```

记录以下参数：

- qs
- _sign
- sid
- callback

2、调用接口：`https://account.xiaomi.com/pass/serviceLoginAuth2`

准备参数：

- user: 小米用户ID
- hash: 账号密码Base64编码大写

请求参数(查询参数)：

```
qs=上面的参数【qs】&_sign=上面的参数【_sign】&sid=上面的参数【micoapi】&callback=上面的参数【callback】&_json=true&user=上面的参数【user】&hash=上面的参数【hash】
```

请求结果如下：

```json
{
	"qs": "?sid=micoapi&_json=true",
	"ssecurity": "XXX",
	"code": 0,
	"passToken": "XXX",
	"description": "成功",
	"securityStatus": 0,
	"nonce": XXX,
	"userId": XXX,
	"cUserId": "XXX",
	"result": "ok",
	"psecurity": "XXX",
	"captchaUrl": null,
	"location": "XXX",
	"pwd": 1,
	"child": 0,
	"desc": "成功"
}
```

记录以下参数：

- nonce
- ssecurity
- location

## 1.2 获取 ServiceToken

到目前为止，我们已经拿到当前账号的：

- nonce
- ssecurity
- location

填入以下 python 脚本

```python
import base64
import hashlib
import requests
from urllib import parse

nonce=1234567890
ssecurity=f"XXX"
location=f"XXX"
n = f"nonce={str(nonce)}&{ssecurity}"
sign = base64.b64encode(hashlib.sha1(n.encode()).digest()).decode()
url = f"{location}&clientSign={parse.quote(sign)}"
print(url)
response = requests.session().get(url)
print(response.text)
print(f"serviceToken: " + response.cookies.get("serviceToken"))
```

请求得到的URL，请求成功后页面得到结果：

```json
{
	"R": "",
	"S": "OK"
}
```

然后再在 Cookie 里即可找到 ServiceToken，拿到 ServiceToken 后即可完成接下来的操作。

# 2 获取设备列表

## 2.1 请求参数

- API:https://api.mina.mi.com/admin/v2/device_list?master=0&requestId=CdPhDBJMUwAhgxiUvOsKt0kwXThAvY
- 请求附加Cookie：
  - userId: 小米用户ID
  - serviceToken: 当前登录用户的 serviceToken

## 2.2 请求结果

```json
{
    "code": 0,
    "message": "Success",
    "data": [
        {
            "deviceID": "设备ID",
            "serialNumber": "设别序列号",
            "name": "小爱触屏音箱",
            "alias": "小爱触屏音箱",
            "current": false,
            "presence": "online",
            "address": "当前登录IP地址",
            "miotDID": "XXX",
            "hardware": "硬件型号",
            "romVersion": "最新稳定版固件版本号",
            "capabilities": {
                "ai_protocol_3_0": 1,
                "content_blacklist": 1,
                "child_mode": 1,
                "voice_print_multidevice": 1,
                "tone_setting": 1,
                "family_album": 1,
                "earthquake": 1,
                "alarm_repeat_option_v2": 1,
                "user_nick_name": 1,
                "player_pause_timer": 1,
                "continuous_dialogue": 1,
                "family_voice": 1,
                "child_mode_2": 1,
                "nearby_wakeup": 1,
                "voice_print": 1,
                "skill_try": 1,
                "voip_signal": 1,
                "mico_current": 1,
                "classified_alarm": 1,
                "screen_mode": 1,
                "mesh": 1,
                "voip_used_time": 1
            },
            "remoteCtrlType": "",
            "deviceSNProfile": "XXX",
            "deviceProfile": "XXX",
            "brokerEndpoint": "XXXX",
            "brokerIndex": 35,
            "mac": "WIFI 硬件 MAC 地址",
            "ssid": ""
        }
    ]
}
```

# 3 获取小米音响 OTA 固件包的地址

## 3.1 升级通道说明

| 通道代号 | 通道名 | 说明                   |
| -------- | ------ | ---------------------- |
| release  | 稳定版 | 正式版 user-release    |
| current  | 测试版 | 工厂调试 factory-debug |
| stable   | 开发版 | 用户调试 user-debug    |

## 3.2 使用说明

### 3.2.1 确定音响型号

用以下型号的音响为例子：

- 型号：LX04
- 通道：稳定版（release）
- 固件版本号：2.30.4

### 3.2.2 准备 MD5 校验参数替换参数

准备以下文本：

```
channel=<channel/>&filterID=<sn/>&locale=zh_CN&model=<model/>&time=1617368871545&version=<version/>&8007236f-a2d6-4847-ac83-c49395ad6d65
```

- 替换`<model/> `为音响型号`LX04`；
- 替换`<channel/>`为通道代号`release`；

- 替换`<version/> `为固件版本号`2.30.4`；

- `<sn/>`是一个可选项，它代表音响的SN码，可以不填此参数，但要保留`filterID=`这个字符串。

替换好的文本如下：

```
channel=release&filterID=&locale=zh_CN&model=LX04&time=1617368871545&version=2.30.4&8007236f-a2d6-4847-ac83-c49395ad6d65
```

保存后，用 Base64 对这段文本进行编码，得到如下文本：

```
Y2hhbm5lbD1yZWxlYXNlJmZpbHRlcklEPSZsb2NhbGU9emhfQ04mbW9kZWw9TFgwNCZ0aW1lPTE2MTczNjg4NzE1NDUmdmVyc2lvbj0yLjMwLjQmODAwNzIzNmYtYTJkNi00ODQ3LWFjODMtYzQ5Mzk1YWQ2ZDY1
```

再将此字符串进行 MD5 32 位加密，得到S码：

```
D0EC04694F3048B18286A8E90C8E2B91
```

### 3.2.3 替换参数

准备以下链接文本：

```
http://api.miwifi.com/rs/grayupgrade/v2/<model/>?model=<model/>&version=<version/>&channel=<channel/>&filterID=<sn/>&locale=zh_CN&time=1617368871545&s=<code/>
```

- 替换`<code/>`为 `2.2.2 替换参数` 小节中得到到的 S 码。
- 其它参数替换方式同小节 `2.2.2 替换参数`。

替换好的链接文本如下：

```
http://api.miwifi.com/rs/grayupgrade/v2/LX04?model=LX04&version=2.30.4&channel=release&filterID=&locale=zh_CN&time=1617368871545&s=D0EC04694F3048B18286A8E90C8E2B91
```

以上就是该音响的对应版本的固件地址。

### 3.2.4 请求结果

```json
{
	"code": "0",
	"data": {
    // 最新稳定版的固件信息
		"upgradeInfo": {
			"size": 475177487,
			"releaseDate": "1663141680381",
			"changelogUrl": "https://cdn.cnbj1.fds.api.mi-img.com/miwifi/c294a408-1136-477a-b40d-c22f45ebeda2.html", // 更新日志页面 URL
			"link": "https://cdn.cnbj1.fds.api.mi-img.com/xiaoqiang/rom/lx04/payload_2.38.105_d6447.bin",// 更新固件包 URL
			"description": "【优化】爱奇艺更新\n【优化】解决云丁猫眼联动的适配问题\n【优化】优化协同唤醒的体验\n【优化】睡眠模式功能优化\n【优化】音箱语音控制电视功能优化，电视需登录小米账号，控制更稳定\n【优化】修复了一些已知问题", // 更新说明
			"weight": "1",
			"otherParam": "{\"FILE_HASH\":\"kEzyVCBydVrrjVxSXhpu368I73cKlgnY2REWFzk83Q0=\",\"FILE_SIZE\":\"475177487\",\"METADATA_HASH\":\"KwAeuHfc93aVpyPBeJch390Av/GMFdozMzdEN57YmKc=\",\"METADATA_SIZE\":\"33263\"}",
			"upgradeId": "64550",
			"hash": "c21c14e98fa85dff570e43a4d1ed6447",
			"toVersion": "2.38.105"
		},
  	// 指定版本的固件信息
		"currentInfo": {
			"size": 52179195,
			"releaseDate": "1610698584064",
			"changelogUrl": "https://cdn.cnbj1.fds.api.mi-img.com/miwifi/36129064-4e20-4216-a3eb-3be2dd16995d.html", // 更新日志页面 URL
			"link": "https://cdn.cnbj1.fds.api.mi-img.com/xiaoqiang/rom/lx04/payload_inc_2.28.12_2.30.4_eb737.bin", // 更新固件包 URL
			"description": "【优化】修复小白视频门铃通话后按钮显示错误的问题以及门锁进入后显示离线的问题\n【优化】支持摄像机或猫眼竖屏适配显示\n【优化】修复了其他一些小问题，系统更稳定", // 更新说明
			"otherParam": "{\"FILE_HASH\":\"VZC1NVrFRWUtWX8UnryM1OdukucV2D69W/DOzueAXvc=\",\"FILE_SIZE\":\"52179195\",\"METADATA_HASH\":\"6dBPtOJ4RWKY8QbvMcWkfWs38uGCZB/L+SHhDh+AiyE=\",\"METADATA_SIZE\":\"86209\"}",
			"upgradeId": "40958",
			"hash": "8826fef0332cdb3f15c337239c5eb737"
		}
	}
}
```

# 4 如何向设备推送 FOTA 升级？

获取到设备信息、刷机包信息后，对字符串进行替换。


  ```
  url=<url/>&deviceId=<deviceId/>&checksum=<checksum/>&version=<version/>&extra=<extra/>&hardware=<hardware/>&requestId=<requestId/>
  ```

- `<url/> `刷机包的地址URL地址，地址中包含`payload.bin`字样
- `<deviceId/>`设备ID
- `<checksum/>`刷机包的HASH值，在响应结果里能找到
- `<version/>`刷机包版本号
- `<extra/>` 刷机包结果里的`otherParam`字段字符串，在提交之前，需要把斜杠"\\"去掉。
- `<hardware/>` 要刷机的设备型号
- `<requestId/>`请求ID，任意随机字符串

3、上述参数替换完成后，使用URL编码，附加在URL后面，作为查询参数进行提交。

- 接口地址：`https://api.mina.mi.com/remote/ota/v2`

例如：

```
POST https://api.mina.mi.com/remote/ota/v2?url=XXX&deviceId=XXX&checksumXXX&version=XXX&extra=XXX&hardwareXXX&requestId=XXX
```

