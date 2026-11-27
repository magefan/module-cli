<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

namespace Magefan\Cli\Controller\Adminhtml\Index;

use Magefan\Cli\Model\Config;
use Magefan\Cli\Model\PhpCommand;
use Magento\Framework\App\Action\HttpGetActionInterface;

class Index extends \Magento\Backend\App\Action implements HttpGetActionInterface
{
    const ADMIN_RESOURCE = 'Magefan_Cli::elements';

    protected $resultPageFactory;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var PhpCommand
     */
    private $phpCommand;

    /**
     * Constructor
     *
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param Config $config
     * @param PhpCommand $phpCommand
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        Config $config,
        PhpCommand $phpCommand
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->config = $config;
        $this->phpCommand = $phpCommand;
        parent::__construct($context);
    }

    /**
     * Execute view action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        if ($this->config->isEnabled()) {
            if (!$this->phpCommand->isConfiguredBinaryValid()) {
                $this->messageManager->addWarningMessage(__(
                    'PHP Command "%1" in Stores > Configuration > Magefan Extensions > Command Line Interface'
                        . ' is not valid, "%2" is used instead. Please enter only the path to PHP binary'
                        . ' with "php" in the file name, e.g. /usr/bin/php8.4.',
                    $this->phpCommand->getConfiguredBinary(),
                    PhpCommand::DEFAULT_BINARY
                ));
            } elseif ($this->phpCommand->getConfiguredBinary() !== '') {
                $this->messageManager->addNoticeMessage(
                    __('Commands will be executed by custom PHP binary: %1', $this->phpCommand->getCommand())
                );
            }
        }

        return $this->resultPageFactory->create();
    }
}
