<?
	class SimpleCMS {
		var $pages;
		var $pagcodes;
		var $pagestructure;
		var $pagedetail;

		var $pag_id;
		var $pag_name;
		var $pag_code;
		var $pag_title;
		var $pag_metadesc;
		var $pag_showindex;
		var $pag_parent;

		/** Initiates class **/
		function SimpleCMS() {
			global $_CONFIG;
			$this->pages = array();
			$this->pagcodes = array();
			$this->pagestructure = array();
			if($_SESSION['admin'] && isset($_POST['updatepage']) && is_numeric($_POST['pag_id'])) {
				$theQ = "UPDATE tblpage
					SET pag_text = '".mysql_real_escape_string($_POST['pag_text'])."',
					pag_active = ".(isset($_POST['pag_active'])?1:0).",
					pag_title = '".mysql_real_escape_string($_POST['pag_title'])."',
					pag_metadesc = '".mysql_real_escape_string($_POST['pag_metadesc'])."',
					pag_intro = '".mysql_real_escape_string($_POST['pag_intro'])."',
					pag_showindex ='".(isset($_POST['pag_showindex'])?1:0)."'";

				$theQ .= " WHERE pag_id = ".$_POST['pag_id'];

				$theQ = mysql_query($theQ);
				if(isset($_FILES['page_thumb']) && is_uploaded_file($_FILES['page_thumb']['tmp_name'])) {

					switch ($_FILES['page_thumb']['type']) {
						case "image/gif" 	: $outype = 'gif'; break;
						case "image/jpeg"	: $outype = 'jpg'; break;
						case "image/pjpeg"	: $outype = 'jpg'; break;
						case "image/png"	: $outype = 'png'; break;
						default: $outype = 'jpg';
					}

					$name = str_replace(' ','_',$_FILES['page_thumb']['name']);
					//$name = $this->pag_name . "-CategoryThumb";
					$uploadDir = $_SERVER['DOCUMENT_ROOT'].'/upload/';
					$tempfile = $uploadDir.microtime(true).rand(1000,9999);
					move_uploaded_file($_FILES['page_thumb']['tmp_name'],$tempfile.".$outype");

					if(isset($_POST['resize'])) {
						exec('convert '.$tempfile.".$outype"." -resize 140x140 -gravity center -extent 140x140 ".$tempfile."t.$outype");
					} else {
						exec('convert '.$tempfile.".$outype"." -resize 140x140^ ".$tempfile."t.$outype");
					}
					exec('convert '.$tempfile.".$outype"." -resize 270x150^ -gravity center -extent 270x150 -quality 70 ".$tempfile."f.$outype");

					$is = getimagesize($tempfile."f.$outype");

					$theQ = "INSERT INTO tblimages (cat_num, img_type, img_filename,img_name,img_width,img_height)
								VALUES (28,'".mysql_real_escape_string($_FILES['page_thumb']['type'])."','".mysql_real_escape_string($name)."',
								'".mysql_real_escape_string($name)."',".$is[0].",".$is[1].")" ;


					$theQ = mysql_query($theQ);

					$img_num = mysql_insert_id();
					rename($tempfile."f.$outype",$uploadDir.$img_num.'_'.$name);
					rename($tempfile."t.$outype",$uploadDir.'thumb/'.$img_num.'_'.$name);
					unlink($tempfile.".$outype");

					$theQ = "UPDATE tblpage SET img_num = $img_num WHERE pag_id = ".$_POST['pag_id'];
					$theQ = mysql_query($theQ);
				}
			}
			$this->prepPages();
			$this->findPage();

		}

		/** Prepares arrays of page names etc **/
		function prepPages() {
			$theQ = "SELECT pag_id, pag_name, pag_parent, pag_active, pag_title, pag_metadesc, pag_showindex, p.img_num, img_filename, pag_intro, pag_text FROM tblpage p LEFT JOIN tblimages i ON (p.img_num = i.img_num) ";
			if(!$_SESSION['admin']) $theQ.="WHERE pag_active = 1 ";
			$theQ.="ORDER BY pag_parent, pag_order";

			$theQ = mysql_query($theQ);
			while ($theR = mysql_fetch_array($theQ)) {

				$name = trim($theR[1]);
				$name = preg_replace("~[^A-Za-z0-9 ]~","",$name);
				$name = preg_replace("~[ ]+~","-",$name);

				$name = $this->buildPageSlug($theR[2],$name);

				if($name=='Home') $name = '';
				$barename = $name;
				$i = 2;
				while($this->pages[strtolower($name)]) {
					$name = $barename.$i;
					$i++;
				}
				$this->pages[strtolower($name)] = $theR;
				$this->pagedetail[$theR['pag_id']] = $theR;
				$this->pagcodes[$theR[0]] = $name;
				$this->pagestructure[$theR[2]][] = array($theR[0],$theR[1],$name,$theR[3],$theR[4],$theR[5]);
			}
			//print_r($this->pagedetail);
		}

		function buildPageSlug($pag_parent, $name) {
			//Check to see if $pag_id has a parent
			//If not, return $name;
			if(!$pag_parent) return $name;
			$parent_details = $this->pagedetail[$pag_parent];
			$parentname = $this->pagcodes[ $parent_details['pag_id'] ];

			$name = $parentname . '/' . $name;

			return $name;
		}


		/** Finds what page should currently be displayed **/
		function findPage() {
			if($_GET['page']) {
				if($this->pages[strtolower($_GET['page'])]) {
					$this->pag_id = $this->pages[strtolower($_GET['page'])][0];
					$this->pag_name = $this->pages[strtolower($_GET['page'])][1];
					$this->pag_parent = $this->pages[strtolower($_GET['page'])][2];
					$this->pag_code = $_GET['page'];
					$this->pag_title = $this->pages[strtolower($_GET['page'])][4];
					$this->pag_metadesc = $this->pages[strtolower($_GET['page'])][5];
				}
			} elseif($_GET['pag_num'] && is_numeric($_GET['pag_num'])) {
				$this->pag_id = $this->pages[strtolower($this->pagcodes[$_GET['pag_num']])][0];
				$this->pag_name = $this->pages[strtolower($this->pagcodes[$_GET['pag_num']])][1];
				$this->pag_parent = $this->pages[strtolower($this->pagcodes[$_GET['pag_num']])][2];
				$this->pag_code = $this->pagcodes[$_GET['pag_num']];
				$this->pag_title = $this->pages[strtolower($this->pagcodes[$_GET['pag_num']])][4];
				$this->pag_metadesc = $this->pages[strtolower($this->pagcodes[$_GET['pag_num']])][5];

			} else {
				$this->pag_id = $this->pagestructure[0][0][0];
				$this->pag_name = $this->pagestructure[0][0][1];
				$this->pag_parent = $this->pagestructure[0][0][2];
				$this->pag_code = $this->pagestructure[0][0][2];
				$this->pag_title = $this->pagestructure[0][0][4];
				$this->pag_metadesc = $this->pagestructure[0][0][5];
			}
			if(!$this->pag_id) {
				foreach($this->pagcodes as $code) {
					if($_GET['page'] == str_replace('-','',$code)) {
						header('Location:/'.$code,301);
						die();
					}
				}
				//fail(ERROR_NOPAGE);
			}
		}

		/** Create the HTML Menu **/
		function doMenu($id = "", $parent = 0, $showhidden = false, $replacechildren = array()) {
			global $_CONFIG;
			$first = true;
			echo "<ul".($id?" class=\"$id\"":"").">\n";
			$hasactive = false;
			if(is_array($this->pagestructure[$parent])) foreach($this->pagestructure[$parent] as $m) if($showhidden || !in_array($m[0],$_CONFIG['skippages'])) {
				$act = false;
				echo "<li class=\"";
				if(!$m[3]) echo "disabled ";
				if($first) echo "first ";
				$nameclass = strtolower(htmlentities($m[1],ENT_COMPAT,'UTF-8'));
				$nameclass = preg_replace("/\W|_/", '-', $nameclass);
				echo $nameclass . " ";
				if($this->pag_code==$m[2]) {
					echo "active ";
					$hasactive = true;
				}
				if(is_array($this->pagestructure[$m[0]])) echo "hassub ";
				ob_start();
				if($parent==0) echo "\"><a href=\"/$m[2]\"><span>".htmlentities($m[1],ENT_COMPAT,'UTF-8')."</span></a>";
				else echo "\"><a href=\"/$m[2]\"><span>".htmlentities($m[1],ENT_COMPAT,'UTF-8')."</span></a>";
				if(is_array($this->pagestructure[$m[0]])) {
					echo "\n";
					if ($replacechildren[$m[0]]) ob_start();
					$act = $this->doMenu("",$m[0]);
					if ($replacechildren[$m[0]]) {
						ob_end_clean();
						echo $replacechildren[$m[0]];
					}
					if($act) $hasactive = true;
				}
				$tag = ob_get_contents();
				ob_end_clean();
				if($act) echo "activechild ";
				echo $tag;
				echo "</li>\n";
				$first = false;
			}
			echo "</ul>\n";
			return $hasactive;
		}

		/** Create the HTML Menu **/
		function doFooterMenu($id = "", $parent = 0, $showhidden = false, $replacechildren = array()) {
			global $_CONFIG;
			$first = true;
			echo "<ul".($id?" class=\"$id\"":"").">\n";
			$hasactive = false;
			if(is_array($this->pagestructure[$parent])) foreach($this->pagestructure[$parent] as $m) if($showhidden || !in_array($m[0],$_CONFIG['skippages'])) {
				$act = false;
				echo "<li class=\"";
				if(!$m[3]) echo "disabled ";
				if($first) echo "first ";
				$nameclass = strtolower(htmlentities($m[1],ENT_COMPAT,'UTF-8'));
				$nameclass = preg_replace("/\W|_/", '-', $nameclass);
				echo $nameclass . " ";
				if($this->pag_code==$m[2]) {
					echo "active ";
					$hasactive = true;
				}
				if(is_array($this->pagestructure[$m[0]])) echo "hassub ";
				ob_start();
				if($parent==0) echo "\"><a href=\"/$m[2]\"><span>".htmlentities($m[1],ENT_COMPAT,'UTF-8')."</span></a>";
				else echo "\"><a href=\"/$m[2]\"><span>".htmlentities($m[1],ENT_COMPAT,'UTF-8')."</span></a>";

				$tag = ob_get_contents();
				ob_end_clean();
				if($act) echo "activechild ";
				echo $tag;
				echo "</li>\n";
				$first = false;
			}
			echo "</ul>\n";
			return $hasactive;
		}

		/** Lists all the page names in <option>s **/
		function doOptions($parent = 0, $level = 0, $selected = 0) {
			foreach($this->pagestructure[$parent] as $m) {
				echo "<option value=\"$m[0]\"";
				if ($selected==$m[0]) echo ' selected="selected"';
				echo ">";
				for($i=0;$i<$level;$i++) {
					echo " -- ";
				}
				echo htmlentities($m[1])."</option>\n";
				if(is_array($this->pagestructure[$m[0]])) {
					$this->doOptions($m[0],$level+1, $selected);
				}
			}
		}

		/** Create a menu for the internal link manager **/
		function doLinkMenu($id = "", $parent = 0) {
			echo "<ul ".($id?"class=\"$id\"":"").">\n";
			foreach($this->pagestructure[$parent] as $m) {
				echo "<li";
				if(!$m[3]) echo " class=\"disabled\"";
				echo "><a href=\"#\" onclick=\"setLinkValue('$m[2]'); return false\">".$m[1]."</a>";

				if(is_array($this->pagestructure[$m[0]])) {
					echo "\n";
					$this->doLinkMenu("",$m[0]);
				}
				echo "</li>\n";
			}
			echo "</ul>\n";
		}

		/** Creates the Administrator Menu **/
		function doAdminMenu($id = "") {
			global $_CONFIG;
			if($_SESSION['admin']) {
				echo "<ul ".($id?"class=\"$id\"":"").">\n";
				echo "<li><a href=\"/_users\">Set Up Users</a></li>\n";
				echo "<li><a href=\"/_pages\">Set Up Pages</a></li>\n";
				if(is_array($_CONFIG['adminmenu'])) foreach($_CONFIG['adminmenu'] as $l=>$n) {
					echo "<li><a href=\"/_$l\">$n</a></li>\n";
				}
				echo "<li><a href=\"/_logout\">Log Out</a></li>\n";
				echo "</ul>";
			}
		}

		/** Displays a simple Breadcrumb Trail **/
		function doBreadCrumb() {
			$list = array();
			$list[] = "<a href=\"/$this->pag_code\">$this->pag_name</a>";

			$code = $this->pag_code;

			while($parent = $this->pages[strtolower($code)][2]) {
				$code = $this->pagcodes[$parent];
				$list[] = "<a href=\"/".$code."\">".$this->pages[strtolower($code)][1]."</a>";
			}

			for($i=sizeof($list)-1; $i>=0; $i--) {
				echo $list[$i];
				if ($i>0) echo " &gt; ";
			}
		}

		function convertLinks($text) {
			return $text;
		}

		/** Display the content or the editor if needed **/
		function doBody() {
			global $_CONFIG, $plugins;
			if(!$this->pag_id) {
?>
	<h2>Whoops!</h2>
	<p>Oh dear, there seems to have been an error. We can't find the page you're looking for.</p>
	<p>Please use the menu above to find what you're after, or you could try going <a href="/">back to our home page</a>.</p>
<?
				return false;
			}
			if(is_array($plugins)) foreach($plugins as $p) if($p->position&POSITION_BEFOREBODY) $p->beforeBody($this);
			$theQ = "SELECT pag_text, pag_active, pag_form, pag_title, pag_metadesc, pag_showindex, p.img_num, img_filename, pag_parent, pag_intro
						FROM tblpage p
						LEFT JOIN tblimages i ON p.img_num = i.img_num
						WHERE pag_id = ".$this->pag_id;
			$theQ = mysql_query($theQ);
			$theR = mysql_fetch_array($theQ);
			if($_SESSION['admin']) {
				if(isset($_GET['edit'])) {
?>
				<div class="clear"></div>
				<form action="/<?=$this->pag_code?>" method="post" enctype="multipart/form-data">
					<textarea name="pag_text" class="mceEditor"><?=htmlentities($theR[0])?></textarea><br />
				<div class="third" style="float: left;">
					<h3>Metadata</h3>
					<label>Page Active? <input type="checkbox" name="pag_active" class="pag_active" id="pag_active" <?=$theR[1]?'checked="checked"':''?> style="width: 15px; height: 15px;"/>
					<label>Page Title:<input name="pag_title" value="<?=htmlentities($theR[3])?>" style="margin-left: 35px; width: 248px;"></label><br>
					<label>Page Intro:</label><textarea name="pag_intro"><?=htmlentities($theR[9])?></textarea><br>
					<label>Meta Description:</label><textarea name="pag_metadesc"><?=htmlentities($theR[4])?></textarea>
				</div>
				<div class="third" style="float: right; width: 425px;">
					<h3>Index</h3>
					<label>Show Index? <input style="width: 15px; height: 15px;" type="checkbox" name="pag_showindex" class="pag_showindex" id="pag_showindex" <?=$theR[5]?'checked="checked"':''?> />
					<label>Thumbnail:<?=htmlentities($theR[6]) >=1? " <a target=\"_blank\" class=\"hint\" href=\"/upload/".$theR[6] .'_' . $theR[7] ."\" >[View Existing]</a>" : ""   ?>
					<input type = "file" name= "page_thumb" class="page_thumb" />
					<label><input type="checkbox" name="resize" id="resize" checked/> Resize Thumbnail?</label>
				</div>
				<div class="clear"></div>
				<div class="clear">&nbsp;</div>

					<label>&nbsp;</label><input type="button" value="Cancel" onclick="if(confirm('Are you sure you want to cancel? All changes will be lost.')) location.href='/<?=$this->pag_code?>'" class="cancel" /> <input type="submit" value="Save Changes" class="confirm" />
					<input type="hidden" name="updatepage" value="1" />
					<input type="hidden" name="pag_id" value="<?=$this->pag_id?>" /><br />


				</form>
<?
				} else {
					if($theR['img_num'] && $theR['pag_parent']==7) {
						$image = '/upload/'.$theR['img_num'].'_'.rawurlencode($theR['img_filename']);
						echo "<img src=\"$image\" alt=\"".htmlentities($theR['pag_name'])."\" class=\"profile-picture\">";
					}
					echo $this->convertLinks($theR[0]);
					//echo $theR[0];
					if (!$theR[0]){
						if($this->pagestructure[$this->pag_id][0][2])
						echo"<p><span style='color: #8A8A8A;'>[This page will automatically redirect to <a href=\"/".$this->pagestructure[$this->pag_id][0][2]."\">" .$this->pagestructure[$this->pag_id][0][1]."</a> when accessed by a non-admin user]</span>";
					}
					if($theR[2]) include('pages/'.$theR[2]);
					echo "<a href=\"/".$this->pag_code."?edit\" class=\"editbutton\">Edit this Page</a>";
				}
			} else {
				if($theR[0]=='' && $theR[2]=='') {
					if(is_array($this->pagestructure[$this->pag_id][0])) {
						header('Location:/'.$this->pagestructure[$this->pag_id][0][2]);
						die();
					}
				} else {
					if($theR['img_num'] && $theR['pag_parent']==7) {
						$image = '/upload/'.$theR['img_num'].'_'.rawurlencode($theR['img_filename']);
						echo "<img src=\"$image\" alt=\"".htmlentities($theR['pag_name'])."\" class=\"profile-picture\">";
					}
					echo $this->convertLinks($theR[0]);

					if($theR[2]) include('pages/'.$theR[2]);
				}
			}

			if($this->pagedetail[$this->pag_id]['pag_showindex']) {
				$theQ = "SELECT p.pag_name, i.img_filename, i.img_num, i.img_width, i.img_height, p.pag_title, p.pag_id, pag_intro
							FROM tblpage p
							LEFT JOIN tblimages i ON (i.img_num = p.img_num)
							WHERE pag_parent = $this->pag_id
							ORDER BY pag_order";
				$theQ = mysql_query($theQ);
				echo "<div id=\"shopContentsWrapper\"><div id=\"shopContents\" class=\"shop_".mysql_num_rows($theQ)."\" >";
				while($theR = mysql_fetch_assoc($theQ)){
						$url = str_replace(' ','',$theR['pag_name']);
						$url = $this->pagcodes[$theR['pag_id']];
						if($theR['img_num']) {
							$image = '/upload/'.$theR['img_num'].'_'.rawurlencode($theR['img_filename']);
						} else {
							$image = '';
						}
					?>
						<div class="product_category"><a href="/<?=$url?>">
						<? if($image) { ?><img src="<?=$image?>" alt="<?=htmlentities($theR['pag_name'])?>"> <? } ?></a>
							<div class="name"><a href="/<?=$url?>"><?=htmlentities($theR['pag_name'])?></a></div>
							<p><?=nl2br(htmlentities($theR['pag_intro']))?></p>
						</div>
					<?
				}
				echo "</div></div>";
			}


			if(is_array($plugins)) foreach($plugins as $p) if($p->position&POSITION_AFTERBODY) $p->afterBody($this);
		}
	}
?>