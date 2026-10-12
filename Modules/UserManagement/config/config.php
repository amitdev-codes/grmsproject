<?php

return [
    'name' => 'UserManagement',

    'default_roles' => [
        [
            'name' => 'Super Admin',
            'name_st' => 'Mookameli e Moholo',
            'code' => 'SUPER_ADMIN',
            'status' => true,
        ],
        [
            'name' => 'IT Admin',
            'name_st' => 'Mookameli oa IT',
            'code' => 'IT_ADMIN',
            'status' => true,
        ],
        [
            'name' => 'Developer',
            'name_st' => 'Mohlahisi',
            'code' => 'DEVELOPER',
            'status' => true,
        ],
        [
            'name' => 'Director',
            'name_st' => 'Motsamaisi',
            'code' => 'DIRECTOR',
            'status' => true,
        ],
        [
            'name' => 'Division Director',
            'name_st' => 'Motsamaisi oa Lefapha',
            'code' => 'DIVISION_DIRECTOR',
            'status' => true,
        ],
        [
            'name' => 'Section Manager',
            'name_st' => 'Mookameli oa Karolo',
            'code' => 'SECTION_MANAGER',
            'status' => true,
        ],
        [
            'name' => 'Helpdesk Officer',
            'name_st' => 'Ofisiri ea Thuso',
            'code' => 'HELPDESK_OFFICER',
            'status' => true,
        ],
        [
            'name' => 'Content Editor',
            'name_st' => 'Mohlophisi oa Litaba',
            'code' => 'CONTENT_EDITOR',
            'status' => true,
        ],
        [
            'name' => 'Citizen',
            'name_st' => 'Moahi',
            'code' => 'CITIZEN',
            'status' => true,
        ],
        [
            'name' => 'RD Officer',
            'name_st' => 'Ofisiri ea RD',
            'code' => 'RD_OFFICER',
            'status' => true,
        ],
        [
            'name' => 'Contractor CLO',
            'name_st' => 'Ofisiri ea Likamano le Sechaba',
            'code' => 'CONTRACTOR_CLO',
            'status' => true,
        ],
        [
            'name' => 'GRC Member',
            'name_st' => 'Setho sa GRC',
            'code' => 'GRC_MEMBER',
            'status' => true,
        ],
        [
            'name' => 'Responsible Manager',
            'name_st' => 'Mookameli ea Ikarabellang',
            'code' => 'RESPONSIBLE_MANAGER',
            'status' => true,
        ],
        [
            'name' => 'Investigator',
            'name_st' => 'Mofuputsi',
            'code' => 'INVESTIGATOR',
            'status' => true,
        ],
        [
            'name' => 'Engineer',
            'name_st' => 'Moenjiniere',
            'code' => 'ENGINEER',
            'status' => true,
        ],
        [
            'name' => 'GRC Chair',
            'name_st' => 'Molula-setulo oa GRC',
            'code' => 'GRC_CHAIR',
            'status' => true,
        ],
        [
            'name' => 'RD Manager',
            'name_st' => 'Mookameli oa RD',
            'code' => 'RD_MANAGER',
            'status' => true,
        ],
        [
            'name' => 'Director General',
            'name_st' => 'Motsamaisi-Kakaretso',
            'code' => 'DIRECTOR_GENERAL',
            'status' => true,
        ],
        [
            'name' => 'RD E&S Unit',
            'name_st' => 'Yuniti ea Tikoloho le Sechaba ea RD',
            'code' => 'RD_ES_UNIT',
            'status' => true,
        ],
        [
            'name' => 'External Reviewer',
            'name_st' => 'Mohlahlobi oa Kantle',
            'code' => 'EXTERNAL_REVIEWER',
            'status' => true,
        ],
        [
            'name' => 'Executive',
            'name_st' => 'Motsamaisi e Moholo',
            'code' => 'EXECUTIVE',
            'status' => true,
        ],
    ],

    /**
     * Base pattern per resource is view/create/edit/delete/export — but
     * export is only listed where it's actually meaningful (nobody needs to
     * "export" a single role or a single FAQ). Grievances additionally gets
     * workflow-specific verbs (allocate/assign/resolve/close/escalate/
     * view_sensitive) on top of the base CRUD set, because "edit" alone
     * can't express "who is allowed to move a case to Resolved" — that
     * needs its own permission, checked at the specific transition, not
     * inferred from a generic edit right.
     */
    'permissions' => [
        // Grievance Core
        'grievances' => [
            'view', 'view_own', 'view_sensitive',
            'create', 'edit', 'delete', 'export',
            'allocate', 'assign', 'resolve', 'close', 'escalate',
        ],

        // Grievance Related (sub-resources)
        'grievance_categories' => ['view', 'create', 'edit', 'delete', 'export'],
        'grievance_channels' => ['view', 'create', 'edit', 'delete', 'export'],
        'grievance_escalations' => ['view', 'create', 'edit', 'delete', 'export'],
        'grievance_workflow_steps' => ['view', 'create', 'edit', 'delete'],
        'grievance_messages' => ['view', 'create', 'edit', 'delete', 'export'],
        'grievance_status_histories' => ['view', 'export'],
        'grievance_sla_policies' => ['view', 'create', 'edit', 'delete', 'export'],
        'grievance_escalation_rules' => ['view', 'create', 'edit', 'delete', 'export'],

        // Resolutions
        'resolutions' => ['view', 'create', 'edit', 'delete', 'export'],
        'resolution_sign_offs' => ['view', 'create', 'edit', 'delete', 'export'],
        'resolution_approval_actions' => ['view', 'create', 'edit', 'delete', 'export'],

        // Inbound SMS
        'inbound_sms' => ['view', 'export'],

        // Reference Sequences
        'reference_sequences' => ['view'],

        // Master Data
        'districts' => ['view', 'create', 'edit', 'delete', 'export'],
        'divisions' => ['view', 'create', 'edit', 'delete', 'export'],
        'sections' => ['view', 'create', 'edit', 'delete', 'export'],
        'project_types' => ['view', 'create', 'edit', 'delete', 'export'],
        'projects' => ['view', 'create', 'edit', 'delete', 'export'],
        'service_providers' => ['view', 'create', 'edit', 'delete', 'export'],

        // User Management
        'roles' => ['view', 'create', 'edit', 'delete'],
        'users' => ['view', 'create', 'edit', 'delete', 'export'],
        'permissions' => ['view'],

        // Frontend/Setting
        'faqs' => ['view', 'create', 'edit', 'delete'],
        'contacts' => ['view', 'edit', 'delete', 'export'],
        'application_settings' => ['view', 'edit'],

        // Reports & Logs
        'reports' => ['view', 'export'],
        'activity_logs' => ['view', 'export'],
        'audit_logs' => ['view', 'export'],
        'logs' => ['view'],
        'page_view_counters' => ['view'],

        // Notifications
        'notifications' => ['view', 'create', 'edit', 'delete', 'export'],
    ],

    'role_permissions' => [
        'Super Admin' => '*',

        'IT Admin' => [
            'users.*', 'roles.*', 'permissions.*',
            'districts.*', 'divisions.*', 'sections.*',
            'project_types.*', 'projects.*', 'service_providers.*',
            'application_settings.*',
            'grievances.view',
            'grievances.edit',
            'grievance_categories.*', 'grievance_channels.*',
            'grievance_sla_policies.*', 'grievance_escalation_rules.*',
            'grievance_workflow_steps.*',
            'notifications.*',
        ],

        'Developer' => '*',

        'Director' => [
            'grievances.view', 'grievances.view_sensitive', 'grievances.allocate', 'grievances.resolve', 'grievances.close', 'grievances.escalate', 'grievances.export',
            'grievance_categories.view', 'grievance_channels.view',
            'grievance_escalations.view', 'grievance_messages.view',
            'reports.*', 'audit_logs.*',
        ],

        'Division Director' => [
            'grievances.view', 'grievances.allocate', 'grievances.export',
            'grievance_categories.view', 'grievance_channels.view',
            'grievance_messages.view', 'grievance_status_histories.view',
            'grievance_sla_policies.view',
            'reports.view', 'reports.export',
            'divisions.view', 'sections.view',
        ],

        'Section Manager' => [
            'grievances.view', 'grievances.assign', 'grievances.resolve', 'grievances.close', 'grievances.escalate',
            'grievance_categories.view', 'grievance_channels.view',
            'grievance_messages.view', 'grievance_status_histories.view',
            'grievance_escalations.view',
            'resolutions.*', 'resolution_sign_offs.*',
            'sections.view', 'projects.view',
        ],

        'Helpdesk Officer' => [
            'grievances.view', 'grievances.create', 'grievances.delete', 'grievances.resolve',
            'grievance_categories.view', 'grievance_channels.view',
            'grievance_messages.create', 'grievance_messages.view',
            'districts.view', 'divisions.view', 'sections.view',
        ],

        'Content Editor' => [
            'faqs.*', 'contacts.*',
            'application_settings.view',
        ],

        'Citizen' => [
            'grievances.create', 'grievances.view_own',
        ],

        'RD Officer' => [
            'grievances.view', 'grievances.create',
            'grievance_categories.view', 'grievance_channels.view',
        ],

        'Contractor CLO' => [
            'grievances.view', 'grievances.close',
            'grievance_categories.view', 'grievance_channels.view',
        ],

        'GRC Member' => [
            'grievances.view', 'grievances.view_sensitive', 'grievances.resolve',
            'grievance_messages.view', 'grievance_status_histories.view',
        ],

        'Responsible Manager' => [
            'grievances.view', 'grievances.allocate', 'grievances.assign',
            'grievances.escalate', 'divisions.view', 'sections.view',
        ],

        'Investigator' => [
            'grievances.view', 'grievances.resolve', 'grievance_messages.view',
            'grievance_messages.create', 'resolutions.*',
        ],

        'Engineer' => [
            'grievances.view', 'grievances.resolve', 'grievance_messages.view',
            'grievance_messages.create', 'projects.view',
        ],

        'GRC Chair' => [
            'grievances.view', 'grievances.view_sensitive', 'grievances.resolve',
            'reports.view',
        ],

        'RD Manager' => [
            'grievances.view', 'grievances.view_sensitive', 'grievances.resolve',
            'grievances.close', 'grievances.escalate', 'reports.view',
        ],

        'Director General' => [
            'grievances.view', 'grievances.view_sensitive', 'grievances.resolve',
            'grievances.close', 'grievances.escalate', 'reports.*',
        ],

        'RD E&S Unit' => [
            'grievances.view', 'grievances.view_sensitive', 'grievances.escalate',
            'grievance_escalations.view', 'reports.view',
        ],

        'External Reviewer' => [
            'grievances.view', 'grievances.escalate', 'grievance_messages.view',
        ],

        'Executive' => [
            'grievances.view', 'grievances.view_sensitive', 'reports.*',
        ],
    ],
];
