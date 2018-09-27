
if(window.location.href.split('/')[3] !== '') {
	$('.yellowBarBottom').css('margin-bottom', "-30px");
}

$('body').removeClass('nojs');

if($('.adminnav').length) {
	if($('.mceEditor').length) loadtinyMCE(500,'')
	window.setInterval(function() {
		$.ajax('/assets/keepalive.php?sid=' + document.cookie.match(/PHPSESSID=[^;]+/)[0].replace('PHPSESSID=',''))
	} ,360000)
}

$('.nav-open-button').click(function(event){
	event.preventDefault()
	$('body').toggleClass('showmenu')
})


$('.questions ul li').click(function(event){
	event.preventDefault();
	var time = 300,
		$arrow = $(this).find('em'),
		open = $arrow.hasClass('down')

	$('.questions li div').slideUp(time)
	$('.questions li em').removeClass('down')

	if(!open) {
		$(this).find('div').stop().slideDown(time)
		$(this).find('em').addClass('down')
	}
})


if($.browser.device = (/android|webos|iphone|ipad|ipod|blackberry|iemobile|opera mini/i.test(navigator.userAgent.toLowerCase())) && $('body').has('table') && window.location.href.split('/')[3] !== "") {

	$('.scrollableTable').prepend("<p><strong>To view the rest of the table, scroll to the right.</strong></p>");

}



$('.answer a').click(function(event){
    event.stopImmediatePropagation();
});
