// JavaScript Document

function makeObjectChild(num) {
	targ = document.getElementById('pagerow_' + num)
	var prev = targ.previousSibling
	if (prev) {
		if (prev.lastChild.tagName != 'UL') {
			var newUL = document.createElement('UL')
			prev.appendChild(newUL)
		}
		prev.lastChild.appendChild(targ)
		targ.firstChild.firstChild.childNodes[3].firstChild.style.display = ''
	}
	setLevels(document.getElementById('editList'), 0)
}

function makeObjectParent(num) {
	targ = document.getElementById('pagerow_' + num)
	var next = targ.parentNode.parentNode
	if (next.nextSibling) {
		next = next.nextSibling
		next.parentNode.insertBefore(targ, next)
	} else {
		next.parentNode.appendChild(targ)
	}
	setLevels(document.getElementById('editList'), 0)
}

function setLevels(list, level) {
	var item = list.firstChild;
	var children = 0
	while(item){
		if (item.tagName && item.tagName.toLowerCase() == 'li') {
			children++
			var itemsChild = item.firstChild;
			//alert(itemsChild.id.substr(0,6))
			while(itemsChild){
				if (itemsChild.tagName && itemsChild.tagName.toLowerCase() == 'input' && itemsChild.id.substr(0,6) == 'level_') {
					itemsChild.value = level;
				}
				if (itemsChild.tagName && itemsChild.tagName.toLowerCase() == 'ul') {
					setLevels(itemsChild, level+1);
				}
				itemsChild = itemsChild.nextSibling;
			}
		}
		item = item.nextSibling;
	}
	if (children == 0) {
		list.parentNode.removeChild(list)
	}
}


function moveObjectUp(targ,colour) {
	var prev = targ.previousSibling
	if (prev) targ.parentNode.insertBefore(targ,prev)
	if (colour) recolour(targ.parentNode)
}

function moveObjectDown(targ,colour) {
	var next = targ.nextSibling
	if (next) targ.parentNode.insertBefore(targ,next.nextSibling)
	if (colour) recolour(targ.parentNode)
}

function recolour(obj) {
	var row = 1
	obj = obj.firstChild
	while (obj) {
		row = 1-row
		obj.className = obj.className.replace('row1','').replace('row0','') + ' row' + row
		obj = obj.nextSibling
	}
}

function insertAfter(newObject,target) {
	if (target.nextSibling) {
		target.parentNode.insertBefore(newObject,target.nextSibling)
	} else {
		target.parentNode.appendChild(newObject)
	}
}


/*function deletePage(caller, child) {
	if (child == 1) {
		alert("Please delete children of this page first")
		return false
	} else if (child == 2) {
		caller.parentNode.removeChild(caller)
	} else {
		var answer = confirm("Delete this page?")
		if (answer) {
			targ = caller.lastChild
			if (targ.name == 'delete[]') targ.value = '1'
			caller.firstChild.style.display = 'none'
			caller.firstChild.nextSibling.nextSibling.firstChild.style.color = 'grey'
			caller.childNodes[8].style.display = 'inline'
		}
	}
}*/

function deletePage(num) {
	var temp = document.getElementById('pagerow_'+ num).firstChild;
	while(temp)	{
		if (temp.tagName && temp.tagName.toLowerCase() == 'ul')	{
			if(temp.firstChild != null) {
				alert("Please delete children of this page first")
				return false
			}
		}
		temp = temp.nextSibling
	}
	if (confirm("Are you sure you want to delete this page?")) {
		var targ = document.getElementById('pagerow_' + num)
		if(parseFloat(num)<0) {
			targ.parentNode.removeChild(targ)
		} else {
			targ.className='deleted'
			document.getElementById('deletepage_' + num).value='1'
		}
	}
}


function undeletePage(num) {
	document.getElementById('pagerow_' + num).className=''
	document.getElementById('deletepage_' + num).value='0'
}

var pageadd = 0
function addPage(caller,level) {
	pageadd--
	//var newUL = document.createElement('UL')
	var newLI = document.createElement('LI')
	newLI.id = 'pagerow_' + pageadd
	 var tag = '<span class="controls"><span style="float: left">' +
	 			'<a onclick="moveObjectUp(this.parentNode.parentNode.parentNode,false); return false;" href="#"><img src="/assets/images/arrowUp.gif" alt="Move Up" title="Move Up" /></a>' +
				'<a onclick="moveObjectDown(this.parentNode.parentNode.parentNode,false); return false;" href="#"><img src="/assets/images/arrowDown.gif" alt="Move Down" title="Move Down" /></a>' +
				'<a onclick="makeObjectChild(' + pageadd + '); return false;" href="#"><img src="/assets/images/arrowRight.gif" alt="Make Child" title="Make Child"/></a>' +
				'<a onclick="makeObjectParent(' + pageadd + '); return false;" href="#" class="leftarrow"><img src="/assets/images/arrowLeft.gif" alt="Make Parent" title="Make Parent"/></a>' +
				'</span><span style="float: right">' +
				'<a href="#" onclick="deletePage(' + pageadd + '); return false;\"><img src="/assets/images/redcross.gif" alt="Delete" title="Delete" /></a>' +
				'<a href="#" onclick="addPage(this.parentNode.parentNode.parentNode, document.getElementById(\'level_' + pageadd + '\').value); return false;"><img src="/assets/images/plus.gif" alt="Add Page" title="Add Page" /></a>' +
				'</span></span>' +
				'|- <input type="checkbox" name="active[' + pageadd + ']" /> <input type="text" name="page[' + pageadd + ']" id="pagename_' + pageadd + '" />' +
				'<input type="hidden" name="level[' + pageadd + ']" value="' + level + '" id="level_' + pageadd + '" />'

	newLI.innerHTML = tag
	caller.parentNode.insertBefore(newLI, caller.nextSibling)
	makeObjectChild(pageadd)
	document.getElementById('pagename_' + pageadd).focus()
}

