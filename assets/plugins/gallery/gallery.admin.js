// JavaScript Document
function saveGalOrder() {
	$(document.body).mask("Saving")
	$.ajax({
		url: "/_gallery/saveorder",
		data: 'l=' + list,
		success: function(msg){
			$(document.body).unmask()
			$('#saveorder').slideUp()
		}
	});
}

function addGalPhoto(show) {
	if(show) {
		$('#addphoto').slideUp()
		$('#addphotodiv').slideDown()
		var start = $('#addphotodiv').offset()
		i = 0
		var int = window.setInterval(function(){
			i++
			$(document.body).scrollTop(start.top+i*10)
			if(i==40) clearInterval(int)
		},10)
	} else {
		$('#addphoto').slideDown()
		$('#addphotodiv').slideUp()		
	}
}

function photoUpStart() {
	$(document.body).mask("Uploading")	
}

function photoUpDone(file) {
	if(file!='') {
		if(!pika) location.reload(true)
		else {
			file = file.replace('[NUM]',pika.list.children().size()+1)
			var li = document.createElement('LI')
			li.innerHTML = file
			li.className = 'new'
			$('#pikame').append(li)
			
			pika.list.children('li.new').wrapInner("<div class='clip' />");
			var thumbs = pika.list.find('li.new img');
			thumbs.each(
				pika.createThumb
			)
			$('#pikame li.new').effect("highlight",{},3000)
			$('#pikame li.new img').fadeTo(250,0.4);
			
			thumbs.bind('click',{self:pika},pika.imgClick)
			thumbs.parents('li.new').removeClass('new')
			pika.thumbs = pika.list.find('img');
			
			var index = $('#pikame').children('li').length
			
			carousel.add(index,file)
			$('#addphotodiv')[0].reset()
		}
	}
	$(document.body).unmask()
}

var list = ''
var carousel, pika

function startCarousel() {
	return $("#pikame").jcarousel({
		scroll:4,					
		initCallback: function(carousel) 
		{
			$(carousel.list).find('img').click(function() {
				carousel.scroll(parseInt($(this).parents('.jcarousel-item').attr('jcarouselindex')));
			});
		}
	});
}

$(document).ready(function (){
	if($("#pikame").length) {
		$("#pikame").PikaChoose({transition:[0]});
	
		startCarousel()
	
		if($('#adminnav').length) $("#pikame").sortable({
			stop: function (event,ui) {
				var i = 0;
				$('#pikame .deleteicon').each(function() {
					i++
					this.innerHTML = i
					list += this.id.substring(4) + ","
					$('#saveorder').slideDown()
				})
			}
		})
	}
});