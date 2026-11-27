<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

namespace Magefan\Cli\Controller\Adminhtml\Index;

use Magefan\Cli\Model\Config;
use Magefan\Cli\Model\MagentoCommand;
use Magefan\Cli\Model\PhpCommand;
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
     * @var MagentoCommand
     */
    private $magentoCommand;

    /**
     * @var PhpCommand
     */
    private $phpCommand;

    /**
     * Constructor
     *
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param \Magento\Framework\Filesystem\DirectoryList $dir
     * @param \Magento\Backend\Model\Auth\Session $authSession
     * @param Config $config
     * @param FormKey $formKey
     * @param MagentoCommand $magentoCommand
     * @param PhpCommand $phpCommand
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\Framework\Filesystem\DirectoryList $dir,
        \Magento\Backend\Model\Auth\Session $authSession,
        Config $config,
        FormKey $formKey,
        MagentoCommand $magentoCommand,
        PhpCommand $phpCommand
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->jsonHelper = $jsonHelper;
        $this->dir = $dir;
        $this->authSession = $authSession;
        $this->config = $config;
        $this->formKey = $formKey;
        $this->magentoCommand = $magentoCommand;
        $this->phpCommand = $phpCommand;
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

            $command = $this->getShellCommand((string)$this->getRequest()->getParam('command'));

            $logFile = $this->dir->getPath('var') . '/mfcli.txt';
            exec(
                'cd ' . escapeshellarg($this->dir->getRoot()) . ' && ' . $command . ' > ' . escapeshellarg($logFile) . ' 2>&1'
            );

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
     * Build shell command from the entered text
     *
     * Only Magento commands can be executed. The entered text is never passed to the shell:
     * the Magento command is taken from it (see MagentoCommand::getWords), every word is escaped
     * and the command is built as "<php binary> <php options> bin/magento <words>".
     *
     * @param string $input
     * @return string
     * @throws \Exception
     */
    protected function getShellCommand(string $input): string
    {
        $words = $this->magentoCommand->getWords($input);
        if (!$words) {
            throw new \Exception(__('Only Magento commands can be executed, e.g. bin/magento cache:flush'), 1);
        }

        if (!$this->_authorization->isAllowed('Magefan_Cli::admin')) {
            $allowedCommands = array_filter(array_map('trim', $this->config->getNonAdminCommands() ?: []));
            if (!in_array($this->magentoCommand->toString($words), $allowedCommands, true)) {
                throw new \Exception(__('You don\'t have permission to execute this command.'), 1);
            }
        }

        if ($this->magentoCommand->isBlocked($words)) {
            throw new \Exception(__('Error: Cannot run this command due to security reasons.'), 1);
        }

        return $this->phpCommand->getCommand() . ' bin/magento ' . $this->magentoCommand->toShellArgs($words);
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
