layui.use(['form','layer','laydate','element'], function(){
	var $ = layui.jquery,upload = layui.upload,layer=layui.layer,form=layui.form;
	var laydate = layui.laydate;
	var element = layui.element;
  
  	//二级分类联动
  	form.on('select(category_one)', function(data){
  		var two_obj = $('select[name="category_id_2"]');
  	  	two_obj.attr('data-id','');

  	  	var pid = data.value;
		getTwoList(layui, form, pid);
	});
  	//第三级联动
	form.on('select(category_two)', function(data){
		var pid = data.value;
		getCategoryList(layui, form, pid, 3);
	});
	//给定了值的时候初始化
	if($('select[name="category_id"]').val())
		getTwoList(layui, form, $('select[name="category_id"]').val());

	//药品切换
	form.on('select(product_type)',function(data){
		var type = data.value;
		changeColumn(type);
	});
	if(typeof(product_type)!='undefined' && product_type>0){
		changeColumn(product_type);
	}

	//年月日选择器
  	laydate.render({
    	elem: '#star_start',
    	type: 'date'
  	});
  	laydate.render({
    	elem: '#star_end',
  	});
	$('#form1,#form2,#form3,#form4,#form5').ajaxForm({
        dataType:'json',
        beforeSend:function(){
            index=layer.load(0, {shade: [0.1, '#393D49']}); //0代表加载的风格，支持0-2
        },
        complete:function(){
            layer.close(index);
        },
        success:function(data){
            if(data.status){
                layer.msg(data.msg,{icon:1});
                setTimeout(function(){
                    window.location.href=data.url;
                },1000)
            }else{
                var item=$("[name='"+data.field+"']");
                item.addClass('layui-form-danger');
                item.focus();
                console.log(data.msg);
                layer.msg(data.msg, {icon: 2});
            }
        },
        error:function(){
            layer.msg('网络异常，请联系系统维护人员', {icon: 2});
        }
    });
    //图片优化
  	element.on('tab(demo)', function(data){
    	//初始化图片
		$('.layui-tab-content img').each(function(){
			common.imgresize(this);
		});
  	});	

});

function getCategoryList(layuiobj,formobj,pid,level=2){
	var two_obj = $('select[name="category_id_2"]');
	var three_obj = $('select[name="category_id_3"]');
	var two_id = two_obj.attr('data-id');
	var three_id = three_obj.attr('data-id');
	$.ajax({
		url: '/product/category/getlist',
		type: 'get',
		data: 'pid='+pid,
		dataType: 'json',
		success:function(res){
			if(res.status){
				if(level == 2)
					two_obj.empty();
				three_obj.empty();
				three_obj.append('<option value="">请选择</option>');
				layuiobj.each(res.data, function(index,val){
					if(level == 2){
						if(two_id>0 && two_id == val.id)
							two_obj.append('<option value="'+val.id+'" selected="selected">'+val.name+'</option>');
						else
							two_obj.append('<option value="'+val.id+'">'+val.name+'</option>');
					}
					else if(level == 3){
						if(three_id>0 && three_id == val.id)
							three_obj.append('<option value="'+val.id+'" selected="selected">'+val.name+'</option>');
						else
							three_obj.append('<option value="'+val.id+'">'+val.name+'</option>');
					}
				});
				formobj.render();
			}
		}
	});
}

function getTwoList(layuiobj,formobj,pid){
	var two_obj = $('select[name="category_id_2"]');
	var three_obj = $('select[name="category_id_3"]');
	var two_id = two_obj.attr('data-id');
	var three_id = three_obj.attr('data-id');
	$.ajax({
		url: '/product/category/twolist',
		type: 'get',
		data: 'pid='+pid+'&cur_two_id='+two_id,
		dataType: 'json',
		success:function(res){
			if(res.status){
				two_obj.empty();
				three_obj.empty();
				two_obj.append('<option value="">请选择</option>');
				three_obj.append('<option value="">请选择</option>');
				layuiobj.each(res.data.two_list, function(index,val){
					if(two_id>0 && two_id == val.id)
						two_obj.append('<option value="'+val.id+'" selected="selected">'+val.name+'</option>');
					else
						two_obj.append('<option value="'+val.id+'">'+val.name+'</option>');
				
				});
				layuiobj.each(res.data.three_list, function(index,val){
					if(three_id>0 && three_id == val.id)
						three_obj.append('<option value="'+val.id+'" selected="selected">'+val.name+'</option>');
					else
						three_obj.append('<option value="'+val.id+'">'+val.name+'</option>');
				});	
				formobj.render();
			}
		}
	});
}

