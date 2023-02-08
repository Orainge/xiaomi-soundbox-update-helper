<?php

function get_sign() {
	$ch = curl_init(); 
	curl_setopt($ch, CURLOPT_URL, "https://account.xiaomi.com/pass/serviceLogin?sid=micoapi&_json=true"); 
	curl_setopt($ch, CURLOPT_HEADER, true);
	curl_setopt($ch, CURLINFO_HEADER_OUT, TRUE);
	curl_setopt($ch, CURLOPT_POST, false);
	//curl_setopt($ch, CURLOPT_HTTPHEADER, array('Cookie: deviceId=DD61C2E6F186DC6D; sdkVersion=iOS-3.2.7', 'User-Agent: MISoundBox/1.4.0 iosPassportSDK/iOS-3.2.7 iOS/11.2.5','Accept-Language: zh-cn','Connection: keep-alive'));
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
	$output = '&&&START&&&{"sid":"micoapi","serviceParam":"{\"checkSafePhone\":false}","desc":"登录验证失败","location":null,"captchaUrl":null,"callback":"https://api.mina.mi.com/sts","code":70016,"qs":"%3Fsid%3Dmicoapi%26_json%3Dtrue","_sign":"xkI9k6Y7vcHJBpsjJjsSqsog7cE="}';
	//$output = curl_exec($ch); 
	curl_close($ch);       
	preg_match('/_sign":"(.*?)"/', $output, $matches, PREG_OFFSET_CAPTURE);
	if (!isset($matches[1])) {
		return '';
	}
	return $matches[1][0];
}


function serviceLoginAuth2($user, $pass, $_sign) {
	//这步有可能会验证验证码,一般不会
	$data = "_json=true&sid=micoapi&user=$user&hash=$pass";
	$ch = curl_init(); 
	curl_setopt($ch, CURLOPT_URL, "https://account.xiaomi.com/pass/serviceLoginAuth2"); 
	curl_setopt($ch, CURLOPT_HEADER, true);
	curl_setopt($ch, CURLINFO_HEADER_OUT, TRUE);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0); // 对认证证书来源的检查
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 1); // 从证书中检查SSL加密算法是否存在
	curl_setopt($ch, CURLOPT_POST, true);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array('Cookie: deviceId=XXXXXXXXXXXXXXXX; sdkVersion=iOS-3.2.7', 'content-type: application/x-www-form-urlencoded'));
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
	curl_setopt($ch, CURLOPT_POSTFIELDS , $data);
	$output = curl_exec($ch); 
	$outhead=curl_getinfo($ch);
	curl_close($ch);      
	preg_match('/location":"(.*?)"/', $output, $matches, PREG_OFFSET_CAPTURE);
	if (!isset($matches[1])) {
		echo $output;
		return '';	
	}
	$result['location'] = $matches[1][0];
	preg_match('/ssecurity":"(.*?)"/', $output, $matches, PREG_OFFSET_CAPTURE);
	$result['ssecurity'] = $matches[1][0];
	preg_match('/nonce":(.*?),/', $output, $matches, PREG_OFFSET_CAPTURE);
	$result['nonce'] = $matches[1][0];
	return $result;

}

function login_miai($url, $clientSign) {
	$ch = curl_init(); 
	curl_setopt($ch, CURLOPT_URL, $url."&clientSign=".urlencode($clientSign)); 
	curl_setopt($ch, CURLOPT_HEADER, true);
	curl_setopt($ch, CURLINFO_HEADER_OUT, true);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0); // 对认证证书来源的检查
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 1); // 从证书中检查SSL加密算法是否存在
	curl_setopt($ch, CURLOPT_POST, false);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array('User-Agent: MISoundBox/1.4.0 iosPassportSDK/iOS-3.2.7 iOS/11.2.5','Accept-Language: zh-cn','Connection: keep-alive'));
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
	$output = curl_exec($ch); 
	$outhead=curl_getinfo($ch);
	curl_close($ch);      
	preg_match('/serviceToken=(.*?);/', $output, $mserviceLoginAuth2atches, PREG_OFFSET_CAPTURE);
	if (!isset($matches[1])) {
		echo $output;
		return '';	
	}
	$res['serviceToken'] = $matches[1][0];
	preg_match('/userId=(.*?);/', $output, $matches, PREG_OFFSET_CAPTURE);
	$res['userId'] = $matches[1][0];
	return $res;

}

function get_device($userid, $serviceToken) {
	$ch = curl_init(); 
	curl_setopt($ch, CURLOPT_URL, "https://api.mina.mi.com/admin/v2/device_list?master=0&requestId=CdPhDBJMUwAhgxiUvOsKt0kwXThAvY"); 
	curl_setopt($ch, CURLOPT_POST, false);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0); // 对认证证书来源的检查
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 1); // 从证书中检查SSL加密算法是否存在
	curl_setopt($ch, CURLOPT_HTTPHEADER, array("Cookie: userId={$userid};serviceToken={$serviceToken}"));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
	$output = curl_exec($ch); 
    $jsondata=json_decode($output,true);

	curl_close($ch);  
    if($jsondata['code'] == 0){
        return $jsondata['data'];
    }
    return '';
}

function serviceToken($nonce, $secrity) {
	#逆向apk获取
	$str = "nonce={$nonce}&".$secrity;
	$sha1 =  sha1($str, true);
	return base64_encode($sha1);
}

