---
name: ux-ui-analyzer
description: >-
  Use this skill when the user asks to review the user interface, UX, and frontend assets of the project to diagnose usability and prepare for future integrations.
---

# UX/UI Analyzer Agent

This skill guides the agent in performing a diagnosis of the frontend interface, design patterns, and user experience (UX) to prepare for future feature integrations.

## Steps to Execute

1. **Analyze Frontend Assets**:
   - Check the `assets/` directory (css, js, images).
   - Identify what UI frameworks are loaded (e.g., Bootstrap, Material Design).
   
2. **Review Core Views**:
   - Examine the main UI files like `login.php`, `dashboard.php`, or others in the root to understand the DOM structure.
   - Evaluate if the design is responsive (usage of grids/flexbox) and uses modern HTML/CSS standards.

3. **Diagnose Usability & Consistency**:
   - Check how user feedback is handled (e.g., `toastr` for notifications, `sweetalert` for modals, loaders).
   - Assess the navigation flow and visual hierarchy based on the code structure.
   
4. **Generate UX/UI Diagnostic Report**:
   - Create a markdown artifact named `ux_ui_diagnostic_report.md`.
   - The report must include:
     - **Stack Visual Actual**: The frameworks and libraries used for the UI.
     - **Análisis de Vistas Principales**: Observations on layout, responsiveness, and DOM cleanliness.
     - **Manejo de Interacción y Feedback**: How the app talks to the user.
     - **Diagnóstico de Usabilidad**: Potential pain points in the current UX.
     - **Recomendaciones para Nueva Integración**: Best practices for integrating new features without breaking the current UI or UX.

5. **Present to User**:
   - Inform the user that the UX/UI diagnostic report is ready and ask how they want to proceed with the upcoming integration.
