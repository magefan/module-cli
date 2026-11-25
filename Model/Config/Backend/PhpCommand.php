<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

declare(strict_types=1);

namespace Magefan\Cli\Model\Config\Backend;

use Magento\Framework\App\Config\Value;
use Magento\Framework\Exception\LocalizedException;

/**
 * Validate PHP Command before save.
 *
 * The value is prepended to every executed command, so it may contain only a path
 * to the PHP binary and safe "-d" options, e.g. /usr/bin/php8.4 -dmemory_limit=-1
 */
class PhpCommand extends Value
{
    /**
     * Allowed path to PHP binary: letters, digits and _ . / -
     */
    private const PATH_PATTERN = '#^[A-Za-z0-9_./-]+$#D';

    /**
     * Allowed "-d" option: -dname=value or -d name=value
     */
    private const INI_PATTERN = '#^([a-z0-9_.]+)=([A-Za-z0-9_.:/-]*)$#iD';

    /**
     * PHP ini settings that may be passed with "-d"
     */
    private const ALLOWED_INI = [
        'memory_limit',
        'max_execution_time',
        'display_errors',
        'display_startup_errors',
        'error_reporting',
        'date.timezone',
        'opcache.enable_cli',
        'xdebug.mode',
    ];

    /**
     * Validate PHP Command
     *
     * @return $this
     * @throws LocalizedException
     */
    public function beforeSave()
    {
        $value = trim((string)$this->getValue());
        if ($value !== '' && !$this->isValid($value)) {
            throw new LocalizedException(__(
                'PHP Command can contain only a path to PHP binary and "-d" options, e.g. %1. Allowed "-d" options: %2.',
                '/usr/bin/php8.4 -dmemory_limit=-1',
                implode(', ', self::ALLOWED_INI)
            ));
        }
        $this->setValue($value);

        return parent::beforeSave();
    }

    /**
     * Check PHP Command format
     *
     * @param string $value
     * @return bool
     */
    private function isValid(string $value): bool
    {
        $parts = preg_split('/ +/', $value);
        $path = array_shift($parts);

        if (!preg_match(self::PATH_PATTERN, $path) || $path[0] === '-') {
            return false;
        }

        while ($parts) {
            $option = array_shift($parts);
            if ($option === '-d') {
                $option = (string)array_shift($parts);
            } elseif (strpos($option, '-d') === 0) {
                $option = substr($option, 2);
            } else {
                return false;
            }

            if (!preg_match(self::INI_PATTERN, $option, $matches)
                || !in_array(strtolower($matches[1]), self::ALLOWED_INI, true)
            ) {
                return false;
            }
        }

        return true;
    }
}
