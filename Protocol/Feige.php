<?php
namespace Sms\Protocol;

use Sms\Protocol\Base;

class Feige extends Base
{

    protected $config;

    protected $errorMessage = [
        10001 => "Account is empty",
        10002 => "The password is empty",
        10004 => "SMS content is empty.",
        10005 => "SMS number is empty.",
        10006 => "The SMS number is wrong.",
        10007 => "There are errors in the number of SMS messages.",
        10008 => "SMS template empty",
        10009 => "No matching template ID found.",
        10010 => "Mismatch between passed parameters and template",
        10011 => "Status Report",
        10012 => "No upbringing",
        10013 => "SMS ID can not be empty.",
        10014 => "SMS ID invalid",
        10015 => "SMS ID error",
        10016 => "The phone number is not in ID.",
        10017 => "No signature ID",
        10018 => "Signature ID is incorrect.",
        10019 => "Wrong timestamp",
        20001 => "ERROR Incorrect username or password",
        20002 => "The account number is discontinued. Please contact the flying pigeon.",
        20003 => "Account is cancelled, please contact customer service!",
        20004 => "IP address authentication failed, please contact customer service!",
        20005 => "Account balance is insufficient, please contact customer service!",
        -99   => "Channel mask word",
        -999  => "IP blacklist",
        -9999 => "System error",
    ];

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
            'Account' => $this->config['username'],
            'Pwd'     => $this->config['password'],
            'Content' => $text,
            'Mobile'  => $phone,
            'SignId'  => $this->config['sign'],
        ];

        $url    = "http://api.feige.ee/SmsService/Send";
        $return = $this->curlPost($url, $data);
        if ($return == false) {
            throw new \Exception('Bad api request');
        }
        $return = json_decode($return, true);
        if ($return['Code'] != 0) {
            if (isset($this->errorMessage[$return['Code']])) {
                throw new \Exception($this->errorMessage[$return['Code']]);
            } else {
                throw new \Exception(empty($return['Message']) ? "Unknown error" : $return['Message']);
            }
        }
        return true;
    }

    private function curlPost($url, $postFields)
    {

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
        return $result;
    }

}
