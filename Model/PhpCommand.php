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
     * Allowed file name of PHP binary: php with an optional version and prefix, e.g. php, php8.4, php84, ea-php84
     */
    private const BINARY_NAME_PATTERN = '#^([A-Za-z0-9_]+-)?php[0-9.]*$#iD';

    /**
     * "." or ".." part of a path
     */
    private const DOT_SEGMENT_PATTERN = '#(^|/)\.{1,2}(/|$)#';

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
     * Check if the value is a valid PHP binary: a full path, e.g. /usr/bin/php8.4, or a name, e.g. php8.4
     *
     * Only letters, digits and _ . / - are allowed. The file name must be php with an optional version
     * and prefix (php, php8.4, php84, ea-php84), so other programs of the server (sh, python, env) and PHP tools
     * (phpunit, phpcs, php-cs-fixer) can not be used.
     * A path must be absolute and without "." or ".." parts: commands are run from Magento root,
     * so a relative path could point to a file in a writable Magento folder (pub/media, var).
     * A name without a path is looked up in PATH.
     *
     * @param string $binary
     * @return bool
     */
    public function isValidBinary(string $binary): bool
    {
        if (!preg_match(self::BINARY_PATTERN, $binary)) {
            return false;
        }

        $slashPosition = strrpos($binary, '/');
        if (false !== $slashPosition) {
            if ($binary[0] !== '/' || preg_match(self::DOT_SEGMENT_PATTERN, $binary)) {
                return false;
            }
            $name = substr($binary, $slashPosition + 1);
        } else {
            $name = $binary;
        }

        return (bool)preg_match(self::BINARY_NAME_PATTERN, $name);
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
     * Get PHP binary with options to display, e.g. /usr/bin/php8.4 -dmemory_limit=-1 -ddisplay_errors=1
     *
     * The value is not escaped for the shell, pass getBinary() and OPTIONS to \Magento\Framework\Shell instead.
     *
     * @return string
     */
    public function getCommand(): string
    {
        return $this->getBinary() . ' ' . implode(' ', self::OPTIONS);
    }
}
