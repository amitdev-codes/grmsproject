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
        'roles' => ['view', 'create', 'edit', 'delete'],
        'users' => ['view', 'create', 'edit', 'delete', 'export'],
        'permissions' => ['view'],

        'grievances' => [
            'view', 'view_own', 'view_sensitive',
            'create', 'edit', 'delete', 'export',
            'allocate', 'assign', 'resolve', 'close', 'escalate',
        ],

        'districts' => ['view', 'create', 'edit', 'delete'],
        'divisions' => ['view', 'create', 'edit', 'delete'],
        'sections' => ['view', 'create', 'edit', 'delete'],

        'settings' => ['view', 'edit'],
        'reports' => ['view', 'export'],
        'audit_logs' => ['view', 'export'],
        'faqs' => ['view', 'create', 'edit', 'delete'],
        'contacts' => ['view', 'edit', 'delete', 'export'], // contact_messages
    ],

    'role_permissions' => [
        'Super Admin' => '*', // also add Gate::before bypass — see earlier snippet

        'IT Admin' => [
            'users.*', 'roles.*', 'permissions.*',
            'districts.*', 'divisions.*', 'sections.*',
            'settings.*',
            'audit_logs.view', 'audit_logs.export',
            'grievances.view',
        ],

        'Director' => [
            'grievances.view', 'grievances.view_sensitive', 'grievances.escalate', 'grievances.export',
            'reports.view', 'reports.export',
        ],

        'Division Director' => [
            'grievances.view', 'grievances.allocate', 'grievances.export',
            'reports.view',
        ],

        'Section Manager' => [
            'grievances.view', 'grievances.assign', 'grievances.resolve', 'grievances.close',
        ],

        'Helpdesk Officer' => [
            'grievances.view', 'grievances.create',
        ],

        'Content Editor' => [
            'faqs.*', 'contacts.view', 'contacts.edit',
        ],

        'Citizen' => [
            'grievances.create', 'grievances.view_own',
        ],
    ],
];

