// JavaScript Document
function deletePricingOption(row) {
	row.parentNode.removeChild(row)	
}
function addPricingOption() {
	var row = document.createElement('TR')
	var num = $('numrows')
	row.innerHTML = $('newrow').value.replace(/~NEW~/g,'NEW_' + num.value)
	$('pricingOptions').appendChild(row)
	num.value = parseFloat(num.value)+1
}

function discountSave(caller) {
	caller.form.className = 'sending'
}

function productSaveComplete(num, html) {
	if($('prd_' + num)) {
		var x = document.createElement('DIV')
		x.innerHTML = html
		$('prd_' + num).innerHTML = x.firstChild.innerHTML
		$('prd_' + num).className = x.firstChild.className
	} else {
		var x = document.createElement('DIV')
		x.innerHTML = html
		$('shopContents').appendChild(x.firstChild)
	}
	currentLB.deactivate()
	initializeLB()
}

function productSave(caller) {
	$('ajax').value = '1';
	caller.form.target = 'submitter';
	caller.form.className = 'sending'
}

function addToCart(id, caller) {

	caller.style.display = 'none'
	addlink	= document.createElement('DIV')
	caller.parentNode.insertBefore(addlink,caller.nextSibling)
	addlink.innerHTML = '<img src="/assets/images/loading.gif" alt="Adding to cart" style="float:none" />'
	var url = '/_shop/addtocart?id=' + id
	xmlHttp=GetXmlHttp()
	xmlHttp.onreadystatechange=function() {
		if (xmlHttp.readyState==4 || xmlHttp.readyState=="complete") {
			var num = xmlHttp.responseText
			addlink.innerHTML = '<img src="/assets/images/tick.png" alt="Added to cart" style="float:left" />Added to cart<br/><a href="/_shop/cart">View Cart</a>'
		}
	}
	xmlHttp.open("GET",url,true)
	xmlHttp.send(null)	
}

function calcShipping(sel) {
	var shp_num = sel.options[sel.selectedIndex].className.substr(3)
	shc_cost[shp_num]
	$('shippingcost').innerHTML = '$' + shc_cost[shp_num].moneyFormat()
	$('totalcost').innerHTML = '$' + (shc_cost[shp_num]+subtotal).moneyFormat()
}