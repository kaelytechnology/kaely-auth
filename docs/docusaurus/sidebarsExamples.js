/** @type {import('@docusaurus/plugin-content-docs').SidebarsConfig} */
const sidebars = {
  examplesSidebar: [
    {
      type: 'doc',
      id: 'examples/intro',
      label: 'Ejemplos Overview',
    },
    {
      type: 'category',
      label: '🔐 Autenticación',
      items: [
        'examples/auth/basic-login',
        'examples/auth/token-auth',
        'examples/auth/session-auth',
        'examples/auth/oauth-integration',
      ],
    },
    {
      type: 'category',
      label: '👥 Gestión de Usuarios',
      items: [
        'examples/users/controllers',
        'examples/users/middleware',
        'examples/users/validation',
        'examples/users/resources',
      ],
    },
    {
      type: 'category',
      label: '🎭 Gestión de Roles',
      items: [
        'examples/roles/controllers',
        'examples/roles/assignments',
        'examples/roles/permissions',
        'examples/roles/validation',
      ],
    },
    {
      type: 'category',
      label: '🔐 Gestión de Permisos',
      items: [
        'examples/permissions/controllers',
        'examples/permissions/checks',
        'examples/permissions/blade',
        'examples/permissions/middleware',
      ],
    },
    {
      type: 'category',
      label: '🍽️ Menús Dinámicos',
      items: [
        'examples/menu/controllers',
        'examples/menu/blade-components',
        'examples/menu/vue-components',
        'examples/menu/react-components',
      ],
    },
    {
      type: 'category',
      label: '🗄️ Multi-Base de Datos',
      items: [
        'examples/multi-db/services',
        'examples/multi-db/controllers',
        'examples/multi-db/transactions',
        'examples/multi-db/sync',
      ],
    },
    {
      type: 'category',
      label: '🔗 OAuth/Socialite',
      items: [
        'examples/oauth/controllers',
        'examples/oauth/frontend',
        'examples/oauth/providers',
        'examples/oauth/callbacks',
      ],
    },
    {
      type: 'category',
      label: '🎨 Frontend',
      items: [
        'examples/frontend/blade',
        'examples/frontend/vue',
        'examples/frontend/react',
        'examples/frontend/alpine',
      ],
    },
    {
      type: 'category',
      label: '🧪 Testing',
      items: [
        'examples/testing/unit-tests',
        'examples/testing/feature-tests',
        'examples/testing/permission-tests',
        'examples/testing/oauth-tests',
      ],
    },
    {
      type: 'category',
      label: '📊 Monitoreo',
      items: [
        'examples/monitoring/logging',
        'examples/monitoring/metrics',
        'examples/monitoring/alerts',
        'examples/monitoring/debugging',
      ],
    },
    {
      type: 'category',
      label: '🚀 Casos de Uso',
      items: [
        'examples/use-cases/ecommerce',
        'examples/use-cases/crm',
        'examples/use-cases/admin-panel',
        'examples/use-cases/multi-tenant',
      ],
    },
  ],
};

module.exports = sidebars; 