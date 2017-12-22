<?php

//include_once('Mail.php');
//include_once('Mail_Mime/mime.php');

$max_allowed_file_size = 500; // size in KB
$allowed_extensions = array("doc", "docx", "pdf", "txt", "rtf");
$upload_folder = '../uploads/';

if(isset($_POST['submit']))
{
	//Get the uploaded file information
	$name_of_uploaded_file =  basename($_FILES['upload']['name']);
	
	//get the file extension of the file
	$type_of_uploaded_file = substr($name_of_uploaded_file, 
							strrpos($name_of_uploaded_file, '.') + 1);
	
	$size_of_uploaded_file = $_FILES["upload"]["size"]/1024;
	
	///------------Do Validations-------------
	if(empty($_POST['fname'])||empty($_POST['lname'])||empty($_POST['email']))
	{
		$errors .= "\n Name and Email are required fields. ";	
	}
	if(IsInjected($email))
	{
		$errors .= "\n Bad email value!";
	}
	
	if($size_of_uploaded_file > $max_allowed_file_size ) 
	{
		$errors .= "\n Size of file should be less than $max_allowed_file_size";
	}
	
	//------ Validate the file extension -----
	$allowed_ext = false;
	for($i=0; $i<sizeof($allowed_extensions); $i++) 
	{ 
		if(strcasecmp($allowed_extensions[$i],$type_of_uploaded_file) == 0)
		{
			$allowed_ext = true;		
		}
	}
	
	if(!$allowed_ext)
	{
		$errors .= "\n The uploaded file is not supported file type. ".
		" Only the following file types are supported: ".implode(',',$allowed_extensions);
	}
	
	//send the email 
	if(empty($errors))
	{
		//copy the temp. uploaded file to uploads folder
		$path_of_uploaded_file = $upload_folder . $name_of_uploaded_file;
		$tmp_path = $_FILES["upload"]["tmp_name"];
		
		if(is_uploaded_file($tmp_path))
		{
		    if(!copy($tmp_path,$path_of_uploaded_file))
		    {
		    	$errors .= '\n error while copying the uploaded file';
		    }
        }
        

		$fname = $_POST['fname'];
        $lname = $_POST['lname'];
		$to = "nick.ifcj@gmail.com";
		$subject= $fname.$lname."'s Resume";
		$email = $_POST['email'];
		$text = "$fname $lname has sent you their resume.\n";
        
        mail_attachment($name_of_uploaded_file, $upload_folder, $to, $email, $fname, $lname, $email, $subject, $message);
        
        /*send the email

		$message = new Mail_mime(); 
		$message->setTXTBody($text); 
		$message->addAttachment($path_of_uploaded_file);
		$body = $message->get();
		$extraheaders = array("From"=>$email, "Subject"=>$subject);
		$headers = $message->headers($extraheaders);
		$mail = Mail::factory("mail");
		$mail->send($to, $headers, $body);
		//redirect to 'thank-you page
		//header('Location: thank-you.html');*/
	}
}

///////////////////////////Functions/////////////////
// Function to validate against any email injection attempts
function IsInjected($str)
{
  $injections = array('(\n+)',
              '(\r+)',
              '(\t+)',
              '(%0A+)',
              '(%0D+)',
              '(%08+)',
              '(%09+)'
              );
  $inject = join('|', $injections);
  $inject = "/$inject/i";
  if(preg_match($inject,$str))
    {
    return true;
  }
  else
    {
    return false;
  }
}
function mail_attachment($filename, $path, $mailto, $from_mail, $from_fname, $from_lname, $replyto, $subject, $message) {
    $file = $path.$filename;
    $file_size = filesize($file);
    $handle = fopen($file, "r");
    $content = fread($handle, $file_size);
    fclose($handle);
    $content = chunk_split(base64_encode($content));
    $uid = md5(uniqid(time()));
    $header = "From: ".$from_fname." ".$from_lname." <".$from_mail.">\r\n";
    $header .= "Reply-To: ".$replyto."\r\n";
    $header .= "MIME-Version: 1.0\r\n";
    $header .= "Content-Type: multipart/mixed; boundary=\"".$uid."\"\r\n\r\n";
    $header .= "This is a multi-part message in MIME format.\r\n";
    $header .= "--".$uid."\r\n";
    $header .= "Content-type:text/plain; charset=iso-8859-1\r\n";
    $header .= "Content-Transfer-Encoding: 7bit\r\n\r\n";
    $header .= $message."\r\n\r\n";
    $header .= "--".$uid."\r\n";
    $header .= "Content-Type: application/octet-stream; name=\"".$filename."\"\r\n"; // use different content types here
    $header .= "Content-Transfer-Encoding: base64\r\n";
    $header .= "Content-Disposition: attachment; filename=\"".$filename."\"\r\n\r\n";
    $header .= $content."\r\n\r\n";
    $header .= "--".$uid."--";
    if (mail($mailto, $subject, "", $header)) {
    echo "mail send ... OK"; // or use booleans here
    } else {
    echo "mail send ... ERROR!";
    }
   }
/*if(isset($_POST['email'])) {


    function died($error) {
        
               // your error code can go here
        
               echo "We are very sorry, but there were error(s) found with the form you submitted. ";
        
               echo "These errors appear below.<br /><br />";
        
               echo $error."<br /><br />";
        
               echo "Please go back and fix these errors.<br /><br />";
        
               die();
        
           }
        
            
        
           // validation expected data exists
        
           if(!isset($_POST['fname']) ||
        
               !isset($_POST['lname']) ||
        
               !isset($_POST['email']) ||) {
        
               died('We are sorry, but there appears to be a problem with the form you submitted.');       
        
           }
        
            
        
           $fname = $_POST['fname']; // required
        
           $lname = $_POST['lname']; // required
        
           $email_from = $_POST['email']; // required            
        
           $error_message = "";
        
           $email_exp = '/^[A-Za-z0-9._%-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,4}$/';
        
         if(!preg_match($email_exp,$email_from)) {
        
           $error_message .= 'The Email Address you entered does not appear to be valid.<br />';
        
         }
        
           $string_exp = "/^[A-Za-z .'-]+$/";
        
         if(!preg_match($string_exp,$fname)) {
        
           $error_message .= 'The First Name you entered does not appear to be valid.<br />';
        
         }
        
         if(!preg_match($string_exp,$lname)) {
        
           $error_message .= 'The Last Name you entered does not appear to be valid.<br />';
        
         }
        
        
         if(strlen($error_message) > 0) {
        
           died($error_message);
        
         }
        
           $email_message = "Form details below.\n\n";
        
            
        
           function clean_string($string) {
        
             $bad = array("content-type","bcc:","to:","cc:","href");
        
             return str_replace($bad,"",$string);
        
           }
        
            
        
           $email_message .= "First Name: ".clean_string($fname)."\n";
        
           $email_message .= "Last Name: ".clean_string($lname)."\n";
        
           $email_message .= "Email: ".clean_string($email_from)."\n";       
            
           $email_to = "nick.ifcj@gmail.com";
           
           $email_subject = clean_string($fname)." ".clean_string($lname)."'s Resume";
       
           // create email headers
        
       $headers = 'From: '.$email_from."\r\n".
        
       'Reply-To: '.$email_from."\r\n" .
        
       'X-Mailer: PHP/' . phpversion();
        
       mail($email_to, $email_subject, $email_message, $headers);         
       
}    */

?>