<?php
namespace Magefan\Cli\Block\Adminhtml;

use Magento\Backend\Block\Template\Context;
use Magento\Framework\App\ResourceConnection;
use Magento\Backend\Block\Template;

class History extends Template
{
    /** @var ResourceConnection */
    private $resource;

    public function __construct(Context $context, ResourceConnection $resource, array $data = [])
    {
        $this->resource = $resource;
        parent::__construct($context, $data);
    }

    /**
     * Get recent logs
     * @param int $limit
     * @return array
     */
    public function getLogs($limit = 100)
    {
        $connection = $this->resource->getConnection();
        $table = $this->resource->getTableName('magefan_cli_log');
        $select = $connection->select()->from($table)->order('executed_at DESC')->limit((int)$limit);
        return $connection->fetchAll($select);
    }
}
