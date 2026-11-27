<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

declare(strict_types=1);

namespace Magefan\Cli\Model\Config\Backend;

use Magefan\Cli\Model\PhpCommand as PhpCommandModel;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Value;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;

/**
 * Validate PHP Command before save: only a path to PHP binary is allowed, e.g. /usr/bin/php8.4
 */
class PhpCommand extends Value
{
    /**
     * @var PhpCommandModel
     */
    private $phpCommand;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param ScopeConfigInterface $config
     * @param TypeListInterface $cacheTypeList
     * @param PhpCommandModel $phpCommand
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        ScopeConfigInterface $config,
        TypeListInterface $cacheTypeList,
        PhpCommandModel $phpCommand,
        ?AbstractResource $resource = null,
        ?AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->phpCommand = $phpCommand;
        parent::__construct($context, $registry, $config, $cacheTypeList, $resource, $resourceCollection, $data);
    }

    /**
     * Validate PHP Command
     *
     * @return $this
     * @throws LocalizedException
     */
    public function beforeSave()
    {
        $value = trim((string)$this->getValue());
        if ($value !== '' && !$this->phpCommand->isValidBinary($value)) {
            throw new LocalizedException(__(
                'PHP Command can contain only a path to PHP binary, and its file name must contain "php",'
                    . ' e.g. %1. Options %2 are added automatically.',
                '/usr/bin/php8.4',
                implode(' ', PhpCommandModel::OPTIONS)
            ));
        }
        $this->setValue($value);

        return parent::beforeSave();
    }
}
