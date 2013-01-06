var buttonPath;  //设置编辑器的图标地址
var imageBrowse = "./view.php"; //设置浏览图片的路径
var linkBrowse;  //设置链接地址
var idTa;        //设置容器的ID,本软体用textarea作为容器存放HTML信息
//OTHER GLOBALS
var oWhizzy; //创建对像
var rng;  //range at current selection
var sel;  //current selection
var papa;//部分按钮，仅适合于IE
var trail = '';//DOM容器

function makeWhizzyWig(txtArea, controls){//创建一个容器存放原始文件
	//无法创建时弹出警告窗口，提示
	if ((navigator.userAgent.indexOf('Safari') != -1 ) || !document.getElementById || !document.designMode ) {
		alert("警告："+t("你的浏览器不支持该功能，使用编辑器要求IE 5.0或FireFox 1.0或Netscape 7.0，建议您使用最新版本"));
		return;
	}
	idTa = txtArea;
	//读取容器里的信息
	var taContent = o(idTa).defaultValue ? o(idTa).defaultValue : o(idTa).innerHTML ? o(idTa).innerHTML: '';
	//读取容器的宽度和高度
	var taWidth = o(idTa).style.width ? o(idTa).style.width : o(idTa).cols + "em";
	var tWidth = parseInt(taWidth)-11 + "px"
	var taHeight = o(idTa).style.height ? o(idTa).style.height : o(idTa).rows + "em";
	o(idTa).style.display = 'none';

	//编辑器的样式
	//  .ctrl { border:1px outset ButtonShadow; padding:5px;width:'+taWidth+'}这是旧样式
	// .btnImage {cursor:pointer; cursor:hand; margin-top:3px; border:2px outset; border-color:#FFFFFF;}

	w('<style type="text/css">body{font:normal 12px/14px 宋体,Tahoma,Arial;}.selector{margin:0 5px 6px 0;}.ctrl {padding:5px 3px 0 5px;/*border:1px #CECFCE solid;background:#F7F3EF;width:'+tWidth+';*/}.btnImage {cursor:pointer; cursor:hand;border:0px outset;} #sourceTa{color:#D1D1D1;}</style>');
	//显示编辑器的字体字号选择栏
	var sels = 'formatblock fontname fontsize';
	var sel = '格式化 字体名 字体大小';
	//显示按钮栏目 如果要显示源码，则在后面输入：html
	var buts = ' bold italic underline | left center right | number bullet indent outdent | undo redo  | color hilite rule | link image table | clean';
	var but = ' 粗体 斜体 下划线 | 居左 居中 居右 | 数字 符号 缩进 取消缩进 | 撤消 恢复  | 字体色 背景色 水平线 | 加入链接 插入图片 插入表格 | 清除代码';
	//显示按钮下的子菜单
	var tbuts = ' tstart add_row_above add_row_below delete_row | add_column_before add_column_after delete_column | table_in_cell';
	var tbut = ' tstart 在上方插入行 在下面插入行 删除此行 | 在前面插入列 在后面插入列 删除此列 | 在此处插入表格';
	//如果需要表格控制结束信息，请在下面写入标识符（注，我也不知道是什么，就保留为空吧）
	var t_end = '';
	buts += tbuts;
	but += tbut;
	controls = controls.toLowerCase();
	if (!controls || controls == "all") {controls = sels +' newline '+ buts +' source';control = sel +' newline '+ but +' source';}
	else {controls += tbuts;control += tbut;}
	w('<div id="CONTROLS" class="ctrl">');
	gizmos = controls.split(' ');
	gizmo = control.split(' ');
	for (var i = 0; i < gizmos.length; i++) {
		//显示按钮信息
		if (gizmos[i]){
			if (gizmos[i] == 'tstart') {
				w('<div id="TABLE_CONTROLS" style="display:none" unselectable="on">');
				t_end = '</div>';
			} else if (gizmos[i] == '|') {
				w(' ');
			} else if (gizmos[i] == 'newline') {
				w(' ');
			} else if (sels.indexOf(gizmos[i]) != -1) {
				makeSelect(gizmos[i]);
			} else if (buts.indexOf(gizmos[i]) != -1) {
				makeButton(gizmos[i],gizmo[i]);
			}
		}
	}

	w(t_end) //表格控制结束
	//加入链接信息
	w(fGo('LINK'));
	if (linkBrowse) w('<input type="button" onclick=doWin("'+linkBrowse+'"); value="'+t("查看")+'"> ');
	w(t('链接网址')+': <input type="text" id="lf_url" style="width:200px" value="http://"> <input type="checkbox" id="lf_new">'+t("在新窗口打开")+fNo(t("确定"),"insertLink()")+'</div>');
	//加入图片信息
	w(fGo('IMAGE'));
	w(t('图片')+': <input type="text" id="if_url" style="width:183px" value="http://"><input id="if_alt" type="hidden"> '+t("图片位置")+':<select id="if_side"><option value="none">'+t("默认")+'</option><option value="left">'+t("居左")+'</option><option value="right">'+t("居右")+'</option></select> <input type="hidden" id="if_border" value="none"><input type="hidden" id="if_margin" value="0">'+fNo(t("插入"),"insertImage()"));
	if (imageBrowse) w(' ');
	w('</div>');
	//插入表格
	w(fGo('TABLE')+t("行")+':<input type="text" id="tf_rows" style="width:20px" value="1"><input type="hidden" id="tf_head" value="0" name="tf_head">'+t("列")+':<input type="text" id="tf_cols" size="2" value="3"> '+t("表格边框")+':<input type="text" id="tf_border" size="2" value="1"> '+fNo(t("插入表格"),"makeTable()")+'</div>');
	//插入颜色代码
	w(fGo('COLOR')+'<input type="hidden" id="cf_cmd"><div style="background:#000000;padding:1px;height:20px;width:20px;float:left"><div id="cPrvw" style="background-color:red; height:20; width:20"></div></div> <input type=text id="cf_color" value="red" size=17 onpaste=vC(value) onblur=vC(value)>&nbsp;<button type="button" onmouseover=vC() onclick=sC()>'+t("确定")+'</button>&nbsp;<button type="button" onclick="hideDialogs();">'+t("取消")+' </button><div style="float:left;z-index:1"><table border=0 cellspacing=1 cellpadding=0 width=100% bgcolor="#000000">');
	//色谱表
	var wC = new Array("00","33","66","99","CC","FF");
	for (i=0; i<wC.length; i++){
		w("<tr>");
		for (j=0; j<wC.length; j++){
			for (k=0; k<wC.length; k++){
				var clr = wC[i]+wC[j]+wC[k];
				w('<td style="background:#'+clr+';height:12px;width:12px" onmouseover=vC("#'+clr+'") onclick=sC("#'+clr+'")></td>');
			}
		}
		w('</tr>');
	}
	w('</table></div></div>');
	w('</div>');
	//编辑器使用iframe作为容器
	w('<iframe style="border:0;width:'+taWidth+';height:'+taHeight+'" id="whizzyWig"></iframe><div style="height:3px"></div>');

	var startHTML = "<html>\n";
	startHTML += "<head>\n";
	startHTML += "</head>\n";
	startHTML += "<body style='background-color:#FFFFFF;font:normal 12px 宋体,Tahoma,Arial;'>\n";
	startHTML += taContent;
	startHTML += "</body>\n";
	startHTML += "</html>";
	//判断不同浏览器使用不同的容器功能
	if (document.frames) {
		oWhizzy = frames['whizzyWig'];
	} else {
		oWhizzy = o("whizzyWig").contentWindow;
		try {
			o("whizzyWig").contentDocument.designMode = "on";
		} catch (e) {
			setTimeout('o("whizzyWig").contentDocument.designMode = "on";', 100);
		}
		oWhizzy.addEventListener("keypress", kb_handler, true);
	}
	oWhizzy.document.open();
	oWhizzy.document.write(startHTML);
	oWhizzy.document.close();
	if (document.all) oWhizzy.document.designMode = "On";
	oWhizzy.focus();
	whereAmI();
}

