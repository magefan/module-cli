<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

declare(strict_types=1);

namespace Magefan\Cli\Block\Adminhtml;

use Magento\Framework\View\Element\Template;
use Magento\Framework\Console\CommandListInterface;
use Magefan\Cli\Model\Config;
use Magento\Framework\AuthorizationInterface;

class Form extends \Magento\Framework\View\Element\Template
{
    /**
     * @var CommandListInterface
     */
    private $commandList;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var AuthorizationInterface
     */
    private $authorization;

    /**
     * @param Template\Context $context
     * @param CommandListInterface $commandList
     * @param Config $config
     * @param AuthorizationInterface $authorization
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        CommandListInterface $commandList,
        Config $config,
        AuthorizationInterface $authorization,
        array $data = []
    ) {
        $this->commandList = $commandList;
        $this->config = $config;
        $this->authorization = $authorization;
        parent::__construct($context, $data);
    }

    /**
     * Preparing global layout
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        $this->pageConfig->getTitle()->set(__('Command Line by Magefan'));
        return parent::_prepareLayout();
    }

    /**
     * Retrieve true if exec function is accessible
     *
     * @return bool
     */
    public function execExist()
    {
        return function_exists('exec');
    }

    /**
     * Retrieve available commands split into most used and other
     *
     * @return array
     */
    public function getMagentoCommands()
    {
        $sortedCommands = [
            'most_used' => [],
            'commands' => []
        ];
        $mostUsedCommands = $this->config->getMostUsedCommands() ?: [];
        $commands = $this->commandList->getCommands();

        if (!$this->authorization->isAllowed('Magefan_Cli::admin')) {
            $commands = $this->config->getNonAdminCommands() ? $this->config->getNonAdminCommands() : [];
        }

        foreach ($commands as $command) {
            $command = is_string($command) ? $command : $command->getName();
            if (in_array($command, $mostUsedCommands)) {
                $sortedCommands['most_used'][] = $command;
            } else {
                $sortedCommands['commands'][] = $command;
            }
        }

        return $sortedCommands;
    }

    /**
     * Retrieve true if module is enabled
     *
     * @return bool
     */
    public function isModuleEnabled()
    {
        return $this->config->isEnabled();
    }
}
