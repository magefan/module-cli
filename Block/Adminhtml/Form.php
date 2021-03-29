<?php
/**
 * Copyright © 2017 Ihor Vansach (ihor@magefan.com). All rights reserved.
 * See LICENSE.txt for license details (http://opensource.org/licenses/osl-3.0.php).
 */

namespace Magefan\Cli\Block\Adminhtml;

use Magento\Framework\View\Element\Template;
use Magento\Framework\Console\CommandListInterface;
use Magefan\Cli\Model\Config;

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
     * Form constructor.
     * @param Template\Context $context
     * @param CommandListInterface $commandList
     * @param Config $config
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        CommandListInterface $commandList,
        Config $config,
        array $data = []
    ) {
        $this->commandList = $commandList;
        $this->config = $config;
        parent::__construct($context, $data);
    }

    /**
     * Preparing global layout
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        $this->pageConfig->getTitle()->set(__('Command Line'));
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
        $sortedCommands = [];
        $mostUsedCommands = $this->config->getMostUsedCommands();
        $commands = $this->commandList->getCommands();
        foreach ($commands as $command) {
            if (in_array($command->getName(), $mostUsedCommands)) {
                $sortedCommands['most_used'][] = $command->getName();
            } else {
                $sortedCommands['commands'][] = $command->getName();
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
