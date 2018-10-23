<?php
namespace Sms\Protocol;

use Sms\Protocol\Base;

class Smsbao extends Base
{
    protected $config;

    protected $errorMessage = [
        30 => "Wrong password",
        40 => "Account does not exist",
        41 => "Insufficient balance",
        43 => "IP address limit",
        50 => "Content contains sensitive words",
        51 => "Cell phone number is incorrect",
    ];
    public function __construct($config)
    {
        if (empty($config['username'])) {
            throw new \Exception('Username can not be empty.');
        }
        if (empty($config['password'])) {
            throw new \Exception('Password can not be empty.');
        }

        $this->config = $config;
    }

    public function send($text, $phone)
    {
        $data = [
            'u' => $this->config['username'],
            'p' => md5($this->config['password']),
            'm' => $phone,
            'c' => $text,
        ];
        if (isset($this->config['sign'])) {
            $data['c'] = "【{$this->config['sign']}】" . $data['c'];
        }
        $url    = 'http://api.smsbao.com/sms?' . http_build_query($data);
        $return = file_get_contents($url);
        if ($return === false) {
            throw new \Exception('Bad api request');
        }
        $return = intval($return);
        if ($return != 0) {
            if (isset($this->errorMessage[$return])) {
                throw new \Exception($this->errorMessage[$return]);
            } else {
                throw new \Exception("Unknown error");
            }

        }
        return true;
    }
}
