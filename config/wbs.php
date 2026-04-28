<?php

return [
    'system_roles' => ['owner', 'project_manager', 'project_leader', 'member'],
    'project_roles' => ['project_manager', 'project_leader', 'member'],
    'project_statuses' => ['draft', 'planning', 'ongoing', 'completed', 'archived'],
    'holiday_types' => ['gazetted', 'weekly_off', 'half_day'],
    'wbs_item_types' => ['phase', 'task', 'subtask', 'deliverable'],
    'content_item_types' => ['copy', 'design', 'development', 'qa', 'deployment', 'documentation'],
    'platforms' => ['web', 'ios', 'android', 'backend', 'infra', 'cross_platform'],
    'priority_levels' => ['low', 'medium', 'high', 'critical'],
    'notification_types' => ['daily_task', 'overdue', 'risk_alert', 'summary'],
    'supported_locales' => [
        'en' => 'English',
        'my' => 'Myanmar',
        'ja' => 'Japanese',
    ],
];
