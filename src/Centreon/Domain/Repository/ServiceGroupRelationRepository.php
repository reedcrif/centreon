<?php
namespace Centreon\Domain\Repository;

use Centreon\Infrastructure\CentreonLegacyDB\ServiceEntityRepository;

class ServiceGroupRelationRepository extends ServiceEntityRepository
{

    /**
     * Export
     *
     * @param int[] $serviceList
     * @param array $templateChainList
     * @return array
     */
    public function export(array $serviceList, array $templateChainList = null): array
    {
        // prevent SQL exception
        if (!$serviceList) {
            return [];
        }

        if ($templateChainList) {
            $serviceList = array_merge($serviceList, $templateChainList);
        }

        $ids = implode(',', $serviceList);

        $sql = <<<SQL
SELECT
    t.*
FROM servicegroup_relation AS t
WHERE t.service_service_id IN ({$ids})
GROUP BY t.sgr_id
SQL;

        $stmt = $this->db->prepare($sql);
        $stmt->execute();

        $result = [];

        while ($row = $stmt->fetch()) {
            $result[] = $row;
        }

        return $result;
    }
}
