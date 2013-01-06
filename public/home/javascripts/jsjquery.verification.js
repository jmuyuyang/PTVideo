$(function(){
	$(".submit #loginbutton").bind("click",function(event){
		$("p.red").remove();
		if($("#user input").val() == 0){
			$("#user input").after("<p class='red'><i>*</i>请输入账号!</p>");
			event.preventDefault();
		}
		if($("#password input").val() == 0){
			$("#password input").after("<p class='red'><i>*</i>请输入密码!</p>");
			event.preventDefault();
		}
	})

	$(".submit #signupbutton").bind("click",function(event){
		if($("#user input").val().length == 0){
			$("#user_tips").html("<i>*</i>请输入账号!");
			event.preventDefault();
		}
		if($("#password input").val().length == 0){
			$("#pass_tips").html("<i>*</i>请输入密码!");
			event.preventDefault();
		}
		if($("#mail input").val().length == 0){
			$("#mail_tips").html("<i>*</i>请输入验证邮箱!");
			event.preventDefault();
		}
		//if($("#required input").val().length == 0){
			//$("#required input").after("<p class='red'><i>*</i>请输入邀请码!</p>");
			//event.preventDefault();
		//}
	})

	$("#signup #user input").blur(function(){
		var val = $(this).val();
		if(val.length != 0){
			$.ajax({ 
				cache: false,
       			type: "GET",
        		url: "/check/?t=user&v="+val,
       			dataType: "json",
				success: function (data) {
					if(data.existed) {
						$("#user_tips").html("<i>*</i>账号已被使用");
						$("#loginbutton").attr("disabled",true);
					}else{
						$("#user_tips").html("账号可以使用");
						$("#loginbutton").attr("disabled",false);
					}
        		}
    		});
		}
	})

	$("#mail input").blur(function(){
		var val = $(this).val();
		var reg = /\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*/;
		if(val.length != 0){
			if(!reg.test($("#mail input").val())){
				$("#mail_tips").html("<i>*</i>email格式不正确");
				$("#loginbutton").attr("disabled",true);
			}else{
				$.ajax({ 
					cache: false,
       				type: "GET",
        			url: "/check/?t=email&v="+val,
       				dataType: "json",
					success: function (data) {
						if(data.existed) {
							$("#mail_tips").html("<i>*</i>email已被使用");
							$("#loginbutton").attr("disabled",true);
						}else{
							$("#mail_tips").html("可以使用");
							$("#loginbutton").attr("disabled",false);
						}
        			}
    			});
    		}
    	}
	})
})
