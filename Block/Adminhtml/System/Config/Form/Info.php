<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

declare(strict_types=1);

namespace Magefan\Cli\Block\Adminhtml\System\Config\Form;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;

class Info extends Field
{
    /**
     * @param AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        return '<div style="padding: 10px; background: #f5f5f5; border: 1px solid #ddd; border-radius: 3px;">' .
            '<strong>Magefan Command Line Interface</strong><br/>' .
            'Version: 1.0.0<br/>' .
            '<a href="https://magefan.com/magento2-extensions" target="_blank">Visit Magefan.com →</a>' .
            '</div>';
    }
}
