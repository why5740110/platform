###用户中心sdk




composer.json
```json
{
    "config":{
        "secure-http":false
    },
    "repositories": [
        {
            "type": "composer",
            "url": "http://test.composer.nisiya.top"
        }
    ],
    "require": {
        "nisiya/ucentersdk":"*"
    }
}


```

composer命令
 ```
 composer install nisiya/ucentersdk
```


用法
```php
<?php


include "vendor/autoload.php";
//param.php 
return [
'usersdk' =>[
        'appid' => '00000000000',
        'appkey' => 'YW***************0HGH',
        //路由映射地址
        'urlmapping' => [
            'ucenter' => 'http://test.api.ucenter.nisiya.top/v2',
            'doctor' =>  'http://test.api-doctor.nisiya.top/v2',
        ],
        'os' => 'uc', //自行申请
        'version' => '11101', //写死
    ],
];    
        //初始化配置文件，建议放在初始化方法中，初始化一次即可
        \nisiya\ucentersdk\Config::setConfig( Yii::$app->params['usersdk']);

        $loginSdk = new \nisiya\ucentersdk\ucenter\LoginSdk();
        $memberSdk = new \nisiya\ucentersdk\ucenter\MemberSdk();
        $registSdk = new \nisiya\ucentersdk\ucenter\RegisterSdk();
        $doctorSdk = new \nisiya\ucentersdk\doctor\DappdoctorSdk();
        $codeSdk = new \nisiya\ucentersdk\ucenter\CodeSdk();

        //###############登陆注册相关###########################
        $mobile = 11111111111;

        $res_mobilelogin = $loginSdk->mobilelogin($mobile , '7878' ,'mall', 'user');
        $this->Dump($res_mobilelogin,'登陆');

        $res_mobilecode= $codeSdk->mobilecode($mobile,'',1,0,0,6);
        $this->Dump($res_mobilecode,'发送手机号');

        $res_login = $loginSdk->login($mobile, '123456', 'user', true);
        $this->Dump($res_login,'账号密码登陆');

        $res_register =  $registSdk->register($mobile."hh1427", '123456', 'user', $mobile, '',true, '', '', 0, 0);
        $this->Dump($res_register,'注册');

        $res_wechat = $loginSdk->wechat(4184630);
        $this->Dump($res_wechat,'wc项目快速登陆');

        $res_login = $loginSdk->verificationlogin('72ea0daa3504f5021e7ce939c9e7b400');
        $this->Dump($res_login,'检查用户是否登陆');
        //###############用户相关##############################
        $res_getuser = $memberSdk->getuser($mobile , 'mobile');
        $this->Dump($res_getuser,'获取用户');

        $res_getusers = $memberSdk->getusers($uids=[4184630] ,1);
        $this->Dump($res_getusers,'获取用户列表');

        $res_verification = $memberSdk->verification($mobile,$type='mobile');
        $this->Dump($res_verification,'验证用户名是否存在');

        $res_getuidsbyphone = $memberSdk->getuidsbyphone([$mobile]);
        $this->Dump($res_getuidsbyphone,'根据手机号列表获取用户列表');

        $res_updateparam = $memberSdk->updateparam(10,['nickname' =>'testsdk']);
        $this->Dump($res_updateparam,'更新用户信息');

        $res_updateparam = $memberSdk->resetpwd(10,'123');
        $this->Dump($res_updateparam,'重置密码');

        //###############医生相关################################

        $res_doctordata=$doctorSdk->info(17);
        $this->Dump($res_doctordata,'医生信息');

        $res_usertable=$doctorSdk->usertable(17);
        $this->Dump($res_usertable,'医生信息user表');

        $res_doctordemeanor=$doctorSdk->doctordemeanor(1111,$page=1,$pagesize = 20);
        $this->Dump($res_doctordemeanor,'获取医生风采照片');

        $res_getInternetDoctorInfo=$doctorSdk->internetdoctor(1111);
        $this->Dump($res_getInternetDoctorInfo,'获取互联网医院医生信息');

        $res_getdoctorid=$doctorSdk->getdoctorid('测试');
        $this->Dump($res_getdoctorid,'医生列表');

        $res_infos=$doctorSdk->infos([17]);
        $this->Dump($res_infos,'医生信息列表');

        $res_infotable=$doctorSdk->infotable(17);
        $this->Dump($res_infotable,'医生信息info表');

        $res_usertable=$doctorSdk->usertable(17);
        $this->Dump($res_usertable,'医生信息user表');

        $res_invitecode=$doctorSdk->getdoctorbycode(11111);
        $this->Dump($res_invitecode,'根据邀请码获取医生');

        $res_nisiya_id=$doctorSdk->nisiyaid(100010);
        $this->Dump($res_nisiya_id,'根据王氏id获取信息');


        $res_updateby=$doctorSdk->updateby(['realname' =>'张三'],'mobile',$mobile);
        $this->Dump($res_updateby,'更新医生信息');

        $res_getAllDoctors=$doctorSdk->getalldoctor(1, 2);
        $this->Dump($res_getAllDoctors,'获取全部医生');

        $res_getAllDoctors=$doctorSdk->updateinternetdoctor(111, ['id_card' =>'xxx']);
        $this->Dump($res_getAllDoctors,'更新互联网医生信息');

        $res_insertdoctor=$doctorSdk->insertdoctor(['doctor_id'=>111,'mobile'=>$mobile],['doctor_id'=>111,'mobile'=>$mobile]);
        $this->Dump($res_insertdoctor,'注册医生');

```