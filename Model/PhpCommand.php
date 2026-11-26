<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

declare(strict_types=1);

namespace Magefan\Cli\Model;

/**
 * PHP binary used to run Magento commands
 *
 * Only a path to the binary can be configured, PHP options are always added by the module.
 */
class PhpCommand
{
    /**
     * PHP binary used when nothing valid is configured
     */
    public const DEFAULT_BINARY = 'php';

    /**
     * PHP options added to every command
     */
    public const OPTIONS = [
        '-dmemory_limit=-1',
        '-ddisplay_errors=1',
    ];

    /**
     * Allowed path to PHP binary: letters, digits and _ . / -
     */
    private const BINARY_PATTERN = '#^[A-Za-z0-9_./-]+$#D';

    /**
     * @var Config
     */
    private $config;

    /**
     * @param Config $config
     */
    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    /**
     * Check if the value is a valid path to PHP binary, e.g. /usr/bin/php8.4
     *
     * Only letters, digits and _ . / - are allowed, and the file name must contain "php"
     * (php, php8.4, ea-php82), so other programs of the server (sh, python, env) can not be used.
     *
     * @param string $binary
     * @return bool
     */
    public function isValidBinary(string $binary): bool
    {
        return (bool)preg_match(self::BINARY_PATTERN, $binary)
            && $binary[0] !== '-'
            && substr($binary, -1) !== '/'
            && false !== stripos(basename($binary), 'php');
    }

    /**
     * Get PHP Command saved in configuration
     *
     * @return string
     */
    public function getConfiguredBinary(): string
    {
        return trim($this->config->getPhpCommand());
    }

    /**
     * Check if the saved PHP Command is empty or valid
     *
     * An invalid value can be left from an older version that allowed options,
     * e.g. "/usr/bin/php7.2 -dmemory_limit=-1".
     *
     * @return bool
     */
    public function isConfiguredBinaryValid(): bool
    {
        $binary = $this->getConfiguredBinary();
        return $binary === '' || $this->isValidBinary($binary);
    }

    /**
     * Get PHP binary to use: the saved one if it is valid, otherwise the default "php"
     *
     * @return string
     */
    public function getBinary(): string
    {
        $binary = $this->getConfiguredBinary();
        return ($binary !== '' && $this->isValidBinary($binary)) ? $binary : self::DEFAULT_BINARY;
    }

    /**
     * Get PHP binary with options, e.g. /usr/bin/php8.4 -dmemory_limit=-1 -ddisplay_errors=1
     *
     * @return string
     */
    public function getCommand(): string
    {
        return escapeshellarg($this->getBinary()) . ' ' . implode(' ', self::OPTIONS);
    }
}
