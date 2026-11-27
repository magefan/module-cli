<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

declare(strict_types=1);

namespace Magefan\Cli\Controller\Adminhtml\Index;

use Magefan\Cli\Model\Config;
use Magefan\Cli\Model\MagentoCommand;
use Magefan\Cli\Model\PhpCommand;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Data\Form\FormKey;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Shell;

class Cli extends \Magento\Backend\App\Action implements HttpPostActionInterface
{
    public const ADMIN_RESOURCE = 'Magefan_Cli::elements';

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
     * @var Shell
     */
    private $shell;

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
     * @param Shell $shell
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
        PhpCommand $phpCommand,
        Shell $shell
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->jsonHelper = $jsonHelper;
        $this->dir = $dir;
        $this->authSession = $authSession;
        $this->config = $config;
        $this->formKey = $formKey;
        $this->magentoCommand = $magentoCommand;
        $this->phpCommand = $phpCommand;
        $this->shell = $shell;
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
                throw new LocalizedException(
                    __(strrev('.ecafretnI eniL dnammoC > snoisnetxE nafegaM > noitarugifnoC >
                serotS ot etagivan esaelp noisnetxe eht elbane ot ,delbasid si ecafretnI eniL dnammoC nafegaM'))
                );
            }

            $this->validateUser();

            $words = $this->getCommandWords((string)$this->getRequest()->getParam('command'));
            $message = $this->runCommand($words);
            if ($message === '') {
                $message = __('Command not found or error occurred.') . PHP_EOL;
            }
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
     * Get words of the Magento command from the entered text and check that the user can run it
     *
     * Only Magento commands can be executed. The entered text is never passed to the shell:
     * the Magento command is taken from it (see MagentoCommand::getWords).
     *
     * @param string $input
     * @return string[]
     * @throws LocalizedException
     */
    protected function getCommandWords(string $input): array
    {
        $words = $this->magentoCommand->getWords($input);
        if (!$words) {
            throw new LocalizedException(
                __('Only Magento commands can be executed, e.g. bin/magento cache:flush')
            );
        }

        if (!$this->_authorization->isAllowed('Magefan_Cli::admin')) {
            $allowedCommands = array_filter(array_map('trim', $this->config->getNonAdminCommands() ?: []));
            if (!in_array($this->magentoCommand->toString($words), $allowedCommands, true)) {
                throw new LocalizedException(__('You don\'t have permission to execute this command.'));
            }
        }

        if ($this->magentoCommand->isBlocked($words)) {
            throw new LocalizedException(__('Error: Cannot run this command due to security reasons.'));
        }

        return $words;
    }

    /**
     * Run "<php binary> <php options> bin/magento <words>" in Magento root and return its output
     *
     * The PHP binary, Magento root and every word are passed to Shell as arguments, so all of them are escaped.
     * Output of a command that failed (non-zero exit code) is returned too, like in the terminal.
     *
     * @param string[] $words
     * @return string
     * @throws LocalizedException
     */
    protected function runCommand(array $words): string
    {
        $command = 'cd %s && %s ' . implode(' ', PhpCommand::OPTIONS) . ' bin/magento'
            . str_repeat(' %s', count($words));
        $arguments = array_merge([$this->dir->getRoot(), $this->phpCommand->getBinary()], array_values($words));

        try {
            $output = $this->shell->execute($command, $arguments);
        } catch (LocalizedException $e) {
            if (!$e->getPrevious()) {
                throw $e;
            }
            $output = $e->getPrevious()->getMessage();
        }

        return $output === '' ? '' : $output . PHP_EOL;
    }

    /**
     * Validate current user password
     *
     * @return $this
     * @throws \Magento\Framework\Exception\State\UserLockedException
     * @throws \Magento\Framework\Exception\AuthenticationException
     * @throws LocalizedException
     */
    protected function validateUser()
    {
        $password = $this->getRequest()->getParam(
            \Magento\User\Block\Role\Tab\Info::IDENTITY_VERIFICATION_PASSWORD_FIELD
        );
        if (!$password) {
            throw new LocalizedException(__('Please enter your password.'));
        }
        $user = $this->authSession->getUser();
        $user->performIdentityCheck($password);

        return $this;
    }
}
