<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

declare(strict_types=1);

namespace Magefan\Cli\Model;

/**
 * Extract a clean Magento command from the text entered in the admin command line
 */
class MagentoCommand
{
    /**
     * Text the Magento command starts after
     */
    public const NEEDLE = 'bin/magento';

    /**
     * Characters that end a shell command or start a shell expression
     */
    private const TERMINATORS = ";&|$`<>()\r\n";

    /**
     * Command namespaces nobody can run, e.g. admin:user:create, admin:user:unlock
     */
    private const BLOCKED_NAMESPACES = [
        ['admin', 'user'],
    ];

    /**
     * Get words of the Magento command, e.g. ['indexer:reindex', 'catalog_product_price']
     *
     * Everything before "bin/magento" and everything from the first shell terminator is dropped.
     * Quoted words ("..." or '...') are kept as one word.
     * Returns an empty array if there is no Magento command.
     *
     * @param string $input
     * @return string[]
     */
    public function getWords(string $input): array
    {
        $position = strpos($input, self::NEEDLE);
        if (false === $position) {
            return [];
        }

        $command = substr($input, $position + strlen(self::NEEDLE));
        if ($command !== '' && !preg_match('/^\s/', $command)) {
            /* e.g. "bin/magentoX" / > / e.g. "bin/magentoX" and not started with space */
            return [];
        }

        $command = substr($command, 0, strcspn($command, self::TERMINATORS));

        preg_match_all('/"([^"]*)"|\'([^\']*)\'|(\S+)/', $command, $matches, PREG_SET_ORDER);
        $words = [];
        foreach ($matches as $match) {
            $words[] = (string)end($match);
        }

        return $words;
    }

    /**
     * Get the command name: the first word that is not an option, e.g. "cache:flush" in "-q cache:flush"
     *
     * @param string[] $words
     * @return string
     */
    public function getName(array $words): string
    {
        foreach ($words as $word) {
            if ($word !== '' && $word[0] !== '-') {
                return $word;
            }
        }

        return '';
    }

    /**
     * Check if the command is in a blocked namespace
     *
     * Symfony Console accepts abbreviated and case-insensitive names: every part of the name
     * can be shortened, but the number of parts must be the same ("a:u:c" and "ADMIN:USER:CREATE"
     * run admin:user:create, see Symfony\Component\Console\Application::find).
     * An empty part ("a::c") is blocked too, although Symfony does not run it.
     *
     * @param string[] $words
     * @return bool
     */
    public function isBlocked(array $words): bool
    {
        $parts = explode(':', strtolower($this->getName($words)));

        foreach (self::BLOCKED_NAMESPACES as $namespace) {
            if (count($parts) !== count($namespace) + 1) {
                continue;
            }

            $isBlocked = true;
            foreach ($namespace as $i => $blockedPart) {
                if ($parts[$i] !== '' && 0 !== strpos($blockedPart, $parts[$i])) {
                    $isBlocked = false;
                    break;
                }
            }

            if ($isBlocked) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get command as text, e.g. "indexer:reindex catalog_product_price"
     *
     * @param string[] $words
     * @return string
     */
    public function toString(array $words): string
    {
        return implode(' ', $words);
    }
}
