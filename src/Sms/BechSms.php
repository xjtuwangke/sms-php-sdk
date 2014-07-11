<?php
/**
 * Created by PhpStorm.
 * User: kevin
 * Date: 14-7-11
 * Time: 19:05
 */

namespace Sms;

/**
 * Class BechSms
 * @link http://sms.bechtech.cn/index.php/Api/doc
 * @package Sms
 */
class BechSms extends SmServiceBase {

    protected $_errors = [
        '02'  =>'IP限制',
        '03'  =>'用户名密码错误',
        '04'  =>'剩余条数不足',
        '05'  =>'信息内容中含有限制词(违禁词)',
        '06'  =>'信息内容为黑内容',
        '07'  =>'该用户的该内容 受同天内内容不能重复发 限制',
        '08'  =>'批量下限不足',
        '09'  =>'字数超出 (已作废)',
        '10'  =>'短信参数有误',
        '11'  =>'已超出100条最大手机数量',
        '12'  =>'防火墙无法处理这种短信',
        '13'  =>'用户账户被冻结',
        '14'  =>'手机号码不正确或者为空',
        '97'  =>'网关异常',
        '98'  =>'不符合的免审模板 (最新) 添加新的免审模板',
        '99'  =>'系统异常',
        '100' =>'系统例行维护（一般会在凌晨0点~凌晨1点期间进行5分钟左右的升级维护',
    ];

    public function single_sms( $mobile , $message ){
        if( $this->is_mobile($mobile) == 0){
            $this->_set_error('ERROR:invalid mobile number');
            return FALSE;
        }
        $bechsms = apibus::init("bechsms");
        $code = $bechsms->sendmsg($this->aKey,$this->sKey,$mobile,$message);
        if($code->result == '01')
            return TRUE;
        else{
            $this->_set_error('ERROR:bad return code:'.$code->result);
            return FALSE;
        }
    }

    public function _send( $text , $mobile ){

        $ch = curl_init();

        $url = "http://sms.bechtech.cn/Api/send/data/json?accesskey={$this->appkey}&secretkey={$this->appsecret}&mobile={$mobile}&content=" . urlencode( text );

        curl_setopt( $ch, CURLOPT_URL, $url );

        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);

        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, array('mobile' => $mobile , 'message' => $text ));

        $res = curl_exec( $ch );
        $err  = curl_error( $ch );

        curl_close( $ch );

        $res = @ ( array ) json_decode( $res );
        if( isset( $res['result'] ) && $res['result'] == '01' ){
            return array( static::RESULT_SUCCESS , '' );
        }
        else{
            if( isset( $res['result'] ) ){
                $error = $this->_parse_error_code( $res['result'] );
            }
            else{
                $error = '未知错误码';
            }
            return array( static::RESULT_FAILURE , $error ) ;
        }

    }

}