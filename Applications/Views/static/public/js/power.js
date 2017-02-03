$('document').ready(function(){
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
                alert(json.msg+"或未登录！");
                location.href="login.html";
            }

        }
    });


    //点击查看权限
    $('body').on('click','#observe',function(){
        $("input:checkbox").prop("checked",false);
        if($("#id").val()!=''){
            var mainUrl=getUrl();
            var fun='/HtPowerController/getpower';
            var myurl=mainUrl+fun;
            var postdata={'id':$("#id").val()}
            $.ajax({
                type: "post",
                data: postdata,
                url: myurl,
                success: function (res) {
                    var json =JSON.parse(res);
                    if(json.status=='1'){
                        //用数组存放该用户拥有的权限代号
                        var powerArr=new Array();
                        for(var i=0;i<json.data.length;i++){
                            powerArr.push(json.data[i]['id']);
                        }
                        //console.log(powerArr);
                        if($.inArray('27',powerArr)!=-1){
                            $("#appsee").prop("checked","checked");
                        }
                        if(jQuery.inArray('28',powerArr)!=-1){
                            $("#appupdate").prop("checked","checked");
                        }
                        if(jQuery.inArray('29',powerArr)!=-1){
                            $("#backsee").prop("checked","checked");
                        }
                        if(jQuery.inArray('30',powerArr)!=-1){
                            $("#backupdate").prop("checked","checked");
                        }
                        if(jQuery.inArray('31',powerArr)!=-1){
                            $("#goodssee").prop("checked","checked");
                        }
                        if(jQuery.inArray('32',powerArr)!=-1){
                            $("#goodsdelete").prop("checked","checked");
                        }
                        if(jQuery.inArray('33',powerArr)!=-1){
                            $("#tocashsee").prop("checked","checked");
                        }
                        if(jQuery.inArray('34',powerArr)!=-1){
                            $("#tocashupdate").prop("checked","checked");
                        }
                        if(jQuery.inArray('35',powerArr)!=-1){
                            $("#power").prop("checked","checked");
                        }
                        if(jQuery.inArray('36',powerArr)!=-1){
                            $("#finance").prop("checked","checked");
                        }
                        if(jQuery.inArray('37',powerArr)!=-1){
                            $("#business").prop("checked","checked");
                        }
                        if(jQuery.inArray('38',powerArr)!=-1){
                            $("#extend").prop("checked","checked");
                        }
                    }
                    else{
                        alert(json.msg);
                        location.href="login.html";
                    }

                }

            })

        }
        else{
            alert("请输入用户ID");
        }
    });
    //监听id和用户名的输入框，其中一个有值，则清空另外一个
    $("#id").on("keyup",function () {
        if($("#id").val()!=''){
            $('#username').val('');
            $('#password').val('');
        }
        else{
            $('#username').attr("disabled",false);
            $('#password').attr("disabled",false)
        }
    });
    $("#username").on("keyup",function () {
        if($("#username").val()!=''){
            $('#id').val('');
        }
        else{
            $('#id').val('');
        }
    });
    $("#password").on("keyup",function () {
        if($("#password").val()!=''){
            $('#id').val('');
        }
        else{
            $('#id').val('');
        }
    });
    //保存用户的权限，在id输入框中输入id，那么修改，如果不输入，则询问是否创建新用户
    $("body").on("click",'#save',function(){
        //1.如果都为空则提示规则
         if($("#id").val()==''&&$("#username").val()==''){
             alert("修改用户请输入ID，新建用户请输入用户名和密码！")
         }
         else{
             var postdata;
             var nodeStr;
             var htNode_id=[];
             for(var i=1;i<$("input:checkbox").length;i++){
                 if($("input:checkbox")[i].checked==true){
                     htNode_id.push($("input:checkbox")[i]['value']);
                 }
             }
             nodeStr=htNode_id.join(',');

             //2.1有id则修改
             if($("#id").val()!=''){
                 if($("#id").val()==1){alert("超级管理员的权限不允许修改!")
                 }else{
                     if(confirm("确认修改该用户的权限吗？")){
                         postdata={'id':$("#id").val(),'htNode_id':nodeStr};
                     }
                 }

             }
             //2.2没有id则创建
             else {
                 if($("#password").val()==''||$("#password").val().length<6){
                     alert("请输入不少于6位的密码!");
                 }
                 else{
                     postdata={'username':$("#username").val(),'password':$("#password").val(),'htNode_id':nodeStr};
                 }
             }
             //console.log(postdata);
             var mainUrl=getUrl();
             var fun="/HtPowerController/setpower2";
             var myUrl=mainUrl+fun;
             $.ajax({
                 type: "post",
                 data: postdata,
                 url: myUrl,
                 success:function (res) {
                     // console.log(res);
                     var json =JSON.parse(res);
                     if(json.status=='1'){
                          alert(json.msg);
                     }else{
                         alert(json.msg);
                     }
                 }
             })

         }
    })

});