//创建按钮
function makeButton(button,btn){
	var ucBut = btn.substring(0,1).toUpperCase();
	ucBut += btn.substring(1);
	ucBut = ucBut.replace(/_/g,' ');
	if (!buttonPath || buttonPath == "textbuttons" || buttonPath == "" ) {
		var butHTML = '<input type="button"  unselectable="On" value="'+t(ucBut)+'" onClick=makeSo("'+button+'")>';
	} else {
		var butHTML = '<img class="btnImage" src="'+buttonPath+button+'.gif"  onMouseDown="selDown(this)" onMouseUp="selUp(this)" alt="'+t(ucBut)+'" title="'+t(ucBut)+'" onClick=makeSo("'+button+'")>';
	}
	w(butHTML);
}

function fGo(id){ return '<div id="'+id+'_FORM" style="display:none"><hr> '; }

//关于取消按钮定义
function fNo(txt,go){
 return ' <input type="button" onclick="'+go+'" value="'+txt+'"> <input type="button" onclick="hideDialogs();" value='+t("取消")+'>';
}

//关于选择框的设定
function makeSelect(select){
	var bH ="insHTML<span style=font-size:";
	var eH = ">";
	if (select == 'formatblock') {
		var h = "标题";
		var values = ["<p>", "<p>", "<h1>", "<h2>", "<h3>", "<h4>", "<h5>", "<h6>", "<address>", "insHTML<big>",  "insHTML<small>", "<pre>"];
		var options = [t("段落样式"), t("段落"), t(h)+"一", t(h)+"二", t(h)+"三", t(h)+"四", t(h)+"五", t(h)+"六", t("地址"), t("大字"), t("小字"), t("预览效果")];
	} else if (select == 'fontname') {
		var values = ["宋体,Tahoma,Arial","宋体,Tahoma,Arial","黑体,Arial Black,Impact","Courier New, Courier, mono","Webdings"];
		var options = [t("字体"), "宋体","黑体","Courier New","Webdings"];
	} else if (select == 'fontsize') {
		var values = ["3","1","2","3","4","5","6","7"];
		var options = [t("字号"),"8px","10px","12px","14px","16px","18px","20px"];
	} else { return }
	w('<select class="selector" id="' + select + '" onchange="doSelect(this.id);">');
	for (var i = 0; i < values.length; i++) {
		w('<option value="' + values[i] + '">' + options[i] + '</option>');
	}
	w('</select>');
}

