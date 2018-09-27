	</div>
	<div class="yellowBarBottom">
		<div class="container">
			<table>
				<tr>
					<td>Switch to Payless and start saving now.</td>
					<td><a id="joinUs" href="/Join-Us">Join Us</a></td>
				</tr>
			</table>
		</div>
	</div>
			<div style="clear:both"></div>
		</div>

		<div class="footer">
			<div class="container">
				<div class="copyright">
					<a href="/"><div id="footerLogo"></div></a>
					<div id="line1">Copyright &copy; <?=date('Y')?> Payless Energy Ltd.<br>
					<a href="/Terms" style="text-decoration: underline;">Terms and Conditions</a> | <a href="/About/Acceptance-Criteria">Acceptance Criteria</a></div>
					<div id="line2"><a href="https://logicstudio.nz" target="_blank">Web Development</a> by <a href="https://logicstudio.nz" target="_blank">The Logic Studio</a></div>

				</div>

				<div class="footerMenu">
					<p>Menu</p>
					<a href="/">Home</a>
					<?$cms->doFooterMenu();?>
				</div>

				<div class="contactDeets">
					<p>Payless Energy Ltd</p>
					<a href="tel:+6434562453">+64 3 456 2453</a>
					<a href="mailto:hello@paylessenergy.co.nz">hello@paylessenergy.co.nz</a>
					<a target="_blank" rel="noopener" href="https://www.facebook.com/Pay.Less.Energy.Limited"><i id="facebookButton" class="fa fa-facebook" aria-hidden="true"></i></a>
				</div>

				<div class="clear"></div>

			</div>
		</div>
		<? include($_SERVER['DOCUMENT_ROOT'].'/assets/scripts/scripts.php'); ?>


	</body>
</html>
<?
	ob_end_flush();
?>