function toInput(caller) {
	var input = caller.parentNode.nextSibling
	var span = caller.parentNode
	span.style.display = 'none';
	input.style.display = '';
	input.nextSibling.style.display = 'inline';
}

function toSpan(caller) {
	var input = caller.previousSibling
	var span = input.previousSibling
	input.style.display = 'none';
	input.nextSibling.style.display = 'none';
	span.style.display = '';
}

function loadtinyMCE(iheight,newclass) {
		tinyMCE.init({
		body_class : newclass,
		theme_advanced_containers_default_class : "TEST",
		mode : "specific_textareas",
		editor_selector : "mceEditor",
		theme : "advanced",
		skin: "cirkuit",
		theme_advanced_toolbar_location : "top",
		theme_advanced_toolbar_align : "left",
		plugins : "proimage,paste,table,prolink,youtubeIframe",
		theme_advanced_buttons1 : "bold,italic,underline,strikethrough,sub,sup,separator,justifyleft,justifycenter,justifyright,justifyfull,separator,styleselect,formatselect,forecolor,fontsizeselect,separator,bullist,numlist,separator,outdent,indent",
		theme_advanced_buttons2 : "undo,redo,separator,hr,removeformat,visualaid,separator,charmap,separator,prolink,link,unlink, separator, proimage,separator,tablecontrols,separator,code, youtubeIframe",
		theme_advanced_buttons3 : "",
		theme_advanced_toolbar_location : "top",
		theme_advanced_toolbar_align : "left",
		theme_advanced_statusbar_location : "bottom",
		theme_advanced_resizing : true,
		theme_advanced_resize_horizontal : false,
		theme_advanced_blockformats : "p,h1,h2,h3,h4,h5,dd,dt",
		width : "100%",
		height:iheight+'px',
		content_css : $('link[rel=stylesheet]').attr('href'),
		relative_urls : false
	})

}

/*function keepAlive(sid) {
	$.ajax('/assets/keepalive.php?sid=' + sid)
}*/

/*function searchRetailers(term) {
	var rows = $$('.retName')
	var haschildren = new Array()
	for(var i=0; i<rows.length; i++) {
		var disp = ''
		if(term!='') {
			var pattern = new RegExp(term,'i')
			if(!pattern.test(rows[i].innerHTML)) {
				disp = 'none'
			} else {
				haschildren[rows[i].className.match(/rr[0-9]+/)] = true
			}
		} else {
			haschildren[rows[i].className.match(/rr[0-9]+/)] = true
		}
		rows[i].parentNode.parentNode.style.display = disp
	}
	rows = $$('.headrow')
	for(var i=0; i<rows.length; i++) {
		var disp = 'none'
		if(haschildren[(rows[i].className.match(/rr[0-9]+/))]) {
			disp = ''
		}
		rows[i].style.display = disp
	}
}

function noteSave(caller, ret_num) {
	caller.form.className = 'sending'
	var url = '/retailernotes.php?notitle=1&addnote=' + ret_num + '&note=' + escape($('newnote').value)
	xmlHttp=GetXmlHttp()
	xmlHttp.onreadystatechange=function() {
		if (xmlHttp.readyState==4 || xmlHttp.readyState=="complete") {
			var resp = xmlHttp.responseText
			var d = document.createElement('DIV')
			d.innerHTML = resp
			d.className = 'note'
			$('noteArea').insertBefore(d,$('noteArea').firstChild)
			caller.form.className = ''
			$('newnote').value = ''
			$('noteBox').className = '';
		}
	}
	xmlHttp.open("GET",url,true)
	xmlHttp.send(null)

}

function changeTab(num) {
	var frames = $$('.tabframe')
	for(var i=0; i<frames.length; i++) {
		if(frames[i].id=='tabframe_' + num) {
			frames[i].style.display = ''
		} else {
			frames[i].style.display = 'none'
		}
	}
	var tabs = $$('.tab')
	for(var i=0; i<tabs.length; i++) {
		if(tabs[i].id=='tab_' + num) {
			tabs[i].className = 'tab active'
		} else {
			tabs[i].className = 'tab'
		}
	}
}*/
