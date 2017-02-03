$('document').ready(function () {
	var mydata=0;
	var currentno=1;//要显示的页面序号
	var postdata={"page":currentno};//ajax请求时传的参数
	var sumrecords=0;//记录总条数
	var pnum=0;//总页数
	var pagesize;//每页显示条数
	//获得初始权限
	var mainurl2=getUrl();
	var fun12="/HtPowerController/inipower";
	var tourl2=mainurl2+fun12;
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
	          //  alert(json.msg);
	        }

	    }
	});
	//页面加载时初始化
	doget(postdata,1);
	//监测每页显示条数的变化
	$("#selecnum").on("change",function () {
		pagesize=$("#selecnum").val();
		currentno=1;
		showtable(mydata,currentno,pagesize);
	});
	//显示下一页，每点击一次，请求一次
	$('.nextpage').on("click",function () {
		if(currentno<pnum){
			currentno=currentno+1;
			postdata={"page":currentno};
			//请求之后就是显示下一个10条，所以页码为1
			doget(postdata,1);}
	});
	//显示上一页，每点击一次，请求一次
	$('.prepage').on("click",function () {
		if(currentno>1){
			currentno=currentno-1;
			postdata={"page":currentno};
			doget(postdata,1);}
	});
	//显示第一页
	$('.fristpage').on("click",function () {
		postdata={"page":1};
		currentno=1;//复位页码
		doget(postdata,1);
	});
	//显示最后一页
	$('.lastpage').on("click",function () {
		postdata={"page":pnum};
		currentno=pnum;//复位页码
		doget(postdata,1);
	});
	//跳转至指定页
	$('.order').on("click",function () {
		var page=parseInt($('#goto').val());
		if(page>pnum||page<1){
			alert("请输入正确的页码");
		}
		else{
			postdata={"page":page};
			currentno=page;//复位页码
			doget(postdata,1)}
	});
	//查找指定ID的提现记录
	$('.serach').on("click",function(){
		var listid=$("#listid").val();
		postdata={"id":listid};
		doget(postdata,1);
	});
	//重新刷新表格，因为当搜索后表格格式发生了变化
	$("#btn1").on("click",function () {
		doget({"page":1},1);
		currentno=1;
	});
	//监测查找输入框，如果查找输入框为空时，显示表格
	$("#listid").on("keyup",function () {
		if($("#listid").val()==''||$("#listid").val()==null){
			$("#btn1").trigger("click");
		}
	});
	//查询已在提现处理中的
	$('.info').on("click",function(){
		postdata={"duiba_stime":"true"};
		doget(postdata,1);
	});
	//查询已在提现成功
	$('.success').on("click",function(){
		postdata={"duiba_success":"true"};
		doget(postdata,1);
	});
	//查询已在提现失败的
	$('.fail').on("click",function(){
		postdata={"duiba_end_errmsg":"true"};
		doget(postdata,1);
	});
	//查询正在申请提现的列表
	$('.apply').on("click",function(){
		postdata={"apply":"true"};
		doget(postdata,1);
	});



	//拒绝提现
	$("body").on("click",".refuse",function(){
		//获取当前用户ID
		var oper_id="1";
		if (confirm("确认拒绝此用户的提现吗？"))
		{
			var iid=$(this).parent().parent().find("td").eq(0).html();
			console.log(iid);
			var refusedata={"id":iid,"duiba_end_errmsg":"后台拒绝提现！","oper_id":oper_id,"duiba_stime":1};
			refusecash(refusedata);
			doget({"page":currentno},1);
		}

	});
	//允许提现
	$("body").on("click",".agree",function(){
		//获取当前用户ID
		var data=null;
		var id=$(this).parent().parent().find("td").eq(0).html();
		for(var i=0;i<mydata.length;i++){
			if(mydata[i]['id']==id){
				data=mydata[i];
			};

		}
		var agreedata={
			"uid":data['uid'],
			"price":parseInt(data['price']),
			"pid":data['id'],
			"alipay":data['alipay'],
			"alipay_name":data['alipay_name']
		};
		if (confirm("确认同意此用户的提现吗？"))
		{
			agreecash(agreedata);
			doget({"page":currentno},1);
		}

	});
	//查看详细
	$("body").on("click",".detail",function(){
		console.log(mydata);
		//获取滚动条位置
		$("#mask").css({"margin-top":$(document).scrollTop()});
		$(document).scrollTop();
		$("#mask").show();
		var data=null;
		var id=$(this).parent().parent().find("td").eq(0).html();
		for(var i=0;i<mydata.length;i++){
			if(mydata[i]['id']==id){
				data=mydata[i];
			};
		}
		$("#id").html(checkstr(data['id']));
		$("#objectId").html(checkstr(data['duiba_order']));
		$("#updatedAt").html(checkstr(data['updatedAt']));
		$("#uid").html(checkstr(data['uid']));
		$("#did").html(checkstr(data['did']));
		$("#paychoose").html(checkstr(data['paychoose']));
		$("#errmsg").html(checkstr(data['duiba_end_errmsg']));
		$("#alipay").html(checkstr(data['alipay']));
		$("#oper_id").html(checkstr(data['oper_id']));

	});
	//点击确定隐藏遮罩
	$("body").on('click',".sure",function(){
		$("#mask").hide();
	});




	//显示表格
	function showtable(data,pageno,psize,pnum){//要显示的数据，显示的页码，每页数据的条数，总页数，当前页码
		var curno=currentno;
		curno<=1?$(".prepage").addClass("disabled"):$(".prepage").removeClass("disabled");
		curno>=pnum?$(".nextpage").addClass("disabled"):$(".nextpage").removeClass("disabled");
		$("tbody").html("");
		//显示表格
		//for(var i=(pageno-1)*psize;i<pageno*psize;i++){-->错误的写法
		for(var i=0;i<data.length;i++){
			var status=getStatus(data[i]["duiba_stime"],data[i]["duiba_success"],data[i]["duiba_end_errmsg"])
			var child="<tr><td>"
				+checkstr(data[i]["id"])+"</td><td>"
				+checkstr(data[i]["duiba_order"])+"</td><td>"
				+checkstr(data[i]["updatedAt"])+"</td><td>"
				+status+"</td><td>"
				+checkstr(data[i]["price"])+"</td><td>"
				+checkstr(data[i]["paychoose"])+"</td><td>"
				+checkstr(data[i]["alipay_name"])+"</td><td>"
				+checkstr(data[i]["alipay"])+"</td>"+getAction(status)+"</tr>";
			$("#tab").append(child);
		}
	}


	//ajax请求获取用户信息
	function doget(postdata,currentno){
		var mainUrl=getUrl();
		var funUrl="/HtToCashController/querycashapply";
		var myurl=mainUrl+funUrl;
		$.ajax({
			type:"post",
			data:postdata,
			url:myurl,
			success:function(res){
				//console.log(res);
				//json字符串转换为json对象
				var json =JSON.parse(res);
				// console.log(json.msg);
				if(json.msg=='ok'){
					if(json.data.data){
						mydata=json.data.data;
						sumrecords=json.data.sum;
					}
					else{
						mydata=json.data;
						sumrecords=1;
						currentno=1;
					}
					//console.log(mydata);
					pnum=Math.ceil(sumrecords/50);
					$("#sumnum").html(sumrecords);
					$("#sum").html(pnum);
					showtable(mydata,currentno,50,pnum);
					$("td").css("line-height","40px");

				}
				else {
					alert(json.msg);
					location.href="login.html";
				}

			}
		});
	}
	//ajax请求拒绝提现
	function refusecash(refusedata){
		var mainUrl=getUrl();
		var funUrl="/HtToCashController/refusetocash";
		var myurl=mainUrl+funUrl;
		$.ajax({
			type:"post",
			data:refusedata,
			url:myurl,
			success:function(res){
				console.log(res);
				//json字符串转换为json对象
				var json =JSON.parse(res);
				alert(json.msg);

			}
		});
	}
	//ajax允许提现--直接请求兑吧接口
	function agreecash(agreedata){
		var mainUrl=getUrl();
		var funUrl="duiba/zhida";
		var myurl=mainUrl+funUrl;
		console.log(agreedata);
		$.ajax({
			type:"post",
			data:agreedata,
			url:myurl,
			success:function(res){
				//json字符串转换为json对象
				console.log(res);
				var json =JSON.parse(res);
				if(json.status==1){
					if(confirm("是否允许跳转到其他网页？")){
						window.open(json.url);
					}}

			}
		});
	}
	//处理字符串，防止为空时，加载表格报错
	function checkstr(str){
		return str==''||str==null? "--":str;
	}
	//判断用户的提现状态:
	// duiba_stime 没值 表示正在申请提现的用户  duiba_stime有值 表示正在申请中  duiba_success 有值 表示提现成功 duiba_end_errmsg 有值 表示提现失败
	function  getStatus(dst,dsu,dee){
		if(dsu!=null){
			return "提现成功";
		}
		else if(dee!=null){
			return "提现失败";
		}else{
			if(dst!=null){
				return "处理中";
			}else {
				return "申请中";
			}
		}
	}
	//根据用户状态显示操作选项，申请中显示拒绝和同意，其他显示查看
	function getAction(str){
		if(str=="申请中"){
			return "<td style='width: 180px;text-align: center'>"
				+"<button class='btn btn-danger refuse' style='margin-right: 10px'>拒绝</button><button  class='btn btn-primary agree'>同意</button></td>";
		}
		else{
			return "<td style='text-align: center'>"
				+"<button class='btn btn-success detail'>查看</td>";

		}

	}

});