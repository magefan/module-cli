<?php
namespace Magefan\Cli\Cron;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Psr\Log\LoggerInterface;

class Cleanup
{
    const XML_PATH_LOG_LIFETIME = 'mfcli/general/log_lifetime';

    /** @var ResourceConnection */
    private $resource;

    /** @var DateTime */
    private $dateTime;

    /** @var ScopeConfigInterface */
    private $scopeConfig;

    /** @var LoggerInterface */
    private $logger;

    public function __construct(
        ResourceConnection $resource,
        DateTime $dateTime,
        ScopeConfigInterface $scopeConfig,
        LoggerInterface $logger
    ) {
        $this->resource = $resource;
        $this->dateTime = $dateTime;
        $this->scopeConfig = $scopeConfig;
        $this->logger = $logger;
    }

    public function execute()
    {
        try {
            $days = (int)$this->scopeConfig->getValue(self::XML_PATH_LOG_LIFETIME) ?: 30;
            $connection = $this->resource->getConnection();
            $table = $this->resource->getTableName('magefan_cli_log');
            $threshold = date('Y-m-d H:i:s', strtotime('-' . $days . ' days', $this->dateTime->gmtTimestamp()));
            $where = ['executed_at < ?' => $threshold];
            $connection->delete($table, $where);
        } catch (\Exception $e) {
            $this->logger->error('Magefan_Cli cron cleanup error: ' . $e->getMessage());
        }
    }
}
