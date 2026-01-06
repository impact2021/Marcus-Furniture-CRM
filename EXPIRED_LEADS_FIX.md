# Expired Leads Auto-Archive Fix

## Problem Statement
The issue with expired leads not being automatically archived was still present. Users would:
- View the active leads tab
- Reload the page
- Edit an enquiry

But expired leads (with past move dates) would not move to the archived page unless the user manually selected "The job has been done" (Completed status).

## Root Causes

1. **Transient Caching**: The auto-archive logic only ran once per hour due to a transient cache (`hs_crm_last_auto_archive`), which prevented frequent checking of expired leads.

2. **Missing Status**: The "Enquiry received" status was not included in the auto-archive UPDATE query, even though it was considered an active status.

3. **No Query Filtering**: The SELECT query for active leads didn't filter out past move dates, so expired leads would still appear in the active tab even after a page reload.

4. **No Edit-Time Check**: When editing an enquiry, there was no check to auto-archive it if the move_date was changed to a past date.

5. **Duplicate Processing**: The auto_archive_if_past_date function could add duplicate archive notes if called multiple times on already archived enquiries.

## Solution Implemented

### 1. Removed Transient Cache
**File**: `includes/class-hs-crm-database.php` (Lines 249-262)

Removed the hourly transient cache so auto-archive runs on every page load when viewing the active tab. This ensures expired leads are immediately detected and archived.

**Before**:
```php
$last_auto_archive = get_transient('hs_crm_last_auto_archive');
if ($last_auto_archive === false) {
    // Auto-archive logic
    set_transient('hs_crm_last_auto_archive', time(), 3600);
}
```

**After**:
```php
// Get current date in local timezone
$current_date = current_time('Y-m-d');
// Auto-archive logic runs immediately
```

### 2. Added "Enquiry received" Status
**File**: `includes/class-hs-crm-database.php` (Line 258)

Included "Enquiry received" in the list of statuses that can be auto-archived.

**Before**:
```php
WHERE status IN ('First Contact', 'Quote Sent', 'Booking Confirmed', 'Deposit Paid')
```

**After**:
```php
WHERE status IN ('Enquiry received', 'First Contact', 'Quote Sent', 'Booking Confirmed', 'Deposit Paid')
```

### 3. Filter Active Leads Query
**File**: `includes/class-hs-crm-database.php` (Lines 264-272)

Added a date filter to the SELECT query to exclude enquiries with past move dates from the active tab, using a properly prepared statement.

**Before**:
```php
$sql = "SELECT * FROM $table_name WHERE status IN (...) $order_clause";
```

**After**:
```php
$sql = $wpdb->prepare(
    "SELECT * FROM $table_name 
     WHERE status IN ('Enquiry received', 'First Contact', 'Quote Sent', 'Booking Confirmed', 'Deposit Paid')
     AND (move_date IS NULL OR move_date >= %s)
     $order_clause",
    $current_date
);
```

### 4. Auto-Archive on Edit
**File**: `includes/class-hs-crm-database.php` (Lines 593-596)

Added a check to auto-archive enquiries when the move_date is updated during editing.

```php
// If move_date was updated, check if it should be auto-archived
if ($result !== false && isset($data['move_date'])) {
    self::auto_archive_if_past_date($id, $data['move_date']);
}
```

### 5. Prevent Duplicate Processing
**File**: `includes/class-hs-crm-database.php` (Lines 765-773)

Enhanced the `auto_archive_if_past_date()` function to check the enquiry's current status and skip processing if already archived or completed.

```php
// Get the current enquiry to check its status
$enquiry = self::get_enquiry($enquiry_id);
if (!$enquiry) {
    return false;
}

// Don't process if already archived or completed
if (in_array($enquiry->status, array('Archived', 'Dead', 'Completed'))) {
    return false;
}
```

## Impact

With these changes, expired leads will now be automatically moved to the Archived tab:

✅ **On page load**: When viewing the active leads tab  
✅ **On reload**: When refreshing the page  
✅ **On edit**: When editing an enquiry and saving changes  
✅ **Immediately**: No more 1-hour wait due to caching  
✅ **No duplicates**: Prevents duplicate archive notes  
✅ **All statuses**: Includes "Enquiry received" status  

## Testing Recommendations

1. Create a test enquiry with a past move date and verify it appears in Archived, not Active
2. View the active tab and verify no enquiries with past dates appear
3. Edit an existing enquiry and change its move date to the past - verify it gets archived
4. Reload the active tab multiple times - verify the behavior is consistent
5. Check that enquiries without move dates still appear in Active tab

## Files Modified

- `includes/class-hs-crm-database.php`
  - Method: `get_enquiries()` - Lines 249-272
  - Method: `update_enquiry()` - Lines 593-596  
  - Method: `auto_archive_if_past_date()` - Lines 765-773

## Database Impact

The auto-archive UPDATE query now runs on every page load instead of hourly. For typical usage:
- Small databases (<1000 enquiries): Negligible performance impact
- Large databases: Consider monitoring query performance

The query is efficient because:
- It uses an indexed column (status)
- It only updates rows that match the WHERE clause
- The date comparison is optimized with prepared statements
