---
name: code-analyzer
description: >-
  Use this skill when the user asks to review the code, understand the data flow, and diagnose how the project works under the hood for future implementations.
---

# Code Analyzer & Diagnostic Agent

This skill guides the agent in performing a deep-dive technical analysis of the project's source code to understand its architecture, data flow, y functionality. The goal is to generate a comprehensive diagnostic report that aids in future implementations.

## Steps to Execute

1. **Identify Entry Points and Routing**:
   - Analyze files like `index.php`, `login.php`, and `dashboard.php` to understand how a user enters the system and how sessions/authentication are handled.
   
2. **Trace the Data Flow and Database Connections**:
   - Locate the database connection logic (e.g., in `classes/` or `Config/PDOConn.php`).
   - Understand how queries are executed (raw SQL vs PDO/prepared statements).
   - Trace how data flows from the frontend (AJAX in `javascripts`) to the backend PHP scripts and into the database.

3. **Analyze Core Functionalities (Carnets and Reports)**:
   - Review the specific files that handle the core business logic: generation of carnets (e.g., `gestionarreportescarnetizacion.php`, `verificarCarnet.php`) and student management (`gestionarusuarios.php`, `gestionarinscripcion.php`).
   - Check how PDF generation is handled.

4. **Evaluate Architecture and Technical Debt**:
   - Assess the codebase structure (is it procedural PHP, MVC, or a custom component-based architecture?).
   - Identify any security risks (e.g., SQL injection vulnerabilities in raw queries, session handling) or deprecated PHP practices.
   - Note the usage of third-party libraries.

5. **Generate the Diagnostic Report**:
   - Create a markdown artifact named `code_diagnostic_report.md` in the workspace or brain directory.
   - The report must include:
     - **Arquitectura General**: High-level overview of how the app is structured.
     - **Flujo de Datos y Autenticación**: How data moves and how security/login works.
     - **Lógica Core (Carnets y Gestión)**: Detailed explanation of how the main features are implemented.
     - **Diagnóstico y Deuda Técnica**: Vulnerabilities, bad practices, or limitations of the current code.
     - **Recomendaciones para Futuras Implementaciones**: Actionable advice for adding new features or modernizing the stack.

6. **Present to User**:
   - Inform the user that the diagnostic report is ready and ask if they want to deep-dive into any specific module or begin planning a new feature based on the findings.
