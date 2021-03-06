<?php
/**
 * Copyright 2011 Facebook, Inc.
 *
 * Licensed under the Apache License, Version 2.0 (the "License"); you may
 * not use this file except in compliance with the License. You may obtain
 * a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS, WITHOUT
 * WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. See the
 * License for the specific language governing permissions and limitations
 * under the License.
 */

namespace Excellence\FacebookLogin\Model\Facebook;

/**
 * Extends the AbstractFacebook class with the intent of using
 * PHP sessions to store user ids and access tokens.
 */
class Authentication extends AbstractFacebook
{
    /**
     * Identical to the parent constructor, except that
     * we start a PHP session to store the user ID and
     * access token if during the course of execution
     * we discover them.
     *
     * @param Array $config the application configuration.
     * @see AbstractFacebook::__construct in facebook.php
     */
    protected $session; 

    public function __construct($config)
    {
        parent::__construct($config);
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->session = $objectManager->create('Magento\Customer\Model\Session');
    }

    /**
     * Provides the implementations of the inherited abstract
     * methods.  The implementation uses PHP sessions to maintain
     * a store for user ids and access tokens.
     */
    protected static $kSupportedKeys =
        array('code', 'access_token', 'user_id');

    protected function setPersistentData($key, $value)
    {
        if (!in_array($key, self::$kSupportedKeys)) {
            self::errorLog('Unsupported key passed to setPersistentData.');

            return;
        }

        $session_var_name            = $this->constructSessionVariableName($key);
        $this->session->setData($session_var_name, $value);
    }

    protected function getPersistentData($key, $default = false)
    {
        if (!in_array($key, self::$kSupportedKeys)) {
            self::errorLog('Unsupported key passed to getPersistentData.');

            return $default;
        }

        $session_var_name = $this->constructSessionVariableName($key);

        $this->session->getData($session_var_name);
        
        return !empty($this->session->getData($session_var_name)) ? $this->session->getData($session_var_name) : $default;
    }

    protected function clearAllPersistentData()
    {
        foreach (self::$kSupportedKeys as $key) {
            $session_var_name = $this->constructSessionVariableName($key);
            $this->session>unsetData($session_var_name);
        }
    }

    protected function constructSessionVariableName($key)
    {
        return implode('_', array('fb',
            $this->getAppId(),
            $key));
    }
}