function text_to_speech($cookie, $deviceId, $message) {
	$url = "https://api.mina.mi.com/remote/ubus?deviceId=$deviceId&message=%7B%22text%22%3A%22".urlencode($message)."%22%7D&method=text_to_speech&path=mibrain&requestId=rb1gB2aATpRd7jfOpaT3pxp85ndZ7t";
	$ch = curl_init(); 
	curl_setopt($ch, CURLOPT_URL, $url); 
	curl_setopt($ch, CURLOPT_HEADER, false);
	curl_setopt($ch, CURLINFO_HEADER_OUT, false);
	curl_setopt($ch, CURLOPT_POST, TRUE);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array("Cookie: {$cookie}"));
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
	curl_setopt($ch, CURLOPT_POSTFIELDS , "");
	$output = curl_exec($ch); 
	print_r($output);
	curl_close($ch);      
	file_put_contents('/tmp/miai_cookie', $cookie);
	file_put_contents('/tmp/miai_deviceId', $deviceId);
	preg_match('/message":"(.*?)"/', $output, $matches, PREG_OFFSET_CAPTURE);
	if (isset($matches[1]) && $matches[1][0] == 'Success') {
		return true;	
	} else {
		echo $output;
	}
	return false;
}

function speech($user, $pass, $text) {
	$success = false;
	$cookie = "";
	$deviceId = "";
	if ($cookie != '' and $deviceId != '') {
	   $success = text_to_speech($cookie, $deviceId, '要是主动开放点，就不要这么麻烦了');
	   var_dump($success);
	} 

	if (!$success) {
		$_sign = get_sign();
		//if ($_sign == '') continue;
		$session = serviceLoginAuth2($user, $pass, $_sign);
		//if ($session == '') continue;

		//print_r($session);

		$clientSign = serviceToken($session['nonce'], $session['ssecurity']);
		
		//if ($clientSign == '') break;
		//print_r($session['location']);
		$miai_session = login_miai($session['location'], $clientSign);
		//if ($miai_session == '') continue;
   
		$device = get_device($miai_session['userId'], $miai_session['serviceToken']);
        //print_r("********************************");
        $newArray =array() ;
        if(is_array($device)){
                //print_r($device);
            foreach($device as $key => $value){
               // $newArray['key'] = $key;
               // echo "{$key}==>{$value}<br>";
               $tmpArray= array();
                foreach($value as $k => $v){
                    //echo "{$k}==>{$v}<br>";
                    if($k == "name"){
                        $tmpArray['name'] = $v;
                    }
                    if($k == "hardware"){
                        $tmpArray['hardware'] = $v;
                    }
                    if($k == "deviceID"){
                        $tmpArray['deviceID'] = $v;
                    }
                    if($k == "serialNumber"){
                        $tmpArray['serialNumber'] = $v;
                    }
                }
                array_push($newArray,$tmpArray);
                unset($tmpArray);
            }
        }
        $cookie = "userId={$miai_session['userId']};serviceToken={$miai_session['serviceToken']}";
        setcookie('Cookie',$cookie);
        $arr = json_encode($newArray,JSON_UNESCAPED_UNICODE);
        echo $arr;
	}
}

function push_firmware($get_arr){
	if(!is_array($get_arr)){
		echo "参数错误";
		return ;
	}
	$tmp_cookie ='';
    if(isset($_COOKIE['Cookie'])){
        $tmp_cookie = "{$_COOKIE['Cookie']};deviceId={$get_arr['deviceId']};sn={$get_arr['sn']}";
		setcookie('Cookie',$tmp_cookie);
	}
	if(empty($tmp_cookie)){
		echo "请重新登录";
		return ;
	}
	print_r("start push");
	$request_id = base64_encode(uniqid());
	$request_id=str_replace('=','',$request_id);
	$data = array( "url" => "{$get_arr['link']}","deviceId" => "{$get_arr['deviceId']}",
	"checksum" =>"{$get_arr['hash']}","version" => "{$get_arr['version']}","extra" =>"{$get_arr['extra']}",
	"hardware" =>"{$get_arr['hardware']}" ,"requestId" => "{$request_id}"
	);
	print_r($data);
	//$data_string = json_encode($data);
	//print_r($data_string);

    $url = "http://api2.mina.mi.com/remote/ota/v2";
	$ch = curl_init(); 

	curl_setopt($ch, CURLOPT_URL, $url); 
	curl_setopt($ch, CURLOPT_POST, true);
	curl_setopt($ch, CURLINFO_HEADER_OUT, TRUE);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0); // 对认证证书来源的检查
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 1); // 从证书中检查SSL加密算法是否存在
	//print_r(array("Cookie:{$tmp_cookie}","Content-Type: application/x-www-form-urlencoded"));
	curl_setopt($ch, CURLOPT_HTTPHEADER,array("Cookie:{$tmp_cookie}","Content-Type: application/x-www-form-urlencoded"));
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
	if (is_array($data))
    {
        $data = http_build_query($data, null, '&');
    }
	curl_setopt($ch, CURLOPT_POSTFIELDS,$data);

	$output = curl_exec($ch); 

	$outhead=curl_getinfo($ch,CURLINFO_HEADER_OUT);
	echo $outhead;
    echo $output;
}

$action =$_GET['action'];
switch($action) {
    case 'login':
        speech($_GET["username"], $_GET["password"], 'Hellod World');
        break;
    case 'push':
        print_r($_GET);
        push_firmware($_GET);
        break;
}
?>