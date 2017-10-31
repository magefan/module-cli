<?php
/**
 * Copyright © 2017 Ihor Vansach (ihor@magefan.com). All rights reserved.
 * See LICENSE.txt for license details (http://opensource.org/licenses/osl-3.0.php).
 */

namespace Magefan\Cli\Block\Adminhtml;

class Form extends \Magento\Framework\View\Element\Template
{
	/**
     * Preparing global layout
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        $this->pageConfig->getTitle()->set(__('Command Line'));
    }


    /**
     * Retrieve true if exec funtion is accesible
     * @return bool
     */
    public function execExist()
    {
    	return function_exists('exec');
    }
}
