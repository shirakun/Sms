<?php
namespace Sms\Protocol;

use Sms\Protocol\Base;

class Smsyun extends Base
{

    protected $config;

    public function __construct($config)
    {
        if (empty($config['username'])) {
            throw new \Exception('Username can not be empty.');
        }
        if (empty($config['password'])) {
            throw new \Exception('Password can not be empty.');
        }
        if (empty($config['sign'])) {
            throw new \Exception('Sign can not be empty.');
        }

        $this->config = $config;
    }

    public function send($text, $phone)
    {
        $data = [
            'method'    => 'sendSMS', //短信发送发送
            'isLongSms' => 0, //0普通短信1长短信
            'username'  => $this->config['username'],
            'password'  => $this->config['password'],
            'extenno'   => '',
            'mobile'    => $phone,
            'content'   => "【{$this->config['sign']}】" . $text,
        ];

        $url    = 'http://sms.smsyun.cc:9012/servlet/UserServiceAPIUTF8';
        $return = $this->curlPost($url, $data);
        if ($return == false) {
            throw new \Exception('Bad api request');
        }
        $return = explode(';', $return);
        if ($return[0] != 'success') {
            throw new \Exception($return[1]);
        }
        return true;
    }

    private function curlPost($url, $postFields)
    {
        // var_dump($url, $postFields);exit;
        $postFields = http_build_query($postFields);
        $ch         = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        $ret = curl_exec($ch);

        if (false == $ret) {
            $result = false;
        } else {
            $rsp = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            if (200 != $rsp) {
                $result = false;
            } else {
                $result = $ret;
            }
        }
        curl_close($ch);
        // var_dump($result);
        return $result;
    }
}
