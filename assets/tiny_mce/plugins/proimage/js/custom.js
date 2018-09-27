// JavaScript Document
var xmlHttp


function GetXmlHttpObject(handler) { 
	var objXMLHttp=null
	if (window.XMLHttpRequest) {
		objXMLHttp=new XMLHttpRequest()
	}
	else if (window.ActiveXObject) {
		objXMLHttp=new ActiveXObject("Microsoft.XMLHTTP")
	}
	return objXMLHttp
}

function deleteCategory(cat,name){
	if(IsNumeric(cat)){
		var answer = confirm("Are you sure you want to delete " + name + " and all the images contained within?")
		if(answer){
			/*url = 'getpage.php?cat=' + cat + '&delcat=1';
			xmlHttp=GetXmlHttpObject()
			xmlHttp.onreadystatechange=pageReady
			xmlHttp.open("GET",url,true)*/
			window.navigate("image.php");		
			//xmlHttp.send(null)
		} else {
			getPage(cat);	
		}
	}
}


function hide(elementid){
	var element = document.getElementById(elementid);
	if(element.style.display == "block"){
		element.style.display = "none"
	} else {
		element.style.display = "block"
	}
}

function getPage(cat,del) {
	if(IsNumeric(del)){
		url = 'getpage.php?cat=' + cat + '&del=' + del;		
	} else {
		url = 'getpage.php?cat=' + cat;
	}
	xmlHttp=GetXmlHttpObject()
	xmlHttp.onreadystatechange=pageReady
	xmlHttp.open("GET",url,true)
	xmlHttp.send(null)
	viewImage()
	document.getElementById('library').innerHTML = '<div style="position:absolute; left:0; top:0; padding:275px 300px; z-index:5000; background-color:#000; -moz-opacity: 0.6; opacity:.60; filter: alpha(opacity=60);"><div style="background:#fff; border:1px solid #000; width:200px; height:30px; padding:10px 0; text-align:center;"><img src="interface/images/progress.gif"><br/>Now loading. Please wait...</div></div>'
}

function pageReady() {
	if (xmlHttp.readyState==4 || xmlHttp.readyState=="complete") { 
		var ret = xmlHttp.responseText
		document.getElementById('library').innerHTML = ret
		viewImage()
	} 
}


function IsNumeric(variable){
   var ValidChars = "0123456789.";
   var IsNumber=true;
   var Char;
   if(IsDefined(variable)){
	   for (i = 0; i < variable.length && IsNumber == true; i++){ 
			Char = variable.charAt(i); 
			if (ValidChars.indexOf(Char) == -1){
				IsNumber = false;
			}
		} 
   } else {
		IsNumber = false;   
   }
	return IsNumber;
}

function IsDefined(variable){
	var undefined;
	return (variable === undefined)? false: true;
}

function addImage(num) {
	document.getElementById('library').style.display = 'none'
	document.getElementById('uploader').style.display = 'block'
	document.getElementById('cat_num').value = num
}
function viewImage() {
	document.getElementById('library').style.display = 'block'
	document.getElementById('uploader').style.display = 'none'
}

function checkCat() {
	var obj = document.getElementById('cat_num')
	if (obj.options[obj.selectedIndex].value==0) {
		document.getElementById('cat_name').style.display = 'inline'
	} else {
		document.getElementById('cat_name').style.display = 'none'
	}
}

function defineDimensions(on){
	var d = document.getElementById('i_div');
	d.style.display = on?'':'none'
}


function insertimg(num,alt,url,width,height) {
	var ed = tinyMCEPopup.editor
	var url = location.host;
	if(width && height)
		ed.execCommand('mceInsertContent', false, '<img alt="'+alt+'" src="http://' + url + '/upload/' + num + '"  width="' + width + '" height="' + height + '"/>', {skip_undo : 1});
	else
		ed.execCommand('mceInsertContent', false, '<img alt="'+alt+'" src="http://' + url + '/upload/' + num + '" />', {skip_undo : 1});
	ed.undoManager.add();

	tinyMCEPopup.close();
}

function imageName(name) {
	name = name.replace(/\\/g,'/').split('/')
	name = name[name.length-1]
	name = name.substring(0,name.length-4)
	if(document.getElementById('img_name').value=='') document.getElementById('img_name').value = name
}