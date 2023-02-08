## How to push FOTA for any device?
- For how to obtain the updated metadata, please refer to the resign section.

-----

- First, you need to obtain the binding information of the Xiaomi account, which contains a variety of tokens.
```
Get inspiration here :D
https://bbs.125.la/forum.php?mod=viewthread&tid=14688395&extra=
```
- When you get the necessary information about the bound device, serviceToken, and deviceID, then:
```
Replace the content of <deviceId/>, <hardware/>, <extra/> in this string of text with the corresponding metadata obtained.
"deviceId=<deviceId/>&hardware=<hardware/>&requestId=Gt6NrZ9VBlF5a1ceXeIR&extra=<extra/>&url=<url/>&version=<version/>&checksum=<checksum/>"
```
- Some explanations:
```
<deviceId/> will be included in the response data when requesting the device list.
```
```
<hardware/> is the model of the corresponding device.
```
```
<extra/> is the "otherParam" in the response data when requesting package information. It is a standard JSON array. You need to remove the delimiter like'\'.
```
```
<url/> is the "link" in the response data when requesting package information, which contains payload.bin
```
```
<checksum/> is the "hash" in the response data when requesting packet information
```
- Send a request to implement the update: 
```
When ready, you can make a POST request in any way. You need to submit URL-encoded information (filled in in the previous step).
```
```
You can continue to use the cookies obtained when you log in.
```
```
After you have prepared this information, submit it to
"https://api.mina.mi.com/remote/ota/v2"
Your machine will prompt to update to the version you specified.
```