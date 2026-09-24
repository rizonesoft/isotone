---
title: Project Structure Guide
description: Complete overview of Isotone's directory structure, file organization, and component architecture
tags: [project-structure, directories, files, organization, architecture]
category: development
priority: 88
last_updated: 2025-08-25
---

# Isotone Project Structure

*Updated on 2025-08-25 - Reflects separation of development tools from production code*

## Project Structure

```
isotone/
├── CLAUDE.md
├── README.md
├── composer.json
├── config.sample.php
├── docs/
│   ├── api/
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
├── error/
│   ├── README.md
├── install/
│   ├── README.md
│   ├── assets/
│   │   └── css/
├── iso-admin/
│   ├── css/
│   ├── includes/
│   ├── js/
│   ├── lumina/
│   │   ├── README.md
├── iso-api/
│   ├── admin/
├── iso-content/
│   ├── README.md
│   ├── cache/
│   ├── logs/
│   ├── plugins/
│   │   ├── hello-isotone/
│   ├── sessions/
│   ├── temp/
│   ├── themes/
│   │   ├── isotone/
│   │   ├── isotone-default/
│   │   ├── neutron/
│   │   └── quantum/
│   └── uploads/
│   │   └── 2025/
├── iso-core/
│   ├── Commands/
│   ├── Config/
│   ├── Core/
│   ├── Services/
├── iso-development/
│   ├── README.md
│   ├── admin/
│   ├── cache/
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
│   ├── tests/
├── iso-includes/
│   ├── assets/
│   ├── js/
│   │   ├── README.md
│   └── lumina/
│   │   └── modules/
├── server/
│   ├── README.md
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
