<?php

namespace Excellence\FacebookLogin\Block\System\Config\Form\Field;

use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Config\Block\System\Config\Form\Field as FormField;
use Excellence\FacebookLogin\Helper\Data as DataHelper;
use Magento\Backend\Block\Template\Context;

class Redirect extends FormField
{
   protected $dataHelper;

   public function __construct(
       Context $context,
       DataHelper $dataHelper,
       array $data = []
   ) {
       $this->dataHelper = $dataHelper;
       parent::__construct($context, $data);
   }

   protected function _getElementHtml(AbstractElement $element)
   {
       $html_id     = $element->getHtmlId();
       $redirectUrl = $this->dataHelper->getAuthUrl();
       $redirectUrl = str_replace('index.php/', '', $redirectUrl);
       $html        = '<input style="opacity:1;" readonly id="' . $html_id . '" class="input-text admin__control-text" value="' . $redirectUrl . '" onclick="this.select()" type="text">';

       return $html;
   }
}