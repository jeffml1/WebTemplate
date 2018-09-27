/*
Created By: Chris Campbell
Website: http://particletree.com
Date: 2/1/2006

Inspired by the lightbox implementation found at http://www.huddletogether.com/projects/lightbox/
*/

/*-------------------------------GLOBAL VARIABLES------------------------------------*/

var detect = navigator.userAgent.toLowerCase();
var OS,browser,version,total,thestring;

/*-----------------------------------------------------------------------------------------------*/

//Browser detect script origionally created by Peter Paul Koch at http://www.quirksmode.org/

function getBrowserInfo() {
	if (checkIt('konqueror')) {
		browser = "Konqueror";
		OS = "Linux";
	}
	else if (checkIt('safari')) browser 	= "Safari"
	else if (checkIt('omniweb')) browser 	= "OmniWeb"
	else if (checkIt('opera')) browser 		= "Opera"
	else if (checkIt('webtv')) browser 		= "WebTV";
	else if (checkIt('icab')) browser 		= "iCab"
	else if (checkIt('msie')) browser 		= "Internet Explorer"
	else if (!checkIt('compatible')) {
		browser = "Netscape Navigator"
		version = detect.charAt(8);
	}
	else browser = "An unknown browser";

	if (!version) version = detect.charAt(place + thestring.length);

	if (!OS) {
		if (checkIt('linux')) OS 		= "Linux";
		else if (checkIt('x11')) OS 	= "Unix";
		else if (checkIt('mac')) OS 	= "Mac"
		else if (checkIt('win')) OS 	= "Windows"
		else OS 								= "an unknown operating system";
	}
}

function checkIt(string) {
	place = detect.indexOf(string) + 1;
	thestring = string;
	return place;
}

/*-----------------------------------------------------------------------------------------------*/

Event.observe(window, 'load', initializeLB, false);
Event.observe(window, 'load', getBrowserInfo, false);
//Event.observe(window, 'unload', Event.unloadCache, false);

var lightbox = Class.create();

var currentLB;
var Height,Width,newWidth,newHeight,wID,hID;
var resizingW = false
var resizingH = false
var lb
var step = 25
function growW() {
	if (newWidth>Width) {
		Width+=(newWidth-Width<step?newWidth-Width:step)
	} else if (Width>newWidth){
		Width-=(Width-newWidth<step?Width-newWidth:step)
	} else {
		clearInterval(wID)	
		resizingW = false
		lb.processInfo2()
	}
	$('lightbox').style.width = Width + 'px'
	$('lightbox').style.marginLeft = '-' + (Math.round(Width/2)+10) + 'px'
}
function growH() {
	if (newHeight>Height) {
		Height+=(newHeight-Height<step?newHeight-Height:step)
	} else if (Height>newHeight){
		Height-=(Height-newHeight<step?Height-newHeight:step)
	} else {
		clearInterval(hID)	
		resizingH = false
		lb.processInfo2()
	}
	$('lightbox').style.height = Height + 'px'
	$('lightbox').style.marginTop = '-' + (Math.round(Height/2)) + 'px'
}

