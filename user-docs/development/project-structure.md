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
├── CLAUDE.md
├── README.md
├── composer.json
├── config.sample.php
├── docs/
│   ├── api-reference/
│   ├── assets/
│   │   ├── css/
│   │   ├── images/
│   │   ├── js/
│   ├── automation/
│   ├── configuration/
│   ├── development/
│   ├── getting-started/
│   ├── icons/
│   ├── toni/
│   └── troubleshooting/
├── install/
│   ├── README.md
├── iso-admin/
│   ├── api/
│   ├── css/
│   ├── includes/
│   ├── js/
├── iso-api/
├── iso-automation/
│   ├── README.md
│   ├── cache/
│   ├── config/
│   ├── src/
│   │   ├── Analyzers/
│   │   ├── Commands/
│   │   ├── Core/
│   │   ├── Dashboard/
│   │   ├── Documentation/
│   │   ├── Generators/
│   │   └── Rules/
│   ├── storage/
│   ├── tailwind/
│   │   ├── README.md
│   │   ├── package.json
│   │   └── src/
├── iso-content/
│   ├── README.md
│   ├── plugins/
│   │   ├── hello-isotone/
│   ├── themes/
│   │   ├── isotone/
│   │   ├── isotone-default/
│   │   ├── neutron/
│   │   └── quantum/
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
│   ├── README.md
│   ├── cache/
│   ├── logs/
│   └── temp/
├── user-docs/
│   ├── .kb/
│   ├── .vitepress/
│   │   └── dist/
│   ├── README.md
│   ├── api/
│   ├── api-reference/
│   ├── automation/
│   ├── configuration/
│   ├── development/
│   ├── getting-started/
│   ├── icons/
│   ├── package.json
│   ├── reference/
│   ├── toni/
│   └── troubleshooting/
```
