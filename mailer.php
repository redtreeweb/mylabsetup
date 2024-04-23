<?php
if($_SERVER["REQUEST_METHOD"] == "POST")
{
  require 'PHPMailer/PHPMailerAutoload.php';

    $mail_o = new PHPMailer;

    // $mail_o->SMTPDebug = 2;                                       // Enable verbose debug output
    $mail_o->isSMTP();                                            // Set mailer to use SMTP
    $mail_o->Host       = 'redtree.mail.pairserver.com';  // Specify main and backup SMTP servers
    $mail_o->SMTPAuth   = true;                                   // Enable SMTP authentication
    $mail_o->Username   = 'webmaster@redtreeclients.com';                     // SMTP username
    $mail_o->Password   = 'betterpasscodethenchangeme';                               // SMTP password
    $mail_o->SMTPSecure = 'tls';                                  // Enable TLS encryption, `ssl` also accepted
    $mail_o->Port       = 587;                                    // TCP port to connect to

    $name = strip_tags(trim($_POST["name"]));
    $email = filter_var(trim($_POST["email"]), FILTER_SANITIZE_EMAIL);
    $phone = trim($_POST["phone"]);
    $practice_type = trim($_POST["practice-type"]);
    $new_or_existing = trim($_POST["new-or-existing"]);

    if ( empty($name) OR !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        //http_response_code(400);
        echo "Oops! There was a problem with your submission. Please complete the form and try again.";
        exit;
    }

    // Build POST request:
    $recaptcha_url = 'https://www.google.com/recaptcha/api/siteverify';
    $recaptcha_secret = '6Lc9T68UAAAAAGYENCm77apIebnP80vo00x7T2DI';
    $recaptcha_response = $_POST['recaptcha_response'];

    function file_get_contents_curl($url) {
      $ch = curl_init();

      curl_setopt($ch, CURLOPT_AUTOREFERER, TRUE);
      curl_setopt($ch, CURLOPT_HEADER, 0);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
      curl_setopt($ch, CURLOPT_URL, $url);
      curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);

      $data = curl_exec($ch);
      curl_close($ch);
      return $data;
    }

    // Make and decode POST request:
    $recaptcha = file_get_contents_curl($recaptcha_url . '?secret=' . $recaptcha_secret . '&response=' . $recaptcha_response);
    $recaptcha = json_decode($recaptcha);

    // Take action based on the score returned:
    if ($recaptcha->score < 0.5) {
      echo "Problem with reCAPTCHA. Please try again or contact administrator.";
      exit;
    }


    $recipient = "wendy@medequipsource.com";
    // $recipient = "nmusser@redtreewd.com";

    $subject = "MedEquip Form Submission";

    $email_content = $name . " has filled out the contact form on LabSetup.com.\n\n";
    $email_content .= "Name: $name\n";
    $email_content .= "Email: $email\n";
    $email_content .= "Phone: $phone\n";
    $email_content .= "Practice Type: $practice_type\n";
    $email_content .= "New or Existing Practice?: $new_or_existing\n";

    $mail_o->From      = 'noreply@redtreeclients.com';
    $mail_o->FromName  = 'LabSetup Form';
    $mail_o->Subject   = 'Someone has filled out the form on LabSetup.com';
    $mail_o->Body      = $email_content;
    $mail_o->AddAddress( $recipient );

  if ($mail_o->send()){
        echo "success";
    } else {
        //http_response_code(500);
        echo "Oops! Something went wrong and we couldn't send your message.";
        echo 'Mailer Error: ' . $mail_o->ErrorInfo;
    }
}
