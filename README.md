Sms
===============

这是一个对一些短信平台api的统一封装

## 使用示例

### 自定义内容

```
<?php
$sms    = new \Sms\Sms('smsbao', ['username'=>'xxx','password'=>'xxx','sign'=>'shira']);

$return = $sms->setPhone('12345678')->setContent("您的验证码是:1234")->send();

if(!$return){
    $error = $sms->getError();
}

```

### 短信模板

```
<?php
$sms    = new \Sms\Sms('smsbao', ['username'=>'xxx','password'=>'xxx','sign'=>'shira']);

$return = $sms->setPhone('12345678')->setData(['code'=>1234,'user'=>'shira'])->setTemplateId(2333)->send();

if(!$return){
    $error = $sms->getError();
}

```

## 目录结构

~~~
Sms
├─Protocol              封装的各平台短信类
│  ├─Smsbao.php         smsbao
│  ├─Smsyun.php         smsyun
│  ├─...
│  
├─Sms.php               统一入口
├─LICENSE               授权说明文件
├─README.md             README 文件

~~~


## 其它

自用的,之后准备封装成composer组件,所以目录可能会变动,之后通知.

