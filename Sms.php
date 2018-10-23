<?php
namespace Sms;

use Sms\Protocol\Feige;
use Sms\Protocol\FeigeTemplate;
use Sms\Protocol\Jpush;
use Sms\Protocol\Smsbao;
use Sms\Protocol\Smsyun;

class Sms
{
    protected $protocol;

    protected $protocolType;

    protected $config = [];

    protected $content;

    protected $data = [];

    protected $templateId;

    protected $phone;

    protected $errorCode;

    protected $errorMessage;

    protected $instance;

    public function __construct($protocol, $config)
    {
        switch ($protocol) {
            case 'smsbao':
                $this->protocol     = new Smsbao($config);
                $this->protocolType = 'text';
                break;

            case 'smsyun':
                $this->protocol     = new Smsyun($config);
                $this->protocolType = 'text';
                break;

            case 'feige':
                $this->protocol     = new Feige($config);
                $this->protocolType = 'text';
                break;

            case 'feige_template':
                $this->protocol     = new FeigeTemplate($config);
                $this->protocolType = 'template';
                break;

            case 'jpush':
                $this->protocol     = new Jpush($config);
                $this->protocolType = 'template';
                break;

            default:
                throw new \Exception('Bad Protocol');
                break;
        }
    }

    public function setPhone($phone)
    {
        $this->phone = $phone;
        return $this;
    }

    public function setContent($content)
    {
        $this->content = $content;
        return $this;
    }

    public function setData($data)
    {
        $this->data = $data;
        return $this;
    }

    public function setTemplateId($id)
    {
        $this->templateId = $id;
        return $this;
    }

    public function getError()
    {
        return $this->errorMessage;
    }

    public function send()
    {
        if (empty($this->protocolType)) {
            throw new \Exception('Bad Protocol Type');
        }
        if (empty($this->phone)) {
            throw new \Exception('Bad Phone');
        }
        // var_dump($this->protocolType);
        if ($this->protocolType == 'template') {
            return $this->sendByTemplate();
        } else {
            return $this->sendByText();
        }
    }

    private function sendByTemplate()
    {
        if (empty($this->templateId)) {
            throw new \Exception('Template ID can not be empty.');
        }

        try {
            $this->protocol->send($this->templateId, $this->data, $this->phone);
            return true;
        } catch (\Exception $e) {
            $this->errorMessage = $e->getMessage();
            return false;
        }
    }

    private function sendByText()
    {
        if (empty($this->content)) {
            throw new \Exception('Content can not be empty.');
        }
        $content = $this->content;
        if (!empty($this->data)) {
            foreach ($this->data as $k => $v) {
                $content = str_replace("{\${$v}}", $v, $content);
            }
        }

        try {
            $this->protocol->send($content, $this->phone);
            return true;
        } catch (\Exception $e) {
            $this->errorMessage = $e->getMessage();
            return false;
        }
    }
}
