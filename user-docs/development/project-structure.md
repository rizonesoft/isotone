---
title: Project Structure Guide
description: Complete overview of Isotone's directory structure, file organization, and component architecture
tags: [project-structure, directories, files, organization, architecture]
category: development
priority: 88
last_updated: 2025-01-20
---

# Isotone Project Structure

*Updated on 2025-08-17 - Reflects Service-Oriented Architecture*

## Project Structure

```
isotone/
├── .claude/
├── .htaccess
├── .windsurf/
│   └── rules/
├── CLAUDE.md
├── README.md
├── composer.json
├── config/
├── config.php
├── config.sample.php
├── install/
├── iso-admin/
│   ├── api/
│   ├── css/
│   ├── includes/
│   ├── js/
├── iso-automation/
│   ├── cache/
│   ├── config/
│   └── src/
│       ├── Analyzers/
│       ├── Commands/
│       ├── Core/
│       ├── Dashboard/
│       ├── Documentation/
│       ├── Generators/
│       └── Rules/
├── iso-content/
│   ├── plugins/
│   │   ├── hello-isotone/
│   ├── themes/
│   │   ├── isotone-default/
│   │   └── neutron/
│   └── uploads/
├── iso-core/
│   ├── Commands/
│   ├── Config/
│   ├── Core/
│   ├── Services/
├── iso-includes/
│   ├── assets/
│   ├── css/
│   ├── js/
│   └── scripts/
├── iso-runtime/
│   ├── cache/
│   ├── logs/
│   └── temp/
├── tailwind-build/
│   └── src/
└── user-docs/
    ├── .vitepress/
    ├── api-reference/
    ├── automation/
    ├── configuration/
    ├── development/
    ├── getting-started/
    ├── toni/
    └── troubleshooting/
```
