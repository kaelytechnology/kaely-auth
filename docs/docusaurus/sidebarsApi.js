/** @type {import('@docusaurus/plugin-content-docs').SidebarsConfig} */
const sidebars = {
  apiSidebar: [
    {
      type: 'doc',
      id: 'api/intro',
      label: 'API Overview',
    },
    {
      type: 'category',
      label: '🔐 Authentication',
      items: [
        'api/auth/login',
        'api/auth/logout',
        'api/auth/me',
        'api/auth/refresh',
      ],
    },
    {
      type: 'category',
      label: '👥 Users',
      items: [
        'api/users/index',
        'api/users/create',
        'api/users/show',
        'api/users/update',
        'api/users/delete',
        'api/users/roles',
        'api/users/permissions',
      ],
    },
    {
      type: 'category',
      label: '🎭 Roles',
      items: [
        'api/roles/index',
        'api/roles/create',
        'api/roles/show',
        'api/roles/update',
        'api/roles/delete',
        'api/roles/permissions',
        'api/roles/users',
      ],
    },
    {
      type: 'category',
      label: '🔐 Permissions',
      items: [
        'api/permissions/index',
        'api/permissions/create',
        'api/permissions/show',
        'api/permissions/update',
        'api/permissions/delete',
        'api/permissions/by-module',
      ],
    },
    {
      type: 'category',
      label: '🍽️ Menu',
      items: [
        'api/menu/user',
        'api/menu/all',
        'api/menu/reorder',
        'api/menu/create',
        'api/menu/update',
      ],
    },
    {
      type: 'category',
      label: '🔗 OAuth',
      items: [
        'api/oauth/providers',
        'api/oauth/redirect',
        'api/oauth/callback',
        'api/oauth/stats',
        'api/oauth/sync',
        'api/oauth/disconnect',
        'api/oauth/link',
      ],
    },
    {
      type: 'category',
      label: '🗄️ System',
      items: [
        'api/system/stats',
        'api/system/database-status',
        'api/system/table-stats',
        'api/system/optimize',
        'api/system/indexes',
        'api/system/validate',
      ],
    },
    {
      type: 'category',
      label: '📊 Responses',
      items: [
        'api/responses/standard',
        'api/responses/errors',
        'api/responses/pagination',
        'api/responses/resources',
      ],
    },
  ],
};

module.exports = sidebars; 