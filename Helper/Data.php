<?php

/**
 * FacebookLogin data helper
 */
namespace Excellence\FacebookLogin\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Customer\Model\CustomerFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\ObjectManagerInterface;

class Data extends AbstractHelper
{
   const XML_PATH_FACEBOOK_ENABLED = 'facebooklogin/facebook/is_enabled';
   const XML_PATH_FACEBOOK_APP_ID = 'facebooklogin/facebook/app_id';
   const XML_PATH_FACEBOOK_APP_SECRET = 'facebooklogin/facebook/app_secret';
   const XML_PATH_FACEBOOK_SEND_PASSWORD = 'facebooklogin/facebook/send_password';
   const XML_PATH_SECURE_IN_FRONTEND = 'web/secure/use_in_frontend';
   protected $customerFactory;
   protected $storeManager;
   protected $objectManager;

   public function __construct(
       Context $context,
       ObjectManagerInterface $objectManager,
       CustomerFactory $customerFactory,
       StoreManagerInterface $storeManager
   ) {
       $this->objectManager   = $objectManager;
       $this->customerFactory = $customerFactory;
       $this->storeManager    = $storeManager;
       parent::__construct($context);
   }

   public function getConfigValue($field, $storeId = null)
   {
       return $this->scopeConfig->getValue(
           $field,
           \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
           $storeId
       );
   }

   public function isEnabled($storeId = null)
   {
       return $this->getConfigValue(self::XML_PATH_FACEBOOK_ENABLED, $storeId);
   }

   public function getAppId($storeId = null)
   {
       return $this->getConfigValue(self::XML_PATH_FACEBOOK_APP_ID, $storeId);
   }

   public function sendPassword($storeId = null)
   {
       return $this->getConfigValue(self::XML_PATH_FACEBOOK_SEND_PASSWORD, $storeId);
   }

   public function getAppSecret($storeId = null)
   {
       return $this->getConfigValue(self::XML_PATH_FACEBOOK_APP_SECRET, $storeId);
   }

   public function getAuthUrl()
   {
       $curl = $this->storeManager->getStore()->getCurrentUrl();
       $param = 'account';
       $pos = strpos($curl, 'checkout');
        if ($pos !== false) {
            $param = 'checkout';
        }
       return $this->_getUrl('facebooklogin/index/callback', array('_secure' => $this->isSecure(), 'auth' => 1,'page' => $param));
   }

   public function isSecure()
   {
       $isSecure = $this->getConfigValue(self::XML_PATH_SECURE_IN_FRONTEND);

       return $isSecure;
   }

   /**
    * @param string $email
    * @return bool|\Magento\Customer\Model\Customer
    */
   public function getCustomerByEmail($email, $websiteId = null)
   {
       /** @var \Magento\Customer\Model\Customer $customer */
       $customer = $this->objectManager->create(
           'Magento\Customer\Model\Customer'
       );
       if (!$websiteId) {
           $customer->setWebsiteId($this->storeManager->getWebsite()->getId());
       } else {
           $customer->setWebsiteId($websiteId);
       }
       $customer->loadByEmail($email);

       if ($customer->getId()) {
           return $customer;
       }

       return false;
   }

   public function createCustomerMultiWebsite($data, $website_id, $store_id)
   {
       
       $customer = $this->customerFactory->create();
       $customer->setFirstname($data['firstname'])
           ->setLastname($data['lastname'])
           ->setEmail($data['email'])
           ->setWebsiteId($website_id)
           ->setStoreId($store_id)
           ->save();
       $customerData = $customer->getDataModel();
       $customerData->setCustomAttribute('facebook_id', $data['id']);
       $customer->updateData($customerData);
       try {
           $customer->save();
       } catch (\Exception $e) {
       }

       return $customer;
   }
}