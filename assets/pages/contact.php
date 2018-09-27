

<div class="main-content" style="clear:both;">
<?
	if (isset($_POST['email'])) {
        $message = nl2br(htmlentities(stop_spam($_POST['message'])));
        $name = stop_spam($_POST['name']);
        $email = stop_spam($_POST['email']);
        if ($name && $email) {
            $mail = new PHPMailer();
            $mail->From = $email;
            $mail->FromName = $name;
            $mail->Subject = "Mail from Contact Page";
            $mail->IsHTML(true);
            $mail->AltBody = $message;
            $mail->Body = $message;
            $mail->IsSMTP(true);

            $mail->AddAddress($_CONFIG['mailForm']);
            $mail->AddBCC('notify@logicstudio.nz');
            if(!$mail->Send()) {
                echo '<p>There was an error with your email and your message was <strong>not</strong> sent. Please try again checking your email is entered correctly.';
                //echo "<p>Mailer Error: " . $mail->ErrorInfo ."</p>";
                $hideForm = true;
            } else {
                echo "<h3>Thanks</h3><p>Your message has been sent, and we will be in contact shortly.</p><br>\n";
                $hideForm = true;
            }
        }// && isset($_POST['message']

        if($_POST['name'] == "") {
            echo "<h4>Please enter a name</h4>";
            $hideForm = true;
        }

        if($_POST['email'] == "") {
            echo "<h4>Please enter an email</h4>";
            $hideForm = true;
        }

        if($_POST['message'] == "") {
            echo "<h4>Please enter a message</h4>";
            $hideForm = true;
        }


    } else {
        echo "<h3>Contact Us</h3>";
    }
	if (!$hideForm) {
?>
	<form action="/<?=$this->pag_code?>" method="post">
		<label>Name</label> <input name="name" class="input" value="<?=$_POST['name']?>"/><br />
		<label>Email</label> <input name="email" class="input" value="<?=$_POST['email']?>"/><br />
		<label>Message</label> <textarea rows="5" cols="50" name="message"><?=$_GET['msg']?><?=$_POST['message']?></textarea><br />
		<label>&nbsp;</label><button type="submit">Submit</button>
	</form>
<?
	}
?>
</div>
</div>