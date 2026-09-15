---
name: content-reviewer
description: >-
  Use this skill when the user asks to review all content and determine which files or directories are superfluous, extra, or unnecessary. It will create a detailed report for decision-making.
---

# Content Reviewer Agent

This skill guides the agent in reviewing a project's structure to identify superfluous, unused, or unnecessary files and directories, generating a comprehensive report for the user to make cleanup decisions.

## Steps to Execute

1. **Solicitar Contexto Técnico y Real**:
   - Antes de realizar cualquier acción, **debes pedirle al usuario que te proporcione el contexto real y técnico del proyecto**.
   - No inicies la revisión hasta que el usuario te explique o confirme la naturaleza del proyecto, las tecnologías usadas y cualquier detalle técnico relevante.
   - Puedes apoyarte en el archivo `AGENTS.md` (si existe) para tener una base, pero debes solicitar confirmación explícita del usuario.

2. **Analyze Project Structure**:
   - Use the `list_dir` tool or workspace exploration to map out the current directory and its files.
   - Ignore standard package manager directories (e.g., `node_modules`, `vendor`, `bower_components`) unless specifically requested.

2. **Identify Superfluous Candidates**:
   - **Large files**: Look for massive `.pdf`, `.sql`, `.zip`, `.tar.gz`, or log files (e.g., `error_log`) in the project root. These usually belong in database backups, cloud storage, or should be added to `.gitignore`.
   - **Temp/Backup files**: Identify files ending in `.bak`, `~`, or `.tmp`.
   - **Redundant scripts**: Find test scripts or old unused files (e.g., `test.php`, `index_old.php`, `copia de ...`).
   - **Unused dependencies/assets**: Check if certain directories seem obsolete or disconnected from the main application.

3. **Determine Criteria for Deletion**:
   - Classify items based on risk: 
     - *High Confidence of being superfluous* (e.g., `error_log`, `.sql` dumps in root).
     - *Medium Confidence* (e.g., random PDFs, old versions of scripts).
     - *Needs manual review* (e.g., specific PHP scripts that look like duplicates).

4. **Generate the Report**:
   - Create a markdown artifact named `cleanup_report.md` in the workspace or the user's brain directory.
   - The report must contain:
     - **Overview**: Brief summary of the findings.
     - **Superfluous Files/Directories**: A categorized list of files recommended for deletion, including their path, size, and reason for being marked as superfluous.
     - **Actionable Recommendations**: Clear next steps for the user.

5. **Present to User**:
   - Let the user know the report has been created and ask them which files they would like to proceed with deleting. DO NOT delete anything automatically without explicit user approval.
