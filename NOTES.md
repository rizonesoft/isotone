# Development Notes & Reminders

## 📝 Quick Notes
<!-- Add quick notes and thoughts here -->

- [2025-08-22T16:00+00:00] Move tailwind directory to correct location - currently in iso-automation/tailwind but should be organized properly #refactor #tailwind

### Quick Prompts

---

## 🔔 Reminders
<!-- Important things to remember -->

- **Create Salt Generator Page**: Need to create a salt/key generation page on the Isotone site (similar to WordPress's https://api.wordpress.org/secret-key/1.1/salt/) and update all documentation to reference it instead of the WordPress URL.

---

## 💡 Ideas & Future Features
<!-- Ideas for future development -->

---

## 🐛 Known Issues & Bugs
<!-- Track issues that need fixing -->

---

## 📋 TODO List
<!-- Things to do (separate from code tasks) -->

- **Admin Area Styling**: Work on implementing proper dark mode and light mode styling for the admin area. Currently, the admin interface needs:
  - Consistent color scheme for dark mode
  - Proper light mode support with good contrast
  - Smooth transitions between modes
  - Storage of user preference
  - Integration with system theme preferences
  - Update all admin pages to support both modes

---

## 🔗 Useful Links & Resources
<!-- Links to documentation, tools, etc. -->

---

## 📊 Project Decisions & Rationale
<!-- Document why certain decisions were made -->

---

## 🧪 Testing Notes
<!-- Notes about testing, edge cases, etc. -->

---

## 📦 Dependencies & Versions
<!-- Important version requirements or compatibility notes -->

---

## 🚀 Deployment Notes
<!-- Notes about deployment, server config, etc. -->

---

## 📌 Code Snippets & Examples
<!-- Useful code snippets to remember -->

---

## 📅 Meeting Notes & Discussions
<!-- Notes from discussions, decisions made -->

---

## ⚠️ Important Warnings
<!-- Critical things to never forget -->

---

## 🎯 Current Focus
<!-- What we're currently working on -->

### ✅ Admin Interface Implementation (Completed 2025-08-14)
- **Modern Admin Layout** (`iso-admin/includes/admin-layout.php`)
  - Collapsible sidebar with submenus for all sections
  - Top admin bar with quick actions, search (Cmd/Ctrl+K)
  - Notification system with toast messages
  - Dark/light mode toggle (UI ready)
  - Breadcrumb navigation
  - Mobile responsive with Alpine.js
  - localStorage persistence for UI preferences

- **New Dashboard** (`iso-admin/dashboard-new.php`)
  - Stats cards, analytics chart (Chart.js)
  - Quick draft widget, recent activity
  - System health monitoring
  - Using Heroicons for consistent icons

- **Migrated Pages**
  - Users management using new layout
  - User edit/add using new layout

### 🚧 Next Development Phase
- Implement remaining admin pages (posts, pages, media, settings)
- Add real data to dashboard widgets
- Create plugin & theme management interfaces
- Implement full dark mode CSS

---

## 📈 Performance & Optimization Notes
<!-- Performance considerations, benchmarks -->

---

## 🔐 Security Notes
<!-- Security considerations, vulnerabilities to check -->

---

## 📚 Learning & Research
<!-- Things to research or learn more about -->

---

## 🎨 Design & UX Notes
<!-- UI/UX decisions, user feedback -->

---

## 🔄 Workflow & Process Notes
<!-- Development workflow reminders -->

---

## 📝 Miscellaneous
<!-- Everything else -->

---

*Last updated: 2025-08-22 (4:00 PM UTC)*