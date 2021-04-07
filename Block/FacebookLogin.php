<?php

namespace Excellence\FacebookLogin\Block;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Excellence\FacebookLogin\Model\Facebook as Facebook;
use Excellence\FacebookLogin\Helper\Data as DataHelper;
use Magento\Customer\Model\Session;
class FacebookLogin extends Template
{
   protected $faceBook;
   protected $storeManager;
   protected $dataHelper;

   public function __construct(
       Context $context,
       Facebook $faceBook,
       DataHelper $dataHelper,
      Session $customerSession,
       array $data = []
   ) {
       $this->faceBook   = $faceBook;
       $this->dataHelper = $dataHelper;
       $this->session           = $customerSession;
       
       parent::__construct($context, $data);
   }

   public function getLoginUrl()
   {
       return $this->faceBook->getFacebookLoginUrl();
   }

   public function isEnabled()
   {
       
       return $this->dataHelper->isEnabled();
   }
   
   public function getEmail(){
       return $this->session->getFemail();
   }
   
   public function getPage(){
       return $this->session->getRpage();
   }

   public function getCustomerLoggedIn(){
    if($this->session->isLoggedIn()) {
      return true;
    }
    return false;
   }
   
   public function getFBId(){
       if($this->session->isLoggedIn()) {
            return $this->session->getCustomer()->getFacebookId();
        }
   }
}