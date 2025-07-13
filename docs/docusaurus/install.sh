#!/bin/bash

# KaelyAuth Documentation Installer
# Script para instalar y configurar Docusaurus para la documentación de KaelyAuth

set -e

echo "🚀 Instalando documentación de KaelyAuth con Docusaurus..."

# Verificar Node.js
if ! command -v node &> /dev/null; then
    echo "❌ Node.js no está instalado. Por favor instala Node.js 18.0 o superior."
    exit 1
fi

NODE_VERSION=$(node -v | cut -d'v' -f2 | cut -d'.' -f1)
if [ "$NODE_VERSION" -lt 18 ]; then
    echo "❌ Node.js versión $NODE_VERSION detectada. Se requiere Node.js 18.0 o superior."
    exit 1
fi

echo "✅ Node.js $(node -v) detectado"

# Verificar npm
if ! command -v npm &> /dev/null; then
    echo "❌ npm no está instalado. Por favor instala npm."
    exit 1
fi

echo "✅ npm $(npm -v) detectado"

# Crear directorio si no existe
if [ ! -d "docusaurus" ]; then
    echo "📁 Creando directorio docusaurus..."
    mkdir -p docusaurus
fi

cd docusaurus

# Verificar si ya está instalado
if [ -f "package.json" ]; then
    echo "📦 Docusaurus ya está instalado. Actualizando dependencias..."
    npm install
else
    echo "📦 Instalando Docusaurus..."
    
    # Crear package.json si no existe
    if [ ! -f "package.json" ]; then
        cat > package.json << 'EOF'
{
  "name": "kaely-auth-docs",
  "version": "0.0.0",
  "private": true,
  "scripts": {
    "docusaurus": "docusaurus",
    "start": "docusaurus start",
    "build": "docusaurus build",
    "swizzle": "docusaurus swizzle",
    "deploy": "docusaurus deploy",
    "clear": "docusaurus clear",
    "serve": "docusaurus serve",
    "write-translations": "docusaurus write-translations",
    "write-heading-ids": "docusaurus write-heading-ids",
    "typecheck": "tsc"
  },
  "dependencies": {
    "@docusaurus/core": "3.1.1",
    "@docusaurus/preset-classic": "3.1.1",
    "@mdx-js/react": "^3.0.0",
    "clsx": "^2.0.0",
    "prism-react-renderer": "^2.3.1",
    "react": "^18.2.0",
    "react-dom": "^18.2.0"
  },
  "devDependencies": {
    "@docusaurus/module-type-aliases": "3.1.1",
    "@docusaurus/types": "3.1.1",
    "@types/react": "^18.2.0",
    "@types/react-dom": "^18.2.0",
    "typescript": "^5.0.4"
  },
  "browserslist": {
    "production": [
      ">0.5%",
      "not dead",
      "not op_mini all"
    ],
    "development": [
      "last 1 chrome version",
      "last 1 firefox version",
      "last 1 safari version"
    ]
  },
  "engines": {
    "node": ">=18.0"
  }
}
EOF
    fi
    
    # Instalar dependencias
    npm install
fi

# Crear estructura de directorios
echo "📁 Creando estructura de directorios..."

mkdir -p docs
mkdir -p api
mkdir -p examples
mkdir -p src/css
mkdir -p src/components
mkdir -p static/img
mkdir -p i18n/en/docusaurus-plugin-content-docs/current

# Crear archivos de configuración si no existen
if [ ! -f "docusaurus.config.js" ]; then
    echo "⚙️ Creando docusaurus.config.js..."
    cat > docusaurus.config.js << 'EOF'
// @ts-check
// Note: type annotations allow type checking and IDEs autocompletion.

const lightCodeTheme = require('prism-react-renderer/themes/github');
const darkCodeTheme = require('prism-react-renderer/themes/dracula');

