<?php
namespace console\controllers;
use common\models\TbDepartmentThirdPartyRelationModel;
use common\models\HospitalDepartmentRelation;
use common\models\TmpDepartmentThirdPartyModel;
use common\models\GuahaoHospitalModel;

class KeshiRelationController extends \yii\console\Controller
{
    public $platform = [
        'henan'=>1,'nanjing'=>2,'haodaifu'=>3
    ];
    public function actionRun($form){
        if(!array_key_exists($form,$this->platform)){
            die("来源错误\n\r");
        }
        $page  = 1;
        $limit = 1000;
        $query = HospitalDepartmentRelation::find()->where(['tp_platform'=>$this->platform[$form]]);
        $total = $query->count();
        $maxPage = ceil($total / $limit)+1;
        do{
            if ($page > $maxPage) {
                break;
            }
            $tpage = $maxPage - $page;
            $offset     = max(0, ($tpage - 1)) * $limit;
            $keshi_list = $query->offset($offset)->limit($limit)->all();
            if (!$keshi_list) {
                echo ('结束：' . date('Y-m-d H:i:s', time())).'没有了！' . PHP_EOL;
                break;
            }

            foreach ($keshi_list as $key => $keshi) {
                $res = TbDepartmentThirdPartyRelationModel::find()->where([
                    'hospital_department_id'=>$keshi->id,
                    'tp_platform'=>$keshi->tp_platform,
                    'tp_department_id'=>$keshi->tp_department_id,
                ])->one();

                if($res){
                    echo $keshi->id . '  已存在！'. PHP_EOL;
                }else{
                    $RelaModel = new TbDepartmentThirdPartyRelationModel();
                    $RelaModel->hospital_department_id = $keshi->id;
                    $RelaModel->tp_platform = $keshi->tp_platform;
                    $RelaModel->tp_department_id = $keshi->tp_department_id;
                    $RelaModel->create_time = time();
                    $RelaModel->status = 1;
                    $RelaModel->save();
                    echo $keshi->id . '  成功！' . date('Y-m-d H:i:s', time()) . PHP_EOL;
                }

                $tmp = TmpDepartmentThirdPartyModel::find()->where([
                    'tp_hospital_code'=>$keshi->tp_hospital_code,
                    'tp_platform'=>$keshi->tp_platform,
                    'tp_department_id'=>$keshi->tp_department_id,
                ])->one();

                if($tmp){
                    echo $keshi->id . '  已存在！'. PHP_EOL;
                }else{
                    $TmpModel = new TmpDepartmentThirdPartyModel();
                    $TmpModel->tp_platform = $keshi->tp_platform;
                    $TmpModel->tp_hospital_code = $keshi->tp_hospital_code;
                    $res = GuahaoHospitalModel::find()->where(['tp_hospital_code'=>$keshi->tp_hospital_code,'tp_platform'=>$keshi->tp_platform])->one();
                    if($res){
                        $TmpModel->hospital_name = $res->hospital_name;
                    }
                    $TmpModel->hospital_department_id = $keshi->id;
                    $TmpModel->third_fkid = $keshi->frist_department_id;
                    $TmpModel->third_fkname = $keshi->frist_department_name;
                    $TmpModel->third_skid = $keshi->second_department_id;
                    $TmpModel->third_skname = $keshi->second_department_name;
                    $TmpModel->tp_department_id = $keshi->tp_department_id;
                    $TmpModel->department_name = $keshi->second_department_name;
                    $TmpModel->is_relation = 1;
                    $TmpModel->save();
                    echo $keshi->id . '  成功！' . date('Y-m-d H:i:s', time()) . PHP_EOL;
                }

            }
            $num = count($keshi_list);
            $page++;
        }while ( $num > 0);

        echo "任务" . date('Y-m-d H:i:s') . "完成！\n";
    }
}