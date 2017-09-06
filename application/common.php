<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用公共文件
//根据首字母排序
function getFirstCharter($data){
    $city = array();
    foreach ($data as $key=>&$v){
        if(empty($v['city_name'])){return '';}
        $str = $v['city_name'];
        $fchar=ord($str{0});
        if($fchar>=ord('A')&&$fchar<=ord('z')) return strtoupper($str{0});
        $s1=iconv('UTF-8','gb2312',$str);
        $s2=iconv('gb2312','UTF-8',$s1);
        $s=$s2==$str?$s1:$str;
        $asc=ord($s{0})*256+ord($s{1})-65536;
        if($asc>=-20319&&$asc<=-20284) $v['initial'] = 'A';
        if($asc>=-20283&&$asc<=-19776) $v['initial'] = 'B';
        if($asc>=-19775&&$asc<=-19219) $v['initial'] = 'C';
        if($asc>=-19218&&$asc<=-18711) $v['initial'] = 'D';
        if($asc>=-18710&&$asc<=-18527) $v['initial'] = 'E';
        if($asc>=-18526&&$asc<=-18240) $v['initial'] = 'F';
        if($asc>=-18239&&$asc<=-17923) $v['initial'] = 'G';
        if($asc>=-17922&&$asc<=-17418) $v['initial'] = 'H';
        if($asc>=-17417&&$asc<=-16475) $v['initial'] = 'J';
        if($asc>=-16474&&$asc<=-16213) $v['initial'] = 'K';
        if($asc>=-16212&&$asc<=-15641) $v['initial'] = 'L';
        if($asc>=-15640&&$asc<=-15166) $v['initial'] = 'M';
        if($asc>=-15165&&$asc<=-14923) $v['initial'] = 'N';
        if($asc>=-14922&&$asc<=-14915) $v['initial'] = 'O';
        if($asc>=-14914&&$asc<=-14631) $v['initial'] = 'P';
        if($asc>=-14630&&$asc<=-14150) $v['initial'] = 'Q';
        if($asc>=-14149&&$asc<=-14091) $v['initial'] = 'R';
        if($asc>=-14090&&$asc<=-13319) $v['initial'] = 'S';
        if($asc>=-13318&&$asc<=-12839) $v['initial'] = 'T';
        if($asc>=-12838&&$asc<=-12557) $v['initial'] = 'W';
        if($asc>=-12556&&$asc<=-11848) $v['initial'] = 'X';
        if($asc>=-11847&&$asc<=-11056) $v['initial'] = 'Y';
        if($asc>=-11055&&$asc<=-10247) $v['initial'] = 'Z';
    }

    return $data;
}
//经验规则
function exp_rule($day){
    $data = array();
    $rule = file_get_contents('rule.json');
    $rule = json_decode($rule,true);
    foreach ($rule as $k=>$v){
        if ($day>=$v['s_day']&&$day<=$v['e_day']){
            $data['kubi'] = $v['kubi'];
            $data['experience'] = $v['exp'];
            break;
        }
    }
    if ($day>=7){
        $data['experience'] += 10;
    }else{
        if(1==$day){
            $data['experience'] += 1;
        }elseif (2==$day||3==$day){
            $data['experience'] += 2;
        }elseif (4==$day||5==$day||6==$day){
            $data['experience'] += 5;
        }
    }
//    $k = rand(168,300);
//    $data['kubi']=$data['kubi']+$k;
    return $data;
}
//创建手机验证码
function code($mobile){
    $code = rand(1000,9999);
    $redis = new Redis();
    $redis->connect("localhost", 6379); //localhost也可以填你服务器的ip
    $redis->select(5);
    $redis->set($mobile,$code);
    $redis->expire($mobile,6000); //EXPIREAT key 1377257300
    return $code;
}
function formatTime(){
    return date('mdHis');
}
function getMillisecond() {
    list($s1, $s2) = explode(' ', microtime());
    return (float)sprintf('%.0f', (floatval($s1) + floatval($s2)) * 1000);
}
//发送手机验证码
function send_sms($username,$pwd,$mobile){
    $post_data = array();
    $post_data['UserName'] = $username;
    $timestamp = formatTime();//时间戳
    $code = code($mobile);
    $post_data['Key'] = md5($username.$pwd.$timestamp);
    $post_data['Timestemp'] = formatTime();
    $post_data['Mobiles'] = $mobile;
    $post_data['Content'] = urlencode("【演库科技】您的验证码是$code"."。演绎未来的通路");
    $post_data['CharSet'] = "utf-8";
    $post_data['SchTime'] = "";
    $post_data['Priority'] = "5";
    $post_data['PackID'] = "";
    $post_data['PacksID'] = "";
    $post_data['ExpandNumber'] = "";
    $post_data['SMSID'] = getMillisecond();//long型数据，此处案例使用了当前的毫秒值，也可根据实际情况进行处理
    $url='http://www.youxinyun.com:3070/Platform_Http_Service/servlet/SendSms';
    $o="";
    foreach ($post_data as $k=>$v)
    {
        $o.= "$k=".$v."&";
    }
    $post_data=substr($o,0,-1);
    $this_header = array("content-type: application/x-www-form-urlencoded;charset=UTF-8");
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_HTTPHEADER,$this_header);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_URL,$url);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $result = curl_exec($ch);//返回相应的标识，具体请参考我方提供的短信API文档
    curl_close($ch);
    return $result;
}
function array_to_object($arr) {
    if (gettype($arr) != 'array') {
        return $arr;
    }
    foreach ($arr as $k => $v) {
        if (gettype($v) == 'array' || getType($v) == 'object') {
            $arr[$k] = (object)array_to_object($v);
        }
    }
    return (object)$arr;
}