//格式化文字信息及效果
function makeSo(command, option) {
	whereAmI();
	hideDialogs();
	if (!document.all) oWhizzy.document.execCommand('useCSS',false, true);
	if ("leftrightcenterjustify".indexOf(command) !=-1) {
		command = "justify" + command;
	} else if (command == "number") {
		command = "insertorderedlist";
	} else if (command == "bullet") {
		command = "insertunorderedlist";
	} else if (command == "rule") {
		command = "inserthorizontalrule";
	} else if ((command == "clean") && (sel != '')) {
		command = "removeformat";
	}
	switch (command) {
		case "color": o('cf_cmd').value="forecolor"; if (textSel()) o('COLOR_FORM').style.display = 'block'; break;
		case "hilite" : o('cf_cmd').value="backcolor"; if (textSel()) o('COLOR_FORM').style.display = 'block'; break;
		case "image" : o('IMAGE_FORM').style.display = 'block'; break;
		case "link" : if (textSel()) o('LINK_FORM').style.display = 'block'; break;
		case "table" : doTable(); break;
		case "delete_row" : doRow('delete','0'); break;
		case "add_row_above" : doRow('add','0'); break;
		case "add_row_below" : doRow('add','1'); break;
		case "delete_column" : doCol('delete','0'); break;
		case "add_column_before" : doCol('add','0'); break;
		case "add_column_after" : doCol('add','1'); break;
		case "table_in_cell" : hideDialogs(); o('TABLE_FORM').style.display = 'block'; break;
		case "clean" : cleanUp(); break;
		default: oWhizzy.document.execCommand(command, false, option);
	}
	oWhizzy.focus;
}

//对选择框输入的信息进行设置筛选
function doSelect(selectname) {
	whereAmI();
	var idx = o(selectname).selectedIndex;
	var selected = o(selectname).options[idx].value;
	if ((selected.indexOf('insHTML') === 0)) {
		if (textSel()) insHTML(selected.replace(/insHTML/,''));
	} else {
		var cmd = selectname;
		oWhizzy.document.execCommand(cmd, false, selected);
	}
	o(selectname).selectedIndex = 0;
	oWhizzy.focus();
}

