baseadminsdk
基础数据后台接口composer SDK

### 简介
方便请求基础数据接口而开发的SDK，需要使用composer引入依赖



### 使用方法
1. 引用sdk: `composer require nisiya/baseadminsdk`

2. 正确引入后的composer.json（只截取了部分内容）
```json
{
    
    "require": {
        "nisiya/baseadminsdk": "^1.0"
    },
    "repositories": [
        {
            "type": "composer",
            "url": "http://test.composer.nisiya.top"
        }
    ]
}


```
2. 以Yii2框架为例：
```php
// get方式接收token
$token = \Yii::$app->request->get('token');
// base后台URL地址/域名
$baseBackendUrl = "127.0.0.1:9501";
$sdk = new SystemSdk($baseBackendUrl);
// 应用关键字
$keyword = "jituanguanwang";
$result = $sdk->checkToken($token, $keyword);
echo "<pre>";
print_r($result);
die;
```


### 返回信息
```php
Array
(
    [success] => 1
    [message] => 请求成功
    [code] => 200
    [data] => Array
        (
            [user] => Array
                (
                    [id] => 964
                    [username] => guowenzheng
                    [user_type] => 1
                    [realname] => 郭文峥
                    [phone] => 
                    [email] => 
                    [avatar] => 
                    [signed] => 
                    [dashboard] => 
                    [dept_id] => 
                    [status] => 0
                    [login_ip] => 127.0.0.1
                    [login_time] => 2022-07-21 01:46:24
                    [backend_setting] => 
                    [created_by] => 
                    [updated_by] => 
                    [created_at] => 2022-07-20 10:55:58
                    [updated_at] => 2022-07-21 01:46:24
                    [remark] => 
                )

            [roles] => Array
                (
                    [0] => group_admin
                )

            [routers] => Array
                (
                    [0] => Array
                        (
                            [id] => 18306779538080
                            [parent_id] => 0
                            [name] => system:department
                            [component] => basedata/department/index
                            [path] => department
                            [redirect] => 
                            [meta] => Array
                                (
                                    [type] => M
                                    [icon] => 
                                    [title] => 资讯管理
                                    [hidden] => 
                                    [hiddenBreadcrumb] => 
                                )

                            [children] => Array
                                (
                                    [0] => Array
                                        (
                                            [id] => 18332269489312
                                            [parent_id] => 18306779538080
                                            [name] => test
                                            [component] => 
                                            [path] => test/delete
                                            [redirect] => 
                                            [meta] => Array
                                                (
                                                    [type] => M
                                                    [icon] => 
                                                    [title] => 资讯删除
                                                    [hidden] => 1
                                                    [hiddenBreadcrumb] => 
                                                )

                                        )

                                )

                        )

                    [1] => Array
                        (
                            [id] => 18306330914464
                            [parent_id] => 0
                            [name] => system:national
                            [component] => basedata/national/index
                            [path] => national
                            [redirect] => 
                            [meta] => Array
                                (
                                    [type] => M
                                    [icon] => 
                                    [title] => 职位管理
                                    [hidden] => 
                                    [hiddenBreadcrumb] => 
                                )

                            [children] => Array
                                (
                                    [0] => Array
                                        (
                                            [id] => 18306509447840
                                            [parent_id] => 18306330914464
                                            [name] => system:national:save
                                            [component] => 
                                            [path] => national/save
                                            [redirect] => 
                                            [meta] => Array
                                                (
                                                    [type] => M
                                                    [icon] => 
                                                    [title] => 职位添加
                                                    [hidden] => 1
                                                    [hiddenBreadcrumb] => 
                                                )

                                        )

                                )

                        )

                )

            [codes] => Array
                (
                    [0] => system:national
                    [1] => system:national:save
                    [2] => system:department
                    [3] => test
                )

        )

)
```
### 返回值简介
```text
user：用户信息
    id：用户ID
    username：用户名
    user_type：用户类型 1：内部 2，外部
    realname：用户真实姓名
    phone： 
    email： 
    avatar： 
    signed： 
    dashboard： 
    dept_id： 
    status：状态 (0正常 1停用)
    login_ip：
    login_time：
    backend_setting： 
    created_by： 
    updated_by： 
    created_at：创建时间
    updated_at：
    remark：
    
roles：角色
    roles： 角色代码
    
routers
    id
    parent_id：父级菜单ID
    name
    component
    path：路由
    redirect
    meta                       
       type
       icon
       title： 路由中文名
       hidden： 是否隐藏 (0是 1否)
       hiddenBreadcrumb
    children: 父级下的子级菜单（）
       id
       parent_id
       name
       component
       path
       redirect
```

### 备注
```text
如果当前项目不支持composer引入baseadminsdk，按如下方式调用：

业务代码流程如下：

1、新版base后台跳转各个应用时，会以get传参方式携带token。
2、子系统接收到token参数。
3、通过post方式请求新版base后台 http://新版base后台地址/system/checkToken 接口，传两个参数：接收到的token 和 当前应用的keyword。
4、返回子系统菜单列表。


如果子系统需要退出登录：

1、通过post方式请求新版base后台 http://新版base后台地址/system/checkLogOut 接口，传一个参数：跳转子系统时传递的token参数。
```