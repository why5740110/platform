CREATE TABLE `tb_tmp_hospital_third_party` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `tp_platform` tinyint(4) unsigned NOT NULL DEFAULT '0' COMMENT '第三方平台类型(1:河南，2:南京，3:好大夫,4:王氏)',
  `tp_hospital_code` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '' COMMENT '第三方医院id',
  `hospital_name` varchar(100) NOT NULL DEFAULT '' COMMENT '医院名称',
  `city_code` int(10) NOT NULL DEFAULT '0' COMMENT '地区代码',
  `province` varchar(30) NOT NULL DEFAULT '' COMMENT '省份',
  `tp_hospital_level` varchar(30) NOT NULL DEFAULT '' COMMENT '医院等级',
  `tp_guahao_section` tinyint(4) NOT NULL DEFAULT '0' COMMENT '是否有时段  0没有 1 有',
  `tp_guahao_verify` varchar(10) NOT NULL DEFAULT '' COMMENT '医院特殊标识：1需要密码，2需要就诊卡，3需要就诊卡密码（多个标识以逗号分隔）',
  `hospital_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '王氏医院ID',
  `is_relation` tinyint(4) NOT NULL DEFAULT '0' COMMENT '是否已关联 0未关联 1已关联',
  `admin_id` smallint(5) unsigned NOT NULL DEFAULT '0' COMMENT '操作人ID',
  `admin_name` varchar(30) NOT NULL DEFAULT '' COMMENT '操作人',
  `create_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COMMENT='新增第三方医院临时表';

