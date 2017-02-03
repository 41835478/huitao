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
           // console.log(json);
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
			//请求之后就是显示下一个50条，所以页码为1
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
	//查找指定ID的商品
	$('.serach').on("click",function(){
		var goodid=$("#goodid").val();
		postdata={"id":goodid};
		doget(postdata,1);
	});
	//重新刷新表格，因为当搜索后表格格式发生了变化
	$("#btn1").on("click",function () {
		doget({"page":1},1);
		currentno=1;
	});
	//监测查找输入框，如果查找输入框为空时，显示表格
	$("#goodid").on("keyup",function () {
		if($("#goodid").val()==''||$("#goodid").val()==null){
			$("#btn1").trigger("click");
		}
	});
	//删除商品
	$("body").on("click",".delete",function(){
		var r=confirm("确认删除商品信息吗？")
		if (r==true)
		{
			var id=$(this).parent().parent().find("td").first().html();
			var deletedata={"id":id};
			deletegood(deletedata);
			doget({"page":currentno},1); //删除后留在当前页
		}

	});
	//添加商品
    $(".addgoods").on("click",function () {
		$("#updateuser").show();
	});
	$(".btcancle").on("click",function () {
		$("#updateuser").hide();
	});
	$(".btconfirm").on("click",function () {
		var num_iid=$("[name='num_iid']").val();
		var store_type=getStoreType($("[name='store_type']").val());
		var item_url=$("[name='item_url']").val();
		var rating=$("[name='rating']").val();
		var title=$("[name='title']").val();
		var price=$("[name='price']").val();
		var volume=$("[name='volume']").val();
		if(num_iid==''){$("[name='num_iid']").css("border","1px solid red");return}else{$("[name='num_iid']").css("border","1px solid #ccc")};
		if(item_url==''){$("[name='item_url']").css("border","1px solid red");return}else{$("[name='item_url']").css("border","1px solid #ccc")};
		if(rating==''){$("[name='rating']").css("border","1px solid red");return}else{$("[name='rating']").css("border","1px solid #ccc")};
        //做ajax请求
		var postdata={
			"num_iid":num_iid,
			"store_type":store_type,
			"item_url":item_url,
			"rating":rating,
			"title":title,
			"price":price,
			"volume":volume,
			"created_date":nowtime()
		};
		var mainUrl=getUrl();
		var funUrl="/HtGoodsController/addgoods";
		var myurl=mainUrl+funUrl;
		$.ajax({
			type:"post",
			data:postdata,
			url:myurl,
			success:function(res){
				//json字符串转换为json对象
				var json =JSON.parse(res);
				// console.log(json);
					alert(json.msg);
				for(var i =0;i<$(".infoma").length;i++){
					$(".infoma")[i].value="";
				}
				$("#updateuser").hide();
				}
		});


	});



    //显示表格
	function showtable(data,pageno,psize,pnum){//要显示的数据，显示的页码，每页数据的条数，总页数，当前页码
		//alert(currentno);
		var curno=currentno;
		curno<=1?$(".prepage").addClass("disabled"):$(".prepage").removeClass("disabled");
		curno>=pnum?$(".nextpage").addClass("disabled"):$(".nextpage").removeClass("disabled");
		$("tbody").html("");
		//显示表格
		//for(var i=(pageno-1)*psize;i<pageno*psize;i++){-->错误的写法
		for(var i=0;i<data.length;i++){
			var url=checkstr(data[i]["item_url"]);
			var store_str=reStoreType(data[i]["store_type"]);
			var child="<tr><td>"
				+checkstr(data[i]["id"])+"</td><td>"
				+checkstr(data[i]["num_iid"])+"</td><td>"
				+store_str+"</td><td style='width:150px;'><a href="+url+">"
				+checkstr(data[i]["item_url"]).substr(0, 13)+"..."+"</a></td><td style='width:150px;'>"
				+checkstr(data[i]["title"]).substr(0, 7)+"..."+"</td><td>"
				+checkstr(data[i]["volume"])+"</td><td>"
				+checkstr(data[i]["price"])+"</td><td>"
				+checkstr(data[i]["rating"])+"</td><td>"
				+checkstr(data[i]["created_date"])+"</td><td style='width:85px; text-align: center'>"
				+"<button class='btn btn-primary delete'>删除</button>"+"</td></tr>";
			$("#tab").append(child);
		}
	}
	//根据平台类型转换为平台名称
	function reStoreType(str){
		if(str=='1'){
			return "淘宝"
		}
		else if(str=='0'){
			return "天猫"
		}
		else{
			return "--";
		}
	}

	//ajax请求获取商品
	function doget(postdata,currentno){
		var mainUrl=getUrl();
		var funUrl="/HtGoodsController/querygoods";
		var myurl=mainUrl+funUrl;
		$.ajax({
			type:"post",
			data:postdata,
			url:myurl,
			success:function(res){
				//console.log(res);
				//json字符串转换为json对象
				var json =JSON.parse(res);
				// console.log(json);
				if(json.status==1){
					if(json.data.data){
						mydata=json.data.data;
						sumrecords=json.data.sum;
					}else{
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
				else{
					alert(json.msg)
					location.href="login.html";
				}
			}
		});
	}
	//ajax请求删除商品
	function deletegood($deletedata){
		var mainUrl=getUrl();
		var funUrl="/HtGoodsController/deletegoods";
		var myurl=mainUrl+funUrl;
		$.ajax({
			type:"post",
			data:$deletedata,
			url:myurl,
			success:function(res){
				//json字符串转换为json对象
				console.log(res);
				var json =JSON.parse(res);
                    alert(json.msg);

			}
		});
	}
	//获取当前时间
	function nowtime(){//将当前时间转换成yyyymmdd格式
		var mydate = new Date();
		var str = "" + mydate.getFullYear();
		var mm = mydate.getMonth()+1
		if(mydate.getMonth()>9){
			str += mm;
		}
		else{
			str += "0" + mm;
		}
		if(mydate.getDate()>9){
			str += mydate.getDate();
		}
		else{
			str += "0" + mydate.getDate();
		}
		return str;
	}
  //处理字符串，防止为空时，加载表格报错
	function checkstr(str){
		return str==''||str==null? "--":str;
	}
	//获取store_type，天猫为0，淘宝为1
   function getStoreType($str)
	{
		return $str === "天猫" ? 0 : 1;

	}



});