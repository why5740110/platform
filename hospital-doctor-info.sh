#!/bin/bash
# 生成医生缓存
for i in $(seq 204); do
  start=$(($(($i - 1)) * 2500 + 1))
  end=$(($i * 2500))
  #  echo "start: $start, end $end"
  php /data/wwwroot/nisiya.top/yii /once-cache/hospital-doctor $start $end >>/tmp/hospital-doctor.log 2>&1 &
  php /data/wwwroot/nisiya.top/yii /create-es-index/doctor $start $end 1 >>/tmp/es-doctor.log 2>&1 &
done

# 生成医院缓存
for i in $(seq 36); do
  start=$(($(($i - 1)) * 1000 + 1))
  end=$(($i * 1000))
  #  echo "start: $start, end $end"
  php /data/wwwroot/nisiya.top/yii /create-es-index/hospital $start $end >>/tmp/es-hospital.log 2>&1 &
done

#生成医院详情缓存
for i in $(seq 36); do
  start=$(($(($i - 1)) * 1000 + 1))
  end=$(($i * 1000))
  #  echo "start: $start, end $end"
  php /data/wwwroot/nisiya.top/yii /once-cache/hospital-detail $start $end >>/tmp/hospital-detail.log 2>&1 &
done

#生成科室缓存
php /data/wwwroot/nisiya.top/yii /hospital/skeshi-list >>/tmp/skeshi-list.log 2>&1 &
#修改科室信息为王氏科室
php /data/wwwroot/nisiya.top/yii /once-cache/keshi-info >>/tmp/keshi-info.log 2>&1 &
#生成第三方医院缓存
php /data/wwwroot/nisiya.top/yii /once-cache/hospital-info >>/tmp/hospital-info.log 2>&1 &
#生成合作平台缓存
php /data/wwwroot/nisiya.top/yii /once-cache/coo-list >>/tmp/coo-list.log 2>&1 &
#生成第三方平台缓存
php /data/wwwroot/nisiya.top/yii /once-cache/platform-list >>/tmp/platform-list.log 2>&1 &
#将科室权重配置数据保存到redis中
php /data/wwwroot/nisiya.top/yii /once-cache/department-config-list >>/tmp/department-config-list.log 2>&1 &
#生成常见科室缓存
php /data/wwwroot/nisiya.top/yii /once-cache/common-department >>/tmp/common-department.log 2>&1 &

exit
