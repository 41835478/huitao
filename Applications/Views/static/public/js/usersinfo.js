$('document').ready(function () {
   // 页面初始化的时候，请求用户的数据
	var mainurl=getUrl();
	var fun1="/HtUser/getuserinfo";
	var tourl1=mainurl+fun1;
	$.ajax({
		type:"get",
		url:tourl1,
		success:function(res){
			//console.log(res);
			var json =JSON.parse(res);
			//console.log(json);
			if(json.status==1){
                $("#id").html(checkstr(json.data['id']));
				$("#username").html(checkstr(json.data['username']));
				$("#phone").html(checkstr(json.data['phone']));
				$("#money").html(checkstr(json.data['money']));
				$("#role").html(getRole(json.data['role']));
				$("#name").html(checkstr(json.data['name']));
				$("#createtime").html(checkstr(json.data['createdAt']));
				$("#optime").html(checkstr(json.data['updatedAt']));
				$("#status").html(getStatus(json.data['status']));
				$("#id").html(checkstr(json.data['id']));

			}else{
				alert(json.msg);
				location.href="login.html";
			}

		}
	});
	var fun12="/HtPowerController/inipower";
	var tourl2=mainurl+fun12;
	$.ajax({
		type:"get",
		url:tourl2,
		success:function(res){
			var json =JSON.parse(res);
			//console.log(json);
			if(json.status==1){
				var powerArr=new Array();
				for(var i=0;i<json.data.length;i++){
					powerArr.push(json.data[i]['id']);
				}
				if($.inArray('27',powerArr)==-1&&$.inArray('28',powerArr)==-1){
					$("#app").css("display",'none');
				}
				if($.inArray('29',powerArr)==-1&&$.inArray('30',powerArr)==-1){
					$("#back").css("display",'none');
				}
				if($.inArray('31',powerArr)==-1&&$.inArray('32',powerArr)==-1){
					$("#shangpin").css("display",'none');
				}
				if($.inArray('33',powerArr)==-1&&$.inArray('34',powerArr)==-1){
					$("#tixian").css("display",'none');
				}
				if($.inArray('35',powerArr)==-1&&$.inArray('39',powerArr)==-1){
					$("#quanxian").css("display",'none');
				}
				if($.inArray('36',powerArr)==-1){
					$("#caiwu").css("display",'none');
				}
				if($.inArray('37',powerArr)==-1){
					$("#zhaoshang").css("display",'none');
				}
				if($.inArray('38',powerArr)==-1){
					$("#tuiguang").css("display",'none');
				}

			}else{
				//alert(json.msg);
			}

		}
	});

	$("#update").on('click',function(){
       $("#updateuser").show();
	});
	$(".bt1").on('click',function(){
		$("#updateuser").hide();
	});
	$(".bt2").on('click',function(){
		//$("#updateuser").hide();
	});
	//点击退出登录
	$("#exit").on("click",function(){
		if(confirm("确认退出登录吗？")){
		var mainurl=getUrl();
		var fun="HtUserController/exitlogin";
		var tourl=mainurl+fun;
		$.ajax({
			type:"get",
			url:tourl
		});
			alert("已退出登录");
			location.href="login.html";
		}});
	//处理字符串，防止为空时，加载表格报错
	function checkstr(str){
		return str==''||str==null? "--":str;
	}
	//处理角色信息，返回字符串1-超级管理员 2-财务 3-客服 4-推管 5-商管 6-推广 7-商务 8-广告主
	function  getRole(no){
		switch(no){
			case '1':
				return "超级管理员";
				break;
			case '2':
				return "财务";
				break;
			case '3':
				return "客服";
				break;
			case '4':
				return "推管";
				break;
			case '5':
				return "商管";
				break;
			case '6':
				return "推广";
				break;
			case '7':
				return "商务";
				break;
			case '8':
				return "广告主";
			default:
				return "--"
		}}
	//处理状态信息 0表示正常，1表示已被删除
	function getStatus(status){
		switch (status){
			case "0":
				return "已被删除";
				break;
			case "1":
				return "正常";
				break;
			default:return "--"
		}
	}

});