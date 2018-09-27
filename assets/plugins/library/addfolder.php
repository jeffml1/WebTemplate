<?
	ob_start();
	include('../../top.php');
	ob_end_clean();
	if($_SESSION['admin'] && isset($_POST['fol_submit'])){
		if ($_POST['fol_name'] != '')
		{
			$theQ = "SELECT MAX(fol_order) FROM tblfolders";
			$theQ = mysql_query($theQ);
			while ($theR = mysql_fetch_row($theQ)) {
				$max = $theR[0] + 1;
				$fol_name = $_POST['fol_name'];
				$page = $_POST['pag_id'];
				
				
				$subQ = "INSERT INTO tblfolders (fol_name, fol_order, pag_id) VALUES ('".mysql_real_escape_string($fol_name)."',".mysql_real_escape_string($max).",".mysql_real_escape_string($page).")";
				$subQ = mysql_query($subQ);
				$fol_num = mysql_insert_id();
				?>
				<script type="text/javascript">
					parent.addfolderdone('<?=$_POST['fol_name']?>', <?=$fol_num?>)
				</script>
	<?	
			}
		}
		else
		{
		?>
				<script type="text/javascript">
					alert('Please name the folder to be created')
				</script>
		<?	
		}
	}
?>