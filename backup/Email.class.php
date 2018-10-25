<?php

/**
 * Created by PhpStorm.
 * User: N.Malezanoski
 * Date: 06/07/2015
 * Time: 9:04 AM
 *
 * Various functions
 */
class Email
{

    private $_mailer;
    private $_transport;


    public function __construct()
    {
       //if ($_SERVER['COMPUTERNAME'] != 'PC-NIKOLA') {
          $this->_transport = Swift_SmtpTransport::newInstance('smtp.', 25);
       //} else {
       //   $this->_transport = Swift_SmtpTransport::newInstance('email.webvault.com.au', 25);
       //}


       // $this->_transport = Swift_SmtpTransport::newInstance('mta3.webvault.com.au', 25); // from chee
        $this->_mailer = Swift_Mailer::newInstance($this->_transport);
//        $this->transport = Swift_SmtpTransport::newInstance('smtpout.intellicentre.net.au', 25)->setEncryption('tls');
//        $this->transport->setTimeout(20);
    }

    public function sendEmail($to, $from, $subject, $body, $additional_headers='', $additional_parameters='', $data=array(), $attachment = array(), $files = array() )
    {
        $result = '';
        //check if $to parameter is  array.
        if (!is_array($to)){
            // convert to array
            $to =  array( $to );
        }

        //check if $from parameter is  array.
        if (!is_array($from)){
            // check if $from is empty. If it is empty then setup $from addres depends of brand
            if ( !empty( $from ) ) {
                // convert to array
                $from = array( $from );
            } else {
                if (!array_key_exists('displayBrand', $data)){
                    $from = array( 'online@money3.com.au' => 'Money3'
                    );
                } else{
                    // if we  have set display brand then brending that email
                    switch($data['displayBrand']){
                        case 'CT':
                            $from = array(
                                'apply@cashtrain.com.au' => 'Cash Train'
                            );
                            break;
                        default:
                            $from = array(
                                'online@money3.com.au' => 'Money3'
                            );
                            break;
                    }
                }
            }
        }
        if(count($to) === 1 && count($from) === 1 && 
            (@$from[0] === 'online@money3.com.au' || @$from[0] === 'apply@cashtrain.com.au' || @$from['online@money3.com.au'] === 'Money3'|| @$from['apply@cashtrain.com.au'] === 'Cash Train')
            && ( @$to[0] === 'apldoc@money3.net.au' || @$to[0] === 'aplclientdoc@money3.net.au') ) {
            $from = array('spam@money3.com.au');
        }

        try {
            $attachmentName = array();

            $message = Swift_Message::newInstance()
                ->setSubject( $subject )
                ->setFrom( $from )
                ->setTo( $to );


            if ( strpos($body,'{{ logoMoney3 }}') !== false ) {
                $cid = $message->embed(Swift_Image::fromPath(__DIR__ . '/../images/money-3-logo.gif'));
                $body = str_replace('{{ logoMoney3 }}', $cid, $body);
            }

            if ( strpos($body,'{{ continueM3 }}') !== false ){
                $cid = $message->embed(Swift_Image::fromPath(__DIR__.'/../templates/emailTemplates/buttons/money3-continue.gif'));
                $body = str_replace('{{ continueM3 }}', $cid, $body);
            }
            if ( strpos($body,'{{ logoCT }}') !== false ) {
                $cid = $message->embed(Swift_Image::fromPath(__DIR__ . '/../images/cash-train/cash-train-logo-2013.gif'));
                $body = str_replace('{{ logoCT }}', $cid, $body);
            }
            if ( strpos($body,'{{ continueCT }}') !== false ) {
                $cid = $message->embed(Swift_Image::fromPath(__DIR__ . '/../templates/emailTemplates/buttons/cashtrain-continue.gif'));
                $body = str_replace('{{ continueCT }}', $cid, $body);
            }

            $message->setBody( $body , 'text/html' );

            if (count($attachment) > 0){
                $attachmentName = array($attachment['setFilename']);
                $attach = Swift_Attachment::newInstance($attachment['setBody'], $attachment['setFilename'], $attachment['setContentType']);
                $message->attach($attach);
            }
            if (count($files) > 0){
                // The two statements above could be written in one line instead
                
                foreach($files as $file) {
                    $message->attach(Swift_Attachment::fromPath($file));
                }
            }
            $result = $this->_mailer->send($message);
            if($to[0] === 'apldoc@money3.net.au' || $to[0] === 'aplclientdoc@money3.net.au' || $to[0] === 'apldoctest@money3.net.au'){
                $this->apldocLog(json_encode($to), json_encode($from), $subject, json_encode($attachmentName));
            }

            
        } catch (Exception $e) {
//            echo '<pre>';
//            var_dump( $e);
//            echo '</pre>';
            try {
                $this->apldocLog(json_encode($to), json_encode($from), $subject, json_encode($attachmentName), $e->getMessage());
            } catch (Exception $e) {
            }
        }

        return $result;

    }

    public function sendEmailToAplWithFile($to, $loanID, $files  ){
        $from = array(
            'apply@money3.com.au' => 'Money3'
        );

        $message = Swift_Message::newInstance()
            ->setSubject( $loanID )
            ->setFrom( $from )
            ->setTo( $to)
        ;


        foreach($files as $fileUploaded){
            $message->attach(Swift_Attachment::fromPath($fileUploaded['tmp_name'])->setFilename($fileUploaded['name']));
        }

        $result = $this->_mailer->send($message);
    }

    public function apldocLog($from, $to, $subject, $attachmentName, $error = 'no'){
        $fields = array(date("Y-m-d H:i:s"), $from, $to, $subject, $attachmentName, $error);
        $filename = $_SERVER['DOCUMENT_ROOT']. '\logs\EMAIL_logs_' . date("Y-m-d") . '.csv';
        $fp = @fopen($filename, 'a');
        if($fp){
            fputcsv($fp, $fields);
            fclose($fp);
        }
    }
}
      