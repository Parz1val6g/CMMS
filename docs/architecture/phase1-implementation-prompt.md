# Phase 1 Implementation Prompt

Copy and paste this into your next session to execute the refactoring:

---

Execute Phase 1 of the frontend refactoring plan defined in [`documentation/frontend-component-audit.md`](documentation/frontend-component-audit.md).

1. Create [`resources/js/Components/Common/CrudPage.jsx`](../resources/js/Components/Common/CrudPage.jsx) — a generic wrapper that consumes the 13× identical ~38-line boilerplate pattern. It must render `AppLayout` + `Modal` + `DataManager` with all the standard props. Each of the 12 entity Index pages should be reduced to `<CrudPage title={t('...')} ... />` plus breadcrumbs only.

2. Create [`resources/js/hooks/useFocusTrap.js`](../resources/js/hooks/useFocusTrap.js) — extract the focus-trap keyboard handler duplicated across [`Modal.jsx`](../resources/js/Components/Common/Modal.jsx#L106), [`DialogModal.jsx`](../resources/js/Components/Common/DialogModal.jsx#L86), and [`WorkspaceDrawer.jsx`](../resources/js/Components/Drawer/WorkspaceDrawer.jsx#L51). Replace the inline implementations with the hook.

3. Create [`resources/js/hooks/useBodyLock.js`](../resources/js/hooks/useBodyLock.js) — extract the body scroll lock duplicated across the same 3 files. Replace inline implementations with the hook.

4. Move `formatDate()` from [`resources/js/Components/Table/Row.jsx`](../resources/js/Components/Table/Row.jsx#L65) to `resources/js/utils/format.js` (create file). Update Row.jsx import and replace the duplicate in [`resources/js/Features/ServiceOrders/Pages/Index.jsx`](../resources/js/Features/ServiceOrders/Pages/Index.jsx#L8).

5. Delete [`resources/js/Components/DataManager/AdvancedFilterBuilder.jsx`](../resources/js/Components/DataManager/AdvancedFilterBuilder.jsx) (deprecated stub).

Follow all engineering principles: max 2 nesting levels, early returns, aggressive DRY, TypeScript/JSDoc types, no conversational filler, use SEARCH/REPLACE diffs for all edits. Use `@ui-styling` skill where applicable. Validate each step with `sequential-thinking` MCP before writing code.
