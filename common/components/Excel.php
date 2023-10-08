<?php
/**
 * Excel类
 * @file Excel.php
 * @author lixinhan <lixinhan@yuanxin-inc.com>
 * @version 2.0
 * @date 2017-12-11
 */
namespace common\components;
class Excel
{
    public $object;
    public $createor='';
    public $lastModityBy='';
    public $title='';
    public $subject='';
    public $description='';
    public $keywords='';
    public $category='';
    public function __construct()
    {
        ini_set('memory_limit',-1);
        set_time_limit(0);
        \PHPExcel_CachedObjectStorageFactory::cache_in_memory_gzip;

        $this->excel = new \PHPExcel();
    }

    /**
     * 导入内容
     * @author lixinhan <lixinhan@yuanxin-inc.com>
     * @date 2017-12-11
     * @param array $data
     * @param array $header
     * @return $this
     */
    public function export($data=[], $header=[]){
        $this->excel->getProperties()->setCreator($this->createor)
            ->setLastModifiedBy($this->lastModityBy)
            ->setTitle($this->title)
            ->setSubject($this->subject)
            ->setDescription($this->description)
            ->setKeywords($this->keywords)
            ->setCategory($this->category);
        $object= $this->excel->setActiveSheetIndex(0);
        //通过header个数判断有多少列
        if(is_array($header)&&count($header)) {
            $n=0;
            foreach ($header as $key => $value) {
                $columns[] = $this->letter($n++);
            }
        }
        //设置列头的值
        $n=0;
        foreach($header as $key=>$value){
            $object->setCellValueExplicit($columns[$n++].'1',$key);
        }
        //设置内容的值
        if(is_array($data)){
            foreach ($data as $key=>$value){
                $i=0;
                foreach ($header as $k=>$v){
                    $object->setCellValueExplicit($columns[$i].($key+2),isset($value[$v])?$value[$v]:'');
                    $i++;
                }
            }
        }
        $this->excel->getActiveSheet()->setTitle('User');
        $this->excel->setActiveSheetIndex(0);
        return $this;
    }

    /**
     * 下载文件
     * @author lixinhan <lixinhan@yuanxin-inc.com>
     * @date 2017-12-11
     * @param $downName
     */
    public function downFile($downName, $version="Excel2007"){
        //  这里修改 Excel2007 和  Excel5  做个兼容，
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="'.$downName.'"');
        header('Cache-Control: max-age=0');
        $objWriter = \PHPExcel_IOFactory::createWriter($this->excel, $version);
        ob_end_clean();
        $objWriter->save('php://output');
        exit();
    }

    /**
     * 保存文件
     * @author lixinhan <lixinhan@yuanxin-inc.com>
     * @date 2017-12-11
     * @param $filename
     */
    public function saveFile($filename, $status = true){
        $objWriter = \PHPExcel_IOFactory::createWriter($this->excel, 'Excel2007');
        $objWriter->save($filename);
        if ($status) {
            exit();
        }
        
    }

    /**
     * 把数字转换成对应的列名
     * @author lixinhan <lixinhan@yuanxin-inc.com>
     * @date 2017-12-11
     * @param $value
     * @return string
     */
    private function letter($value){
        $letter='';
        do{
            $letter=chr(65+($value%26)).$letter;
            $temp=intval($value/26);
            if($temp>0){
                $value=$value-26;
            }
            $value=intval($value/26);
        }while($temp!=0);
        return  $letter;
    }}