//字体颜色读取
function vC(colour) {
	if (!colour) colour = o('cf_color').value;
	o('cPrvw').style.backgroundColor = colour;
	o('cf_color').value = colour;
}
//背景颜色设置
function sC(color) {
	hideDialogs();
	var cmd = o('cf_cmd').value;
	if ((cmd == "backcolor") && (!document.all)) cmd = "hilitecolor";
	if  (!color) color = o('cf_color').value;
	if (document.selection) rng.select();
	oWhizzy.document.execCommand(cmd, false, color);
	oWhizzy.focus();
}
//插入链接信息
function insertLink(URL) {
	hideDialogs();
	if (!URL) URL = o("lf_url").value;
	cmd = URL ? "createlink" : "unlink";
	if (document.selection) rng.select();
	if (URL && o("lf_new").checked) {
		insHTML('<a href="'+URL+'" target="_blank">');
	} else {
		oWhizzy.document.execCommand(cmd, false, URL);
	}
	oWhizzy.focus();
}
//插入图片信息
function insertImage(URL, side, border, margin, alt) {
	hideDialogs();
	if (!URL) URL = o("if_url").value;
	if (URL) {
		if (!alt) alt = o("if_alt").value ? o("if_alt").value: URL.replace(/.*\/(.+)\..*/,"$1");
		img = '<img alt="' + alt + '" src="' + URL +'" ';
		if (!side) side = o("if_side").value;
		if ((side == "left") || (side == "right")) img += 'align="' + side + '"';
		if (!border)  border = o("if_border").value;
		if (border.match(/^\d+$/)) border+='px solid';
		if (!margin) margin = o("if_margin").value;
		if (margin.match(/^\d+$/)) margin+='px';
		if (border || margin) img += 'style="border:' + border + ';margin:' + margin + ';"';
		img += '/>';
		insHTML(img);
	}
	oWhizzy.focus();
}
//插入表格操作
function doTable(){
	if (trail.indexOf('TABLE') > 0){
		o('TABLE_CONTROLS').style.display = "block";
	} else {
		o('TABLE_FORM').style.display = 'block';
	}
}
//插入或删除一行
function doRow(toDo,below) {
	var paNode = papa;
	while (paNode.tagName != "TR") paNode = paNode.parentNode;
	var tRow = paNode.rowIndex;
	var tCols = paNode.cells.length;
	while (paNode.tagName != "TABLE") paNode = paNode.parentNode;
	if (toDo == "delete") {
		paNode.deleteRow(tRow);
	} else {
		var newRow = paNode.insertRow(tRow+parseInt(below));
		for (i = 0; i < tCols; i++){
			var newCell = newRow.insertCell(i);
			newCell.innerHTML = "";
		}
	}
}
//插入或删除一列
function doCol(toDo,after) {
	var paNode = papa;
	while (paNode.tagName != 'TD') paNode = paNode.parentNode;
	var tCol = paNode.cellIndex;
	while (paNode.tagName != "TABLE") paNode = paNode.parentNode;
	var tRows = paNode.rows.length;
	for (i = 0; i < tRows; i++){
		if (toDo == "delete") {
			paNode.rows[i].deleteCell(tCol);
		} else {
			var newCell = paNode.rows[i].insertCell(tCol+parseInt(after));
			newCell.innerHTML = "#";
		}
	}
}
//创建表格
function makeTable() {
	hideDialogs();
	var rows = o('tf_rows').value;
	var cols = o('tf_cols').value;
	var border = o('tf_border').value;
	var head = o('tf_head').value;
	if ((rows > 0) && (cols > 0)) {
		var table = '<table border="' + border + '">';
		for (var i=1; i <= rows; i++) {
			table = table + "<tr>";
			for (var j=1; j <= cols; j++) {
				if (i==1) {
					if (head=="1") {
						table += "<th>Title"+j+"</th>";
					} else {
						table += "<td>"+j+"</td>";
					}
				} else if (j==1) {
					table += "<td>"+i+"</td>";
				} else {
					table += "<td>#</td>";
				}
			}
			table += "</tr>";
		}
		table += " </table>";
		insHTML(table);
	}
}
//弹出窗口设置
function doWin(URL) {
	window.open(URL,'popWhizz','toolbar=no,location=no,status=no,menubar=no,scrollbars=yes,resizable=no,width=800,height=480,top=0,left=0');
}

