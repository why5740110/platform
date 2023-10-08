##增加admin信息
ALTER TABLE `tb_guahao_scheduleplace_relation` add COLUMN `admin_id` smallint(6) NOT NULL DEFAULT '0' COMMENT '操作人id',
add COLUMN `admin_name` varchar(50) NOT NULL DEFAULT '' COMMENT '操作人姓名';

ALTER TABLE `tb_guahao_schedule` add COLUMN   `admin_id` smallint(6) NOT NULL DEFAULT '0' COMMENT '操作人id',
add COLUMN  `admin_name` varchar(50) NOT NULL DEFAULT '' COMMENT '操作人姓名';

ALTER TABLE `tb_doctor_third_party_relation` add COLUMN   `admin_id` smallint(6) NOT NULL DEFAULT '0' COMMENT '操作人id',
add COLUMN  `admin_name` varchar(50) NOT NULL DEFAULT '' COMMENT '操作人姓名';

ALTER TABLE `tb_hospital_department_relation` add COLUMN   `admin_id` smallint(6) NOT NULL DEFAULT '0' COMMENT '操作人id',
add COLUMN  `admin_name` varchar(50) NOT NULL DEFAULT '' COMMENT '操作人姓名';

##修改挂号医院禁用
ALTER TABLE `tb_guahao_hospital` modify COLUMN  `status` tinyint(4) unsigned NOT NULL DEFAULT '0' COMMENT '状态(0:未关联, 1:已关联,2:禁用)';

##删除挂号排班为全天的数据
DELETE from tb_guahao_schedule WHERE visit_nooncode = 4;


##修改comment说明
alter table `tb_tmp_doctor_third_party` MODIFY `tp_platform` tinyint(4) unsigned NOT NULL DEFAULT '0' COMMENT '第三方平台类型(1:河南，2:南京，3:好大夫,4:王氏,5:健康160)';
alter table `tb_doctor_third_party_relation` MODIFY `tp_platform` tinyint(4) unsigned NOT NULL DEFAULT '0' COMMENT '第三方平台类型(1:河南，2:南京，3:好大夫,4:王氏,5:健康160)';
alter table `tb_guahao_hospital` MODIFY `tp_platform` tinyint(4) unsigned NOT NULL DEFAULT '0' COMMENT '第三方平台类型(1:河南，2:南京，3:好大夫,4:王氏,5:健康160)';
alter table `tb_guahao_interrogation` MODIFY `tp_platform` tinyint(4) unsigned NOT NULL DEFAULT '0' COMMENT '第三方平台类型(1:河南，2:南京，3:好大夫,4:王氏,5:健康160)';
alter table `tb_guahao_order` MODIFY `tp_platform` tinyint(4) unsigned NOT NULL DEFAULT '0' COMMENT '第三方平台类型(1:河南，2:南京，3:好大夫,4:王氏,5:健康160)';
alter table `tb_guahao_schedule` MODIFY `tp_platform` tinyint(4) unsigned NOT NULL DEFAULT '0' COMMENT '第三方平台类型(1:河南，2:南京，3:好大夫,4:王氏,5:健康160)';
alter table `tb_guahao_scheduleplace_relation` MODIFY `tp_platform` tinyint(4) unsigned NOT NULL DEFAULT '0' COMMENT '第三方平台类型(1:河南，2:南京，3:好大夫,4:王氏,5:健康160)';
alter table `tb_tmp_department_third_party` MODIFY `tp_platform` tinyint(4) unsigned NOT NULL DEFAULT '0' COMMENT '第三方平台类型(1:河南，2:南京，3:好大夫,4:王氏,5:健康160)';
alter table `tb_tmp_hospital_third_party` MODIFY `tp_platform` tinyint(4) unsigned NOT NULL DEFAULT '0' COMMENT '第三方平台类型(1:河南，2:南京，3:好大夫,4:王氏,5:健康160)';
alter table `tb_department_third_party_relation` MODIFY `tp_platform` tinyint(4) unsigned NOT NULL DEFAULT '0' COMMENT '第三方平台类型(1:河南，2:南京，3:好大夫,4:王氏,5:健康160)';

alter table `tb_guahao_schedule` MODIFY  `visit_nooncode` tinyint(4) unsigned NOT NULL DEFAULT '0' COMMENT '午别 1:上午 2：下午 3:晚上';
alter table `tb_guahao_schedule` MODIFY  `status` tinyint(4)  NOT NULL DEFAULT '1' COMMENT '出诊状态(-1:已取消 0约满 1可约 2停诊 3已过期 4其他)';

alter table `tb_doctor` MODIFY `miao_doctor_id` int(10) unsigned  NOT NULL DEFAULT '0' COMMENT '王氏医生ID(默认是没有关联)';


alter table `tb_guahao_order` add COLUMN `device_source` tinyint(2) unsigned NOT NULL DEFAULT '1' COMMENT '设备来源(1：H5，2：APP，3：小程序，4：PC)' after  `tp_platform`;

ALTER TABLE `tb_doctor_third_party_relation` DROP INDEX guahao_tp_doctor_relation;
ALTER TABLE `tb_doctor_third_party_relation` add INDEX  `guahao_tp_doctor_relation` (`doctor_id`,`tp_platform`,`tp_doctor_id`,`status`);
ALTER TABLE `tb_hospital_department_relation` add INDEX  `hospital_id_fkname_skname` (`hospital_id`,`frist_department_name`,`second_department_name`);


##增加es字段
curl -XPUT '192.168.3.25:9700/hospital_doctor_index/_mapping' -d '{"properties":{"tb_doctor_third_scheduleplace":{"type": "text"}}}' -H "Content-Type: application/json"