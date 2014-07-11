<?php

namespace Sms;

/**
 * Class Luosimao
 * @package Sms
 * @description
 * @link http://luosimao.com/docs/api/24
 */
class Luosimao extends SmServiceBase {

    /**
     * @var api_key
     */

    public function _send( $text , $mobile ){

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, "https://sms-api.luosimao.com/v1/send.json");

        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);

        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, TRUE);
        curl_setopt($ch, CURLOPT_SSLVERSION , 3);

        curl_setopt($ch, CURLOPT_HTTPAUTH , CURLAUTH_BASIC);
        curl_setopt($ch, CURLOPT_USERPWD  , 'api:key-' . $this->appkey );


        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, array('mobile' => $mobile , 'message' => $text ));

        $res = curl_exec( $ch );
        $err  = curl_error( $ch );

        curl_close( $ch );

        $res = @ ( array ) json_decode( $res );
        if( isset( $res['msg'] ) && $res['msg'] == 'ok' ){
            return array( static::RESULT_SUCCESS , '' );
        }
        else{
            return array( static::RESULT_FAILURE , '' );
        }

    }

}