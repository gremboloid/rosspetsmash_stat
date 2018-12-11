<?php

namespace app\stat;

use Swift_Mailer;
use app\stat\model\User;

/**
 * Description of Mailer
 *
 * @author kotov
 */
class Mailer 
{
    /** @var */
    protected $swmailer;
    protected $smtpServer;
    protected $smtpPort;
    protected $userName;
    protected $password;
    protected $senderName;
    protected $senderEmail;

    public function __construct() {
        $this->smtpServer = Configuration::get('SMTPServer');
        $this->smtpPort = Configuration::get('SMTPPort');
        $this->userName = Configuration::get('MailUserName');
        $this->password = Configuration::get('MailPassword');
        $this->senderName = Configuration::get('SenderName');
        $this->senderEmail = Configuration::get('AdminEmail');
        $transport = (new \Swift_SmtpTransport($this->smtpServer, $this->smtpPort))
                ->setUsername($this->userName)
                ->setPassword($this->password);
        $this->swmailer = new Swift_Mailer($transport);
        
        //$this->swmailer = ;
    }
    
    public static function isEmailExist($email) {
        if (!Validate::emailExist($email)) {
            return json_encode([
                    'STATUS' => EMAIL_NOT_EXIST,
                    'MESSAGE' => l('ERROR_EMAIL_NOT_EXIST','messages')
                ]);
        } else {
            return json_encode([
                    'STATUS' => EMAIL_STATUS_VALID
                ]);            
        }
        
    }
    public function sendNewPassword($email) {
        if (!Validate::isEmail($email) || !Validate::emailExist($email)) {
            return json_encode([
                'STATUS' => EMAIL_NOT_VALIDATE,
                'MESSAGE' => l('ERROR_EMAIL_NOT_EXIST','messages')
            ]);
        }
      //  $sender_name = Configuration::get('SenderName');
      //  $admin_email = Configuration::get('AdminEmail');
        $newpasswd = Tools::generatePassword();
        
        $msg_text = 'Ваш новый пароль: '.$newpasswd;
        $user_id = User::getFieldByValue('Email', $email);
        $user = new User($user_id);
        $user->setNewPassword($newpasswd);
        $user->saveObject();
        $message = (new \Swift_Message('Восстановление утраченного пароля'))
                ->setFrom([$this->senderEmail => $this->senderName])
                ->setTo([$email])
                ->setBody($msg_text);
        
        
        if ( count($this->swmailer->send($message)) > 0) {
            return json_encode([
                'STATUS' => EMAIL_STATUS_VALID,
                'MESSAGE' => l('SEND_NEW_PASSWORD','messages')
            ]);
        }
        
    }
    public function sendMessage($email,$head,$msg) 
    {
        if (!Validate::isEmail($email)) {
            return false;
        }
        $msg_text = $msg;
        $message = (new \Swift_Message($head))
                ->setFrom([$this->senderEmail => $this->senderName])
                ->setTo([$email])
                ->setContentType('text/html')
                ->setBody($msg_text);
        if ( count($this->swmailer->send($message)) == 0) {
            return false;
        }
        return true;
        
        
    }
    
}