/** @type {import('@docusaurus/types').Config} */
const config = {
  title: 'KaelyAuth',
  tagline: 'Sistema Avanzado de Autenticación y Autorización para Laravel',
  favicon: 'img/favicon.ico',

  // Set the production url of your site here
  url: 'https://kaely-auth.com',
  // Set the /<baseUrl>/ pathname under which your site is served
  // For GitHub pages deployment, it is often '/<projectName>/'
  baseUrl: '/',

  // GitHub pages deployment config.
  // If you aren't using GitHub pages, you don't need these.
  organizationName: 'kaely', // Usually your GitHub org/user name.
  projectName: 'kaely-auth', // Usually your repo name.

  onBrokenLinks: 'throw',
  onBrokenMarkdownLinks: 'warn',

  // Even if you don't use internalization, you can use this field to set useful
  // metadata like html lang. For example, if your site is Chinese, you may want
  // to replace "en" with "zh-Hans".
  i18n: {
    defaultLocale: 'es',
    locales: ['es', 'en'],
    localeConfigs: {
      es: {
        label: 'Español',
        htmlLang: 'es',
      },
      en: {
        label: 'English',
        htmlLang: 'en',
      },
    },
  },

  presets: [
    [
      'classic',
      /** @type {import('@docusaurus/preset-classic').Options} */
      ({
        docs: {
          sidebarPath: require.resolve('./sidebars.js'),
          // Please change this to your repo.
          // Remove this to remove the "edit this page" links.
          editUrl:
            'https://github.com/kaely/kaely-auth/edit/main/docs/',
          routeBasePath: '/',
        },
        blog: {
          showReadingTime: true,
          // Please change this to your repo.
          // Remove this to remove the "edit this page" links.
          editUrl:
            'https://github.com/kaely/kaely-auth/edit/main/website/blog/',
        },
        theme: {
          customCss: require.resolve('./src/css/custom.css'),
        },
      }),
    ],
  ],

  themeConfig:
    /** @type {import('@docusaurus/preset-classic').ThemeConfig} */
    ({
      // Replace with your project's social card
      image: 'img/kaely-auth-social-card.jpg',
      navbar: {
        title: 'KaelyAuth',
        logo: {
          alt: 'KaelyAuth Logo',
          src: 'img/logo.svg',
        },
        items: [
          {
            type: 'docSidebar',
            sidebarId: 'tutorialSidebar',
            position: 'left',
            label: 'Documentación',
          },
          {
            type: 'docSidebar',
            sidebarId: 'apiSidebar',
            position: 'left',
            label: 'API',
          },
          {
            type: 'docSidebar',
            sidebarId: 'examplesSidebar',
            position: 'left',
            label: 'Ejemplos',
          },
          {
            href: 'https://github.com/kaely/kaely-auth',
            label: 'GitHub',
            position: 'right',
          },
          {
            type: 'localeDropdown',
            position: 'right',
          },
        ],
      },
      footer: {
        style: 'dark',
        links: [
          {
            title: 'Documentación',
            items: [
              {
                label: 'Inicio',
                to: '/',
              },
              {
                label: 'Instalación',
                to: '/docs/installation',
              },
              {
                label: 'Configuración',
                to: '/docs/configuration',
              },
              {
                label: 'API',
                to: '/docs/api',
              },
            ],
          },
          {
            title: 'Comunidad',
            items: [
              {
                label: 'GitHub',
                href: 'https://github.com/kaely/kaely-auth',
              },
              {
                label: 'Issues',
                href: 'https://github.com/kaely/kaely-auth/issues',
              },
              {
                label: 'Discussions',
                href: 'https://github.com/kaely/kaely-auth/discussions',
              },
            ],
          },
          {
            title: 'Más',
            items: [
              {
                label: 'Blog',
                to: '/blog',
              },
              {
                label: 'Changelog',
                to: '/docs/changelog',
              },
              {
                label: 'Contribuir',
                to: '/docs/contributing',
              },
            ],
          },
        ],
        copyright: `Copyright © ${new Date().getFullYear()} KaelyAuth. Built with Docusaurus.`,
      },
      prism: {
        theme: lightCodeTheme,
        darkTheme: darkCodeTheme,
        additionalLanguages: ['php', 'bash', 'json'],
      },
      colorMode: {
        defaultMode: 'light',
        disableSwitch: false,
        respectPrefersColorScheme: true,
      },
    }),

  plugins: [
    [
      '@docusaurus/plugin-content-docs',
      {
        id: 'api',
        path: 'api',
        routeBasePath: 'api',
        sidebarPath: require.resolve('./sidebarsApi.js'),
      },
    ],
    [
      '@docusaurus/plugin-content-docs',
      {
        id: 'examples',
        path: 'examples',
        routeBasePath: 'examples',
        sidebarPath: require.resolve('./sidebarsExamples.js'),
      },
    ],
  ],
};

