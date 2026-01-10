# Reviewer Session Tracking System

## Overview
This system tracks reviewer activity to calculate:
1. **Average Review Time** - Average time reviewers spend on the system
2. **Current Streak** - Consecutive days of reviewing questions

## Database Structure

### reviewer_sessions table
- `session_id` - Primary key
- `reviewer_ID` - Foreign key to reviewers table
- `login_at` - Timestamp when reviewer logs in
- `logout_at` - Timestamp when reviewer logs out (nullable)
- `duration_minutes` - Calculated session duration in minutes

## How to Use

### 1. Track Login
When a reviewer logs in, create a new session:

```php
use App\Models\ReviewerSession;
use Illuminate\Support\Facades\Auth;

// After successful login
ReviewerSession::create([
    'reviewer_ID' => Auth::guard('reviewer')->id(),
    'login_at' => now()
]);
```

### 2. Track Logout
When a reviewer logs out, end the session:

```php
use App\Models\ReviewerSession;
use Illuminate\Support\Facades\Auth;

// Before logout
$session = ReviewerSession::where('reviewer_ID', Auth::guard('reviewer')->id())
    ->whereNull('logout_at')
    ->latest('login_at')
    ->first();

if ($session) {
    $session->endSession();
}
```

### 3. Get Statistics
The statistics are automatically calculated in the profile page:

```php
// Average session duration
$avgTime = ReviewerSession::getAverageSessionDuration($reviewerId);

// Current streak (consecutive days with reviews)
$streak = ReviewerSession::getCurrentStreak($reviewerId);
```

## Current Streak Logic

The streak is calculated based on consecutive days where the reviewer has:
- Approved or Rejected questions
- Activity must be within the last 2 days (today or yesterday)
- Breaks if there's more than 1 day gap between activities

## Implementation Steps

### Step 1: Add to Login Controller
In your reviewer login controller (after successful authentication):

```php
ReviewerSession::create([
    'reviewer_ID' => $reviewer->reviewer_ID,
    'login_at' => now()
]);
```

### Step 2: Add to Logout Controller
In your reviewer logout controller (before logout):

```php
$session = ReviewerSession::where('reviewer_ID', $reviewer->reviewer_ID)
    ->whereNull('logout_at')
    ->latest('login_at')
    ->first();

if ($session) {
    $session->endSession();
}
```

### Step 3: Optional - Auto Logout Inactive Sessions
You can create a scheduled job to automatically close sessions that have been open for too long:

```php
// In app/Console/Kernel.php
protected function schedule(Schedule $schedule)
{
    $schedule->call(function () {
        // Close sessions open for more than 12 hours
        $staleSessions = ReviewerSession::whereNull('logout_at')
            ->where('login_at', '<', now()->subHours(12))
            ->get();
            
        foreach ($staleSessions as $session) {
            $session->endSession();
        }
    })->hourly();
}
```

## Testing

To test the system with sample data:

```php
// Create sample sessions for testing
use App\Models\ReviewerSession;
use Carbon\Carbon;

// Session 1: 45 minutes
ReviewerSession::create([
    'reviewer_ID' => 1,
    'login_at' => Carbon::now()->subDays(5),
    'logout_at' => Carbon::now()->subDays(5)->addMinutes(45),
    'duration_minutes' => 45
]);

// Session 2: 30 minutes
ReviewerSession::create([
    'reviewer_ID' => 1,
    'login_at' => Carbon::now()->subDays(4),
    'logout_at' => Carbon::now()->subDays(4)->addMinutes(30),
    'duration_minutes' => 30
]);

// Create review activities for streak testing
use App\Models\Question;

// This would give a 3-day streak if done on consecutive days
```

## Notes

- The average time is displayed in a human-readable format (e.g., "45 min", "1 hr 20 min")
- The streak only counts days where the reviewer actually reviewed questions (Approved/Rejected status)
- If there's no session data, "N/A" will be displayed for average time
- If there's no recent activity (more than 1 day), streak will be 0
