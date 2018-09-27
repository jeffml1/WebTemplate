<?
	
	session_start();
	if(!$_SESSION['admin']) {
		header('Location:/');
		die();
	}
	require_once('assets/lib.php');
	connectToDB();
		
	$pageQ = "SELECT pag_id FROM tblpage";
	$pageR = mysql_query($pageQ);
	while ($pageRow = mysql_fetch_row($pageR)) {
		$index = $pageRow[0];
		$_SESSION['pageDrawn'][$index] = 0;
	}
	
	function makeCode($length = 5) {
		$allowed = 'ABCDEFGHJKMNPQRSTUVWXYZ123456789';
		$len = strlen($allowed)-1;
		$word = '';
		for ($i=0;$i<$length;$i++) {
			$word.=substr($allowed,rand(0,$len),1);
		}
		return $word;
	}
	function fixLink($link) {
		$pattern = "/([^A-Za-z0-9]+)/";
		$result = preg_replace($pattern, "", $link);
		return $result;
	}
	

	function showLink($id, $parent = 0, $level = 0) {
		if ($_SESSION['pageDrawn'][$id] == 0) {
			$select = "SELECT pag_name, pag_parent, pag_active FROM tblpage WHERE (pag_id = $id)";
			$answer = mysql_query($select);
			$answer = mysql_fetch_row($answer);
			$realParent = $answer[1];
			$link = $answer[0];
			if ($parent != 0) $isChild = 1;
			
			$hasChild = "SELECT pag_id FROM tblpage WHERE (pag_parent = $id)";
			$hasChild = mysql_query($hasChild);
			if (mysql_num_rows($hasChild) > 0) $child = 1;
			else $child = 0;
			
			if ($realParent == $parent)	{
				echo "<li id=\"pagerow_$id\"><span class=\"controls\">";
				echo "<span style=\"float: left\">";
				echo "<a onclick=\"moveObjectUp(this.parentNode.parentNode.parentNode,false); return false;\" href=\"#\"><img src=\"/assets/images/arrowUp.gif\" alt=\"Move Up\" title=\"Move Up\"/></a>";	
				echo "<a onclick=\"moveObjectDown(this.parentNode.parentNode.parentNode,false); return false;\" href=\"#\"><img src=\"/assets/images/arrowDown.gif\" alt=\"Move Down\" title=\"Move Down\" /></a>";
				echo "<a onclick=\"makeObjectChild($id); return false;\" href=\"#\"><img src=\"/assets/images/arrowRight.gif\" alt=\"Make Child\" title=\"Make Child\"/></a>";
				echo "<a onclick=\"makeObjectParent($id); return false;\" href=\"#\" class=\"leftarrow\"><img src=\"/assets/images/arrowLeft.gif\" alt=\"Make Parent\" title=\"Make Parent\" /></a></span>";
				echo "<span style=\"float: right\">";
				//echo "<a href=\"#\" onclick=\"toInput(document.getElementById('pagelink_$id')); return false;\" style=\"margin-left: 20px\"><img src=\"/assets/images/edit.png\" alt=\"Edit Name\" title=\"Edit Name\" /></a>";
				echo "<a href=\"#\" onclick=\"deletePage($id); return false;\"><img src=\"/assets/images/redcross.gif\" alt=\"Delete\" title=\"Delete\" /></a>";
				echo "<a href=\"#\" onclick=\"addPage(this.parentNode.parentNode.parentNode, document.getElementById('level_$id').value); return false;\"><img src=\"/assets/images/plus.gif\" alt=\"Add Page\" title=\"Add Page\" /></a>";
			
				echo "</span></span>";
				echo "|- ";
				echo "<input type=\"checkbox\" name=\"active[$id]\" ";
				if ($answer[2]) echo "checked";
				echo "/> ";
				echo "<span><a href=\"#\" onclick=\"toInput(this); return false;\" id=\"pagelink_$id\">".htmlentities($link)."</a></span>";
				echo "<input name=\"page[$id]\" style=\"display: none\" value=\"".htmlentities($link)."\" id=\"pagename_$id\" />";
				echo "<input type=\"button\" value=\"Cancel\" onclick=\"toSpan(this); document.getElementById('pagename_$id').value = document.getElementById('pagelink_$id').innerHTML;  return false;\"  style=\"display: none\" />";
				echo "<input type=\"hidden\" name=\"level[$id]\" value=\"$level\" id=\"level_$id\" />";
				echo "<a href=\"#\" onclick=\"undeletePage($id); return false;\" class=\"hidecancel\">Undo Delete</a>";
				echo "<input type=\"hidden\" name=\"delete[$id]\" value=\"0\" id=\"deletepage_$id\" />";
			}
			
			$checkChildren = "SELECT pag_id FROM tblpage WHERE (pag_parent = $id) ORDER BY pag_order";
			$childResult = mysql_query($checkChildren);
			
			if ($childResult) {
				if (mysql_num_rows($childResult) > 0) {
					echo "<ul>";
					while ($row = mysql_fetch_row($childResult)) {
						showLink($row[0], $id, ($level + 1));
					}
					echo "</ul>";
				}
			}
			echo "</li>";
			$_SESSION['pageDrawn'][$id] = 1;
		}
	}
	
	/* Page for editing the order of pages in the menus or adding pages */
	/*if($_SESSION['pri_id'] >= 2){*/	
		if (isset($_POST['save'])) {
		//echo "<pre>";
		//print_r($_POST);
		//echo "</pre>";
		$curID = $_POST['formid'];
			if ($_SESSION['formid'][$curID]) {
				unset($_SESSION['formid'][$curID]);
				$i = 0;
				//$oldnames = $_POST['oldpage'];
				//$levels = $_POST['level'];
				//$ids = $_POST['pagID'];
				$delete = $_POST['delete'];
				$lastLevel = array();
				$active = array();
				foreach($_POST as $key=>$value) {
					if ($key == 'page') {
						foreach ($value as $num=>$name) {
							$thisLevel = $_POST['level'][$num];
							$old = $_POST['oldpage'][$num];
							if ($num < 0) {
								//echo $num;
								$namquery = "INSERT INTO tblpage (pag_name, pag_parent, pag_order, pag_active) VALUES 
									('".mysql_real_escape_string($name)."', ".(($thisLevel > 0 ? $lastLevel[$thisLevel - 1] : 0)).", $i, ".(isset($_POST['active'][$num])?1:0).")";
								$namresult = mysql_query($namquery) or die(mysql_error()."<br />$namquery");
								$num = mysql_insert_id();
							} else {			
								if ($_POST['level'][$num] > 0) $parent = $lastLevel[$thisLevel - 1];
								else $parent = 0;
								$levquery = "UPDATE tblpage SET pag_name = '".mysql_real_escape_string($name)."', pag_parent = $parent, pag_order = $i WHERE pag_id = ".$num;
								$levresult = mysql_query($levquery);
								if ($_POST['delete'][$num] == '1'){
									$delQuery = "DELETE FROM tblpage WHERE pag_id = ".$num;
									$delResult = mysql_query($delQuery);
									$status = "$name deleted.<br />";
								}
							}
							$lastLevel[$thisLevel] = $num;
							$i++;
						}
					} elseif ($key=='active') {
						foreach ($value as $num=>$on) {
							$active[] = $num;
						}
					}
				}
				if(sizeof($active)) {
					$list = implode(',',$active);
					$q = "UPDATE tblpage SET pag_active = 1 WHERE pag_id IN($list)";
					$q = mysql_query($q);
					$q = "UPDATE tblpage SET pag_active = 0 WHERE pag_id NOT IN($list)";
					$q = mysql_query($q);
				} else {
					$q = "UPDATE tblpage SET pag_active = 0";
					$q = mysql_query($q);
				}
			}
			
		}
	
		if (isset($_POST['addPage'])) {
			$curID = $_POST['formid'];
			if ($_SESSION['formid'][$curID]) {
				unset($_SESSION['formid'][$curID]);
				$pageName = $_POST['pageName'];
				$pageParent = $_POST['parent'];
				$insertQ = "INSERT INTO tblpage (pag_name, pag_parent, pag_order, pag_text) VALUES ('".mysql_real_escape_string($pageName)."', ";
				if ($pageParent == "none") $insertQ .= "0,"; 
				else $insertQ .= "".mysql_real_escape_string($pageParent).",";
				$insertQ.= " 200, '".mysql_real_escape_string($_CONFIG['defaultPageText'])."')";
				$insertR = mysql_query($insertQ);// or die("Could not insert page:" .mysql_error());
				/*$pageQ = "INSERT INTO tbluserpages (pag_id, use_id) VALUES (".mysql_insert_id().", ".$_SESSION['use_id'].")";
				$pageR = mysql_query($pageQ);*/
			
				$status = "<div class=\"infobox\">Page <strong>$pageName</strong> added successfully</div>";			
				unset($_POST['addPage']);
			}
		}
		
		if (isset($_GET['del'])) {
			$curID = $_GET['formid'];
			if ($_SESSION['formid'][$curID]) {
				unset($_SESSION['formid'][$curID]);
				$pageID = mysql_real_escape_string($_GET['del']);
				$deleteQ = "DELETE FROM tblpage WHERE pag_id = $pageID"; 
				$deleteR = mysql_query($deleteQ);// or die("Could not insert page:" .mysql_error());
			
				$status = "<div class=\"infobox\">Page deleted successfully</div>";		
				//unset($_POST['addPage']);
			}
		}
		
		$formid = makeCode();
		$_SESSION['formid'][$formid] = true;	
		
		$addQ = "SELECT pag_id, pag_name, pag_parent FROM tblpage ORDER BY pag_order";
		$addR = mysql_query($addQ) or die("Could not retrieve information: ".mysql_error());
		
		$editQ = "SELECT pag_id FROM tblpage ORDER BY pag_order";
		$editR = mysql_query($editQ) or die("Could not retrieve information: ".mysql_error());
		
		include('assets/top.php');