module.exports = config;
EOF
fi

# Crear archivos de sidebar si no existen
if [ ! -f "sidebars.js" ]; then
    echo "📚 Creando sidebars.js..."
    cat > sidebars.js << 'EOF'
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
EOF
fi

if [ ! -f "sidebarsApi.js" ]; then
    echo "🔗 Creando sidebarsApi.js..."
    cat > sidebarsApi.js << 'EOF'
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
EOF
fi

if [ ! -f "sidebarsExamples.js" ]; then
    echo "💡 Creando sidebarsExamples.js..."
    cat > sidebarsExamples.js << 'EOF'
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
EOF
fi

# Crear archivo CSS personalizado si no existe
if [ ! -f "src/css/custom.css" ]; then
    echo "🎨 Creando custom.css..."
    cat > src/css/custom.css << 'EOF'
/**
 * Any CSS included here will be global. The classic template
 * bundles Infima by default. Infima is a CSS framework designed to
 * work well for content-focused websites.
 */

/* You can override the default Infima variables here. */
:root {
  --ifm-color-primary: #2e8555;
  --ifm-color-primary-dark: #29784c;
  --ifm-color-primary-darker: #277148;
  --ifm-color-primary-darkest: #205d3b;
  --ifm-color-primary-light: #33925d;
  --ifm-color-primary-lighter: #359962;
  --ifm-color-primary-lightest: #3cad6e;
  --ifm-code-font-size: 95%;
  --docusaurus-highlighted-code-line-bg: rgba(0, 0, 0, 0.1);
}

/* For readability concerns, you should choose a lighter palette in dark mode. */
[data-theme='dark'] {
  --ifm-color-primary: #25c2a0;
  --ifm-color-primary-dark: #21af90;
  --ifm-color-primary-darker: #1fa588;
  --ifm-color-primary-darkest: #1a8870;
  --ifm-color-primary-light: #29d5b0;
  --ifm-color-primary-lighter: #32d8b4;
  --ifm-color-primary-lightest: #4fddbf;
  --docusaurus-highlighted-code-line-bg: rgba(0, 0, 0, 0.3);
}

/* Custom styles for KaelyAuth documentation */

/* Hero section styling */
.hero {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  padding: 4rem 0;
}

.hero__title {
  font-size: 3rem;
  font-weight: bold;
  margin-bottom: 1rem;
}

.hero__subtitle {
  font-size: 1.5rem;
  opacity: 0.9;
  margin-bottom: 2rem;
}

/* Feature cards */
.feature-card {
  border: 1px solid var(--ifm-color-emphasis-300);
  border-radius: 8px;
  padding: 1.5rem;
  margin: 1rem 0;
  transition: all 0.3s ease;
}

.feature-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

/* Code blocks */
.prism-code {
  border-radius: 8px;
  font-size: 0.9rem;
}

/* API endpoint styling */
.api-endpoint {
  background: var(--ifm-color-emphasis-100);
  border-radius: 6px;
  padding: 0.5rem 1rem;
  margin: 0.5rem 0;
  font-family: 'Monaco', 'Menlo', 'Ubuntu Mono', monospace;
}

.api-endpoint.get {
  border-left: 4px solid #61dafb;
}

.api-endpoint.post {
  border-left: 4px solid #28a745;
}

.api-endpoint.put {
  border-left: 4px solid #ffc107;
}

.api-endpoint.delete {
  border-left: 4px solid #dc3545;
}

/* Command styling */
.command {
  background: var(--ifm-color-emphasis-100);
  border-radius: 6px;
  padding: 1rem;
  margin: 1rem 0;
  font-family: 'Monaco', 'Menlo', 'Ubuntu Mono', monospace;
}

.command-title {
  font-weight: bold;
  color: var(--ifm-color-primary);
  margin-bottom: 0.5rem;
}

/* Status badges */
.status-badge {
  display: inline-block;
  padding: 0.25rem 0.5rem;
  border-radius: 4px;
  font-size: 0.75rem;
  font-weight: bold;
  text-transform: uppercase;
}

.status-badge.success {
  background: #28a745;
  color: white;
}

.status-badge.warning {
  background: #ffc107;
  color: #212529;
}

