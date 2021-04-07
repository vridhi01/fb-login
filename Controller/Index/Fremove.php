<?php

namespace Excellence\FacebookLogin\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Customer\Model\Session;
use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Model\Url as CustomerUrl;
use Magento\Customer\Model\CustomerFactory;
use Magento\Customer\Model\Account\Redirect as AccountRedirect;
use Magento\Store\Model\StoreManagerInterface;
class Fremove extends Action
{
   protected $resultPageFactory;

   public function __construct(
       Context $context,
       PageFactory $resultPageFactory,
       Session $customerSession,
       AccountManagementInterface $customerAccountManagement,
       CustomerUrl $customerHelperData,
            AccountRedirect $accountRedirect,
           CustomerFactory $customerFactory,
           StoreManagerInterface $storeManager
   ) {
       
       $this->resultPageFactory = $resultPageFactory;
       $this->session = $customerSession;
        $this->customerAccountManagement = $customerAccountManagement;
        $this->customerUrl = $customerHelperData;
        $this->accountRedirect = $accountRedirect;
        $this->customerFactory = $customerFactory;
        $this->storeManager    = $storeManager;
        parent::__construct($context);
   }

   public function execute()
   {
     $email = $this->session->getCustomer()->getEmail();
     $customer1 = $this->customerFactory->create();
     $customer1->setWebsiteId($this->storeManager->getWebsite()->getId());
     $customer1->loadByEmail($email);
     $customerData = $customer1->getDataModel();
     $customerData->setCustomAttribute('facebook_id', '');
     $customer1->updateData($customerData);
     $customer1->save(); 
         $this->messageManager->addSuccess(__('Facebook account removed successfully.'));           
                
       return $this->accountRedirect->getRedirect();
   }
}