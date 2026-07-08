<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Command Line page via default admin route (admin/admin/mfcli_index/index).
 */

namespace Magefan\Cli\Controller\Adminhtml\MfcliIndex;

use Magefan\Cli\Model\Config;

class Index extends \Magento\Backend\App\Action
{
    const ADMIN_RESOURCE = 'Magefan_Cli::elements';

    protected $resultPageFactory;

    /** @var Config */
    private $config;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        Config $config
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->config = $config;
        parent::__construct($context);
    }

    public function execute()
    {
        $phpCommand = $this->config->getPhpCommand();
        if ($phpCommand && $this->config->isEnabled()) {
            $this->messageManager->addNoticeMessage(__('Commands will be executed by custom PHP binary: ') . $phpCommand);
        }
        return $this->resultPageFactory->create();
    }
}
