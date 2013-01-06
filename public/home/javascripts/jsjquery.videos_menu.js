function toggleSlie(index){
	$("#videos"+index+" .watch-more").toggle();
	$("#videos"+index+" ul").toggle();
	if($("#videos"+index+" .watch-more").css("display")=="none"){
		$("#videos"+index+" .title a").html($("#videos"+index+" .title a").html().replace("收起","展开"));
		$("#videos"+index+" .title a").removeClass("sh-block").addClass("sd-block");
	}else{
		$("#videos"+index+" .title a").html($("#videos"+index+" .title a").html().replace("展开","收起"));
		$("#videos"+index+" .title a").removeClass("sd-block").addClass("sh-block");
	}
};
function scrollTo(index){
	var top=0;
	switch(index){
		case 0:top=$("#videos0").offset().top-70;
		break;
		case 1:top=$("#videos2").offset().top-70;
		break;
		case 2:top=$("#videos4").offset().top-70;
		break;
		default:top=$("#videos0").offset().top-70;
		break;
	};
	$("html,body").stop().animate({scrollTop: top},1);
};
function turnTo(q){
	var page_len = $(".page ul li").length - 1;
	var a_index = $(".page ul li[class=selected]").index();
	if (a_index != 1 && q == 0){
		var a_t_index = a_index - 1;
		var set_a_href = $(".page ul li a").eq(a_t_index).attr("href");
		$(".prev").attr("href", set_a_href);
		$(".prev").get(0).click();
	}
	if(a_index != page_len && q == 1){
		var a_t_index = a_index + 1;
		var set_a_href = $(".page ul li a").eq(a_t_index).attr("href");
		$(".next").attr("href", set_a_href);
		$(".next").get(0).click();
	}
}

$('.slide-list').cycle({ 
    fx:      'scrollLeft',
    speed: 1000,
	timeout: 5000,
	pause: 1,
    pager:  '#dolist',
    next: '#aRight',
	prev: '#aLeft'
 });   