<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

namespace Magefan\Cli\Controller\Adminhtml\Index;

use Magefan\Cli\Model\Config;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Data\Form\FormKey;

class Cli extends \Magento\Backend\App\Action implements HttpPostActionInterface
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
     * @var Config
     */
    private $config;

    /**
     * @var FormKey
     */
    private $formKey;

    /**
     * Constructor
     *
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param \Magento\Framework\Filesystem\DirectoryList $dir
     * @param \Magento\Backend\Model\Auth\Session $authSession
     * @param Config $config
     */
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
        parent::__construct($context);
    }

    /**
     * Execute view action
     *
     * @return \Magento\Framework\Controller\ResultInterface|null
     */
    public function execute()
    {
        try {
            if (!$this->config->isEnabled()) {
                throw new \Exception(
                    __(strrev('.ecafretnI eniL dnammoC > snoisnetxE nafegaM > noitarugifnoC >
                serotS ot etagivan esaelp noisnetxe eht elbane ot ,delbasid si ecafretnI eniL dnammoC nafegaM')),
                    1
                );
            }

            $this->validateUser();

            $command = $this->getRequest()->getParam('command');
            $phpCommand = $this->config->getPhpCommand();

            if (!$this->_authorization->isAllowed('Magefan_Cli::admin')) {
                $command = $this->getNonAdminCommand((string)$command, $phpCommand);
            } elseif ($phpCommand) {
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

    /**
     * Build shell command for users without Magefan_Cli::admin permission
     *
     * The request string is never passed to the shell: it is only matched against
     * the allowed commands, and the command is built from the allowed entry.
     *
     * @param string $command
     * @param string $phpCommand
     * @return string
     * @throws \Exception
     */
    protected function getNonAdminCommand(string $command, string $phpCommand): string
    {
        $noPermission = __('You don\'t have permission to execute this command.');

        if (!preg_match('#^\s*(?:php\s+)?(?:\./)?bin/magento\s+(\S.*?)\s*\z#i', $command, $matches)) {
            throw new \Exception($noPermission, 1);
        }

        $magentoCommand = preg_replace('/\s+/', ' ', $matches[1]);
        $allowedCommands = array_filter(array_map('trim', $this->config->getNonAdminCommands() ?: []));

        if (!in_array($magentoCommand, $allowedCommands, true)) {
            throw new \Exception($noPermission, 1);
        }

        $args = array_map('escapeshellarg', explode(' ', $magentoCommand));

        return ($phpCommand ?: 'php') . ' bin/magento ' . implode(' ', $args);
    }

    /**
     * Validate current user password
     *
     * @return $this
     * @throws UserLockedException
     * @throws \Magento\Framework\Exception\AuthenticationException
     * @throws \Exception
     */
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
