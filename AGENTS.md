# Repository Guide

## Project

This is a small, page-based PHP/MySQLi library management application for Perpustakaan USTJ. It is organized into `admin/` pages, public `frontend/` pages, and authenticated member pages under `frontend/anggota/`. Shared database setup is in `koneksi.php`; `SimpleXLSX.php` supports spreadsheet imports.

## Working In This Repository

- Preserve the existing PHP page-and-include structure. Avoid introducing a framework, Composer, or a build step unless the task requires it.
- Use the existing `koneksi.php` connection and nearby query patterns. Use prepared statements for new queries that contain user-provided values, and escape output for the relevant HTML context.
- Keep role-specific pages in their existing directories and follow neighboring relative include and asset paths.
- Public frontend pages use Bootstrap and Bootstrap Icons through CDN links in the shared frontend layout. Reuse those conventions rather than adding a separate UI framework.
- Keep user-facing text in Indonesian where appropriate, matching the surrounding pages.
- Treat database schema and credentials as environment-specific; do not invent schema changes or expose connection details.

## Running And Checking

- Run the app locally with XAMPP Apache and MySQL, with the expected database configured for `koneksi.php`.
- There is no Composer/package manifest or automated test suite in the repository. For changed PHP files, run `php -l path/to/file.php` when PHP CLI is available.
- For database-backed behavior, verify the affected workflow in the local XAMPP environment with representative data.
