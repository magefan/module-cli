<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

declare(strict_types=1);

namespace Magefan\Cli\Model\Config\Backend;

use Magefan\Cli\Model\Config\Source\Commands as CommandsSource;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Value;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;

/**
 * Validate a list of commands before save: only names of existing Magento commands are allowed, without arguments
 *
 * The multiselect offers only command names, but any value can be posted, e.g. "config:set web/secure/base_url x".
 * Without this check such a value would let users with no "Command Line Admin" access run commands with arguments.
 */
class Commands extends Value
{
    /**
     * @var CommandsSource
     */
    private $commandsSource;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param ScopeConfigInterface $config
     * @param TypeListInterface $cacheTypeList
     * @param CommandsSource $commandsSource
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        ScopeConfigInterface $config,
        TypeListInterface $cacheTypeList,
        CommandsSource $commandsSource,
        ?AbstractResource $resource = null,
        ?AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->commandsSource = $commandsSource;
        parent::__construct($context, $registry, $config, $cacheTypeList, $resource, $resourceCollection, $data);
    }

    /**
     * Check that every selected value is the name of an existing Magento command
     *
     * @return $this
     * @throws LocalizedException
     */
    public function beforeSave()
    {
        $value = $this->getValue();
        $commands = is_array($value) ? $value : explode(',', (string)$value);

        $allowedCommands = [];
        foreach ($this->commandsSource->toOptionArray() as $option) {
            $allowedCommands[] = (string)$option['value'];
        }

        foreach ($commands as $command) {
            $command = (string)$command;
            if ($command !== '' && !in_array($command, $allowedCommands, true)) {
                throw new LocalizedException(__(
                    '"%1" is not a Magento command name. Only command names can be selected, without arguments.',
                    $command
                ));
            }
        }

        return parent::beforeSave();
    }
}
