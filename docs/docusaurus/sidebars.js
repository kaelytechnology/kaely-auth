/** @type {import('@docusaurus/plugin-content-docs').SidebarsConfig} */
const sidebars = {
  tutorialSidebar: [
    {
      type: 'doc',
      id: 'intro',
      label: 'Introducción',
    },
    {
      type: 'category',
      label: '🚀 Comenzando',
      items: [
        'installation',
        'quick-start',
        'configuration',
        'migration-guide',
      ],
    },
    {
      type: 'category',
      label: '📚 Guías',
      items: [
        'guides/authentication',
        'guides/permissions',
        'guides/roles',
        'guides/menu',
        'guides/multi-database',
        'guides/oauth',
        'guides/middleware',
        'guides/blade-directives',
      ],
    },
    {
      type: 'category',
      label: '⚙️ Configuración',
      items: [
        'config/environment-variables',
        'config/database',
        'config/oauth-providers',
        'config/cache',
        'config/api',
      ],
    },
    {
      type: 'category',
      label: '🛠️ Comandos',
      items: [
        'commands/installation',
        'commands/database',
        'commands/oauth',
        'commands/maintenance',
      ],
    },
    {
      type: 'category',
      label: '🔧 Desarrollo',
      items: [
        'development/architecture',
        'development/services',
        'development/models',
        'development/middleware',
        'development/extending',
      ],
    },
    {
      type: 'category',
      label: '🚨 Solución de Problemas',
      items: [
        'troubleshooting/common-issues',
        'troubleshooting/dependencies',
        'troubleshooting/database',
        'troubleshooting/oauth',
        'troubleshooting/debugging',
      ],
    },
    {
      type: 'category',
      label: '📖 Referencia',
      items: [
        'reference/classes',
        'reference/methods',
        'reference/events',
        'reference/changelog',
        'reference/contributing',
      ],
    },
  ],
};

module.exports = sidebars; 