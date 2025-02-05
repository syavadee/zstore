<?php

namespace App\Modules\Shop\Pages;

use App\Application as App;
use App\Entity\User;
use App\Entity\Customer;
use App\Helper;
use App\System;
use Zippy\Html\Form\TextInput as TextInput;
use Zippy\Html\Link\ClickLink;

class UserLogin extends \Zippy\Html\WebPage
{

    public function __construct() {
        parent::__construct();
   
        $form = new \Zippy\Html\Form\Form('loginform');
        $form->add(new TextInput('userphone'));
        $form->add(new TextInput('userpassword'));
        $form->add(new \Zippy\Html\Form\CheckBox('remember'));
        $form->add(new ClickLink('recall',$this,'onRecall'));
        $form->add(new ClickLink('signup',$this,'onSignup'));
        $form->onSubmit($this, 'onsubmit');

        $this->add($form);

        $form = new \Zippy\Html\Form\Form('recallform');
        $form->add(new TextInput('rcuserphone'));
        $form->setVisible(false);
        $form->onSubmit($this, 'onrcsubmit');
        $this->add($form);

        $form = new \Zippy\Html\Form\Form('signinform');
        $form->setVisible(false);
        $form->add(new TextInput('suserphone'));
        $form->add(new TextInput('sname'));
        $form->add(new TextInput('suserpassword'));
        $form->add(new TextInput('sconfirm'));
        $form->onSubmit($this, 'onsisubmit');
        $this->add($form);
 
 
        $this->setError('');
        $this->setSuccess('');
    }

    public function onrcsubmit($sender) {
        $phone = $sender->rcuserphone->getText();
        if (  strlen($phone) != Helper::PhoneL()) {
            $this->setError("tel10", Helper::PhoneL());
            return;
        }       
        $c = Customer::getByPhone($phone);
        if (  $c == null) {
            $this->setError("phonenotfound" );
            return;
        } 
        $p= substr(base64_encode(md5(time())),0,8);
        $c->passw = $p;
        $c->save();
        $ret = \App\Entity\Subscribe::sendSMS($phone, \App\Helper::l("recoverypass", $p));
        if(strlen($ret)  >0 ){
           \App\Helper::logerror($ret) ;          
           $this->setError('SMS error') ;
           return ; 
        }
        $this->setSuccess("passsent")  ;
        $this->loginform->setVisible(true) ;
        $this->recallform->setVisible(false) ;
       
    }
    
    public function onsisubmit($sender) {
        $phone = $sender->suserphone->getText();
        $name = $sender->sname->getText();
        $pass = $sender->suserpassword->getText();
        $conf = $sender->sconfirm->getText();
        if (  strlen($phone) != Helper::PhoneL()) {
            $this->setError("tel10", Helper::PhoneL());
            return;
        }       
        if (  strlen($pass) ==0  ) {
            $this->setError("enterpassword") ;
            return;
        }       
        if (  $pass != $conf) {
            $this->setError("invalidconfirm") ;
            return;
        }       
        $c = Customer::getByPhone($phone);
        if (  $c != null) {
            $this->setError("phonefound" );
            return;
        }   
        $c = new  Customer();
        $c->customer_name = $name;
        
        $c->phone = $phone;
        $c->passw = $pass;
      
        $c->type =  Customer::TYPE_BAYER;
        $c->save();
        $this->loginform->setVisible(true) ;
        $this->signinform->setVisible(false) ;
                   
    }        
        
    public function onsubmit($sender) {
        global $logger, $_config;
        $shop = System::getOptions("shop");
 
        $this->setError('');
        $phone = $sender->userphone->getText();
        $password = $sender->userpassword->getText();
        $phone = \App\Util::handlePhone($phone);
        $sender->userpassword->setText('');
        
        if (  strlen($phone) != Helper::PhoneL()) {
            $this->setError("tel10", Helper::PhoneL());
            return;
        }
        $c = Customer::getByPhone($phone);
        if (  $c == null) {
            $this->setError("phonenotfound" );
            return;
        }
        if ( strlen($password)==0 ||  $c->passw != $password) {
            $this->setError("enterpassword" );
            return;
        }
        if ($shop["uselogin"] == 1) {
            if ($c->allowedshop != 1) {
                
                $this->setError("notallowedshop" );
                return;
            }
        }
        System::setCustomer($c->customer_id)  ;
        System::getSession()->custname = $c->customer_name;
        if ($sender->remember->isChecked()) {
            setcookie("remembercust", $c->customer_id . '_' . md5($c->customer_id . $_config['common']['salt']), time() + 60 * 60 * 24 * 30);
        } else {
            setcookie("remembercust", '', 0);
        }
        App::Redirect("\\App\\Modules\\Shop\\Pages\\Main",0);

        
    }

    public function beforeRequest() {
        parent::beforeRequest();

        if (System::getCustomer()  > 0) {
          App::Redirect("\\App\\Modules\\Shop\\Pages\\Main",0);

        }
    }

    public function setError($msg,$p=null) {
        $msg = Helper::l($msg,$p) ;

        $this->_tvars['alerterror'] = $msg;
    }
  
    public function setSuccess($msg) {

        $msg = Helper::l($msg ) ;

        $this->_tvars['alertsuccess'] = $msg;
    }
    
    public function onRecall($sender) {

        $this->loginform->setVisible(false) ;
        $this->recallform->setVisible(true) ;
    }
    
    public function onSignup($sender) {
         $this->loginform->setVisible(false) ;
        $this->signinform->setVisible(true) ;


    }

}
