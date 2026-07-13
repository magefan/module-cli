<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */
declare(strict_types=1);

namespace Magefan\Cli\Plugin\Menu;

use Magento\Backend\Model\Menu\Builder;
use Magento\Backend\Model\Menu;

class MergeCliMenuGroupsPlugin
{
    private const MAIN_GROUP_ID = 'Magefan_Cli::elements3';
    private const DUPLICATE_GROUP_ID = 'Magefan_Cli::history3';
    private const MAIN_ITEM_ID = 'Magefan_Cli::elements2';
    private const HISTORY_ITEM_ID = 'Magefan_Cli::history2';

    public function afterGetResult(Builder $subject, Menu $result)
    {
        try {
            $mainGroup = $this->findItem($result, self::MAIN_GROUP_ID);
            $duplicateGroup = $this->findItem($result, self::DUPLICATE_GROUP_ID);

            if ($mainGroup && $duplicateGroup) {
                $historyItem = $this->findItem($result, self::HISTORY_ITEM_ID);
                if ($historyItem) {
                    $result->move(self::HISTORY_ITEM_ID, self::MAIN_GROUP_ID);
                }
                $result->remove(self::DUPLICATE_GROUP_ID);

                // Явно виставляємо порядок трьох пунктів усередині групи "Cli"
                $mainGroup = $this->findItem($result, self::MAIN_GROUP_ID);
                $configItem = $this->findChildByTitle($mainGroup, 'Configuration');

                $result->move(self::MAIN_ITEM_ID, self::MAIN_GROUP_ID, 10);    // Command Line
                $result->move(self::HISTORY_ITEM_ID, self::MAIN_GROUP_ID, 20); // Command Line History
                if ($configItem) {
                    $result->move($configItem->getId(), self::MAIN_GROUP_ID, 30); // Configuration
                }
            }
        } catch (\Exception $e) {
            // fail silently, never break menu rendering
        }

        return $result;
    }

    private function findItem($menu, string $itemId)
    {
        foreach ($menu as $item) {
            if ($item->getId() === $itemId) {
                return $item;
            }
            if ($item->hasChildren()) {
                $found = $this->findItem($item->getChildren(), $itemId);
                if ($found) {
                    return $found;
                }
            }
        }
        return null;
    }

    private function findChildByTitle($parent, string $title)
    {
        if (!$parent || !$parent->hasChildren()) {
            return null;
        }
        foreach ($parent->getChildren() as $child) {
            if ((string)$child->getTitle() === $title) {
                return $child;
            }
        }
        return null;
    }
}