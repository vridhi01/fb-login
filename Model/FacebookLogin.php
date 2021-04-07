<?php

namespace Excellence\FacebookLogin\Model;

/**
 * FacebookLogin Model
 *
 * @method \Excellence\FacebookLogin\Model\Resource\Page _getResource()
 * @method \Excellence\FacebookLogin\Model\Resource\Page getResource()
 */
class FacebookLogin extends \Magento\Framework\Model\AbstractModel
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Excellence\FacebookLogin\Model\ResourceModel\FacebookLogin');
    }

}
