<?php
/**
 * Created by PhpStorm.
 * User: kevin
 * Date: 14-7-11
 * Time: 5:33
 */

namespace Sms;


class SmServiceBase {

    static $mobile_regx = '/^1[3-9]{1}\d{9}$/';

    const SP_CMCC = '/^1(([3][456789])|([5][012789])|([8][278]))[0-9]{8}$/';
    const SP_Unicom = '/^1(([3][012])|([5][56])|([8][56]))[0-9]{8}$/';
    const SP_Telecom = '/^1(([3][3])|([5][3])|([8][09]))[0-9]{8}$/';

    const RESULT_INVALID_MOBILE = 'not a valid phone number';
    const RESULT_SUCCESS = 'success';
    const RESULT_UNKNOWN = 'unknown return';
    const RESULT_FAILURE = 'failed';
    const RESULT_TOO_MANY_TIMES = 'too many time within a period';

    const LOG_ERROR = 0;
    const LOG_WARNING = 1;
    const LOG_NOTICE  = 2;

    /**
     * @var null
     */
    protected $appkey = NULL;

    /**
     * @var null
     */
    protected $appsecret = NULL;

    /**
     * @var int 同一手机号码多少秒内只能发一次
     */
    protected $debounce = 30;

    /**
     * @var int 默认若API请求失败重发一次
     */
    protected $retry = 1;

    /**
     * @var function
     */
    protected $logger;

    /**
     * @var null a cache to store last sms timestamp
     */
    protected $get_cache;

    protected $set_cache;

    /**
     * @var string
     */
    protected $cache_prefix = 'SmService_Debounce_';

    /**
     * @param array $options
     */

    /**
     * @var array
     */
    protected $_errors = array();

    public function __construct( array $options ){

        $this->logger = function( $message , $context , $flag ){return TRUE;};

        $this->set_cache = function( $key , $value , $duration ){return TRUE;};

        $this->get_cache = function( $key ){return FALSE;};

        $this->_read_option( $options , 'appkey' , 'appsecret' , 'debounce' , 'retry' , 'logger' , 'set_cache' , 'get_cache' );
    }

    protected function _read_option(){
        $args = func_get_args();
        $options = array_shift( $args );
        foreach( $args as $key ){
            if( isset( $options[$key] ) ){
                $this->$key = $options['key'];
            }
        }
    }

    /**
     * @description 正则表达式验证手机号码
     * @param $string
     * @return bool true:是手机号码 | false:不是手机号码
     */
    static public function isMobile( $string ){
        if( preg_match( static::$mobile_regx , $string ) ){
            return true;
        }
        else{
            return false;
        }
    }

    /**
     * @param $text
     * @param $mobile
     * @return int  状态码
     */
    public function send( $text , $mobile ){

        if( static::isMobile( $mobile ) ){
            $count = 0;
            $result = NULL;

            if( false == $this->_debounce( $mobile ) ){
                $this->_log( $text , $mobile , $count , static::LOG_ERROR , static::RESULT_TOO_MANY_TIMES );
            }

            while( $result !== static::RESULT_SUCCESS && $count++ < $this->retry ){
                list( $result , $err ) = $this->_send( $text , $mobile );
                $this->_record( $mobile );
                if( $result !== static::RESULT_SUCCESS ){
                    $this->_log( $text , $mobile , $count , static::LOG_ERROR , $result . $err );
                }
                else{
                    $this->_log( $text , $mobile , $count , static::LOG_NOTICE , $result );
                }
            }
            return $result;
        }
        else{
            $this->_log( $text , $mobile , 0 , static::LOG_NOTICE , static::RESULT_INVALID_MOBILE );

            return static::RESULT_INVALID_MOBILE;
        }
    }

    /**
     * @param $text
     * @param $mobile
     * @return array
     */
    protected function _send( $text , $mobile ){
        return array( static::RESULT_UNKNOWN , '' );
    }

    protected function _debounce( $mobile ){
        if( $this->debounce <= 0 ){
            return true;
        }
        else{
            if( call_user_func( $this->get_cache , $this->cache_prefix . $mobile ) == 1 ){
                return false;
            }
            else{
                return true;
            }
        }
    }

    protected function _record( $mobile ){
        if( $this->debounce <= 0 ){
            return false;
        }
        else{
            return call_user_func( $this->set_cache , $this->cache_prefix . $mobile , 1 , $this->debounce );
        }
    }

    /**
     * @param $text
     * @param $mobile
     * @param $count
     * @param $flag
     */
    protected function _log( $text , $mobile , $count , $flag , $message = '' ){
        $context = array(
            'text' => $text,
            'mobile' => $mobile,
            'count' => $count
        );
        return call_user_func( $this->logger , $message , $context , $flag );
        /*
        switch( $flag ){
            case static::LOG_NOTICE:
                if( method_exists( $this->logger , 'addNotice') ){
                    $this->logger->addNotice( $message , $context );
                }
                break;
            case static::LOG_WARNING:
                if( method_exists( $this->logger , 'addWarning') ){
                    $this->logger->addWarning( $message , $context );
                }
                break;
            case static::LOG_ERROR:
                if( method_exists( $this->logger , 'addError') ){
                    $this->logger->addError( $message , $context );
                }
                break;
            default:
                if( method_exists( $this->logger , 'addDebug') ){
                    $this->logger->addDebug( $message , $context );
                }
                break;
        }*/
    }

    protected function _parse_error_code( $code ){
        if( array_key_exists( $code , $this->_errors ) ){
            return $this->_errors[$code];
        }
        else{
            return '未知错误码';
        }
    }



} 