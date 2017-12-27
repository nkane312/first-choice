<?php
$recipient_email    = "nick.ifcj@gmail.com"; //recepient
//$from_email         = "info@your_domain.com"; //from email using site domain.


if(!isset($_SERVER['HTTP_X_REQUESTED_WITH']) AND strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') {
    die('Sorry Request must be Ajax POST'); //exit script
}

if($_POST){
    
    $sender_fname    = filter_var($_POST["fname"], FILTER_SANITIZE_STRING); //capture sender first name
    $sender_lname    = filter_var($_POST["lname"], FILTER_SANITIZE_STRING); //capture sender last name
    $sender_email   = filter_var($_POST["email"], FILTER_SANITIZE_EMAIL); //capture sender email
    //$country_code   = filter_var($_POST["phone1"], FILTER_SANITIZE_NUMBER_INT);
    //$phone_number   = filter_var($_POST["phone2"], FILTER_SANITIZE_NUMBER_INT);
    $subject = "$sender_fname $sender_lname's Resume from firstchoicegroupinc.com\n";
    //$subject        = filter_var($_POST["subject"], FILTER_SANITIZE_STRING);
    //$message        = filter_var($_POST["message"], FILTER_SANITIZE_STRING); //capture message

    $attachment = $_FILES['file_attach'];
    $reply_email = "resumes@firstchoicegroupinc.com";
    
    //php validation, exit outputting json string
    if(strlen($sender_fname)<1){
        print json_encode(array('type'=>'error', 'text' => 'Name is too short or empty!'));
        exit;
    }
    if(strlen($sender_lname)<2){
      print json_encode(array('type'=>'error', 'text' => 'Name is too short or empty!'));
      exit;
    }
    if(!filter_var($sender_email, FILTER_VALIDATE_EMAIL)){ //email validation
        print json_encode(array('type'=>'error', 'text' => 'Please enter a valid email!'));
        exit;
    }
    /*if(!$phone_number){ //check for valid numbers in phone number field
        print json_encode(array('type'=>'error', 'text' => 'Enter only digits in phone number'));
        exit;
    }*/
    if(strlen($subject)<3){ //check emtpy subject
        print json_encode(array('type'=>'error', 'text' => 'Subject is required'));
        exit;
    }
    /*if(strlen($message)<3){ //check emtpy message
        print json_encode(array('type'=>'error', 'text' => 'Too short message! Please enter something.'));
        exit;
    }*/

    
    //$file_count = count($attachments['name']); //count total files attached
    $boundary = md5("firstchoicegroupinc.com"); 
    
    //construct a message body to be sent to recipient
    $message_body =  "Resume sent from firstchoicegroupinc.com by $sender_fname $sender_lname attached.\n";
    $message_body .=  "------------------------------\n";
    //$message_body .=  "$message\n";
    //$message_body .=  "------------------------------\n";
    $message_body .=  "$sender_fname\n";
    $message_body .=  "$sender_lname\n";
    $message_body .=  "$sender_email\n";
    //$message_body .=  "$country_code$phone_number\n";
    
    if($attachment){ //if attachment exists

      
        //header
        $headers = "MIME-Version: 1.0\r\n"; 
        $headers .= "From:".$sender_email."\r\n"; 
        $headers .= "Reply-To: ".$reply_email."" . "\r\n";
        $headers .= "Content-Type: multipart/mixed; boundary = $boundary\r\n\r\n"; 
        
        //message text
        $body = "--$boundary\r\n";
        $body .= "Content-Type: text/plain; charset=ISO-8859-1\r\n";
        $body .= "Content-Transfer-Encoding: base64\r\n\r\n"; 
        $body .= chunk_split(base64_encode($message_body)); 

        //attachments
        //for ($x = 0; $x < $file_count; $x++){       
            //if(!empty($attachments['name'][$x])){
                
                if($attachment['error']>0) //exit script and output error if we encounter any
                {
                    $mymsg = array( 
                    1=>"The uploaded file exceeds the upload_max_filesize directive in php.ini", 
                    2=>"The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form", 
                    3=>"The uploaded file was only partially uploaded", 
                    4=>"No file was uploaded", 
                    6=>"Missing a temporary folder" ); 
                    print  json_encode( array('type'=>'error',$mymsg[$attachment['error']]) ); 
                    exit;
                }
                
                //get file info [$x]
                $file_name = $attachment['name'];
                $file_size = $attachment['size'];
                $file_type = $attachment['type'];
                
                
                //var_dump($file_size);
                //echo $attachments['tmp_name'][$x];

                //read file 
                $handle = fopen($attachment['tmp_name'], "r");
                $content = fread($handle, $file_size);
                fclose($handle);
                $encoded_content = chunk_split(base64_encode($content)); //split into smaller chunks (RFC 2045)
                
                $body .= "--$boundary\r\n";
                $body .="Content-Type: $file_type; name=".$file_name."\r\n";
                $body .="Content-Disposition: attachment; filename=".$file_name."\r\n";
                $body .="Content-Transfer-Encoding: base64\r\n";
                $body .="X-Attachment-Id: ".rand(1000,99999)."\r\n\r\n"; 
                $body .= $encoded_content; 
            //}
        //}

    }else{ //send plain email otherwise
       $headers = "From:".$sender_email."\r\n".
        "Reply-To: ".$reply_email. "\n" .
        "X-Mailer: PHP/" . phpversion();
        $body = $message_body;
    }
    
    //var_dump($headers);

    $sentMail = mail($recipient_email, $subject, $body, $headers);

    //var_dump($sentMail);

    if($sentMail) //output success or failure messages
    {    
           
        print json_encode(array('type'=>'done', 'text' => 'Thank you for your email!'));
        exit;
    }else{
      print_r(error_get_last());
        print json_encode(array('type'=>'error', 'text' => 'Could not send mail! Please check your information and file size/type.'));  
        exit;
    }
}
?>