<h2>Internal Link</h2>
<?


?>



<div id="single">

<?		
	$ccount = 0;
	$scount = 0;
	$liststart = false;
	$sliststart = false;
	echo '<ul class="pagetree">';
	for($pcount = 0; $pcount < count($parent); $pcount++ ){
		//draw parent (pag_id, pag_name, pag_parent, pag_order)
		echo '<li><input type="radio"';
		if(!isset($_POST['use_id']) && $_POST[$parent[$pcount][0]]){
			echo ' checked="checked"';
		}
		echo ' name="link" onclick="setLinkValue(this.value)"  value="'.$parent[$pcount][4].'" class="linkbox">';
		echo $parent[$pcount][1];
		while($child[$ccount][2] == $parent[$pcount][0]){
			if(!$liststart){

				$liststart = true;
				echo '<ul>';
			}
			//draw child
			echo '<li><input type="radio"';
			if(!isset($_POST['use_id']) && $_POST[$child[$ccount][0]]=="on"){
				echo ' checked="checked"';
			}		
			echo ' name="link"  value="'.$child[$ccount][4].'" class="linkbox"> ';							
			echo $child[$ccount][1];
			while($subchild[$scount][2] == $child[$ccount][0]){
				if(!$sliststart){
					$sliststart = true;
					echo '<ul>';
				}
				//draw subchild
				echo '<li><input type="radio"';
				if(!isset($_POST['use_id']) && $_POST[$subchild[$scount][0]]=="on"){
				echo ' checked="checked"';
				}		
				echo ' name="link"  value="'.$subchild[$scount][4].'" class="linkbox"> ';	
				
				echo $subchild[$scount][1].'</li>';
				//end subchild
				$scount++;
			}
			if($sliststart){
				$sliststart = false;
				echo '</ul>';
			}
			echo '</li>';
			
			//end child
			$ccount++;
		}
		//end parent
		if($liststart){
			$liststart = false;
			echo '</ul>';
		}
		echo '</li>';
	}
	echo '</ul>';
?>
<br />
<label for="linktext">Link Text</label>
<input onkeydown="checkText(this)" onkeyup="checkText(this)" type="text" name="linktext" id="linktext" />

<input disabled="disabled" onclick="insertLink(document.getElementById('single'))" type="button" name="link" value="Insert Link" id="linkbutton" />
<input onclick="deleteLink()" style="display:none" type="button" name="deletelink" value="Remove Link" id="delete_link" />

</div>