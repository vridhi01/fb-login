<?php

namespace Excellence\FacebookLogin\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Excellence\FacebookLogin\Model\Facebook;
use Magento\Store\Model\StoreManagerInterface;
use Excellence\FacebookLogin\Helper\Data as DataHelper;
use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Model\Url as CustomerUrl;
use Magento\Customer\Model\Session;
class Callback extends Action
{
   protected $resultPageFactory;
   protected $facebook;
   protected $dataHelper;
   protected $accountManagement;
   protected $customerUrl;
   protected $session;


   public function __construct(
       Context $context,
       Facebook $facebook,
       StoreManagerInterface $storeManager,
       DataHelper $dataHelper,
       PageFactory $resultPageFactory,
       AccountManagementInterface $accountManagement,
       CustomerUrl $customerUrl,
       Session $customerSession
   ) {
       parent::__construct($context);
       $this->facebook          = $facebook;
       $this->storeManager      = $storeManager;
       $this->dataHelper        = $dataHelper;
       $this->resultPageFactory = $resultPageFactory;
       $this->accountManagement = $accountManagement;
       $this->customerUrl       = $customerUrl;
       $this->session           = $customerSession;
   }

   public function execute()
   {
       
       $isAuth   = $this->getRequest()->getParam('auth');
       $facebook = $this->facebook->newFacebook();
       $page = 'account';
       if($this->getRequest()->getParam('page')){
           $page   = $this->getRequest()->getParam('page');
       }
       
       $userId   = $facebook->getUser();
       if ($isAuth && !$userId && $this->getRequest()->getParam('error_reason') == 'user_denied') {
           return $this->_appendJs("<script>window.close()</script>");
           return;
       } elseif ($isAuth && !$userId) {
           $loginUrl = $facebook->getLoginUrl(array('scope' => 'email'));
           return $this->_appendJs("<script type='text/javascript'>top.location.href = '$loginUrl';</script>");
           return;
       }
       $user = $this->facebook->getFacebookUser();
      
       if ($isAuth && $user) {
           $store_id   = $this->storeManager->getStore()->getStoreId();
           $website_id = $this->storeManager->getStore()->getWebsiteId();
           $data       = array('firstname' => $user['first_name'], 'lastname' => $user['last_name'], 'email' => $user['email'],'id'=>$user['id']);
           if ($data['email']) {
               $customer = $this->dataHelper->getCustomerByEmail($data['email'], $website_id); //add edition
               if (!$customer || !$customer->getId()) {
                   $customer = $this->dataHelper->createCustomerMultiWebsite($data, $website_id, $store_id);
                   if ($this->dataHelper->sendPassword()) {

                       $customer->sendPasswordReminderEmail();

                   }
               }else{
                   if(!$customer->getFacebookId()){
                    $this->session->setFacebookId($data['id']);	
                    $this->session->setFemail($data['email']);
                    $this->session->setRpage($page);	
                    
                    return $this->_appendJs("<script type=\"text/javascript\">try{window.opener.location.href=\"" .$this->storeManager->getStore()->getUrl('facebooklogin/index/index'). "\";}catch(e){window.opener.location.reload(true);} window.close();</script>");   
                    
                    return;
                   }
               }
               $confirmationStatus = $this->accountManagement->getConfirmationStatus($customer->getId());
               if ($confirmationStatus === AccountManagementInterface::ACCOUNT_CONFIRMATION_REQUIRED) {
                   $this->customerUrl->getEmailConfirmationUrl($customer->getEmail());
               } else {
                   $this->session->setCustomerAsLoggedIn($customer);
               }
               if($page == 'checkout'){
                   return $this->_appendJs("<script type=\"text/javascript\">try{window.opener.location.href=\"" . $this->storeManager->getStore()->getUrl('checkout') . "\";}catch(e){window.opener.location.reload(true);} window.close();</script>");
               }else{
                return $this->_appendJs("<script type=\"text/javascript\">try{window.opener.location.href=\"" . $this->_loginPostRedirect() . "\";}catch(e){window.opener.location.reload(true);} window.close();</script>");
               }
               return;
           } else {
               return $this->_appendJs("<script type=\"text/javascript\">try{window.opener.location.reload(true);}catch(e){window.opener.location.href=\"" . $this->storeManager->getStore()->getUrl() . "\"} window.close();</script>");
               return;
           }
       }
   }

   protected function _appendJs($string)
   {
       return $string;
   }

   protected function _loginPostRedirect()
   {
      return $this->storeManager->getStore()->getUrl('customer/account');
   }
}
