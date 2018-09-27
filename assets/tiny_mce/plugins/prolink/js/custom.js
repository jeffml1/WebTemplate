// JavaScript Document
var inst = tinyMCEPopup.editor;
var link_value = '';



function showLink() {
	var value = getSelectedText();
	document.getElementById('linktext').value = value;
	checkText(document.getElementById('linktext'));
}

function setLinkValue(val){
	var text = '<a href="/'+val+'">'+getSelectedText()+'</a>';
	insertText(text);
}

function checkText(textbox){
	if(textbox.value!='' && link_value!=''){
		document.getElementById('linkbutton').disabled=false;
	} else {
		document.getElementById('linkbutton').disabled=true;
	}	
}

function deleteLink(){
	inst.execCommand('mceInsertContent', false, inst.selection.getNode().innerHTML , {skip_undo : 1});
	inst.undoManager.add();
	tinyMCEPopup.close();
}

function insertLink(page){
	var tree = page.getElementsByTagName('UL');
	var pages = tree[0].getElementsByTagName('INPUT');
	//var checkbox = ;
	
	
	var text = '<a href="'+link_value+'">'+document.getElementById('linktext').value+'</a>';
	insertText(text);

}

function insertText(text){
	inst.execCommand('mceInsertContent', false,  text , {skip_undo : 1});
	inst.undoManager.add();
	tinyMCEPopup.close();
}

function getElementsByStyleClass (className) {
  var all = document.all ? document.all :
  document.getElementsByTagName('*');
  var elements = new Array();
  for (var e = 0; e < all.length; e++)
  if (all[e].className == className)
  elements[elements.length] = all[e];
  return elements;
}

function insertFile1(filename){
//	var filename = object.id.substring(7,object.id.length);
	filelink = '<a href="/upload/files/'+filename+'">'+getSelectedText()+'</a>';
	insertText(filelink);
}

/*function deleteFile(filename){
	var ajax = new sack();
	ajax.setVar("filename", filename); // recomended method of setting data to be parsed.
	ajax.requestFile = "ajax.php";
	ajax.method = 'post';
	ajax.onCompletion = function(){
		if(ajax.response=='OK'){
			document.getElementById("insert_"+filename).parentNode.parentNode.style.display='none';
		} else {
			alert('Deletion Failed');	
		}
	}
	ajax.runAJAX();
}*/

function getSelectedText(){
	var value = inst.selection.getContent();
	if(inst.selection.getNode().tagName=="A"){
		value = inst.selection.getNode().innerHTML;
		inst.selection.select(inst.selection.getNode());
	}
	return value;
}

function uploadFile(object){

}