?>
	<h2>Setup Pages</h2>
<?
		echo $status;
	?>	
	<form action="/_pages" method="post" class="user">
		<fieldset>
			<legend>Add new page</legend>
			<table>
				<tr>
					<td>Page Name:</td>
					<td><input type="text" name="pageName" /></td>
				</tr>
				<tr>
					<td>Page Parent:</td>
					<td><select name="parent">
					<option value="none">None</option>
					<?
						/*while ($addRow = mysql_fetch_row($addR)) {
							echo "<option value=\"$addRow[0]\">".htmlentities($addRow[1])."</option>";
						}*/
						$cms->doOptions();
					?>
					</select>
					</td>
				</tr>
			</table><br />
			<input type="hidden" name="formid" value="<?=$formid?>" />
			<input type="hidden" name="addPage" value="1" />
			<input type="submit" value="Add Page" class="confirm" />
		</fieldset>
	</form>
	
	
	<form action="/_pages" method="post" class="user">
		<fieldset>
			<legend>Edit pages</legend>
			<ul id="editList">
<?
	while ($editRow = mysql_fetch_row($editR)) {
		showLink($editRow[0], 0, 0);
	}
?>
			</ul>
			<br />
			<input type="hidden" name="formid" value="<?=$formid?>" />
			<input type="submit" name="save" value="Save Changes" class="confirm" />
		</fieldset>
	</form>
	
<?
	include('assets/bottom.php');
?>