.status-badge.error {
  background: #dc3545;
  color: white;
}

.status-badge.info {
  background: #17a2b8;
  color: white;
}

/* Table styling */
table {
  border-collapse: collapse;
  width: 100%;
  margin: 1rem 0;
}

th, td {
  border: 1px solid var(--ifm-color-emphasis-300);
  padding: 0.75rem;
  text-align: left;
}

th {
  background: var(--ifm-color-emphasis-100);
  font-weight: bold;
}

/* Navigation improvements */
.navbar {
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.navbar__brand {
  font-weight: bold;
}

/* Sidebar improvements */
.menu__link {
  border-radius: 4px;
  margin: 0.125rem 0;
}

.menu__link--active {
  background: var(--ifm-color-primary);
  color: white;
}

/* Footer improvements */
.footer {
  background: var(--ifm-color-emphasis-900);
  color: var(--ifm-color-emphasis-100);
}

/* Responsive improvements */
@media (max-width: 768px) {
  .hero__title {
    font-size: 2rem;
  }
  
  .hero__subtitle {
    font-size: 1.25rem;
  }
  
  .feature-card {
    margin: 0.5rem 0;
  }
}

/* Dark mode improvements */
[data-theme='dark'] {
  .feature-card {
    border-color: var(--ifm-color-emphasis-400);
  }
  
  .api-endpoint {
    background: var(--ifm-color-emphasis-200);
  }
  
  .command {
    background: var(--ifm-color-emphasis-200);
  }
}

/* Custom admonition styles */
.admonition {
  border-radius: 8px;
  margin: 1rem 0;
}

.admonition-heading {
  font-weight: bold;
  margin-bottom: 0.5rem;
}

/* Code copy button */
.code-block-wrapper {
  position: relative;
}

.code-block-wrapper button {
  position: absolute;
  top: 0.5rem;
  right: 0.5rem;
  background: var(--ifm-color-emphasis-700);
  color: white;
  border: none;
  border-radius: 4px;
  padding: 0.25rem 0.5rem;
  font-size: 0.75rem;
  cursor: pointer;
  opacity: 0;
  transition: opacity 0.3s ease;
}

.code-block-wrapper:hover button {
  opacity: 1;
}

/* Search improvements */
.aa-DetachedContainer {
  border-radius: 8px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

/* Breadcrumb improvements */
.breadcrumbs {
  margin: 1rem 0;
  font-size: 0.875rem;
}

.breadcrumbs__item {
  color: var(--ifm-color-emphasis-600);
}

.breadcrumbs__item--active {
  color: var(--ifm-color-emphasis-900);
}

/* Pagination improvements */
.pagination-nav {
  margin: 2rem 0;
}

.pagination-nav__link {
  border: 1px solid var(--ifm-color-emphasis-300);
  border-radius: 8px;
  padding: 1rem;
  transition: all 0.3s ease;
}

.pagination-nav__link:hover {
  border-color: var(--ifm-color-primary);
  text-decoration: none;
}

/* TOC improvements */
.table-of-contents {
  border-left: 2px solid var(--ifm-color-emphasis-300);
  padding-left: 1rem;
}

.table-of-contents__link {
  color: var(--ifm-color-emphasis-600);
  text-decoration: none;
  transition: color 0.3s ease;
}

.table-of-contents__link:hover {
  color: var(--ifm-color-primary);
}

.table-of-contents__link--active {
  color: var(--ifm-color-primary);
  font-weight: bold;
}

/* Custom scrollbar */
::-webkit-scrollbar {
  width: 8px;
}

::-webkit-scrollbar-track {
  background: var(--ifm-color-emphasis-100);
}

::-webkit-scrollbar-thumb {
  background: var(--ifm-color-emphasis-400);
  border-radius: 4px;
}

::-webkit-scrollbar-thumb:hover {
  background: var(--ifm-color-emphasis-600);
}

/* Print styles */
@media print {
  .navbar,
  .footer,
  .pagination-nav,
  .table-of-contents {
    display: none;
  }
  
  .main-wrapper {
    margin: 0;
    padding: 0;
  }
}
EOF
fi

# Crear README si no existe
if [ ! -f "README.md" ]; then
    echo "📖 Creando README.md..."
    cat > README.md << 'EOF'
# KaelyAuth Documentation

Esta es la documentación oficial de **KaelyAuth** construida con [Docusaurus](https://docusaurus.io/).

## 🚀 Características

- 📚 **Documentación Completa** - Guías paso a paso y referencias
- 🌐 **Multiidioma** - Español e inglés
- 🔍 **Búsqueda Inteligente** - Con Algolia
- 📱 **Responsive** - Optimizado para móviles
- 🎨 **Tema Moderno** - Diseño limpio y profesional
- ⚡ **Rápido** - Construido con React

## 📦 Instalación

### Prerrequisitos

- **Node.js** 18.0 o superior
- **npm** o **yarn**

### Instalar Dependencias

```bash
npm install
```

### Desarrollo Local

```bash
npm start
```

La documentación estará disponible en `http://localhost:3000`.

### Construir para Producción

```bash
npm run build
```

### Servir Build de Producción

```bash
npm run serve
```

## 📁 Estructura del Proyecto

```
docusaurus/
├── docs/                    # Documentación principal
├── api/                     # Documentación de API
├── examples/                # Ejemplos prácticos
├── src/                     # Código fuente
├── static/                  # Archivos estáticos
├── docusaurus.config.js     # Configuración principal
├── sidebars.js              # Sidebar principal
├── sidebarsApi.js           # Sidebar de API
├── sidebarsExamples.js      # Sidebar de ejemplos
└── package.json             # Dependencias
```

## 🌐 Internacionalización

La documentación soporta múltiples idiomas:

- **Español** (por defecto) - `/`
- **English** - `/en/`

## 🔍 Configuración de Búsqueda

La documentación usa Algolia para búsqueda. Para configurar:

1. Crear cuenta en [Algolia](https://www.algolia.com/)
2. Crear índice para `kaely-auth`
3. Actualizar configuración en `docusaurus.config.js`

## 🚀 Despliegue

### GitHub Pages

```bash
npm run deploy
```

### Netlify

1. Conectar repositorio a Netlify
2. Configurar build command: `npm run build`
3. Configurar publish directory: `build`

### Vercel

1. Conectar repositorio a Vercel
2. Configurar framework preset: `Docusaurus`
3. Desplegar automáticamente

## 🤝 Contribuir

### Reportar Bugs

1. Crear issue en [GitHub](https://github.com/kaely/kaely-auth/issues)
2. Incluir pasos para reproducir
3. Incluir información del sistema

### Sugerir Mejoras

1. Crear discussion en [GitHub](https://github.com/kaely/kaely-auth/discussions)
2. Describir la funcionalidad deseada
3. Proporcionar casos de uso

### Contribuir Código

1. Fork del repositorio
2. Crear rama para feature
3. Hacer cambios
4. Crear pull request

## 📚 Recursos

- [Docusaurus Documentation](https://docusaurus.io/docs)
- [MDX Documentation](https://mdxjs.com/)
- [Algolia DocSearch](https://docsearch.algolia.com/)
- [React Documentation](https://reactjs.org/docs/)

## 📄 Licencia

Este proyecto está bajo la licencia MIT. Ver el archivo [LICENSE](https://github.com/kaely/kaely-auth/blob/main/LICENSE) para más detalles.

---

**¿Necesitas ayuda?** 🤝

- 📖 [Documentación de Docusaurus](https://docusaurus.io/docs)
- 🐛 [Reportar Bug](https://github.com/kaely/kaely-auth/issues)
- 💡 [Sugerir Mejora](https://github.com/kaely/kaely-auth/discussions)
- 📧 [Contacto](mailto:support@kaely-auth.com)
EOF
fi

echo "✅ Instalación completada exitosamente!"

echo ""
echo "🎉 ¡Documentación de KaelyAuth instalada!"
echo ""
echo "📋 Próximos pasos:"
echo "1. Ejecutar: npm start"
echo "2. Abrir: http://localhost:3000"
echo "3. Comenzar a escribir documentación"
echo ""
echo "📚 Recursos útiles:"
echo "- Docusaurus docs: https://docusaurus.io/docs"
echo "- MDX docs: https://mdxjs.com/"
echo "- React docs: https://reactjs.org/docs/"
echo ""
echo "🚀 ¡Disfruta documentando KaelyAuth!"

cd .. 