lightbox.prototype = {

	yPos : 0,
	xPos : 0,
	newWidth : 0,
	newHeight : 0,
	response : '',
	done : false,
	ready : false,
	
	resize: function(newX, newY) {
		/* Get current browser window size */
		var myWidth;
		var myHeight;
		
		if( typeof( window.innerWidth ) == 'number' ) { 		
			//Non-IE 
			myWidth = window.innerWidth;
			myHeight = window.innerHeight; 
		} else if( document.documentElement && ( document.documentElement.clientWidth || document.documentElement.clientHeight ) ) { 
			//IE 6+ in 'standards compliant mode' 
			myWidth = document.documentElement.clientWidth; 
			myHeight = document.documentElement.clientHeight; 
		} else if( document.body && ( document.body.clientWidth || document.body.clientHeight ) ) { 
			//IE 4 compatible 
			myWidth = document.body.clientWidth; 
			myHeight = document.body.clientHeight; 
		}
		
		myWidth-=20
		myHeight-=20
		
		
		$('lightbox').style.width = '550px'
		$('lightbox').style.height = '450px'
		Width = 550
		Height = 450
		newWidth = newX
		if(newWidth>myWidth) newWidth = myWidth
		newHeight = newY
		if(newHeight>myHeight) newHeight = myHeight
		if(false) {
			var dist = 0
			if (newWidth>Width) {
				dist = newWidth-Width
			} else if (Width>newWidth) {
				dist = Width-newWidth
			}
			wID = setInterval(growW,Math.round(200/dist*6))
			resizingW = true
			if (newHeight>Height) {
				dist = newHeight-Height
			} else if (Height>newHeight) {
				dist = Height-newHeight
			}
			hID = setInterval(growH,Math.round(200/dist*6))
			resizingH = true
		} else {
			$('lightbox').style.width = newWidth + 'px'
			$('lightbox').style.height = newHeight + 'px'
			$('lightbox').style.marginLeft = '-' + (Math.round(newWidth/2)+10) + 'px'
			$('lightbox').style.marginTop = '-' + (Math.round(newHeight/2)) + 'px'
			resizingW = false
			resizingH = false
		}
	},

	initialize: function(ctrl) {
		if(ctrl.href.indexOf('?')>=0) {
			this.content = ctrl.href + '&notitle';
		} else {
			this.content = ctrl.href + '?notitle';
		}
		Event.observe(ctrl, 'click', this.activate.bindAsEventListener(this), false);
		ctrl.onclick = function(){return false;};
		if(ctrl.rel != '') {
			var size = ctrl.rel.split(',')
			this.newWidth = size[0]
			this.newHeight = size[1]
		}
	},
	
	// Turn everything on - mainly the IE fixes
	activate: function(){
		//alert('..')
		currentLB = this;
		if (browser == 'Internet Explorer'){
			this.getScroll();
			this.prepareIE('100%', 'hidden');
			this.setScroll(0,0);
			this.hideSelects('hidden');
		}
		this.displayLightbox("block");
	},
	
	// Ie requires height to 100% and overflow hidden or else you can scroll down past the lightbox
	prepareIE: function(height, overflow){
		bod = document.getElementsByTagName('body')[0];
		bod.style.height = height;
		bod.style.overflow = overflow;
  
		htm = document.getElementsByTagName('html')[0];
		htm.style.height = height;
		htm.style.overflow = overflow; 
	},
	
	// In IE, select elements hover on top of the lightbox
	hideSelects: function(visibility){
		selects = document.getElementsByTagName('select');
		for(i = 0; i < selects.length; i++) {
			selects[i].style.visibility = visibility;
		}
	},
	
	// Taken from lightbox implementation found at http://www.huddletogether.com/projects/lightbox/
	getScroll: function(){
		if (self.pageYOffset) {
			this.yPos = self.pageYOffset;
		} else if (document.documentElement && document.documentElement.scrollTop){
			this.yPos = document.documentElement.scrollTop; 
		} else if (document.body) {
			this.yPos = document.body.scrollTop;
		}
	},
	
	setScroll: function(x, y){
		window.scrollTo(x, y); 
	},
	
	displayLightbox: function(display){
		$('overlay').style.display = display;
		$('lightbox').style.display = display;
		if(display != 'none') {
			if(this.newWidth && this.newHeight) this.resize(this.newWidth,this.newHeight)
			this.loadInfo();
		}
	},
	
	// Begin Ajax request based off of the href of the clicked linked
	loadInfo: function() {
		lb = this
		var myAjax = new Ajax.Request(
        this.content,
        {method: 'get', parameters: "", onComplete: this.processInfo.bindAsEventListener(this)}
		);
		this.done = false
		this.ready = false
		
	},
	
	// Display Ajax response
	processInfo: function(response){
		this.response = response
		this.ready = true
		this.processInfo2()
	},
	
	processInfo2: function() {
		if (!resizingW && !resizingH && !this.done && this.ready) {
			info = "<div id='lbContent'>" + this.response.responseText + "</div>";
			new Insertion.Before($('lbLoadMessage'), info)
			$('lightbox').className = "done"
			this.actions();	
			currentLB = this
			this.done = true
			loadtinyMCE(300,'lightbox')
		}
	},
	
	// Search through new links within the lightbox, and attach click event
	actions: function(){
		lbActions = $$('.lbAction');

		for(i = 0; i < lbActions.length; i++) {
			if (lbActions[i].type=='button') 
				Event.observe(lbActions[i], 'click', this[lbActions[i].name].bindAsEventListener(this), false);
			else 
				Event.observe(lbActions[i], 'click', this[lbActions[i].rel].bindAsEventListener(this), false);
			lbActions[i].onclick = function(){return false;};
		}
		/*var lbEvals = document.getElementsByClassName('lbEval');

		for(i = 0; i < lbActions.length; i++) {
			eval(lbEvals[i].rel)	
		}*/

	},
	
	// Example of creating your own functionality once lightbox is initiated
	insert: function(e){
	   link = Event.element(e).parentNode;
	   Element.remove($('lbContent'));
	 
	   var myAjax = new Ajax.Request(
			  link.href,
			  {method: 'get', parameters: "", onComplete: this.processInfo.bindAsEventListener(this)}
	   );
	 
	},
	
	// Example of creating your own functionality once lightbox is initiated
	deactivate: function(){
		if($('lbContent')) Element.remove($('lbContent'));
		
		if (browser == "Internet Explorer"){
			this.setScroll(0,this.yPos);
			this.prepareIE("auto", "auto");
			this.hideSelects("visible");
			this.setScroll(0,this.yPos);
		}
		
		this.displayLightbox("none");
	}
}

/*-----------------------------------------------------------------------------------------------*/

// Onload, make all links that need to trigger a lightbox active
function initializeLB(){
	if(!$('lightbox')) addLightboxMarkup();
	var lbox = $$('.lbOn');
	for(i = lbox.length-1; i>=0; i--) {
		valid = new lightbox(lbox[i]);
		lbox[i].className = lbox[i].className.replace('lbOn','lbOnBAK')
	}
	
}

Event.observe(window,'load',initializeLB)

// Add in markup necessary to make this work. Basically two divs:
// Overlay holds the shadow
// Lightbox is the centered square that the content is put into.
function addLightboxMarkup() {
	bod 				= document.getElementsByTagName('body')[0];
	overlay 			= document.createElement('div');
	overlay.id		= 'overlay';
	overlay.className = 'lbAction'
	overlay.rel = 'deactivate'
	lb					= document.createElement('div');
	lb.id				= 'lightbox';
	lb.className 	= 'loading';
	lb.innerHTML	= '<div id="closebar"><a href="#" class="lbAction" rel="deactivate">Close Window <img src="/assets/images/close.gif"/></a></div><div id="lbLoadMessage"><img src="/assets/images/progress.gif" /><p>Now Loading&hellip;</p></div>';
	bod.appendChild(overlay);
	bod.appendChild(lb);
}