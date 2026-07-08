<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

namespace Magefan\Cli\Block\Adminhtml;

use Magento\Framework\View\Element\Template;
use Magento\Framework\Console\CommandListInterface;
use Magento\Framework\View\Helper\SecureHtmlRenderer;
use Magento\Framework\Data\Form\FormKey;
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
     * @var \Magento\Framework\App\ResourceConnection
     */
    private $resource;

    /**
     * @var SecureHtmlRenderer
     */
    private $secureHtmlRenderer;

    /**
     * @var FormKey
     */
    private $formKey;

    /**
     * Form constructor.
     * @param Template\Context $context
     * @param CommandListInterface $commandList
     * @param Config $config
     * @param AuthorizationInterface $authorization
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param SecureHtmlRenderer|null $secureHtmlRenderer
     * @param FormKey|null $formKey
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        CommandListInterface $commandList,
        Config $config,
        AuthorizationInterface $authorization,
        \Magento\Framework\App\ResourceConnection $resource,
        ?SecureHtmlRenderer $secureHtmlRenderer = null,
        ?FormKey $formKey = null,
        array $data = []
    ) {
        $this->commandList = $commandList;
        $this->config = $config;
        $this->authorization = $authorization;
        $this->resource = $resource;
        $this->secureHtmlRenderer = $secureHtmlRenderer ?? \Magento\Framework\App\ObjectManager::getInstance()->get(SecureHtmlRenderer::class);
        $this->formKey = $formKey ?? \Magento\Framework\App\ObjectManager::getInstance()->get(FormKey::class);
        parent::__construct($context, $data);
    }

    /**
     * Get form key for AJAX POST (required by Magento admin).
     * @return string
     */
    public function getFormKeyValue()
    {
        return $this->formKey->getFormKey();
    }

    /**
     * Render script tag securely (works without Magefan_Community).
     * @param string $script
     * @return string
     */
    public function renderScript($script)
    {
        return $this->secureHtmlRenderer->renderTag('script', [], $script, false);
    }

    /**
     * Return recent commands history (most recent first)
     * @param int $limit
     * @return array
     */
    public function getCommandHistory($limit = 100)
    {
        try {
            $connection = $this->resource->getConnection();
            $table = $this->resource->getTableName('magefan_cli_log');
            $select = $connection->select()->from($table, ['command'])->order('executed_at DESC')->limit((int)$limit);
            $rows = $connection->fetchAll($select);
            $commands = [];
            foreach ($rows as $r) {
                if (!empty($r['command'])) {
                    $commands[] = $r['command'];
                }
            }
            return $commands;
        } catch (\Exception $e) {
            return [];
        }
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
