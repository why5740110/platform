<?php
/**
 * @file index.php
 * @author xiujianying
 * @version 1.0
 * @date 2020/8/10
 */

use \common\helpers\Url;
use common\libs\HashUrl;
use \yii\helpers\ArrayHelper;
use \yii\helpers\Html;
use \mobile\widget\HospitalViewWidget;
use mobile\widget\HospitalCityWidget;
use \mobile\widget\DoclistViewWidget;

$this->registerCssFile(Url::getStaticUrl("css/home.css"));
$this->registerCssFile(Url::getStaticUrlTwo("pages/home/index.css"),['depends'=>'mobile\assets\AppAsset']);
$this->registerJsFile(Url::getStaticUrlTwo('pages/home/index.js'),['depends'=>'mobile\assets\AppAsset']);
$this->registerJsFile(Url::getStaticUrl("js/home.js"));

?>

<?php
//是否已定位
$isPositioned = ArrayHelper::getValue($autoArr,'city_pid');
if($ua == 'mini'){
    $isPositioned = true;
}
?>

<div class="main_wrapper home_box">
    <div class="home_top_box">
        <?php
        if(ArrayHelper::getValue($selectArr, 'city')){
            $cityName = ArrayHelper::getValue($selectArr, 'city');
            $region = ArrayHelper::getValue($selectArr, 'pinyin');
        }else{
            $cityName = ArrayHelper::getValue($autoArr,'city');
            if (!$cityName) {
                $cityName = '全国';
            }
        }
        $region = (isset($region) && !empty($region)) ? $region : 0;
        ?>
        <div class="location_city">
            <span class="city_name" localid="<?=ArrayHelper::getValue($selectArr, 'city_pid')?>"><?=$cityName?></span>
            <i class="city_select_icon"></i>
        </div>
        <!-- 搜索框 start -->
<!--            <div class="search_box">-->
                <a class="search_box" href="<?=Url::to(['search/so'])?>">
                    <i class="search_box_icon"></i>
                    <input class="search_text" type="text" placeholder="搜索医院、科室、医生" readonly/>
<!--                    <span class="search_button">搜索</span>-->
                </a>
