# Project Context: TIC SCV Education System

This project is a PHP-based web application for an educational/administrative management system. 

## Project Overview
- **Core Technology**: PHP (Vanilla/Custom structure)
- **Database**: MySQL (reference `uybntujx_tic.sql`)
- **Key Features**: Managing users, students, profiles, roles, enrollments, credentials (carnet), and reporting.
- **Frontend Assets**: Managed via `assets`, `bower_components`, `dist`, `fonts`, `javascripts`.

## Guidelines for AI Agents working on this project:
1. **Understand the Architecture**: This is a legacy/traditional PHP application. Many views and logics are in the root directory or separated into `classes`/`component`.
2. **Database interactions**: Ensure SQL queries are secure. Look into `classes/` for existing database wrapper methods before writing raw queries.
3. **Language**: Keep documentation, comments, and variable names in Spanish as established in the current codebase (e.g., `gestionarusuarios.php`, `asignarmodulodocentes.php`).
4. **Avoid modifying third-party libraries**: Do not touch `bower_components` or `plugins` unless explicitly requested.
5. **Logs and Backups**: `error_log` and `.sql` dumps are present in the root. Do not commit these or consider them part of the core source code.
