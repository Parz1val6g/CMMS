---
name: devops-automation
description: |
  Automation scripting (Bash/Batch/Node.js) and task orchestration.
  - Portability: Write cross-platform compatible scripts.
  - Logging: Structured success/error reporting for CLI tools.
  - Secrets: Use Environment Variables for sensitive data.
modeSlugs:
  - web-clone
  - code
---

# Devops Automation

## Instructions

When creating automation tools or terminal scripts:

1. **Portability:** Ensure Bash scripts are POSIX compliant and Batch scripts handle paths with spaces correctly.
2. **Environment Focus:** Never hardcode paths or API keys. Use `.env` files or system environment variables.
3. **Structured Execution:** Use clear logging for each step (e.g., `[INFO]`, `[ERROR]`, `[SUCCESS]`).
4. **Error Handling:** Always check return codes of executed commands. Exit with non-zero codes on failure to support automation pipelines.
5. **Modular Design:** Build CLI tools that can be easily called by a central assistant (like J.A.R.V.I.S.) or integrated into CI/CD workflows.