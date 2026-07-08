<?php
/**
 * Command Line History page via default admin route (admin/admin/mfcli_history/index).
 */

namespace Magefan\Cli\Controller\Adminhtml\MfcliHistory;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class Index extends Action
{
    const ADMIN_RESOURCE = 'Magefan_Cli::elements';

    /** @var PageFactory */
    private $resultPageFactory;

    public function __construct(Context $context, PageFactory $resultPageFactory)
    {
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context);
    }

    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Magefan_Cli::history');
        $resultPage->getConfig()->getTitle()->prepend(__('Command Line History'));
        return $resultPage;
    }
}
