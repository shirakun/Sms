<?php
namespace Sms\Protocol;

use Sms\Protocol\Base;

class Jpush extends Base
{
    protected $config;

    public function __construct($config)
    {
        if (empty($config['appKey'])) {
            throw new \Exception('Username can not be empty.');
        }
        if (empty($config['masterSecret'])) {
            throw new \Exception('Password can not be empty.');
        }
        // if (empty($config['sign'])) {
        //     throw new \Exception('Sign can not be empty.');
        // }

        $this->config = $config;
    }

    public function send($temp_id, $temp_para = [], $mobile)
    {
        $body = [
            'mobile'  => $mobile,
            'temp_id' => $temp_id,
        ];
        if (!empty($temp_para)) {
            $body['temp_para'] = $temp_para;
        }
        $url    = 'https://api.sms.jpush.cn/v1/messages';
        $return = $this->request('POST', $url, $body);
        if (!is_array($return)) {
            throw new \Exception($return);
        }
        if ($return['http_code'] != 200) {
            throw new \Exception("Bad request code {$return['http_code']}");
        }
        if ($response['body']['is_valid'] == false) {
            if (isset($response['body']['error']['message'])) {
                throw new \Exception($response['body']['error']['message']);
            } else {
                throw new \Exception("Unknown error");
            }
        }

        return true;
    }

    private function request($method, $url, $body = [])
    {
        $ch      = curl_init();
        $options = array(
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER         => true,
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                'Connection: Keep-Alive',
            ],
            CURLOPT_USERAGENT      => 'JSMS-API-PHP-CLIENT',
            CURLOPT_CONNECTTIMEOUT => 20,
            CURLOPT_TIMEOUT        => 120,
            CURLOPT_HTTPAUTH       => CURLAUTH_BASIC,
            // CURLOPT_USERPWD        => $this->appKey . ":" . $this->masterSecret,
            CURLOPT_USERPWD        => $this->config['app_key'] . ":" . $this->config['master_secret'],
            CURLOPT_URL            => $url,
            CURLOPT_CUSTOMREQUEST  => $method,
        );

        $options[CURLOPT_SSL_VERIFYPEER] = false;
        $options[CURLOPT_SSL_VERIFYHOST] = 0;

        if (!empty($body)) {
            $options[CURLOPT_POSTFIELDS] = json_encode($body);
        }
        curl_setopt_array($ch, $options);
        $output = curl_exec($ch);
        if ($output === false) {
            return "Error Code:" . curl_errno($ch) . ", Error Message:" . curl_error($ch);
        } else {
            $httpCode    = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
            $header_text = substr($output, 0, $header_size);
            $body        = substr($output, $header_size);
            $headers     = array();
            foreach (explode("\r\n", $header_text) as $i => $line) {
                if (!empty($line)) {
                    if ($i === 0) {
                        $headers[0] = $line;
                    } else if (strpos($line, ": ")) {
                        list($key, $value) = explode(': ', $line);
                        $headers[$key]     = $value;
                    }
                }
            }
            $response['headers']   = $headers;
            $response['body']      = json_decode($body, true);
            $response['http_code'] = $httpCode;
        }
        curl_close($ch);
        return $response;
    }

}
