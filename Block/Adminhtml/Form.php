<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

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
     * Form constructor.
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
    }


    /**
     * Retrieve true if exec funtion is accesible
     * @return bool
     */
    public function execExist()
    {
        return function_exists('exec');
    }

    /**
     * @return array
     */
    public function getMagentoCommands()
    {
        $sortedCommands = [
            'most_used' => [],
            'commands' => []
        ];
        $mostUsedCommands = $this->config->getMostUsedCommands();
        $commands = $this->commandList->getCommands();

        if (!$this->authorization->isAllowed('Magefan_Cli::admin')) {
            $commands = $this->config->getNonAdminCommands() ? $this->config->getNonAdminCommands() : [];
        }

        foreach ($commands as $command) {
            $command = gettype($command) == 'string' ? $command : $command->getName();
            if (in_array($command, $mostUsedCommands)) {
                $sortedCommands['most_used'][] = $command;
            } else {
                $sortedCommands['commands'][] = $command;
            }
        }

        return $sortedCommands;
    }

    /**
     * @return bool
     */
    public function isModuleEnabled()
    {
        return $this->config->isEnabled();
    }
}
