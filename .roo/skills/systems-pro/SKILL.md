---
name: systems-pro
description: |
  Low-level C/C++ systems programming and memory safety.
  - Memory: Strict manual management and NULL checks.
  - Safety: Use bounds-checked functions (snprintf/strncpy).
  - Structure: Centralized resource cleanup (goto cleanup labels).
modeSlugs:
  - web-clone
  - code
---

# Systems Pro

## Instructions

When working with C or C++, prioritize stability and memory safety:

1. **Memory Allocation:** Always check if a pointer is `NULL` immediately after `malloc` or `calloc`.
2. **Bounds Checking:** Avoid unsafe functions like `gets` or `sprintf`. Use `snprintf`, `strncpy`, and `fgets` strictly.
3. **Resource Management:** Follow the Linear Flow rule. Use a centralized `cleanup:` label with `goto` for deallocating memory/file pointers to prevent leaks.
4. **Pointer Hygiene:** Nullify pointers after `free()` to prevent double-free or dangling pointer issues.
5. **Performance:** Choose the correct data structure (linked lists, hash tables) to minimize time complexity while remaining memory-efficient.