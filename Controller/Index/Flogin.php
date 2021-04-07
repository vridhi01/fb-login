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
use Magento\Framework\Exception\AuthenticationException;
use Magento\Framework\Exception\EmailNotConfirmedException;

class Flogin extends Action
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
        $this->resultFactory = $context->getResultFactory();
        parent::__construct($context);
   }

   public function execute()
   {
       $post = $this->getRequest()->getParams();
       $page = 'account';
       if($this->getRequest()->getParam('page')){
           $page   = $this->getRequest()->getParam('page');
       }
        if ($post) {
            $login = $post;
            if (!empty($login['email']) && !empty($login['password'])) {
                try {
                    $customer = $this->customerAccountManagement->authenticate($login['email'], $login['password']);
                    
                    $this->session->setCustomerDataAsLoggedIn($customer);
                    $this->session->regenerateId();
                    $customer1 = $this->customerFactory->create();
                    $customer1->setWebsiteId($this->storeManager->getWebsite()->getId());
                    $customer1->loadByEmail($login['email']);
                    $customerData = $customer1->getDataModel();
                    $customerData->setCustomAttribute('facebook_id', $this->session->getFacebookId());
                    $customer1->updateData($customerData);
                    $customer1->save(); 
                    
                } catch (EmailNotConfirmedException $e) {
                    
                    $value = $this->customerUrl->getEmailConfirmationUrl($login['email']);
                    $message = __(
                        'This account is not confirmed.' .
                        ' <a href="%1">Click here</a> to resend confirmation email.',
                        $value
                    );
                    $this->messageManager->addError($message);
                    $this->session->setUsername($login['email']);
                } catch (AuthenticationException $e) {
                    
                    $message = __('Invalid login or password.');
                    $this->messageManager->addError($message);
                    $this->session->setUsername($login['email']);
                } catch (\Exception $e) {
                     
                    $this->messageManager->addError(__('Invalid login or password.'));
                }
            } else {
                $this->messageManager->addError(__('A login and a password are required.'));
            }
        }
        if($page == 'checkout'){
                  $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
           // Your code
           $resultRedirect->setPath('checkout');
           return $resultRedirect;
        }else{
       return $this->accountRedirect->getRedirect();
               }
   }
}