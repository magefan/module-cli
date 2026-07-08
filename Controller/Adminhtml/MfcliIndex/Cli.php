<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * AJAX endpoint for executing CLI commands (used when page is admin/admin/mfcli_index/index).
 */

namespace Magefan\Cli\Controller\Adminhtml\MfcliIndex;

use Magefan\Cli\Model\Config;
use Magento\Framework\Data\Form\FormKey;
use Magento\Framework\App\ResourceConnection;

class Cli extends \Magento\Backend\App\Action
{
    const ADMIN_RESOURCE = 'Magefan_Cli::elements';

    protected $resultPageFactory;
    protected $jsonHelper;
    protected $dir;
    protected $authSession;
    /** @var Config */
    private $config;
    /** @var FormKey */
    private $formKey;
    /** @var ResourceConnection */
    private $resource;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\Framework\Filesystem\DirectoryList $dir,
        \Magento\Backend\Model\Auth\Session $authSession,
        Config $config,
        FormKey $formKey
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->jsonHelper = $jsonHelper;
        $this->dir = $dir;
        $this->authSession = $authSession;
        $this->config = $config;
        $this->formKey = $formKey;
        $this->resource = $context->getObjectManager()->get(ResourceConnection::class);
        parent::__construct($context);
    }

    public function execute()
    {
        try {
            if (!$this->config->isEnabled()) {
                throw new \Exception(
                    __(strrev('.ecafretnI eniL dnammoC > snoisnetxE nafegaM > noitarugifnoC > serotS ot etagivan esaelp noisnetxe eht elbane ot ,delbasid si ecafretnI eniL dnammoC nafegaM')),
                    1
                );
            }

            $this->validateUser();

            $command = $this->getRequest()->getParam('command');
            $phpCommand = $this->config->getPhpCommand();

            if (!$this->_authorization->isAllowed('Magefan_Cli::admin')) {
                $needle = 'bin/magento ';
                $position = stripos($command, $needle);
                $needleLen = strlen($needle);
                $commandStart = $position + $needleLen;
                $magentoCommand = substr($command, $commandStart);

                if ($position === false || !in_array($magentoCommand, $this->config->getNonAdminCommands())) {
                    throw new \Exception(__('You don\'t have permission to execute this command.'), 1);
                }
            }

            if ($phpCommand) {
                if (stripos($command, 'php ') === 0) {
                    $command = str_replace('php ', $phpCommand . ' ', $command);
                } elseif (stripos($command, 'bin/magento') === 0) {
                    $command = $phpCommand . ' ' . $command;
                }
            }

            $blackCommands = ['admin:user'];
            foreach ($blackCommands as $bc) {
                if (strpos($command, $bc) !== false) {
                    throw new \Exception(__('Error: Cannot run this command due to security reasons.'), 1);
                }
            }

            if (strpos($command, 'cd') === 0) {
                throw new \Exception(__('cd command is not supported.'), 1);
            }

            $logFile = $this->dir->getPath('var') . '/mfcli.txt';
            exec($c = 'cd ' . $this->dir->getRoot() . ' && ' . $command . ' > ' . $logFile . ' 2>&1', $a, $b);

            $message = file_get_contents($logFile);
            if (!$message) {
                $message = __('Command not found or error occurred.') . PHP_EOL;
            }
            try {
                $connection = $this->resource->getConnection();
                $tableName = $this->resource->getTableName('magefan_cli_log');
                $userId = null;
                try {
                    $user = $this->authSession->getUser();
                    $userId = $user ? (int)$user->getId() : null;
                } catch (\Exception $e) {
                    $userId = null;
                }

                $connection->insertMultiple($tableName, [
                    ['command' => $command, 'result' => $message, 'user_id' => $userId, 'executed_at' => (new \DateTime())->format('Y-m-d H:i:s')]
                ]);
            } catch (\Exception $e) {
                // ignore
            }

            unlink($logFile);
        } catch (\Exception $e) {
            $message = $e->getMessage() . PHP_EOL;
        }

        $response = [
            'message' => nl2br($message),
            'newFormKey' => $this->formKey->getFormKey()
        ];

        return $this->getResponse()->representJson(
            $this->jsonHelper->jsonEncode($response)
        );
    }

    protected function validateUser()
    {
        $password = $this->getRequest()->getParam(
            \Magento\User\Block\Role\Tab\Info::IDENTITY_VERIFICATION_PASSWORD_FIELD
        );
        if (!$password) {
            throw new \Exception(__('Please enter your password.'));
        }
        $user = $this->authSession->getUser();
        $user->performIdentityCheck($password);

        return $this;
    }
}
