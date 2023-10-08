##增加字段
ALTER TABLE `tb_guahao_scheduleplace_relation` add COLUMN  `hospital_department_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '王氏医院科室ID' after  `realname`,
add COLUMN `status` tinyint(1)  NOT NULL DEFAULT '0' COMMENT '审核状态(-1:审核失败,0:审核中,1:审核成功,2:用户关闭)' after  `hospital_department_id`;

##更新旧的出诊地为审核通过
update tb_guahao_scheduleplace_relation set `status` = 1 WHERE tp_platform != 6;


ALTER TABLE `tb_tmp_doctor_third_party` ADD COLUMN `status` TINYINT (1) UNSIGNED NOT NULL DEFAULT '1' COMMENT '是否正常(1:正常,0:禁用)' after `is_relation`;