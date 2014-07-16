<?php
/**
 * Created by PhpStorm.
 * User: kevin
 * Date: 14-7-16
 * Time: 22:26
 */

namespace Sms;

/**
 * Class Tui3
 * @package Sms
 * @link http://www.tui3.com/Members/doc/page/smssend/
 */
class Tui3 extends SmServiceBase{

    public function _send( $text , $mobile ){

        $ch = curl_init();

        //http://www.tui3.com/api/send/?k=发送密钥&r=执行结果格式&p=短信产品id&t=接收手机号&c=发送内容

        curl_setopt($ch, CURLOPT_URL, "http://www.tui3.com/api/send/?k={$this->appkey}&r=json&p=1&t={$mobile}&c=" . urlencode( $text ) );

        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);

        $res = curl_exec( $ch );
        $err  = curl_error( $ch );

        curl_close( $ch );

        $res = @ ( array ) json_decode( $res );
        if( isset( $res['err_code'] ) && $res['err_code'] == '0' ){
            return array( static::RESULT_SUCCESS , '' );
        }
        else{
            return array( static::RESULT_FAILURE , '' );
        }

    }

} 