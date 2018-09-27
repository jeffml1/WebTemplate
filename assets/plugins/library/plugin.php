<?/*if($_SESSION['admin']) {*/
		$_CONFIG['js'][] =	'/assets/plugins/library/library.admin.js';
	/*}*/
	
	class library {
		var $haschildren;
		var $parents;
		var $foldercount;
	
		
		function library() {
			$this->haschildren = array();
			$this->parents = array();
			$this->foldercount = 0;
			$this->position = POSITION_AFTERBODY;
		}
		
		function afterBody($cms) {	
			
			$this->checkforfiles($cms->pag_id, 1);
			
			if(($_SESSION['admin'] || sizeof($this->haschildren) > 0) && !isset($_GET['edit'])) {
			?>
				<div class="rightbar">
				<?
				if($_SESSION['admin'] && $this->foldercount <= 0) {
				?>
					<div class="filebox" id="filebox" style="display:none">
				<?
				}else {
					?>
					<div class="filebox" id="filebox">
					<?
				}
				?>
					<ul>
						<?
						$this->showchildren(0, $cms->pag_id);					
						?>
					</ul></div>
					<?
					if($_SESSION['admin']) {
						$this->addfolder();
						$this->addfile();
					}
					?>
				</div>	
			<?
			}
			$this->foldercount = 0;			
		}
		
		function checkforfiles($folder, $toplevel) {
			if($toplevel == 1) {
				$theQ = "SELECT fol_num FROM tblfolders WHERE pag_id = $folder";
			} else {
				$theQ = "SELECT fol_num FROM tblfolders WHERE fol_parent = $folder";
			}
			$theQ = mysql_query($theQ);
			if (mysql_num_rows($theQ)) {
				$this->foldercount++;
				while ($theR = mysql_fetch_row($theQ)) {
					array_push($this->parents, $theR[0]);
					$subQ = "SELECT file_num FROM tblfiles WHERE fol_num = $theR[0]";
					$subQ = mysql_query($subQ);
					if (mysql_num_rows($subQ)) {
						foreach($this->parents as $p) {
							$this->haschildren[] = $p;
						}
					}
					$this->checkforfiles($theR[0], 0);
					array_pop($this->parents);
				}
			}
		}
		
		function showchildren($fol_par, $pag_id = 0) {
			$theQ = "SELECT fol_num, fol_name, fol_order, pag_id, fol_parent FROM tblfolders WHERE ";
			if (!$_SESSION['admin']) {
				$theQ.= "fol_num IN (".implode(',',$this->haschildren).") AND ";
			}
			if($pag_id > 0) {
				$theQ.= "pag_id = $pag_id";
			} else {
				$theQ.= "fol_parent = $fol_par";
			}
			$theQ = mysql_query($theQ);			
			while ($theR = mysql_fetch_row($theQ)) {
				echo "<li class=\"folder closed\" id=\"fol_".htmlentities($theR[0])."\"><a href=\"#\" class=\"folderlink\">".htmlentities($theR[1])."</a>";
				if($_SESSION['admin']) {
				echo "<a href=\"#\" onclick=\"addfile(true,$theR[0]);\" class=\"add\" return false></a>";
				}
				echo"\n<ul>\n";
				$this->showchildren($theR[0]);
				$subQ = "SELECT file_name, file_filename, file_protected, file_num FROM tblfiles WHERE fol_num = $theR[0]";
				$subQ = mysql_query($subQ);
				if(mysql_num_rows($subQ)) {
					while ($subR = mysql_fetch_row($subQ)) {
						$pos = strpos($subR[1], '.');
						$type = substr($subR[1], $pos+1);
						if ($type == 'jpg' || $type == 'jpeg' || $type == 'jpe' || $type == 'gif' || $type == 'png') {
							$type = 'img';
						}
						echo "<li class=\"file $type\"><a href=\"/upload/library/".$subR[3].'_'.$subR[1]."\" target=\"_blank\">".htmlentities($subR[0])."</a>";
						echo "<input type=\"hidden\" name=\"file_num\" value=\"".$subR[3]."\" /></li>\n";
					}
				}	
				echo"</ul>\n";
				echo "<input type=\"hidden\" name=\"fol_num\" value=\"$theR[0]\" /></li>";
			}
		}

		function addfolder() {
			global $cms;
		?>
		<div style="clear:both"></div>
		<a href="#" onclick="addfolder(true); return false" class="action" id="addfolder">Add new folder</a>
		<form id="addfolderdiv" method="post" enctype="multipart/form-data" action="/_library/addfolder" style="display:none" target="folderuploader">
		<label>Folder Name:</label> <input name="fol_name" maxlength="50" id="fol_name"/><br /><br />
		<input type="button" onclick="addfolder(false)" class="cancel" value="Cancel" />
		<input type="submit" onclick="addfolder(false)" value="Create Folder" class="confirm" />
		<input type="hidden" name="fol_submit" value="1" />
		<input type="hidden" name="pag_id" class="pag_id" value="<?=$cms->pag_id?>"/>
		</form>
		<iframe name="folderuploader" src="/_library/addfolder" style="display:none"></iframe>
		<?
		}
		
		function addfile() {
			global $cms;
		?>
		<div style="clear:both"></div>
		<form id="addfilediv" method="post" enctype="multipart/form-data" action="/_library/addfile" style="display:none" target="fileuploader">
		<label>File:</label> <input type="file" name="file" /><br /><br />
		<label>Name:</label> <input name="file_name" maxlength="60" id="file_name"/><br /><br />
		<label>Protected:</label> <input type="checkbox" name="protected" /><br /><br /> 
		<input type="button" onclick="addfile(false)" class="cancel" value="Cancel" />
		<input type="submit" onclick="addfile(false)" value="Upload File" class="confirm" />
		<input type="hidden" name="fol_num" id="fol_num" />
		<input type="hidden" name="file_submit" value="1" />
		</form>
		<iframe name="fileuploader" src="/_library/addfile" style="display:none"></iframe>
		<?
		}
	}	
?>