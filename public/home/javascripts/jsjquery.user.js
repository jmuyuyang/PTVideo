
$("#power .usersearch input").blur(function(){
	var $parent = $(this).parents("#power");
	var op_selected = $parent.find(".mold").val();
	var search_input = $(this).val();
	if(search_input == "管理员搜索" || search_input=="普通用户搜索"){
		$(this).val("");
	}
	if(search_input == "" && op_selected == "0"){
		$(this).val("管理员搜索");
	}
	if(search_input == "" && op_selected == "1"){
		$(this).val("普通用户搜索");
	}
}).focus(function(){
	$(this).triggerHandler("blur");
});

$(".power").submit(function(){
	var powerSet = $(this).find(".set").val();
	var uid = $(this).find(".uid").val();
	$.ajax({ 
		cache: false,
       	type: "GET",
        url: "/power/"+uid+"?set="+powerSet,
       	dataType: "json",
		error: function(){alert("设置失败");},
		success: function (data) {
			alert("设置成功");
        }
    });
    return false;
});

$(".outime .detail").click(function(){
	var $parent = $(this).parents("div.invite");
	var $tips = $parent.find(".tip");
	alert($parent.html());
	if($tips.find(".user").text() != ""){
		$tips.slideToggle();
	}else{
		$.ajax({
			cache:false,
			type:"GET",
			url:"/inviteu/"+$tips.find(".tip-id").val(),
			dataType:"json",
			error:function(){alert("设置失败")},
			success:function(data){
				$tips.find(".user").text(data.username);
				$tips.find(".usetime").text(data.addtime);
				$tips.slideToggle();
			}
		});
	}
});

$("#power .button").click(function(){
	var u_type = $("#power .mold").val();
	var search = encodeURI($("#power .usersearch input").val());
	window.location.href = "/manage/"+search+"?type="+u_type;
});

$("#invitecode a").click(function(){
	var $parent = $(this).parent();
	$parent.find(".prompt").text("正在获取中");
	$.ajax({ 
		cache: false,
       	type: "GET",
        url: "/invite",
       	dataType: "json",
		error: function(){alert("获取失败");},
		success: function (data) {
			$parent.find(".prompt").text("获取成功");
			$parent.find("input").val(data.inviteHash);
        }
    });
});


