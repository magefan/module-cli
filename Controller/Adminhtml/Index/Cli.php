<?php
/**
 * Copyright © 2017 Ihor Vansach (ihor@magefan.com). All rights reserved.
 * See LICENSE.txt for license details (http://opensource.org/licenses/osl-3.0.php).
 */

namespace Magefan\Cli\Controller\Adminhtml\Index;

class Cli extends \Magento\Backend\App\Action
{
    const ADMIN_RESOURCE = 'Magefan_Cli::elements';

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $jsonHelper;

    /**
     * @var \Magento\Framework\Filesystem\DirectoryList
     */
    protected $dir;

    /**
     * Backend auth session
     *
     * @var \Magento\Backend\Model\Auth\Session
     */
    protected $authSession;

    /**
     * Constructor
     *
     * @param \Magento\Backend\App\Action\Context  $context
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param \Magento\Framework\Filesystem\DirectoryList $dir
     * @param \Magento\Backend\Model\Auth\Session $authSession
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\Framework\Filesystem\DirectoryList $dir,
        \Magento\Backend\Model\Auth\Session $authSession
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->jsonHelper = $jsonHelper;
        $this->dir = $dir;
        $this->authSession = $authSession;
        parent::__construct($context);
    }

    /**
     * Execute view action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        try {
            $this->validateUser();

            $command = $this->getRequest()->getParam('command');

            $blackCommands = ['admin:user'];
            foreach ($blackCommands as $bc) {
                if (strpos($command, $bc) !== false) {
                    throw new \Exception(__('Error: Cannot run this command due to security reason.'), 1);
                }
            }

            if (strpos($command, 'cd') === 0) {
                throw new \Exception(__('cd command is not supported.'), 1);
            }

            $logFile = $this->dir->getPath('var') . '/mfcli.txt';
            exec($c = 'cd ' . $this->dir->getRoot() . ' && ' . $command . ' > ' . $logFile, $a, $b);
            $message = file_get_contents($logFile);
            if (!$message) {
                $message = __('Command not found or error occurred.' . PHP_EOL);
            }
            unlink($logFile);
        } catch (\Exception $e) {
            $message = $e->getMessage() . PHP_EOL;
        }

        $response = ['message' => nl2br($message)];

        return $this->getResponse()->representJson(
            $this->jsonHelper->jsonEncode($response)
        );
    }

        /**
     * Validate current user password
     *
     * @return $this
     * @throws UserLockedException
     * @throws \Magento\Framework\Exception\AuthenticationException
     */
    protected function validateUser()
    {
        $password = $this->getRequest()->getParam(
            \Magento\User\Block\Role\Tab\Info::IDENTITY_VERIFICATION_PASSWORD_FIELD
        );
        $user = $this->authSession->getUser();
        $user->performIdentityCheck($password);

        return $this;
    }
}
