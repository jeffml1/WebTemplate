function addfolder(show) {
	if(show) {
		$('#addfolder').slideUp()
		$('#addfolderdiv').slideDown()
		var start = $('#addfolderdiv').offset()
		i = 0
		var int = window.setInterval(function(){
			i++
			$(document.body).scrollTop(start.top+i*10)
			if(i==40) clearInterval(int)
		},10)
	} else {	
		$('#addfolderdiv').slideUp()
		$('#addfolder').slideDown()
	}
}

function addfile(show,fol_num) {
	if(show) {
		$('#addfile').slideUp()
		$('#addfilediv').slideDown()
		var start = $('#addfilediv').offset()
		i = 0
		var int = window.setInterval(function(){
			i++
			$(document.body).scrollTop(start.top+i*10)
			if(i==40) clearInterval(int)
		},10)
		$('#fol_num').val(fol_num)
	} else {	
		$('#addfilediv').slideUp()	
		$('#addfile').slideDown()
	}
}

$(document).ready(function (){
	if($('#adminnav').length) $("#filebox ul").sortable({
		distance: 5,
		connectWith: $('#filebox ul'),
		start: function (event,ui) {$('#filebox li').css('padding', '4px 2px 4px 18px') },
		stop: function (event,ui) {
			if( $(ui.item).hasClass('file') && $(ui.item).parent().parent().is('li')==false) {
				event.preventDefault();
				return false
			}
			$('ul').filter(function() {
				return $(this).children().length==0
			}).empty()
			
			var i = 0
			
			var folders = files = ''
			var getChildren = function(list,parent) {
				list.children('li').each(function() {
					i++
					num = $(this).children('input').val()
					if($(this).hasClass('folder')) {
						folders+= num + ":" + parent + ':' + i + ","
					} else {
						files+= num + ":" + parent + ':' + i + ","
					}
					getChildren($(this).children('ul'),num)
				})
			}
			getChildren($("#filebox > ul"),0)
			
			$.ajax({	
				url: '/_library/saveorder',
				data: 'folders=' + folders + '&files=' + files + '&page='+ $('.pag_id').val(),
				type: 'POST',
				success: function( data ) {
					$('#filebox ul').each(function() {
						var folder = $(this)
						$(this).children('li.file').each(function() {
							$(this).appendTo(folder)
						})
					})
					if($('#adminnav').length) {
						setDelete();
					}
				}
			});
			$('#filebox li').css('padding', '2px 2px 2px 18px')
		}	
	})
	if($('#adminnav').length) {
		setDelete();
	}
	
	initiatefolders()	
	
	$('.add').click(function(event){
		$(this).parent().toggleClass('open').toggleClass('closed')
		event.preventDefault();
	});
});

	function setDelete() {
		$('#filebox .delete').remove()
		
		$(".folder:not(:has(li))").each(function() {
			$a = $('<a href="#"></a>')
				.addClass('delete')
				.click(function() {
					var folder = $(this).parent()
					var num = $(this).parent().attr('id')
					var index = num.indexOf("_")
					num = num.substring(index+1)
					$.ajax({
						url:'/_library/delete?num=' + num + '&type=folder',
						success: function( data ) {
							folder.remove()
						}
					})
					return false
				})
			
			$(this).children('ul').before($a)
		})
		$('.file').each(function() {
			$('<a href="#"></a>')
				.addClass('delete')
				.click(function() {
					var file = $(this).parent()
					var num = $(this).parent().children('input').val()
					$.ajax({
						url:'/_library/delete?num=' + num + '& type=file',
						success: function( data ) {
							if (data == '1') {
								file.remove()
							} else alert('There has been an error and your file could not be deleted');
						}
					})
					return false
				})
				.appendTo($(this))
		})
		
	}
	
	function addfiledone(filename, name, parent, type, num) {
		$file = $('<li class="file ' + type + '"><a href="/upload/library/' + filename + '">' + name + '</a><input type="hidden" name="file_num" value="' + num + '" /></li>\n')
		$file.appendTo($('#fol_' + parent + ' > ul'))
		setDelete()
		$('#file_name').val('')
	}
	
	function addfolderdone(name, fol_num) {
		$fol = $('<li/>')
			.attr('id','fol_' + fol_num)
			.addClass('folder open')
			.html('<a href="#" class="folderlink">' + name + '</a><a href=\"#\" onclick=\"addfile(true,' + fol_num + ');\" class=\"add\" return false></a>')
			.append($('<ul/>'))
			.appendTo($('#filebox > ul'))
		setDelete()
		initiatefolders()
		$('#fol_name').val('')
		$('.filebox').css('display', 'block');
	}
	
	function initiatefolders() {
		$('.folderlink').click(function(event){
		$(this).parent().toggleClass('open').toggleClass('closed')
		event.preventDefault();
	});	
	
	
	}