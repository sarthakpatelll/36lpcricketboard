# Fix index.php Error: Undefined $latest and count() on null

## Steps:
- [x] 1. Edit index.php: Replace `count($latest)` with `$latest_count > 0 ? 1 : 0` in line 139 stats card.
- [ ] 2. Test: Reload index.php, confirm no PHP errors.
- [ ] 3. Verify UI: Ensure "Latest Results" card displays correctly with link to results.php.

**Status: Starting step 1**