<!--            </div>-->
    </div>
    <div class="main_box home_main_box">
        <ul class="safeguard">
            <li>官方号源</li>
            <li>医生本人</li>
            <li>平台认证</li>
            <li>诊前退款</li>
        </ul>
        <div class="classify_box">
            <span onclick="shenceHomeData({'current_page':'msapp_register_home','current_page_name':'挂号首页','position_order':1 , 'position_name':'按医院'})">
                <a class="c_wrap" onclick="_maq.click({'click_id':'挂号-M首页按医院-挂号按钮' , 'click_url':'<?= rtrim(\Yii::$app->params['domains']['mobile'], '/').Url::to(['hospitallist/index', 'region' => $pinyin]) ?>'})" href="<?= Url::to(['hospitallist/index', 'region' => $pinyin]) ?>">
                    <div class="c_img_box">
                        <img class="c_img" src="<?=url::to('@staticTwo/pages/home/img/img_hospital.png')?>" />
                    </div>
                    <p class="c_name">按医院</p>
                    <p class="c_tag">全国知名医院</p>
                </a>
            </span>
            <span onclick="shenceHomeData({'current_page':'msapp_register_home','current_page_name':'挂号首页','position_order':2 , 'position_name':'按科室'})">
                <a class="c_wrap" onclick="_maq.click({'click_id':'挂号-M首页按科室-挂号按钮' , 'click_url':'<?= rtrim(\Yii::$app->params['domains']['mobile'], '/').Url::to(['hospitallist/department-list', 'region' => $pinyin]) ?>'})" href="<?= Url::to(['hospitallist/department-list', 'region' => $pinyin]) ?>">
                    <div class="c_img_box">
                        <img class="c_img" src="<?=url::to('@staticTwo/pages/home/img/img_keshi.png')?>" />
                    </div>
                    <p class="c_name">按科室</p>
                    <p class="c_tag">按科室挂号</p>
                </a>
            </span>
            <span onclick="shenceHomeData({'current_page':'msapp_register_home','current_page_name':'挂号首页','position_order':3 , 'position_name':'按医生'})">
                <a class="c_wrap" onclick="_maq.click({'click_id':'挂号-M首页按医生-挂号按钮' , 'click_url':'<?= rtrim(\Yii::$app->params['domains']['mobile'], '/').Url::to(['doctorlist/index', 'region' => $pinyin]) ?>'})" href="<?= Url::to(['doctorlist/index', 'region' => $pinyin]) ?>">
                    <div class="c_img_box">
                        <img class="c_img" src="<?=url::to('@staticTwo/pages/home/img/img_doctor.png')?>" />
                    </div>
                    <p class="c_name">按医生</p>
                    <p class="c_tag">全国知名专家</p>
                </a>
            </span>
        </div>
        <div class="classify_keshi">
            <a class="c_wrap" href="/hospital/hospitallist/departments/<?= $region?>_0_1_0_1.html">
                <img src="<?=url::to('@staticTwo/pages/home/img/icon_neike.png')?>" />
                <p>内科</p>
            </a>
            <a class="c_wrap" href="/hospital/hospitallist/departments/<?= $region?>_0_2_0_1.html">
                <img src="<?=url::to('@staticTwo/pages/home/img/icon_waike.png')?>" />
                <p>外科</p>
            </a>
            <a class="c_wrap" href="/hospital/hospitallist/departments/<?= $region?>_0_11_0_1.html">
                <img src="<?=url::to('@staticTwo/pages/home/img/icon_pifuke.png')?>" />
                <p>皮肤科</p>
            </a>
            <a class="c_wrap" href="/hospital/hospitallist/departments/<?= $region?>_0_<?=YII_ENV == 'prod'? 314 : 331 ?>_0_1.html">
                <img src="<?=url::to('@staticTwo/pages/home/img/icon_nanke.png')?>" />
                <p>男科</p>
            </a>
            <a class="c_wrap" href="/hospital/hospitallist/departments/<?= $region?>_0_3_0_1.html">
                <img src="<?=url::to('@staticTwo/pages/home/img/icon_fuchanke.png')?>" />
                <p>妇产科</p>
            </a>
        </div>
    </div>
    <div class="main_box">
        <div class="list_head_box">
          <span class="list_title"><i class="icon_my_register"></i>我的挂号</span>
            <a class="list_more" href="<?= Url::to(['/hospital/my/guahaolist']) ?>">查看记录<i class="icon_arrow_right"></i></a>
        </div>
    </div>
    <?php if (!empty($lunbo)): ?>
        <div class="main_box swiper mySwiper banner_swiper">
            <div class="swiper-wrapper">
                <?php foreach ($lunbo as $value) : ?>
                    <div class="swiper-slide">
                        <a href="<?php echo $value['link']; ?>">
                            <img src="<?php echo $value['imagelink']; ?>" width="100%"/>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="swiper-pagination banner_pagination"></div>
        </div>
    <?php endif; ?>
    <div class="main_box">
        <div class="list_head_box">
            <span class="list_title">热门医院</span>
            <a class="list_more" href="<?=Url::to(['hospitallist/index'])?>">更多<i class="icon_arrow_right"></i></a>
        </div>
        <div class="list_content">

            <?php if(isset($hospital_list) && is_array($hospital_list)){
                foreach($hospital_list as $key => $row){
                    ?>
                    <?=HospitalViewWidget::widget(['row'=>$row,'type' => 2, 'order_key' => $key, 'shence_type' => 1])?>
                <?php } } ?>
        </div>
    </div>
    <div class="main_box_new">
        <div class="list_head_box">
            <span class="list_title">热门医生</span>
            <a class="list_more" href="<?=Url::to(['doctorlist/index'])?>">更多<i class="icon_arrow_right"></i></a>
        </div>
        <div class="department_swiper">
            <span class="xz">全部<i></i></span>
            <?php if(isset($department) && is_array($department)){ ?>
                <?php foreach($department as $key => $row){ ?>
                    ?>
                <span data_department_id="<?=$row['second_department_id'];?>" data_province_id="<?=$province_id;?>"  data_city_id="<?=$city_id;?>"><?=$row['second_department_name'];?><i></i></span>
            <?php } } ?>
        </div>
        <div class="list_content" id="list_content">
            <?php if(isset($doctor_list) && is_array($doctor_list)){ ?>
                <?php foreach($doctor_list as $key => $value){ ?>
                    <div class="doc_item">
                    <?php $value['shece_doctor_hospital'] = $value['doctor_hospital']; ?>
                    <?= DoclistViewWidget::widget(['row' => $value, 'type' => 1, 'shence_type' => 2]); ?>
                    </div>
                <?php } } ?>
        </div>
    </div>