//切换商品类型
function changeColumn(type){
	//初始化字段
	type = parseInt(type);
	$(".tab_item_2 .layui-form-item").each(function(){
		$(this).show();
	});
	$('[name="usage"]').parent().parent().find('label').html('用法用量');
	$('[name="material"]').parent().parent().find('label').html('成份');

	if(type>1){ //非药品
		$("[name='property']").parent().parent().hide();
		$("[name='chemname']").parent().parent().hide();
		$('[name="en_name"]').parent().parent().hide();
		$('[name="py_name"]').parent().parent().hide();
		$('[name="takedays"]').parent().parent().hide();
		$('[name="for_pregnant_used"]').parent().parent().hide();
		$('[name="for_child_used"]').parent().parent().hide();
		$('[name="for_old_used"]').parent().parent().hide();
		$('[name="drug_interact"]').parent().parent().hide();
		$('[name="drug_dosage"]').parent().parent().hide();
		$('[name="clinic_trial"]').parent().parent().hide();
		$('[name="drug_phar_tox"]').parent().parent().hide();
		$('[name="pk"]').parent().parent().hide();
		$("[name='property']").removeAttr('required');
		$("[name='property']").removeAttr('lay-verify');	
	}else {
		$("[name='property']").parent().parent().show();
		$("[name='property']").attr('required', 'required');
		$("[name='property']").attr('lay-verify', 'required');
	}

	switch(type){
		case 2: //器械
			$('[name="usage"]').parent().parent().find('label').html('使用方法'); //用法用量
			$('[name="dosage_from"]').parent().parent().hide(); //剂型
			$('[name="shape"]').parent().parent().hide(); //性状
			$('[name="adverse_reactions"]').parent().parent().hide(); //不良反应
			$('[name="abstain_from"]').parent().parent().hide(); //禁忌
			$('[name="not_suitable_people"]').parent().parent().hide(); //不适用人群	
			break;
		case 3: //保健
			$('[name="abstain_from"]').parent().parent().hide();
			$('[name="usage"]').parent().parent().find('label').html('使用方法');
			$('[name="not_suitable_people"]').parent().parent().hide();
			break;
		case 4: //饮片
			$('[name="dosage_from"]').parent().parent().hide();
			$('[name="license_no"]').parent().parent().hide(); //批准文号
			break;
		case 5: //护理
			$('[name="dosage_from"]').parent().parent().hide(); //剂型
			$('[name="material"]').parent().parent().hide(); //成份
			$('[name="shape"]').parent().parent().hide(); //性状
			$('[name="adverse_reactions"]').parent().parent().hide(); //不良反应
			$('[name="abstain_from"]').parent().parent().hide(); //禁忌
			$('[name="not_suitable_people"]').parent().parent().hide(); //不使用人群	
			$('[name="usage"]').parent().parent().find('label').html('使用方法');
			break;
		case 6: // 计生
			$('[name="dosage_from"]').parent().parent().hide(); //剂型
			$('[name="shape"]').parent().parent().hide(); //性状
			$('[name="subtitle"]').parent().parent().hide(); //功能主治
			$('[name="business_name"]').parent().parent().hide(); //生产厂商
			break;
		case 7: //母婴
			$('[name="shape"]').parent().parent().hide(); //性状
			$('[name="adverse_reactions"]').parent().parent().hide(); //不良反应
			$('[name="not_suitable_people"]').parent().parent().hide(); //不适用人群	
			break;
		case 8: //食品
			$('[name="dosage_from"]').parent().parent().hide(); //剂型
			$('[name="material"]').parent().parent().find('label').html('主要原料'); //成份
			$('[name="usage"]').parent().parent().find('label').html('使用方法');
			$('[name="subtitle"]').parent().parent().hide(); //功能主治
			$('[name="adverse_reactions"]').parent().parent().hide(); //不良反应
			$('[name="abstain_from"]').parent().parent().hide(); //禁忌
			$('[name="not_suitable_people"]').parent().parent().hide(); //不适用人群	
			break;
		case 9: //日用百货
			$('[name="dosage_from"]').parent().parent().hide(); //剂型
			$('[name="shape"]').parent().parent().hide(); //性状
			$('[name="material"]').parent().parent().find('label').html('材质'); //成份
			$('[name="usage"]').parent().parent().find('label').html('使用方法');
			$('[name="subtitle"]').parent().parent().hide(); //功能主治
			$('[name="adverse_reactions"]').parent().parent().hide(); //不良反应
			$('[name="abstain_from"]').parent().parent().hide(); //禁忌
			$('[name="not_suitable_people"]').parent().parent().hide(); //不适用人群	
			$('[name="suitable_people"]').parent().parent().hide(); //适用人群	
			$('[name="store_way"]').parent().parent().hide(); //贮藏
			$('[name="license_no"]').parent().parent().hide(); //批准文号
			break;
	}

}