//清理HTML代码
function cleanUp(){
	whereAmI();
	var iHTML = oWhizzy.document.body.innerHTML;
	iHTML = iHTML.replace(/<\/?DIR>/gi, "");
	iHTML = iHTML.replace(/<\/?FONT[^>]*>/gi, "");
	iHTML = iHTML.replace(/<SPAN[^>]*>(<[^>]*>)*<\/SPAN>/gi, "");
	iHTML = iHTML.replace(/ class=Mso[A-Za-z]*/gi, "");
	iHTML = iHTML.replace(/ STYLE=\"[^\"]*\"/gi, "");
	iHTML = iHTML.replace(/<\/?o:[^>]*>/gi, "");
	iHTML = iHTML.replace(/<\/?st1:[^>]*>/gi, "");
	iHTML = iHTML.replace(/[憭]/g, "'");
	iHTML = iHTML.replace(/[摂]/g, '"');
	iHTML = iHTML.replace(/align=justify/gi, "");
	iHTML = iHTML.replace(/<(TABLE|TD)(.*)WIDTH[^A-Za-z>]*/gi, "<$1$2");
	iHTML = iHTML.replace(/<([^>]+)><\/\1>/gi, "");
	oWhizzy.document.body.innerHTML = iHTML;
}
//隐藏表格图像链接等操作栏设置
function hideDialogs() {
	o('LINK_FORM').style.display='none';
	o('IMAGE_FORM').style.display='none';
	o('COLOR_FORM').style.display='none';
	o('TABLE_FORM').style.display='none';
	o('TABLE_CONTROLS').style.display='none';
}
//清理代码，当点击确认添加后
function syncTextarea() {
	var b = oWhizzy.document.body;
	b.innerHTML = b.innerHTML.replace(/<([^>]+)><\/\1>/gi, "");
	o(idTa).value = (window.get_xhtml) ? get_xhtml(b) : b.innerHTML;
}
//键盘操作加粗斜体下划线
function kb_handler(evt) {
	var w = evt.target.id;
	if (evt.ctrlKey) {
		var key = String.fromCharCode(evt.charCode).toLowerCase();
		var cmd = '';
		switch (key) {
			case 'b': cmd = "bold"; break;
			case 'i': cmd = "italic"; break;
			case 'u': cmd = "underline"; break;
		}
		if (cmd) {
			makeSo(cmd, true);
			evt.preventDefault();
			evt.stopPropagation();
		}
	}
}
//手动插入HTML代码操作说明
function insHTML(html) {
	whereAmI();
	if (!html) html = prompt("请输入HTML代码", "");
	if (!html) return;
	if (document.all) {
		html = html + rng.htmlText;
		try { oWhizzy.document.selection.createRange().pasteHTML(html); }
		catch (e) { }
	} else {
		if (sel) html = html + sel;
		var fragment = oWhizzy.document.createDocumentFragment();
		var div = oWhizzy.document.createElement("div");
		div.innerHTML = html;
		while (div.firstChild) {
			fragment.appendChild(div.firstChild);
		}
		sel.removeAllRanges();
		rng.deleteContents();
		var node = rng.startContainer;
		var pos = rng.startOffset;
		switch (node.nodeType) {
			case 3: if (fragment.nodeType == 3) {
				node.insertData(pos, fragment.data);
				rng.setEnd(node, pos + fragment.length);
				rng.setStart(node, pos + fragment.length);
			} else {
				node = node.splitText(pos);
				node.parentNode.insertBefore(fragment, node);
				rng.setEnd(node, pos + fragment.length);
				rng.setStart(node, pos + fragment.length);
			}
			break;
			case 1: node = node.childNodes[pos];
			node.parentNode.insertBefore(fragment, node);
			rng.setEnd(node, pos + fragment.length);
			rng.setStart(node, pos + fragment.length);
			break;
		}
		sel.addRange(rng);
	}
	oWhizzy.focus();
}
//获得通用的代码信息
function whereAmI() {
	oWhizzy.focus();
	if (document.all) {
		sel = oWhizzy.document.selection;
		if (sel != null) {
			rng = sel.createRange();
			switch (sel.type) {
				case "Text":case "None":
					papa = rng.parentElement(); break;
				case "Control":
					papa = rng.item(0); break;
				default:
					papa = frames['whizzyWig'].document.body;
			}
			var paNode = papa;
			trail = papa.tagName + '>' +sel.type;
			while (paNode.tagName != 'BODY') {
				paNode = paNode.parentNode;
				trail = paNode.tagName + '>' + trail;
			}
			window.status = trail;
		}
		sel = rng.text;
	} else {
		sel = oWhizzy.getSelection();
		if (sel != null) rng = sel.getRangeAt(sel.rangeCount - 1).cloneRange();
	}
}
//判断链接信息是否存在
function textSel() {
	if (sel != "") return true;
	else {alert(t("请选择要设置的文本信息")); return false;}
}
//获得环境信息ID号
function o(objectName) { return document.getElementById(objectName); }
//显示效果
function w(string) { return document.write(string); }
//翻译
function t(key) {return (window.language && language[key]) ? language[key] :  key;}
//鼠标操作
function selDown(ctrl) {ctrl.style.borderStyle = 'inset';}
function selUp(ctrl) {ctrl.style.borderStyle = 'outset';}