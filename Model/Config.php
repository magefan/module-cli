<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

declare(strict_types=1);

namespace Magefan\Cli\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class Config
{
    /**
     * @var string
     */
    const XML_PATH_EXTENSION_ENABLED = 'mfcli/general/enabled';

    /**
     * @var string
     */
    const XML_PATH_PHP_COMMAND = 'mfcli/general/php';

    /**
     * @var string
     */
    const XML_PATH_MOST_USED_COMMANDS = 'mfcli/general/commands';

    /**
     * @var string
     */
    const XML_PATH_NON_ADMIN_COMMANDS = 'mfcli/general/non_admin_commands';

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * Config constructor.
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Retrieve true if module is enabled
     *
     * @param null $storeId
     * @return bool
     */
    public function isEnabled($storeId = null): bool
    {
        return (bool)$this->getConfig(
            self::XML_PATH_EXTENSION_ENABLED,
            $storeId
        );
    }

    /**
     * Retrieve PHP command
     *
     * @param null $storeId
     * @return string
     */
    public function getPhpCommand($storeId = null): string
    {
        return (string)$this->getConfig(
            self::XML_PATH_PHP_COMMAND,
            $storeId
        );
    }

    /**
     * @param null $storeId
     * @return array|null
     */
    public function getMostUsedCommands($storeId = null): ?array
    {
        $commands = $this->getConfig(
            self::XML_PATH_MOST_USED_COMMANDS,
            $storeId
        );

        if ($commands) {
            $commands = explode(',', $commands);
        }

        return $commands;
    }

    /**
     * @param null $storeId
     * @return array|null
     */
    public function getNonAdminCommands($storeId = null): ?array
    {
        $commands = $this->getConfig(
            self::XML_PATH_NON_ADMIN_COMMANDS,
            $storeId
        );

        if ($commands) {
            $commands = explode(',', $commands);
        }

        return $commands;
    }

    /**
     * Retrieve store config value
     *
     * @param string $path
     * @param null $storeId
     * @return mixed
     */
    public function getConfig(string $path, $storeId = null)
    {
        return $this->scopeConfig->getValue(
            $path,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }
}
