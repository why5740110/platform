<?php
use yii\helpers\Html;

/* @var $this \yii\web\View */
/* @var $content string */
use yii\helpers\Url;
?>
<style>
    body{background:#FFF;  color:#333;-webkit-tap-highlight-color:transparent;}
    h1,h2,h3,h4,h5,h6{font-size:100%;font-weight:normal;}
    ul,li{ padding: 0; list-style: none; }
    .backer_top{position: relative; border-bottom: 1px solid #ddd;font-size: 14px;}
    .backer_top h2{height: 44px; line-height: 44px;text-align: center; margin: 0;padding: 0;}
    .backer_top .go_back{position: absolute; left: 10px; top: 12px; color: #428bca;  }
    .bgfff{ background: #fff; }
    .p10{ padding: 10px; }
    .mt10{margin-top: 10px;}
    .mb10{margin-bottom: 10px;}
    .tc{ text-align: center; }
    .tr{ text-align: right; }
    .fs20{ font-size: 20px; }
    .navTabs {height: 40px; line-height: 40px; border-bottom: 1px solid #ddd;}
    .navTabs ul li{ width: 33.3%; float: left; text-align: center; font-size: 15px;}
    .navTabs ul li.on{color: #428bca; border-bottom: 2px solid #428bca; height: 39px;}
    .navTabs ul li.on a{color: #428bca; }
    .backer_tips{background: #fffad9; color: #ed7263; padding: 10px;}
    .backer_tips i.icon{ background: url(<?php echo yii::$app->params['AdminDomain']; ?>/img/tip_icon.png) no-repeat; width: 14px; height: 18px; background-size: 14px 16px; display: inline-block; vertical-align: middle; margin-right: 8px; }
    .table .width_sm{ width: 50%;white-space: inherit !important; text-align: left; }
    .backer_btn .label{ height: 32px; line-height: 28px; display: inline-block; }
    .backer_list_tab {background: #fff; text-align: center; border-bottom: 1px solid #ddd; padding-top: 10px;}
    .backer_list_tab ul li { border: none; width: 32.3%; padding-left: 0; text-align: center; height: 35px; line-height: 35px; border-right: 1px solid #ddd; display: inline-block;}
    .backer_list_tab ul li:last-child{ border-right: none; }
    .backer_list_tab .diqu_box span {  font-size: 14px;}
    .backer_list_tab li i.down, .backer_list_tab li i.up{background:url(<?php echo yii::$app->params['AdminDomain']; ?>/img/d_l_icon.png) no-repeat;background-size: 41px auto; width: 14px; height: 7px; display: inline-block; vertical-align: middle; float: right; margin-top:8px; margin-right: 3px;}
    .backer_list_tab li i.up{background-position: -16px 0; display: none;}
    .backer_list_tab li a.on i.down{ display:none;}
    .backer_list_tab li a.on i.up{ display: inline-block;}
    .backer_list_tab .diqu_box{width:110px;height:25px;line-height:25px;text-align:center;font-size:15px;overflow: hidden; margin: 4px auto 0 auto;}
    .depart .reg_one li a.on i.icon { position: absolute; left: 0;top: 20px; background:url(<?php echo yii::$app->params['AdminDomain']; ?>/img/d_l_icon.png) no-repeat;background-size: 41px auto; width: 14px; height: 14px; display: inline-block; vertical-align: middle; background-position:-29px 0;}
    .depart .reg_one li {position: relative;}
    .dov_open {position: absolute; width: 100%; background: #fff;}
    /*.regions, .depart { position: absolute; z-index: 101; width: 100%; height: 100%; background: #fff;  overflow: scroll;}*/
    .regions li a, .depart li a, .otherpart li a { display: block; padding: 10px 0;  font-size: 17px;  border-bottom: 1px dashed #ddd;  padding-left: 15px;}
    @media screen and (max-width:320px){.backer_list_tab .diqu_box{width:90px;}}
    @media screen and (max-width:320px){.nav>li{font-size: 13px !important;}}
    /*文字截断*/
    .ui-whitespace{ -webkit-box-sizing:border-box;box-sizing:border-box}
    .ui-nowrap-flex,.ui-nowrap-multi{display:-webkit-box;overflow:hidden;text-overflow:ellipsis;-webkit-box-orient:vertical;}
    .ui-nowrap-flex{-webkit-line-clamp:1;-webkit-box-flex:1;height:inherit}
    .ui-nowrap-multi{-webkit-line-clamp:2}

    /*插件样式修改*/
    a { color: #333; text-decoration: none;}
    a:hover, a:focus{text-decoration: none;}
    .table{border-top: 1px solid #ddd; border-left: 1px solid #ddd; text-align: center; font-size: 13px; background: #fff;}
    .table>thead>tr>th{border-bottom: 1px solid #ddd; border-right: 1px solid #ddd; background: #f5f5f5;text-align: center; font-size: 13px;}
    .table>tbody>tr>td{border-right: 1px solid #ddd;border-bottom: 1px solid #ddd; }
    .table>tbody>tr>td .blue a{padding: 0 5px; color: #428bca;}
    .table-responsive{ border: none; }
    .modal-header{ border-bottom: none; }
    .modal-footer{ border-top: none; }
    .modal-dialog{ top: 28%; }

    .dropdown-menu>li>a {
        color: #333!important;
    }
    .widthaudio{width: 100%;}
    .widththirty{width: 50%;}
    @media screen and (max-width:320px){ .do_go_add{ display: block; margin-top: 10px;}}}
    

</style>

<header class="main-header">

    <?= Html::a('<span class="logo-mini">APP</span><span class="logo-lg">' . Yii::$app->name . '</span>', Yii::$app->homeUrl, ['class' => 'logo']) ?>

    <nav class="navbar navbar-static-top" role="navigation">

        <a href="#" class="sidebar-toggle" data-toggle="push-menu" role="button">
            <span class="sr-only">切换导航</span>
        </a>

        <div class="navbar-custom-menu">

            <ul class="nav navbar-nav">
                <?php

                if (isset(Yii::$app->controller->userInfo)) {
                    ?>
                
                    <li class="dropdown">
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                            <?php
                            $userName = Yii::$app->controller->userInfo['username'];
                            $realName = Yii::$app->controller->userInfo['realname'];
                            ?>
                            <?php echo empty($realName) ? $userName : $userName . '(' . $realName . ')'; ?>
                            <span class="caret"></span>
                        </a>
                        <ul class="dropdown-menu" role="menu">
                            <li><a href="<?= Yii::$app->params['api_url']['base_new']['usercenter'] ?>"><i class="icon-check"></i>修改密码</a></li>
                            <li><a href="<?= Url::to('/site/logout') ?>"><i class="icon-off"></i>退出</a></li>
                        </ul>
                    </li>

                    <?php

                }

                ?>

            </ul>
        </div>
    </nav>
    <!-- alert组件 -->
   <!-- <script src="/assets/static/js/sweetalert/dist/es6-promise.min.js"></script>
    <script src="/assets/static/js/sweetalert/dist/es6-promise.auto.min.js"></script>
    <script src="/assets/static/js/sweetalert/dist/sweetalert2.min.js"></script>
    <link href="/assets/static/js/sweetalert/dist/sweetalert2.min.css" rel="stylesheet" />-->
</header>
