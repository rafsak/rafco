<?php

declare(strict_types=1);

function audit_log(?int $userId, string $eventType, string $moduleName, ?string $entityType, ?int $entityId, string $summary, array $eventData = []): void
{
    $sql = 'INSERT INTO audit_logs (user_id, event_type, module_name, entity_type, entity_id, action_summary, event_data, ip_address, user_agent)
            VALUES (:user_id, :event_type, :module_name, :entity_type, :entity_id, :action_summary, :event_data, :ip_address, :user_agent)';

    $query = db()->prepare($sql);
    $query->execute([
        'user_id' => $userId,
        'event_type' => $eventType,
        'module_name' => $moduleName,
        'entity_type' => $entityType,
        'entity_id' => $entityId,
        'action_summary' => $summary,
        'event_data' => $eventData ? json_encode($eventData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : null,
        'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
    ]);
}
