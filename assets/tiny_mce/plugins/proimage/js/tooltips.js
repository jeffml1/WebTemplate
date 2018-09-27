/***********************************************
* Cool DHTML tooltip script II- © Dynamic Drive DHTML code library (www.dynamicdrive.com)
* This notice MUST stay intact for legal use
* Visit Dynamic Drive at http://www.dynamicdrive.com/ for full source code
* Modified by Ian Simpson of ProSouth Solutions to automate tip generation - 07/12/2007
* Modified by Paige Saunders of ProSouth Solutions to work with items of a certain class - 05/05/2007
* Modified by Paige Saunders of ProSouth Solutions to totally automate tip generation by reading html 
* and also make non-javascript friendly - 15/05/2007
* Modified by Paige Saunders of ProSouth Solutions to work with Prosite and dynamic styles - 22/07/2007
***********************************************/

var tip_offsetfromcursorX=30 //Customize x offset of tooltip
var tip_offsetfromcursorY=-10 //Customize y offset of tooltip
var tips
var tip_offsetdivfrompointerX=-50 //Customize x offset of tooltip DIV relative to pointer image
var tip_offsetdivfrompointerY=0 //Customize y offset of tooltip DIV relative to pointer image. Tip: Set it to (height_of_pointer_image-1).

document.write('<div id="dhtmltooltip"></div>') //write out tooltip DIV
document.write('<img id="dhtmlpointer" />') //write out pointer image

var tip_ie=document.all
var tip_ns6=document.getElementById && !document.all
var	enabletip=false
if (tip_ie||tip_ns6){
	var tip_tipobj=document.all? document.all["dhtmltooltip"] : document.getElementById? document.getElementById("dhtmltooltip") : "";
	var tip_pointerobj=document.all? document.all["dhtmlpointer"] : document.getElementById? document.getElementById("dhtmlpointer") : "";
}	

function ietruebody(){
return (document.compatMode && document.compatMode!="BackCompat")? document.documentElement : document.body
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

function genTips(pointer,tipclass) {
	var pointer = (pointer==null)?"/assets/tooltips/tiparrow.png":pointer;
	var tipclass = (tipclass==null)?"standard":tipclass;
	document.getElementById('dhtmlpointer').src = pointer;
	document.getElementById('dhtmltooltip').className = tipclass;
	document.getElementById('dhtmlpointer').className = tipclass;

	var labels = getElementsByStyleClass('tip');
	tips = new Array(labels.length);
	for (var i=0; i<labels.length; i++) {
		if(labels[i].firstChild.nodeName=='#text'){
			tips[labels[i].attributes['for'].value] = labels[i].firstChild.nodeValue;
			labels[i].firstChild.nodeValue="";
		} else if(labels[i].lastChild.nodeName=='#text'){
			tips[labels[i].attributes['for'].value] = labels[i].lastChild.nodeValue;
			labels[i].lastChild.nodeValue="";
		}
		var applied = document.getElementById(labels[i].attributes['for'].value);
		applied.onmouseout=function() {hideddrivetip()}
		applied.onmouseover=function() {
			showMyTip(this)
		}
		//This makes the box go away when the click on the box.
		applied.onclick = function() {hideddrivetip() }
	}
}

function showMyTip(sender) {
	ddrivetip(tips[sender.id],300);
}

function ddrivetip(thetext, thewidth, thecolor){
if ((tip_ns6||tip_ie) && thetext.length>0){
if (typeof thewidth!="undefined") tip_tipobj.style.width=thewidth+"px"
if (typeof thecolor!="undefined" && thecolor!="") tip_tipobj.style.backgroundColor=thecolor
tip_tipobj.innerHTML=thetext
enabletip=true
return false
}
}

function positiontip(e){
if (enabletip){
var nondefaultpos=false
var tip_curX=(tip_ns6)?e.pageX : event.clientX+ietruebody().scrollLeft;
var tip_curY=(tip_ns6)?e.pageY : event.clientY+ietruebody().scrollTop;
//Find out how close the mouse is to the corner of the window
var tip_winwidth=tip_ie&&!window.opera? ietruebody().clientWidth : window.innerWidth-20
var tip_winheight=tip_ie&&!window.opera? ietruebody().clientHeight : window.innerHeight-20

var tip_rightedge=tip_ie&&!window.opera? tip_winwidth-event.clientX-tip_offsetfromcursorX : tip_winwidth-e.clientX-tip_offsetfromcursorX
var tip_bottomedge=tip_ie&&!window.opera? tip_winheight-event.clientY-tip_offsetfromcursorY : tip_winheight-e.clientY-tip_offsetfromcursorY

var tip_leftedge=(tip_offsetfromcursorX<0)? tip_offsetfromcursorX*(-1) : -1000

//if the horizontal distance isn't enough to accomodate the width of the context menu
if (tip_rightedge<tip_tipobj.offsetWidth){
//move the horizontal position of the menu to the left by it's width
tip_tipobj.style.left=tip_curX-tip_tipobj.offsetWidth+"px"
nondefaultpos=true
}
else if (tip_curX<tip_leftedge)
tip_tipobj.style.left="5px"
else{
//position the horizontal position of the menu where the mouse is positioned
tip_tipobj.style.left=tip_curX+tip_offsetfromcursorX-tip_offsetdivfrompointerX+"px"
tip_pointerobj.style.left=tip_curX+tip_offsetfromcursorX+"px"
}

//same concept with the vertical position
if (tip_bottomedge<tip_tipobj.offsetHeight){
tip_tipobj.style.top=tip_curY-tip_tipobj.offsetHeight-tip_offsetfromcursorY+"px"
nondefaultpos=true
}
else{
tip_tipobj.style.top=tip_curY+tip_offsetfromcursorY+tip_offsetdivfrompointerY+"px"
tip_pointerobj.style.top=tip_curY+tip_offsetfromcursorY+"px"
}
tip_tipobj.style.visibility="visible"
if (!nondefaultpos)
tip_pointerobj.style.visibility="visible"
else
tip_pointerobj.style.visibility="hidden"
}
}

function hideddrivetip(){
if (tip_ns6||tip_ie){
enabletip=false
tip_tipobj.style.visibility="hidden"
tip_pointerobj.style.visibility="hidden"
tip_tipobj.style.left="-1000px"
tip_tipobj.style.backgroundColor=''
tip_tipobj.style.width=''
}
}

document.onmousemove=positiontip
	
