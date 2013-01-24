$(".expand a").click(function(){
	var msg = new message($(this));
	msg.click();
})


var message = function(object){
	this.object = object;
	this.informlist = object.parent().parent().find(".informlist");
}

message.prototype.click = function(){
	if(parseInt(this.informlist.attr("get-data"))){
		if(this.informlist.attr("action-type") == "fold"){
			this.setUnFold();
		}else{
			this.setFold();
		}
	}else{
		this.fetch();
	}
}

message.prototype.setFold = function(){
	var hiddenMsg = this.informlist.find(".hidden");
	hiddenMsg.slideUp();
	this.informlist.attr("action-type","fold");
	this.object.find("span").text("查看余下"+hiddenMsg.length+"条消息");
}

message.prototype.setUnFold = function(){
	this.informlist.find(".hidden").slideDown();
	this.informlist.attr("action-type","unfold");
	this.object.find("span").text("收起");
}

message.prototype.fetch = function(){
	var msgType = this.object.attr("msg-type");
	var $this = this;

	function format(addtime){
		var date=new Date(addtime*1000);//时间戳要乘1000
    	var month=(date.getMonth()+1<10)?('0'+(date.getMonth()+1)):(date.getMonth()+1); 
    	var day=(date.getDate()<10)?('0'+date.getDate()):(date.getDate());
    	var hour=(date.getHours()<10)?('0'+date.getHours()):(date.getHours());  
   		var minute=(date.getMinutes()<10)?('0'+date.getMinutes()):(date.getMinutes());
    	return (month+'-'+day+' '+hour+':'+minute);
	}

	jQuery.ajax({
		cache: false,
       	type: "GET",
        url: "/msglist/"+msgType,
       	dataType: "json",
       		error: function(){
       	},
       	success:function(data){
       		for(var idx in data){
       			$this.informlist.append('<li class="hidden"><p class="informtxt">'+data[idx].content+'<time>('+format(data[idx].create_time)+')</time></p></li>');
       		}
       		$this.informlist.attr("get-data",1);
   			$this.setUnFold();
       	}
	});
}

