# Repository Cleanup: Remove Obsolete Files

## Summary
This maintenance task removes obsolete SQL dump files and unused HTML assets to improve repository organization and reduce clutter.

## Problem
- Two SQL dump files (`draf_db_web_school.sql` and `draf_db_web_school_v2.sql`) in the database directory are no longer needed
- Large HTML files in `public/backend/` appear to be unused template assets
- These files add unnecessary bloat and confusion to the repository

## Solution
1. Remove obsolete SQL dump files from `database/` directory
2. Remove unused HTML template files from `public/backend/` directory
3. Keep essential files like LICENSE and README in public/backend

## Impact
- **Risk**: Very low - these files are not referenced by the application code
- **Benefits**: Cleaner repository, reduced confusion, smaller clone size
- **Rollback**: Files can be restored from git history if needed

## Files to be removed
- `database/draf_db_web_school.sql` (25KB)
- `database/draf_db_web_school_v2.sql` (26KB) 
- `public/backend/*.html` files (multiple large HTML files)

## Files to keep
- `public/backend/LICENSE` (license file)
- `public/backend/README.md` (documentation)
- `public/backend/assets/` directory (if used)
- `public/backend/images/` directory (if used)

## Checklist
- [ ] Verify files are not referenced in code
- [ ] Remove SQL dump files
- [ ] Remove unused HTML files
- [ ] Keep essential documentation and assets
- [ ] Run tests to ensure no functionality is broken
- [ ] Update any documentation that references removed files