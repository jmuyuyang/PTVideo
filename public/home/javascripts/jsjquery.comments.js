$("#content").bind("blur keyup",function(){
	var $parent = $(this).parents("div.commentAreaBox");
	var $maxinput = $parent.find("#maxinput");
	var len = $(this).val().length;
	len = (len>200)?200-len:len;
	$maxinput.text(len);
	if(len == 0 || len > 200){
		$parent.find(".submit .button").attr("disabled","disabled");
	}else{
		$parent.find(".submit .button").removeAttr("disabled");
	}
}).focus(function(){
	$(this).triggerHandler("blur");
});
$("ul.rating li").click(function(){
	var $parent = $(this).parents(".rating");
	var $tips = $parent.find(".tips");
	if(parseInt($tips.attr("has-score"))) {
		alert("您已经评过分了,无法再次评分");
		return;
	}
	$(this).removeClass("grey").addClass("red");
	$(this).prevAll().removeClass("grey").addClass("red");
	$(this).nextAll().removeClass("red").addClass("grey");
	var title = parseInt($(this).children().attr("title"));	
	var vid = $(".videoclass").val();
	$.ajax({ 
		cache: false,
       	type: "POST",
        url: "/vote?vid="+vid,
       	dataType: "json",
        data: { score: title },
		error: function(){alert("评分失败");},
		success: function (data) { 
			if(data.operation == 'failed'){
				alert("无法评分");
				return;
			}
			$tips.text("您给此资源的评分是："+title+"分");
			$tips.attr("has-score",1);
			var scoreAvg = parseFloat($("#score-avg").text());
			var voteNum = parseInt($("#vote-num").text());
			var newScore = new Number((scoreAvg*voteNum+title)/(voteNum+1));
			$("#vote-num").text(voteNum+1);
			$("#score-avg").text(newScore.toFixed(2));
			var cl = $(this).children().attr("class");
			$(this).parent().removeClass().addClass("rating "+cl+"star");
			$(this).blur();
		}
	});
});
$(".submit a").click(function(){
	var $parent = $(this).parents(".commentArea");
	var comments = $parent.find("#content").val();
	var vid = $(".videoclass").val();
	if(comments.length == 0 || comments.length > 200) return false;
	$.ajax({ 
		cache: false,
       	type: "POST",
        url: "/comment?vid="+vid,
       	dataType: "json",
        data: { content: comments },
		error: function(){alert("评论失败");},
		success: function (data) {
			$(".commentAreaBox #content").val("");
			$("#comments-list").prepend("<div class='comments'><div class='viewer'>"+data.author+"</div><div class='detail'><div class='content'>"+data.content+"</div><time class='publish'>"+data.add_time+"</time></div><span class='reply'>回复</span></div>");
			$(".reply-succeed").animate({top: "70px",opacity:"1"},1000)
			.animate({top:"-=40px",opacity:"0"},2000);
        }
    });
});
$(".watch-more a").click(function(){
	var cid = $(".watch-more").attr("current-id");
	var vid = $(".videoclass").val();
	function comptime(beginTime){
		var endTime = new Date().getTime(); 
  	  	var secondNum = parseInt((endTime-beginTime*1000)/1000);//计算时间戳差值    
   		if(secondNum>=0&&secondNum<60){ 
        	return secondNum+' seconds ago'; 
    	} 
    	else if (secondNum>=60&&secondNum<3600){ 
        	var nTime=parseInt(secondNum/60); 
        	return nTime+' miniutes ago'; 
    	} 
    	else if (secondNum>=3600&&secondNum<3600*24){ 
        	var nTime=parseInt(secondNum/3600); 
        	return nTime+' hours ago'; 
    	} 
    	else{ 
        	var nTime = parseInt(secondNum/86400); 
        	return nTime+' days ago'; 
    	} 
	} 

	$.ajax({
		cache:false,
		type:"GET",
		url:"/gcomment/"+vid+"?cid="+cid,
		dataType:"json",
		error: function(){alert("评论失败");},
		success:function(data){
			var preDiv = "<div class='more-comment' ></div>"
			for(var idx in data){
				$("#comments-list").append("<div class='comments'><div class='viewer'>"+data[idx].author+"</div><div class='detail'><div class='content'>"+data[idx].content+"</div><time class='publish'>"+comptime(data[idx].add_time)+"</time></div><span class='reply'>回复</span></div>");
			}
			if(data.length < 10){
				$(".watch-more").hide();
			}else{
				$(".watch-more").attr("current-id",data[data.length-1].id);
			}
		}
	});
	return false;
})
$(".reply").live("click",function(){
	var $reply_parent = $(this).parents("div.comments");
	if(!$reply_parent.children().hasClass("reply-box")){
		var reply = $(this).parents(".videoComment").find(".reply-box.hidden").clone();
		$reply_parent.append(reply).find(".reply-box").removeClass("hidden");
		var reply_author = $reply_parent.find(".viewer").html();
		$(".reply_content").val("回复@"+reply_author+": ");
	}
	$(".reply_content").bind("blur keyup",function(){
		var $parent = $(this).parents("div.reply-box");
		var $reply_maxinput = $parent.find("#reply_maxinput");
		var reply_len = $(this).val().length;
		reply_len = (reply_len>200)?200-reply_len:reply_len;
		$reply_maxinput.text(reply_len);
		if(reply_len == 0 || reply_len > 200){
			$("span.submit").attr("disabled","disabled");
		}else{
			$("span.submit").removeAttr("disabled");
		}
	}).focus(function(){
		$(this).triggerHandler("blur");
	});
	$("span.cancel").click(function(){
		var $parent = $(this).parents("div.reply-box");
		$parent.remove();
	});
	$("span.submit").click(function(){
		var $parent = $(this).parents("div.reply-box");
		var comment = $parent.find(".reply_content").val();
		var len = $parent.find(".reply_content").val().length;
		if (len > 200 || len == 0){
			return false;
		}
		var reply_id = $reply_parent.attr("replay-id");
		var vid = $(".videoclass").val();
		$.ajax({ 
			cache: false,
       		type: "POST",
        	url: "/comment?vid="+vid+"&reply="+reply_id,
       		dataType: "json",
        	data: { content: comment },
			error: function(){alert("评论失败");},
			success: function (data) {
				$("#comments-list").prepend("<div class='comments'><div class='viewer'>"+data.author+"</div><div class='detail'><div class='content'>"+data.content+"</div><time class='publish'>"+data.time+"</time></div><span class='reply'>回复</span></div>");
				$(".reply-succeed").animate({top: "70px",opacity:"1"},1000)
				.animate({top:"-=40px",opacity:"0"},2000);
        	}
    	});
		$parent.remove();
	});
});

$(".searchBtn").click(function(){
	var $parent = $(this).parents("div.searchInputBox");
	var kw = $parent.find(".searchInp").val();
	var cl = $parent.find(".searchInp").attr("cl");
	$parent.find("p").remove();
	if(kw.length==0){
		$parent.append("<p>请输入至少一个关键字！</p>");
		return false;
	}else{
		kw = encodeURI(kw);
		location.href = "/svideo/"+kw+"?cl="+cl;
	}
});

$(".search-box ul li").click(function(){
	var cl = $(this).attr("id");
	$("input.searchInp").attr("cl",cl);
	$(this).addClass("selected")
		.siblings().removeClass("selected");
	return false;
});

$(window).scroll(function(){
	if($(document).scrollTop()>0){
		$(".gotop").show();
	}else{
		$(".gotop").hide();
	}
});
