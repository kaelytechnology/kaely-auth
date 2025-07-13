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
│   ├── intro.md            # Página de introducción
│   ├── installation.md     # Guía de instalación
│   ├── configuration.md    # Configuración
│   ├── guides/            # Guías específicas
│   ├── config/            # Configuración detallada
│   ├── commands/          # Comandos disponibles
│   ├── development/       # Desarrollo
│   ├── troubleshooting/   # Solución de problemas
│   └── reference/         # Referencia
├── api/                   # Documentación de API
│   ├── intro.md           # Overview de API
│   ├── auth/              # Endpoints de autenticación
│   ├── users/             # Endpoints de usuarios
│   ├── roles/             # Endpoints de roles
│   ├── permissions/       # Endpoints de permisos
│   ├── menu/              # Endpoints de menú
│   ├── oauth/             # Endpoints de OAuth
│   ├── system/            # Endpoints del sistema
│   └── responses/         # Formatos de respuesta
├── examples/              # Ejemplos prácticos
│   ├── intro.md           # Overview de ejemplos
│   ├── auth/              # Ejemplos de autenticación
│   ├── users/             # Ejemplos de usuarios
│   ├── roles/             # Ejemplos de roles
│   ├── permissions/       # Ejemplos de permisos
│   ├── menu/              # Ejemplos de menús
│   ├── multi-db/          # Ejemplos multi-base de datos
│   ├── oauth/             # Ejemplos de OAuth
│   ├── frontend/          # Ejemplos de frontend
│   ├── testing/           # Ejemplos de testing
│   ├── monitoring/        # Ejemplos de monitoreo
│   └── use-cases/         # Casos de uso
├── src/                   # Código fuente
│   ├── css/               # Estilos personalizados
│   └── components/        # Componentes React
├── static/                # Archivos estáticos
│   └── img/               # Imágenes
├── docusaurus.config.js   # Configuración principal
├── sidebars.js            # Sidebar principal
├── sidebarsApi.js         # Sidebar de API
├── sidebarsExamples.js    # Sidebar de ejemplos
└── package.json           # Dependencias
```

## 🌐 Internacionalización

La documentación soporta múltiples idiomas:

- **Español** (por defecto) - `/`
- **English** - `/en/`

### Agregar Nuevos Idiomas

1. Editar `docusaurus.config.js`
2. Agregar el idioma a `i18n.locales`
3. Crear archivos de traducción en `i18n/`

### Traducir Contenido

```bash
npm run write-translations
```

## 🔍 Configuración de Búsqueda

La documentación usa Algolia para búsqueda. Para configurar:

1. Crear cuenta en [Algolia](https://www.algolia.com/)
2. Crear índice para `kaely-auth`
3. Actualizar configuración en `docusaurus.config.js`:

```javascript
algolia: {
  appId: 'YOUR_SEARCH_APP_ID',
  apiKey: 'YOUR_SEARCH_API_KEY',
  indexName: 'kaely-auth',
  contextualSearch: true,
}
```

## 🎨 Personalización

### Estilos CSS

Editar `src/css/custom.css` para personalizar estilos.

### Componentes

Los componentes se pueden personalizar en `src/components/`.

### Temas

Docusaurus soporta temas claro y oscuro automáticamente.

## 📝 Escribir Documentación

### Formato Markdown

La documentación usa Markdown con extensiones MDX:

```markdown
---
id: page-id
title: Título de la Página
sidebar_label: Etiqueta en Sidebar
---

# Título Principal

Contenido de la página...
```

### Componentes React

Puedes usar componentes React en MDX:

```jsx
import Tabs from '@theme/Tabs';
import TabItem from '@theme/TabItem';

<Tabs>
  <TabItem value="php" label="PHP">
    ```php
    // Código PHP aquí
    ```
  </TabItem>
  <TabItem value="bash" label="Bash">
    ```bash
    # Comando bash aquí
    ```
  </TabItem>
</Tabs>
```

### Admonitions

```markdown
:::tip Tip
Información útil aquí.
:::

:::warning Warning
Advertencia importante.
:::

:::danger Danger
Información crítica.
:::

:::info Info
Información adicional.
:::

:::note Note
Nota importante.
:::
```

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