alter table `tb_doctor` add COLUMN `is_incr` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否是加号0否1是' after `is_plus`;


##修改comment说明
alter table `tb_tmp_doctor_third_party` MODIFY `tp_platform` tinyint(4) unsigned NOT NULL DEFAULT '0' COMMENT '第三方平台类型(1:河南，2:南京，3:好大夫,4:王氏)';
alter table `tb_doctor_third_party_relation` MODIFY `tp_platform` tinyint(4) unsigned NOT NULL DEFAULT '0' COMMENT '第三方平台类型(1:河南，2:南京，3:好大夫,4:王氏)';
alter table `tb_guahao_hospital` MODIFY `tp_platform` tinyint(4) unsigned NOT NULL DEFAULT '0' COMMENT '第三方平台类型(1:河南，2:南京，3:好大夫,4:王氏)';
alter table `tb_guahao_interrogation` MODIFY `tp_platform` tinyint(4) unsigned NOT NULL DEFAULT '0' COMMENT '第三方平台类型(1:河南，2:南京，3:好大夫,4:王氏)';
alter table `tb_guahao_order` MODIFY `tp_platform` tinyint(4) unsigned NOT NULL DEFAULT '0' COMMENT '第三方平台类型(1:河南，2:南京，3:好大夫,4:王氏)';
alter table `tb_guahao_schedule` MODIFY `tp_platform` tinyint(4) unsigned NOT NULL DEFAULT '0' COMMENT '第三方平台类型(1:河南，2:南京，3:好大夫,4:王氏)';
alter table `tb_guahao_scheduleplace_relation` MODIFY `tp_platform` tinyint(4) unsigned NOT NULL DEFAULT '0' COMMENT '第三方平台类型(1:河南，2:南京，3:好大夫,4:王氏)';
alter table `tb_tmp_department_third_party` MODIFY `tp_platform` tinyint(4) unsigned NOT NULL DEFAULT '0' COMMENT '第三方平台类型(1:河南，2:南京，3:好大夫,4:王氏)';
alter table `tb_tmp_hospital_third_party` MODIFY `tp_platform` tinyint(4) unsigned NOT NULL DEFAULT '0' COMMENT '第三方平台类型(1:河南，2:南京，3:好大夫,4:王氏)';
alter table `tb_department_third_party_relation` MODIFY `tp_platform` tinyint(4) unsigned NOT NULL DEFAULT '0' COMMENT '第三方平台类型(1:河南，2:南京，3:好大夫,4:王氏)';

alter table `tb_guahao_schedule` MODIFY  `visit_nooncode` tinyint(4) unsigned NOT NULL DEFAULT '0' COMMENT '午别 1:上午 2：下午 3:晚上 4:全天';
alter table `tb_guahao_schedule` add  COLUMN `first_practice` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否是第一执业0否1是' after `status`;


##增加tb_doctor_third_party_relation表字段
alter table `tb_doctor_third_party_relation` 
add COLUMN   `realname` varchar(50) NOT NULL DEFAULT '' COMMENT '医生姓名' after `doctor_id`,
add COLUMN `tp_hospital_code` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '' COMMENT '第三方医院id' after `tp_doctor_id`,
add COLUMN `hospital_name` varchar(100) NOT NULL DEFAULT '' COMMENT '医院名称' after `tp_hospital_code`,
add COLUMN `tp_frist_department_id` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '' COMMENT '第三方一级科室ID' after `hospital_name`,
add COLUMN `frist_department_name` varchar(50) NOT NULL DEFAULT '' COMMENT '第三方一级科室名称' after `tp_frist_department_id`,
add COLUMN `tp_department_id` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '' COMMENT '第三方二级科室ID' after `frist_department_name`,
add COLUMN `second_department_name` varchar(50) NOT NULL DEFAULT '' COMMENT '第三方二级科室名称' after `tp_department_id`;


alter table `tb_guahao_scheduleplace_relation` 
add COLUMN `doctor_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '医生ID' after `tp_platform`,
add COLUMN   `realname` varchar(50) NOT NULL DEFAULT '' COMMENT '医生姓名' after `doctor_id`;


alter table `tb_guahao_scheduleplace` add COLUMN `hospital_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '医院id' after `scheduleplace_id`;
alter table `tb_guahao_scheduleplace` add COLUMN `hospital_name` varchar(100) NOT NULL DEFAULT '' COMMENT '医院名称' after `hospital_id`;


##删除索引
ALTER TABLE `tb_guahao_scheduleplace_relation` DROP INDEX guahao_tp_scheduleplace_relation;
ALTER TABLE `tb_guahao_schedule` DROP INDEX tp_scheduling_relation;


##增加第三方一级科室
alter table `tb_guahao_schedule` add COLUMN  `tp_frist_department_id` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '' COMMENT '第三方一级科室ID' AFTER `scheduleplace_name`;
alter table `tb_guahao_schedule` add COLUMN `tp_frist_department_name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '' COMMENT '第三方一级科室名称' AFTER `tp_frist_department_id`;

##删除执业地字段
ALTER TABLE `tb_guahao_scheduleplace` DROP INDEX doctor_id;
ALTER TABLE `tb_guahao_scheduleplace` drop COLUMN doctor_id;
ALTER TABLE `tb_guahao_scheduleplace` drop COLUMN realname;