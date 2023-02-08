# 1 获取小米账号的 Token

- 首先进入小米账号官网，获取小米账号 ID **（不是手机号！不是手机号！不是手机号！）**

- 然后，准备下面链接：

  ```
  https://account.xiaomi.com/pass/serviceLoginAuth2?_json=true&sid=micoapi&locale=zh_CN&user=</userId>&hash=</password>
  ```

  将链接中的`</userId> `替换成小米账号 ID，将`</password> `替换成经过 MD5 32位加密的密码，然后用 `POST` 请求发送，就可以得到 Token 了。

# 2 获取小米音响 OTA 固件包的地址

以下算法为试验性算法，可能会随着官方的升级而失效。

## 2.1 升级通道说明

| 通道代号 | 通道名 | 说明                   |
| -------- | ------ | ---------------------- |
| release  | 稳定版 | 正式版 user-release    |
| current  | 测试版 | 工厂调试 factory-debug |
| stable   | 开发版 | 用户调试 user-debug    |

## 2.2 使用说明

### 2.2.1 确定音响型号

用以下型号的音响为例子：

- 型号：LX04
- 通道：稳定版（release）
- 固件版本号：2.30.4

### 2.2.2 准备 MD5 校验参数替换参数

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

再将此字符串进行 MD5 32位加密，得到S码：

```
D0EC04694F3048B18286A8E90C8E2B91
```

### 2.2.3 替换参数

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

# 3 如何向设备推送 FOTA 升级？

1、首先需要获取小米账号的绑定信息，其中包含各种token。

```
Get inspiration here :D
https://bbs.125.la/forum.php?mod=viewthread&tid=14688395&extra=
```

2、获取到绑定的设备、serviceToken、deviceID这些必要信息后，对以下字符串进行替换：


  ```
  deviceId=<deviceId/>&hardware=<hardware/>&requestId=Gt6NrZ9VBlF5a1ceXeIR&extra=<extra/>&url=<url/>&version=<version/>&checksum=<checksum/>
  ```

- `<deviceId/>`要刷机的机器ID
- `<hardware/>` 要刷机的设备型号
- `<extra/>` is the "otherParam" in the response data when requesting package information. It is a standard JSON array. You need to remove the delimiter like'\\'.
- `<url/> `刷机包的地址URL地址，地址中包含`payload.bin`字样
- `<checksum/>`刷机包的HASH值，在响应结果里能找到

3、上述链接完成后，将参数使用URL编码后使用POST提交。

接口地址：`https://api.mina.mi.com/remote/ota/v2`
