<?
	//$_CONFIG['adminmenu']['/shop'] = 'Shop Setup';
	$_CONFIG['css'][] = 	'/assets/plugins/shop/shop.css';
	$_CONFIG['js'][] = 		'/assets/plugins/shop/shop.js';
	$_CONFIG['adminmenu']['shop/discounts'] = 'Discounts';
	$_CONFIG['adminmenu']['shop/countries'] = 'International Settings';
	$_CONFIG['adminmenu']['shop/shipping'] = 'Shipping Prices';
	$_CONFIG['adminmenu']['shop/orders?s=4'] = 'Pending Orders';
	
	class shop {
		var $position;
		var $pricelevel;
		var $dsc_type;
		
		function shop() {
			$this->position = POSITION_AFTERBODY+POSITION_ADMINLOGIN;
		}
		
		function trackTrace($code) {
			if(preg_match('~[A-Z]{2}[0-9]{9}NZ~',$code)) {
				$url = 'http://www.trackandtrace.courierpost.co.nz/search/'.$code;
				return "(<a href=\"$url\" target=\"_blank\">Track with Courier Post</a>)";			
			} elseif(preg_match('~([A-Z]{2}) ?([0-9]{8})~',$code,$m)) {
				//NZ Couriers is the same
				$url = 'http://www.posthaste.co.nz/phl/servlet/ITNG_TAndTServlet?page=1&Key_Type=Ticket&VCCA=Enabled&product_code='.$m[0].'&serial_number='.$m[1].'&Submit=Track+Courier+Ticket';
				return "(<a href=\"$url\" target=\"_blank\">Track with Post Haste</a>)";
			}
			return false;
		}
		
		function getPriceLevel() {
			if(!$_SESSION['countrycode']) {
				$ip = $_SERVER['REMOTE_ADDR'];
				$ch = curl_init('http://api.wipmania.com/'.$ip.'?'.$_SERVER['HTTP_HOST']);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				$_SESSION['countrycode'] = curl_exec($ch);
				curl_close($ch);
			}
			if($_SESSION['ret_num']) {
				//Retailer
				$this->dsc_type = 2;
				return $_SESSION['pri_num'];
			} else {
				//Customer
				$this->dsc_type = 1;
				$theQ = "SELECT pri_num FROM tblcountry WHERE cou_code = '".mysql_real_escape_string($_SESSION['countrycode'])."'";
				$theQ = mysql_query($theQ);
				if(mysql_num_rows($theQ)) return mysql_result($theQ,0);
				else return 4;
			}
		}
		
		function displayProduct($prd_num) {
			if(!$this->pricelevel) $this->pricelevel = $this->getPriceLevel();
			$theQ = "SELECT prd_num, prd_name, prd_desc, prd_detail, i.img_num, prd_active, i.img_filename FROM tblproduct p
				LEFT JOIN tblimages i ON (p.img_num = i.img_num)
				WHERE prd_num = $prd_num";
			$theQ = mysql_query($theQ);
			$theR = mysql_fetch_assoc($theQ);
			$hasfullinfo = $theR[3]?true:false;
			echo "<div class=\"product".($theR['prd_active']?'':' inactive')."\" id=\"prd_".$prd_num."\">";
			if($_SESSION['admin']) {
				echo "<a href=\"/_shop/add?p=$prd_num\" rel=\"900,1200\" class=\"lbOn action\" style=\"float:right\">Edit this Product</a>\n";
			}
			if($hasfullinfo) echo "<a href=\"/_shop/".$theR['prd_num']."-".preg_replace('~[^A-Za-z0-9_]~','',str_replace(' ','_',$theR['prd_name']))."\">";
			elseif($theR['img_num']) echo "<a href=\"/_shop/image?".$theR['prd_num']."\" class=\"lbOn\" rel=\"320,342\">";
			if($theR['img_num']) echo "<img src=\"/upload/thumb/".$theR['img_num'].'_'.$theR['img_filename']."\" alt=\"".htmlentities($theR['prd_name'])."\">\n";
			else echo "<img src=\"/assets/images/nopic.png\" alt=\"".htmlentities($theR['prd_name'])."\">\n";
			echo "</a>";
			echo "<div class=\"pricing\">";
			$this->displayPricing($prd_num);
			echo "</div>\n";
			if($hasfullinfo) echo "<a href=\"/_shop/".$theR['prd_num']."-".preg_replace('~[^A-Za-z0-9_]~','',str_replace(' ','_',$theR['prd_name']))."\">";
			echo "<h4>".htmlentities($theR['prd_name'])."</h4>";
			if($hasfullinfo) echo "</a>\n";
			echo nl2br(htmlentities($theR['prd_desc']));
			echo "</div>";
		}
		
		function displayPricing($prd_num) {
			if(!$this->pricelevel) $this->pricelevel = $this->getPriceLevel();
			$subQ = "SELECT po.opt_num, opt_name, opt_stock, price, MAX(dsc_percent) FROM tblpriceoption po 
				LEFT JOIN tblprice p ON (po.opt_num = p.opt_num AND p.pri_num = ".$this->pricelevel.") 
				LEFT JOIN tblproduct pr ON (po.prd_num = pr.prd_num) 
				LEFT JOIN tbldiscountprod dp ON (pr.prd_num = dp.prd_num)
				LEFT JOIN tbldiscountpage dc ON (pr.pag_num = dc.pag_num)
				LEFT JOIN tbldiscount d ON ((dp.dsc_num = d.dsc_num OR dc.dsc_num = d.dsc_num) AND NOW() BETWEEN dsc_start AND dsc_end AND dsc_type = ".$this->dsc_type." AND (dsc_code = '' OR dsc_code IS NULL OR dsc_code = '".mysql_real_escape_string($_SESSION['dsc_code'])."') AND dsc_minspend <= 0)
				WHERE po.prd_num = $prd_num GROUP BY opt_num";
			$subQ = mysql_query($subQ);
			while ($subR = mysql_fetch_row($subQ)) {
				echo "<strong>$subR[1]:</strong> ";
				if($subR[4]>0) {
					//Discounted
					echo "<s>$".number_format($subR[3],2)."</s><br />";
					echo "<span class=\"special\">Now $".number_format($subR[3]*(100-$subR[4])/100,2)."</span>";
				} else {
					//Normal price
					echo "$".number_format($subR[3],2);
				}
				echo "<br />";
				if(!$subR[2]) {
					//Not In Stock
					echo "Out of stock";
				} else if($_SESSION['shcart'][$subR[0]]) {
					//Already in cart
					echo $_SESSION['shcart'][$subR[0]]." already in <a href=\"/_shop/cart\">cart</a><br />";
				} else {
					//In stock
					echo "<a href=\"/_shop/cart?p=$subR[0]\" onclick=\"addToCart($subR[0],this); return false\">Add to cart</a>";
				}
				echo "<br /><br />";
			}
		}
		
		function afterBody($cms) {
			if(is_numeric($cms->pag_id) && !isset($_GET['edit'])) {
				$theQ = "SELECT prd_num FROM tblproduct WHERE pag_num = ".$cms->pag_id;
				if(!$_SESSION['admin']) {
					$theQ.=" AND prd_active = 1";
				}
				$theQ.=" ORDER BY prd_order, prd_active DESC, prd_num";
				$theQ = mysql_query($theQ);
				if(mysql_num_rows($theQ)) {
					echo "<div id=\"shopContents\">";
					while ($theR = mysql_fetch_row($theQ)) {
						$this->displayProduct($theR[0]);
					}
					echo "</div>";
				}
				if($_SESSION['admin']) {
					echo "<a href=\"/_shop/add?c=".$cms->pag_id."\" rel=\"900,1200\" class=\"lbOn action\" id=\"addprodbutton\">Add new Product</a>";
				}
				$thispage = $cms->pages[strtolower($cms->pag_code)];
				$parent = $cms->pagcodes[$thispage[2]];
				$parentpage = $cms->pages[strtolower($parent)];
				
				
				if($cms->pagestructure[$cms->pag_id]) {
					echo "<div id=\"leftbar\">";
					$cms->doMenu("",$cms->pag_id);
					echo "</div>";
				} elseif ($parentpage[2]) {
					echo "<div id=\"leftbar\">";
					$cms->doMenu("",$parentpage[0]);
					echo "</div>";				
				}
			}
		}
		
		function adminLogin($cms) {
			$theQ = "SELECT COUNT(*) FROM tblorder WHERE ost_num = 2";
			$theQ = mysql_query($theQ);
			if($num = mysql_result($theQ,0)) {
				echo "<div class=\"infobox\"><a href=\"/_shop/orders?s=2\" class=\"action\" style=\"float:right\">View Orders</a><strong>Note:</strong><br />You have $num wholesale orders awaiting authorisation</div>";
			}
			$theQ = "SELECT COUNT(*) FROM tblorder WHERE ost_num = 4";
			$theQ = mysql_query($theQ);
			if($num = mysql_result($theQ,0)) {
				echo "<div class=\"infobox\"><a href=\"/_shop/orders?s=4\" class=\"action\" style=\"float:right\">View Orders</a><strong>Note:</strong><br />You have $num orders awaiting processing</div>";
			}
			$theQ = "SELECT COUNT(*) FROM tblorder WHERE ost_num = 5";
			$theQ = mysql_query($theQ);
			if($num = mysql_result($theQ,0)) {
				echo "<div class=\"infobox\"><a href=\"/_shop/orders?s=5\" class=\"action\" style=\"float:right\">View Orders</a><strong>Note:</strong><br />You have $num orders on hold</div>";
			}
		}
		
		function shoppingCart() {
			global $_CONFIG;
			if(!$_SESSION['shcart'] && $_COOKIE['shcart']) $_SESSION['shcart'] = unserialize($_COOKIE['shcart']);//;
			if($_POST['dsc_code']) $_SESSION['dsc_code'] = $_POST['dsc_code'];
			if(isset($_GET['p']) && is_numeric($_GET['p'])) $_SESSION['shcart'][$_GET['p']]+=1;
			if(isset($_GET['del']) && is_numeric($_GET['del'])) {
				$_SESSION['shcart'][$_GET['del']] = $qty;
				if(!$qty) unset($_SESSION['shcart'][$_GET['del']]);
			}
			setcookie('shcart',serialize($_SESSION['shcart']),time()+60*60*2);
			if(isset($_POST['qty']) && is_array($_POST['qty'])) {
				foreach($_POST['qty'] as $opt_num=>$qty) if(is_numeric($opt_num.$qty)) {
					$_SESSION['shcart'][$opt_num] = $qty;
					if(!$qty) unset($_SESSION['shcart'][$opt_num]);
				}
			}
			if(!$this->pricelevel) $this->pricelevel = $this->getPriceLevel();
			$_SESSION['shcart'][0] = 1;
			$opts = array();
			foreach($_SESSION['shcart'] as $opt_num=>$qty) {
				if(is_numeric($opt_num) && is_numeric($qty) && $qty>0) $opts[] = $opt_num;
			}
			echo "<form method=\"post\" action=\"/_shop/cart\">";
			if(isset($_POST['checkout'])) {
				if(isset($_POST['payments']) && !isset($_POST['updateshipping'])) {
					//Check we have required data. If OK, save to database and forward on to payments.
					$err = array();
					if(!$_POST['ord_name']) $err[] = 'Please enter your name';
					if(!$_POST['ord_email'] || !check_email($_POST['ord_email'])) $err[] = 'Please enter your email address';
					if(!$_POST['ord_phone']) $err[] = 'Please enter your phone number';
					if(!$_POST['ord_address']) $err[] = 'Please enter your postal address';
					if(sizeof($err)) {
						echo "<br /><strong>Please correct the following errors before continuing:</strong><ul>";
						foreach($err as $e) {
							echo "<li>$e</li>\n";
						}
						echo "</ul>";
					} else {
						//Save and forward
						$theQ = "INSERT INTO tblorder (ord_name, ord_email, ord_phone, ord_address, cou_code, ord_placed, ost_num, ord_ip, dsc_code) VALUES (
							'".mysql_real_escape_string($_POST['ord_name'])."',
							'".mysql_real_escape_string($_POST['ord_email'])."',
							'".mysql_real_escape_string($_POST['ord_phone'])."',
							'".mysql_real_escape_string($_POST['ord_address'])."',
							'".mysql_real_escape_string($_POST['cou_code'])."',
							now(), 1, '".mysql_real_escape_string($_SERVER['REMOTE_ADDR'])."', '".mysql_real_escape_string($_SESSION['dsc_code'])."')";
						$theQ = mysql_query($theQ);
						$ord_num = mysql_insert_id();
						$total = 0;
						foreach($_SESSION['finalcart'] as $odl_num=>$ord) {
							$theQ = "INSERT INTO tblorderline (ord_num, odl_num, opt_num, odl_desc, odl_price, odl_quantity, prd_num) VALUES (
								$ord_num, 
								$odl_num,
								'".mysql_real_escape_string($ord[0])."',
								'".mysql_real_escape_string($ord[1])."',
								'".mysql_real_escape_string($ord[2])."',
								'".mysql_real_escape_string($ord[3])."',
								'".mysql_real_escape_string($ord[4])."')";
							$theQ = mysql_query($theQ);
							$total+= $ord[2]*$ord[3];
						}
						$theQ = "SELECT shc_cost FROM tblshippingweight sw LEFT JOIN tblshippingcost sc ON (sw.shw_num = sc.shw_num) LEFT JOIN tblcountry c ON (c.shp_num = sc.shp_num) WHERE c.cou_code = '".mysql_real_escape_string($_POST['cou_code'])."' AND ".$_SESSION['finalweight']." BETWEEN shw_from AND shw_to";
						$theQ = mysql_query($theQ);
						$shipping = mysql_result($theQ,0);
						$theQ = "UPDATE tblorder SET ord_shipping = $shipping, ord_total = $total WHERE ord_num = $ord_num";
						$theQ = mysql_query($theQ);
						
						if($_SESSION['ret_num'] && is_numeric($_SESSION['ret_num'])) {
							//Save their account number
							$theQ = "UPDATE tblorder SET ret_code = (SELECT ret_code FROM tblretailers WHERE ret_num = ".$_SESSION['ret_num'].") WHERE ord_num = $ord_num";
							$theQ = mysql_query($theQ);
						}
						
						$_SESSION['shcart'] = array();
						$_SESSION['finalcart'] = array();
						setcookie('shcart','',0);
						
						$hidecart = true;
						if($_SESSION['ret_num'] && is_numeric($_SESSION['ret_num']) && isset($_POST['onaccount'])) {
							// Set to "on account", display confirmation message
							$theQ = "UPDATE tblorder SET ost_num = 2 WHERE ord_num = $ord_num";
							$theQ = mysql_query($theQ);
							echo "<p>Thanks - your order has been placed and will be dispatched shortly</p>";
							$mailer = new CustomMailer();
							$mailer->sendMail($_POST['ord_email'],'Order Confirmation',"Hi,<br><br>Thanks for placing an order with ".$_CONFIG['sitename'].". Your order has been received and is currently being processed.<br><br>We'll be in touch soon to let you know when your order is on its way.<br><br>Thanks again,<br><br>The team at ".$_CONFIG['sitename']);
							
							$mailer = new CustomMailer();
							$mailer->sendMail($_CONFIG['ordermail'],'New order placed on account',"A new order has been placed by ".$_POST['ord_name'].".<br><br>Please log in to process this order.");

						} else {
							// Forward to Paypal, die()
							$space = strpos($_POST['ord_name'],' ');
							$paypal = 'https://www.paypal.com/cgi-bin/webscr?business='.urlencode($_CONFIG['paypal']).'&cmd=_xclick&currency_code=NZD&custom='.$ord_num.'&amount='.$total.'&shipping='.$shipping.'&item_name=Payment for Order&country='.$_POST['cou_code'].'&email='.urlencode($_POST['ord_email']).'&first_name='.urlencode(substr($_POST['ord_name'],0,$space)).'&last_name='.urlencode(substr($_POST['ord_name'],$space));
							header('Location:'.$paypal);
							die();
						}
					}
				} elseif($_SESSION['ret_num'] && !isset($_POST['updateshipping'])) {
					$theQ = "SELECT ret_name, ret_email, ret_phone, ret_address FROM tblretailers WHERE ret_num = ".$_SESSION['ret_num'];
					$theQ = mysql_query($theQ);
					$theR = mysql_fetch_assoc($theQ);
					$_POST['ord_name'] = $theR['ret_name'];
					$_POST['ord_email'] = $theR['ret_email'];
					$_POST['ord_phone'] = $theR['ret_phone'];
					$_POST['ord_address'] = $theR['ret_address'];
				}
				if(!$hidecart) {
					echo "<br /><p>Please complete all details below:</p>";
					echo "<label>Name</label><input name=\"ord_name\" value=\"".htmlentities($_POST['ord_name'])."\" /><br /><br />\n";
					echo "<label>Email Address</label><input name=\"ord_email\" value=\"".htmlentities($_POST['ord_email'])."\" /><br /><br />\n";
					echo "<label>Phone Number</label><input name=\"ord_phone\" value=\"".htmlentities($_POST['ord_phone'])."\" /><br /><br />\n";
					echo "<label>Shipping Address<br /><span class=\"tip\">Please note we cannot ship to PO Boxes</span></label><textarea name=\"ord_address\" cols=\"30\" rows=\"4\">".htmlentities($_POST['ord_address'])."</textarea><br /><br />\n";
					echo "<label>Country</label><select name=\"cou_code\" onchange=\"calcShipping(this)\">";
					$theQ = "SELECT cou_name, cou_code, shp_num FROM tblcountry ORDER BY cou_name";
					$theQ = mysql_query($theQ);
					while($theR = mysql_fetch_row($theQ)) {
						echo "<option value=\"$theR[1]\" class=\"fr_$theR[2]\"";
						if(($theR[1]==$_POST['cou_code']) || (!$_POST['cou_code'] && $theR[1]==$_SESSION['countrycode'])) {
							echo ' selected="selected"';
							$shp_num = $theR[2];
						}
						echo ">$theR[0]</option>\n";
					}
					echo"</select><br /><br />\n";
					echo "<noscript>";
					echo "Please note if you change the shipping country you will need to update the shipping cost with the button below.<br /><br /><input type=\"submit\" name=\"updateshipping\" value=\"Update Shipping Cost\" />\n";
					echo "</noscript>";
				}
			}
			$opts = implode(',',$opts);
			$total = 0;
			$showdisc = false;
			for($i=0;$i<3;$i++) if(!$hidecart) {
				$_SESSION['finalcart'] = array();
				$weight = 0;
				$theQ = "SELECT po.opt_num, opt_name, prd_name, opt_stock, price, MAX(dsc_percent), opt_weight, po.prd_num FROM tblpriceoption po 
							LEFT JOIN tblprice p ON (po.opt_num = p.opt_num AND p.pri_num = ".$this->pricelevel.") 
							LEFT JOIN tblproduct pr ON (po.prd_num = pr.prd_num) 
							LEFT JOIN tbldiscountprod dp ON (pr.prd_num = dp.prd_num)
							LEFT JOIN tbldiscountpage dc ON (pr.pag_num = dc.pag_num)
							LEFT JOIN tbldiscount d ON ((dp.dsc_num = d.dsc_num OR dc.dsc_num = d.dsc_num) AND NOW() BETWEEN dsc_start AND dsc_end AND dsc_type = ".$this->dsc_type." AND (dsc_code = '' OR dsc_code IS NULL OR dsc_code = '".mysql_real_escape_string($_SESSION['dsc_code'])."') AND dsc_minspend <= $total)
							WHERE po.opt_num IN ($opts) GROUP BY opt_num";
				$theQ = mysql_query($theQ);
				if(!mysql_num_rows($theQ)) {
					echo "You currently have nothing in your shopping cart.";
					$i=3;
				} else {
					$row = 0;
					if($i==2) {
						$total = 0;
						echo "<table class=\"cart\">";
						echo "<tr><th colspan=\"2\">Product</th><th>Price</th><th>Quantity</th><th>";
						if($showdisc) echo "Discount";
						echo "</th><th>Total</th></tr>\n";
					}
					while($theR = mysql_fetch_row($theQ)) {
						$weight+=$theR[6]*$_SESSION['shcart'][$theR[0]];
						$row = 1-$row;
						$sub = $theR[4]*$_SESSION['shcart'][$theR[0]]*(100-$theR[5])/100;
						if($i!=1) $total+=$sub;
						if($i==1 && $theR[5]) {
							$showdisc = true;
						}
						if($i==2) {
							echo "<tr class=\"row$row\">";
							echo "<td>".htmlentities($theR[2])."</td>\n";
							echo "<td>$theR[1]</td>\n";
							echo "<td>$".number_format($theR[4],2)."</td>\n";
							if(isset($_POST['checkout'])) echo "<td>".$_SESSION['shcart'][$theR[0]]."</td>\n<td>";
							else echo "<td><input name=\"qty[$theR[0]]\" value=\"".$_SESSION['shcart'][$theR[0]]."\" style=\"width:30px\" /> <a href=\"/_shop/cart?del=$theR[0]\"><img src=\"/assets/images/trash.gif\" style=\"vertical-align:middle\" alt=\"Remove this item from cart\" title=\"Remove this item from cart\" /></td>\n<td>";
							if($showdisc) echo "".($theR[5]?"$theR[5]%":'')."\n";
							echo "</td><td>$".number_format($sub,2)."</td>\n";
							echo "</tr>\n";
							$_SESSION['finalcart'][] = array($theR[0],$theR[1],$sub,$_SESSION['shcart'][$theR[0]],$theR[7]);
						}
					}	
					if($i==2) {
						
						if(isset($_POST['checkout'])) {
							$js = "<script type=\"text/javascript\">\n";
							$js.= "var shc_cost = new Array()\n";
							$theQ = "SELECT shp_num, shc_cost FROM tblshippingweight sw LEFT JOIN tblshippingcost sc ON (sw.shw_num = sc.shw_num) WHERE $weight BETWEEN shw_from AND shw_to";
							$theQ = mysql_query($theQ);
							while($theR = mysql_fetch_row($theQ)) {
								$js.= "shc_cost[$theR[0]] = $theR[1]\n";
								if($theR[0]==$shp_num) $shc_cost = $theR[1];
							}
							$_SESSION['finalweight'] = $weight;
							$js.= "var subtotal = ".number_format($total,2)."\n";
							$js.= "</script>\n";
							if($_SESSION['ret_num']) {
							
								//echo "<tr><td colspan=\"6\" style=\"text-align:right\">&nbsp;<br /><input type=\"submit\" value=\"Proceed to Payments &raquo;\" style=\"width:170px;\" /></td></tr>\n";
								echo "<tr><td colspan=\"5\" style=\"text-align:right\"><strong>Total ex GST:</strong></td><td><strong id=\"totalcost\">$".number_format($total,2)."</strong></td></tr>\n";
								echo "<tr><td colspan=\"6\"><strong>Please note:</strong> Freight will be added to this total</td></tr>\n";
								echo "<tr><td colspan=\"6\" style=\"text-align:right\"><input type=\"submit\" value=\"Place order on Account &raquo;\" name=\"onaccount\" /><input type=\"hidden\" name=\"checkout\" value=\"1\" /><input type=\"hidden\" name=\"payments\" value=\"1\" /></td></tr>\n";
							} else {
								echo "<tr><td colspan=\"5\" style=\"text-align:right\"><strong>Shipping:</strong></td><td id=\"shippingcost\">$".number_format($shc_cost,2)."</td></tr>\n";
								echo "<tr><td colspan=\"5\" style=\"text-align:right\"><strong>Total:</strong></td><td><strong id=\"totalcost\">$".number_format($total+$shc_cost,2)."</strong></td></tr>\n";
								echo "<tr><td colspan=\"7\">When you click &quot;Proceed to Payments&quot; below you will be forwarded to PayPal. To pay with a credit card choose the &quot;Continue&quot; link on the left hand section of that page.<br /><br />After payment has been processed you will be returned to this site</td></tr>";
								echo "<tr><td colspan=\"6\" style=\"text-align:right\">&nbsp;<br /><input type=\"submit\" value=\"Proceed to Payments &raquo;\" /><input type=\"hidden\" name=\"checkout\" value=\"1\" /><input type=\"hidden\" name=\"payments\" value=\"1\" /></td></tr>\n";
							}						
							echo "</table>\n";
						} else {
							if($_SESSION['ret_num']) {
								echo "<tr><td colspan=\"3\"></td><td><input type=\"submit\" value=\"Update\" style=\"width:60px\" /></td><td style=\"text-align:right\"><strong>Total ex GST:</strong></td><td><strong>$".number_format($total,2)."</strong></td></tr>\n";
							} else {
								echo "<tr><td colspan=\"3\"></td><td><input type=\"submit\" value=\"Update\" style=\"width:60px\" /></td><td style=\"text-align:right\"><strong>Total:</strong></td><td><strong>$".number_format($total,2)."</strong></td></tr>\n";
							}
							echo "<tr><td colspan=\"6\" style=\"text-align:right\">&nbsp;<br /><input type=\"submit\" value=\"Proceed to Checkout &raquo;\" name=\"checkout\" /></td></tr>\n";
							echo "</table>\n";
							echo "<h3>Promotional Code</h3>\n";
							echo "<p>If you have a promotional code enter it here. Please note only one code can be used per order.</p>";
							echo "<input name=\"dsc_code\" value=\"".htmlentities($_SESSION['dsc_code'])."\"> <input type=\"submit\" value=\"Apply Promotional Code\" />";
						}
					}
				}
			}
			echo "</form>";
			echo $js;
		}
	}
	
	class product {
		var $prd_num;
		var $pag_num;
		var $prd_code;
		var $prd_name;
		var $prd_desc;
		var $prd_detail;
		var $img_num;
		var $prd_order;
		var $prd_active;
		var $po;
		
		function product($prd_num = 0) {
			$this->po = array();
			if($prd_num && is_numeric($prd_num)) $this->loadFromDB($prd_num);
		}
		
		function saveToDB() {
			if(!is_numeric($this->prd_num)) {
				$theQ = "INSERT INTO tblproduct (pag_num) VALUES (0)";
				$theQ = mysql_query($theQ);
				$this->prd_num = mysql_insert_id();
			}
			$theQ = "UPDATE tblproduct SET pag_num = '".mysql_real_escape_string($this->pag_num)."', prd_code = '".mysql_real_escape_string($this->prd_code)."', prd_name = '".mysql_real_escape_string($this->prd_name)."', prd_desc = '".mysql_real_escape_string($this->prd_desc)."', prd_detail = '".mysql_real_escape_string($this->prd_detail)."', img_num = '".mysql_real_escape_string($this->img_num)."', prd_order = '".mysql_real_escape_string($this->prd_order)."', prd_active = '".mysql_real_escape_string($this->prd_active)."' WHERE prd_num = ".$this->prd_num;
			$theQ = mysql_query($theQ);
			$okpo = array(0);
			if(is_array($this->po)) foreach($this->po as $num=>$p) {
				if(is_numeric($p->opt_num) && $p->opt_num>0) {
					$theQ = "UPDATE tblpriceoption SET prd_num = $this->prd_num, opt_name = '".mysql_real_escape_string($p->opt_name)."', opt_weight = '".mysql_real_escape_string($p->opt_weight)."', opt_stock = '".mysql_real_escape_string($p->opt_stock)."' WHERE opt_num = ".$p->opt_num;
					$theQ = mysql_query($theQ);
				} else {
					$theQ = "INSERT INTO tblpriceoption (prd_num, opt_name, opt_weight, opt_stock) VALUES ($this->prd_num, '".mysql_real_escape_string($p->opt_name)."', '".mysql_real_escape_string($p->opt_weight)."', '".mysql_real_escape_string($p->opt_stock)."')";
					$theQ = mysql_query($theQ);
					$this->po[$num]->opt_num = mysql_insert_id();
				}
				$theQ = "DELETE FROM tblprice WHERE opt_num = ".$this->po[$num]->opt_num;
				$theQ = mysql_query($theQ);
				if(is_array($p->prices)) foreach($p->prices as $pri_num=>$price) if(is_numeric($pri_num)) {
					$theQ = "INSERT INTO tblprice (opt_num, pri_num, price) VALUES (".$this->po[$num]->opt_num.", $pri_num, '".mysql_real_escape_string($price)."')";
					$theQ = mysql_query($theQ);
				}
				$okpo[] = $this->po[$num]->opt_num;
			}
			$okpo = implode(',',$okpo);
			$theQ = "DELETE FROM tblpriceoption WHERE opt_num NOT IN ($okpo) AND prd_num = $this->prd_num";
			$theQ = mysql_query($theQ);
		}
		
		function loadFromDB($prd_num) {
			$theQ = "SELECT prd_num, pag_num, prd_code, prd_name, prd_desc, prd_detail, img_num, prd_order, prd_active FROM tblproduct WHERE prd_num = $prd_num";
			$theQ = mysql_query($theQ);
			$theR = mysql_fetch_assoc($theQ);
			foreach ($theR as $var=>$val) {
				$this->$var = $val;
			}
			$theQ = "SELECT opt_num FROM tblpriceoption WHERE prd_num = $prd_num";
			$theQ = mysql_query($theQ);
			while($theR = mysql_fetch_row($theQ)) $this->po[] = new priceOption($theR[0]);
		}
		
		function loadFromPOST() {
			if($_POST['pag_num']) $this->pag_num = $_POST['pag_num'];
			$this->prd_code = $_POST['prd_code'];
			$this->prd_name = $_POST['prd_name'];
			$this->prd_desc = $_POST['prd_desc'];
			$this->prd_detail = $_POST['prd_detail'];
			$this->prd_active = isset($_POST['prd_active'])?1:0;
			if(is_array($_POST['opt_name'])) {
				$this->po = array();
				foreach($_POST['opt_name'] as $num=>$name) {
					$p = new priceOption();
					if(is_numeric($num)) $p->opt_num = $num;
					$p->opt_name = $name;
					$p->opt_weight = preg_replace('~[^0-9]*~','',$_POST['opt_weight'][$num]);
					$p->opt_stock = isset($_POST['opt_stock'][$num])?1:0;
					foreach($_POST['price'][$num] as $opt=>$price) {
						$p->prices[$opt] = preg_replace('~[^0-9\.]*~','',$price);
					}
					$this->po[] = $p;
				}
			}
			
				
			if(isset($_FILES['prd_image']) && is_uploaded_file($_FILES['prd_image']['tmp_name'])) {
				switch ($_FILES['prd_image']['type']) {
					case "image/gif" 	: $outype = 'gif'; break;
					case "image/jpeg"	: $outype = 'jpg'; break;
					case "image/pjpeg"	: $outype = 'jpg'; break;
					case "image/png"	: $outype = 'png'; break;
					default: $outype = 'jpg';
				}		
				
				$name = str_replace(' ','_',$_FILES['prd_image']['name']);
				$uploadDir = '../../../upload/';
				$tempfile = $uploadDir.microtime(true).rand(1000,9999);
				move_uploaded_file($_FILES['prd_image']['tmp_name'],$tempfile.".$outype");
				
				
				exec('convert '.$tempfile.".$outype"." -thumbnail '150x150>' -background white -gravity center -extent 150x150 ".$tempfile."t.$outype");
				exec('convert '.$tempfile.".$outype"." -thumbnail '300x300>' -background white -gravity center -extent 300x300 ".$tempfile."f.$outype");

				$theQ = "INSERT INTO tblimages (cat_num, img_type, img_data_full, img_data_thumb, img_filename,img_name,img_width,img_height) VALUES (1,'".mysql_real_escape_string($_FILES['prd_image']['type'])."','".mysql_real_escape_string(file_get_contents($tempfile."f.$outype"))."', '".mysql_real_escape_string(file_get_contents($tempfile."t.$outype"))."','".mysql_real_escape_string($name)."','".mysql_real_escape_string($this->prd_name)."',300,300)" ;
				$theQ = mysql_query($theQ);
				
				$this->img_num = mysql_insert_id();
				rename($tempfile."f.$outype",$uploadDir.$this->img_num.'_'.$name);
				rename($tempfile."t.$outype",$uploadDir.'thumb/'.$this->img_num.'_'.$name);
				unlink($tempfile.".$outype");
			}
		}
	}
	class priceOption {
		var $opt_num;
		var $opt_name;
		var $opt_weight;
		var $opt_stock;
		var $prices;
		
		function priceOption($opt_num = 0) {
			$this->prices = array();
			if($opt_num && is_numeric($opt_num)) $this->loadFromDB($opt_num);
		}
		
		function loadFromDB($opt_num) {
			$theQ = "SELECT opt_num, opt_name, opt_weight, opt_stock FROM tblpriceoption WHERE opt_num = $opt_num";
			$theQ = mysql_query($theQ);
			$theR = mysql_fetch_assoc($theQ);
			foreach ($theR as $var=>$val) {
				$this->$var = $val;
			}
			$theQ = "SELECT pri_num, price FROM tblprice WHERE opt_num = $opt_num";
			$theQ = mysql_query($theQ);
			while($theR = mysql_fetch_row($theQ)) {
				$this->prices[$theR[0]] = $theR[1];
			}
		}
	}
?>