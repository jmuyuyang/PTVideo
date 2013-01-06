function menu_tree(meval)
{
  var left_n=eval(meval);
  if (left_n.style.display=="none")
  { eval(meval+".style.display='';"); }
  else
  { eval(meval+".style.display='none';"); }
}

 function setcollect(){
    var setcollect=document.getElementById('intocollect').style.display;
	if (setcollect=='none') {
	  document.getElementById('intocollect').style.display='block';
	  return false;
   }
    else {
	  var collectupload=document.getElementById('videoinput');
      collectupload.submit();
	  return false;
	}
 }
  function shotselect(){
	   //视频截图选择
	  var shottype=document.getElementById('videomplayer');
	  var type=shottype.options[shottype.selectedIndex].value;
	  if(type=='2'){ document.getElementById('Screenshot').innerHTML='<input type="file" name="screenfile" />&nbsp&nbsp&nbsp由于系统限制，图片格式必须为jpg格式';}
	  else { document.getElementById('Screenshot').innerHTML='截屏秒数:&nbsp&nbsp&nbsp<input type="text" id="mframe" name="mframe">';}
  }

  function addchd(cid){
	    var classid='addchdclass_'+cid;
	    document.getElementById(classid).style.display="";
		return false;
  }

  function setVideoType(selectid){	  
	  var select=selectid.options[selectid.selectedIndex].value;
	  if(select == 0){
	  	document.getElementById('file_type').disabled = true;
	  	document.getElementById("videofile").innerHTML = '<input type="text" name="file" size=70 maxlengh=180>';
	  }else{
	  	document.getElementById('file_type').disabled = false;
	  }
	  return false;
  }

  function setFileType(selectid){
  	var select=selectid.options[selectid.selectedIndex].value;
  	if(select=='file') document.getElementById('videofile').innerHTML='<input type="file" name="file">';
	else if(select=='text') document.getElementById("videofile").innerHTML = '<input type="text" name="file" size=70 maxlengh=180>';
  }

  function videocheck(){
      //alert(typeof(document.getElementById('videoselect')))
	  if(typeof(document.getElementById('videoselect'))=='object'){
		   var videoselect=document.getElementById('videoselect');
		   if(videoselect.options[videoselect.selectedIndex].value==''){
			   alert('请选择分类');
			   return false;
		   }
	  }
	  if(typeof(document.getElementById('title'))=='object'){
		if(document.getElementById('imdb').value==''){
		   if(document.getElementById('title').value==''){
			   alert('请输入影片名称');
			   return false;
		   }
		}
	  }
	return true;
  }

function getVideoInfo(doc){
	var imdb=document.getElementById('imdb').value;
	if(imdb!=''){
		var url = '/admin/grabdouban?imdb='+imdb;
		var xmlhttp = createXMLHTTPRequest();
		xmlhttp.onreadystatechange=function(){
			if(xmlhttp.readyState !=4) {
				doc.value='信息获取中';
			}
			if(xmlhttp.readyState ==4){
				doc.value='获取信息';
				var response = xmlhttp.responseText;
				setVideoInfo(response);
			}
		};
		xmlhttp.open("GET",url,true);
		xmlhttp.setRequestHeader("X-Requested-With","XMLHttpRequest"); 
		xmlhttp.send(null);
	}
}

function setVideoInfo(response){
	var responseText = eval("("+response+")");
	if(responseText.error == 1) alert('无法获取豆瓣网中相关视频信息,请重新输入');
	else{
		alert(responseText.content);
		document.getElementById('edited').innerHTML=responseText.content;
		document.getElementById('title').value=responseText.title;
	}
}

function createXMLHTTPRequest(){
	var xmlHttp=null;
  	try{
    // Firefox, Opera 8.0+, Safari
    	xmlHttp=new XMLHttpRequest();
    }
  	catch (e){
    // Internet Explorer
   		try{
      		xmlHttp=new ActiveXObject("Msxml2.XMLHTTP");
      	}
    	catch (e){
     	 xmlHttp=new ActiveXObject("Microsoft.XMLHTTP");
      	}
    }
  	return xmlHttp;
}
