<?php

namespace App\Modules\Shop\Pages;

use App\Application as App;
use App\Entity\Doc\Document;
use App\Entity\Customer;
use App\Modules\Shop\Basket;
use App\System;
use App\Helper as H;
use Zippy\Html\Form\DropDownChoice;
use Zippy\Html\Form\Form;
use Zippy\Html\Form\TextArea;
use Zippy\Html\Form\TextInput;
use Zippy\Html\Form\Date;
use Zippy\Html\Image;
use Zippy\Html\Label;
use Zippy\Html\Panel;
use Zippy\Html\Link\ClickLink;
use WayForPay\SDK\Collection\ProductCollection;
use WayForPay\SDK\Credential\AccountSecretTestCredential;
use WayForPay\SDK\Domain\Client;
use WayForPay\SDK\Domain\Product;
use WayForPay\SDK\Wizard\PurchaseWizard;
//страница оплаты заказа
class OrderPay extends Base
{

    
    private $order;
    private $c;
    

    public function __construct($orderid) {
        parent::__construct();
       

        $this->order = Document::load($orderid);
        if($this->order == null){
            App::RedirectHome() ;
            return;
        }        
        
        $this->order = $this->order->cast();      
        
        $cid = System::getCustomer() ;
        $this->c =  Customer::load($cid);
        if($this->c == null){
            App::RedirectHome() ;
            return;
        }        
    
        $number = preg_replace('/[^0-9]/', '', $this->order->document_number);
    
        $this->_tvars['onumber'] = $number;
        $this->_tvars['detail'] = array();
        
        foreach($this->order->unpackDetails('detaildata') as $item){
            $this->_tvars['detail'][] = array(
              'itemname'=>$item->itemname,
              'qty'=> H::fqty( $item->qty),
              'price'=> H::fa($item->price),
              'sum'=>H::fa($item->price * $item->qty)
            );             
        }
        
        
    }
 
 
    public  function onPayed($args, $post) {
         $order= Document::load($this->orderid) ;
           
         $payed = \App\Entity\Pay::addPayment($order->document_id, $order->document_date, $order->payed, $order->headerdata['payment'],   'WayForPay');
         if ($payed > 0) {
             $order->payed = $payed;
    
         }
         \App\Entity\IOState::addIOState($this->document_id, $this->payed, \App\Entity\IOState::TYPE_BASE_INCOME);
         $order->save();
                   
         return json_encode(array(), JSON_UNESCAPED_UNICODE);
    }
 
}
