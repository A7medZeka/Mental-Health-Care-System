<?php

/**
 * Interface for audit management operations
 * Implements ISP by focusing only on audit-related operations
 */
interface AdminAuditManagerInterface {
    
    /**
     * Get total count of audit logs
     * @return int
     */
    public function getAuditLogsCount(): int;
    
    /**
     * Retrieve audit logs with pagination support
     * @param int|null $limit
     * @param int|null $offset
     * @return array
     */
    public function getAuditLogs(?int $limit = null, ?int $offset = null): array;
}