</div>

<!--地区选择弹窗-->
<?=HospitalCityWidget::widget();?>
<input type="hidden"  class="index_domains" value="<?=rtrim(\Yii::$app->params['domains']['mobile'], '/').Url::to(['index/index'])?>" >

<?php
$this->registerJsFile(Url::getStaticUrl("js/home.js"));
?>

<script src="<?=Url::getStaticUrl("js/jquery-1.11.1.min.js");?>"></script>

<script src="https://www.nisiyacdn.com/static/js/ms-hybrid-1.1.6.js"></script>
<script>
    $(function () {
        // 2022-03-30 如果数据加载异常加载 2 s 后隐藏不一直加载
        window.onload = function(){
            setTimeout("hiddenLoading()",4000);
        }
        //先弹框提示
        var is_position = "<?php echo $is_position;?>";
        var cookie_key = 'position';
        var tools_title = localStorage.getItem(cookie_key);
        if (tools_title <= 0) {
            if(is_position == 'ok'){
                $('.zqxy_pop_all').show();
            }
        }

        $('.ty_btn').click(function () {
            set(cookie_key, 1)
            setCookie(cookie_key, 1, 30000);
            $('.zqxy_pop_all').hide();
            location.reload();
        });

        $('.bty_btn').click(function () {
            $('.zqxy_pop_all').hide();
            setCookie(cookie_key, 2, 30000);
            set(cookie_key, 2)
            location.reload();
        });

        function setCookie(cname,cvalue,exdays){
            var d = new Date();
            d.setTime(d.getTime()+(exdays*24*60*60*1000));
            var expires = "expires="+d.toGMTString();
            document.cookie = cname+"="+cvalue+"; "+expires;
        }

        function set(key,value) {
            var curTime = new Date().getTime();
            var dateee = new Date().toJSON(curTime);
            var date = new Date(+new Date(dateee)+8*3600*1000*300000).toISOString().replace(/T/g,' ').replace(/\.[\d]{3}Z/,'');
            localStorage.setItem(key,value,date);
        }
    })
    // 修改加载loading 隐藏
    function hiddenLoading() {
        var isPositioned = "<?php echo $isPositioned;?>";
        if (isPositioned == false){
            $(".home_loadCon").hide();
        }
    }
</script>

<script type="text/javascript">
    function appShare(){
        //设置app 分享
        if(window.MSHybridJS.msBrowserEnv == 'msPatientApp'){
            window.MSHybridJS.onEnv('msPatientApp',function(){
                MSHybridJS.updateAppMessageShareData({
                    desc: '<?= $shareData['desc'] ?>', // 分享描述
                    link: '<?= $shareData['link'] ?>', // 分享链接，该链接域名或路径必须与当前页面对应的公众号JS安全域名一致
                    imgUrl: '<?= $shareData['imgUrl'] ?>', // 分享图标
                    title: '<?= $shareData['title'] ?>', // 分享标题
                    success: function () {
                        // 设置成功
                        console.log('设置成功');
                    }
                })
            })
        }
    }
    //app分享
    appShare();
</script>

<?php
\mobile\widget\ShenceStatisticsWidget::widget(['type' => '','data'=>[]]);
?>
<input style="display: none" id="shenceplatform_type" value="<?=\Yii::$app->controller->getUserAgent()?>">
<script>
    //挂号首页点击按照按医院、按科室、按医生和医院顺序埋点
    function shenceHomeData(data) {
        if ($("#shenceplatform_type").val() == 'patient') {
            //sensors.track('RegisterClick', data);
        }
    }
</script>



