<?php
/** Practical 13 - protected AJAX events UI. All data operations use Fetch + JSON. */
declare(strict_types=1);
require_once __DIR__ . '/auth.php';
$user = auth_require_role('admin');
$token = auth_csrf();
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <meta name="csrf-token" content="<?=auth_e($token)?>">
  <title>Practical 13 | StudentHub AJAX Event Management</title>
  <link rel="stylesheet" href="css/style.css">
  <link rel="stylesheet" href="css/practical13.css">
  <script src="js/practical13-events.js" defer></script>
</head>
<body class="p10-body p13-body">
  <header class="p10-header p13-header">
    <h1>StudentHub <span class="p13-dot">/</span> AJAX Event Management</h1>
    <nav aria-label="Main navigation">
      <a href="index.html">Home</a>
      <a href="admin.php">P14 Admin Dashboard</a>
      <a href="student_directory.php">Manage Students</a>
      <a href="ajax_events.php" aria-current="page">AJAX CRUD</a>
      <span class="p13-signedin">Signed in: <?=auth_e($user['username'])?> (Admin)</span>
    </nav>
  </header>
  <main class="p13-shell">
    <section class="p13-lead">
      <div>
        <div class="p13-eyebrow">PRACTICAL 13 · PHP JSON API + JAVASCRIPT FETCH</div>
        <h2>Event Management Dashboard</h2>
        <p>List, add, edit, delete and search events asynchronously — without a full page reload.</p>
      </div>
      <div class="p13-actions"><button id="refreshBtn" type="button" class="p13-outline">↻ Refresh List</button>
        <button id="newEventBtn" type="button" class="p13-primary">+ New Event</button></div>
    </section>

    <div id="message" class="p13-message" role="status" aria-live="polite" hidden></div>
    <section class="p13-stats" aria-label="Event statistics">
      <div class="p13-stat"><span>Total Events</span><strong id="totalEvents">—</strong><small>From MySQL</small></div>
      <div class="p13-stat"><span>Upcoming Events</span><strong id="upcomingEvents">—</strong><small>Today onward</small></div>
      <div class="p13-stat"><span>Filtered Results</span><strong id="filteredEvents">—</strong><small>AJAX response</small></div>
      <div class="p13-stat"><span>API Connection</span><strong id="apiState" class="p13-api-state">Waiting</strong><small id="lastRequest">No requests yet</small></div>
    </section>

    <div class="p13-grid">
      <section class="p13-panel p13-editor" id="editor">
        <div class="p13-panel-label">CREATE OR UPDATE</div>
        <div class="p13-section-title"><h3 id="editorTitle">Add New Event</h3><span id="editBadge" class="p13-edit-badge" hidden>Edit mode</span></div>
        <p class="p13-muted">Validated in JavaScript and PHP before saving to MySQL.</p>
        <form id="eventForm" novalidate>
          <input type="hidden" id="eventId" value="">
          <label for="title">Event Title <span>*</span></label>
          <input id="title" name="title" maxlength="150" minlength="2" required placeholder="e.g. API Workshop">
          <div class="p13-two-fields">
            <div><label for="event_date">Event Date <span>*</span></label><input type="date" id="event_date" name="event_date" required></div>
            <div><label for="category">Category <span>*</span></label>
              <select id="category" name="category" required><option value="Technical">Technical</option><option value="Workshop">Workshop</option>
              <option value="Seminar">Seminar</option><option value="Cultural">Cultural</option><option value="Sports">Sports</option>
              <option value="Other">Other</option></select></div>
          </div>
          <label for="venue">Venue <span>*</span></label><input id="venue" name="venue" maxlength="150" minlength="2" required placeholder="e.g. Lab 2">
          <label for="description">Description <span>*</span></label>
          <textarea id="description" name="description" rows="4" maxlength="2000" minlength="2" required placeholder="Describe the event..."></textarea>
          <div class="p13-form-actions">
            <button id="saveBtn" type="submit" class="p13-primary p13-full">+ Add Event</button>
            <button id="cancelEditBtn" type="button" class="p13-outline" hidden>Cancel Edit</button>
          </div>
        </form>
      </section>

      <section class="p13-panel p13-list">
        <div class="p13-section-title p13-results-title"><div><div class="p13-panel-label">ASYNC DATABASE RECORDS</div><h3>All Events</h3></div>
          <span id="resultsBadge" class="p13-count">0 events</span></div>
        <div class="p13-filters">
          <div class="p13-search"><label for="eventSearch">Search Title, Category, Venue (300 ms debounce)</label>
            <input id="eventSearch" type="search" autocomplete="off" placeholder="Search events without reloading..."></div>
          <div class="p13-category"><label for="categoryFilter">Category</label><select id="categoryFilter">
            <option value="">All Categories</option><option>Technical</option><option>Workshop</option><option>Seminar</option>
            <option>Cultural</option><option>Sports</option><option>Other</option></select></div>
          <button id="clearBtn" type="button" class="p13-outline">Reset</button>
        </div>
        <div class="p13-loading" id="loadingIndicator" role="status" aria-live="polite" hidden>
          <span class="p13-spinner" aria-hidden="true"></span> Loading events using Fetch API...
        </div>
        <div class="p13-tablewrap"><table class="p13-table" aria-label="Events from MySQL">
          <thead><tr><th>ID</th><th>Event / Description</th><th>Date / Venue</th><th>Category</th><th>Actions</th></tr></thead>
          <tbody id="eventsBody"><tr><td colspan="5" class="p13-empty">Waiting for API response...</td></tr></tbody>
        </table></div>
        <div class="p13-listfoot"><span id="resultInfo">The list updates without a full page refresh.</span>
        <span class="p13-small-tip">Tip: press F12 → Network → Fetch/XHR to capture API evidence</span></div>
      </section>
    </div>
    <details class="p13-details"><summary>API endpoints &amp; network screenshot reference</summary>
      <div class="p13-endpoint-list"><code>GET api/events_list.php</code><code>GET api/event_stats.php</code>
        <code>POST api/events_create.php</code><code>POST api/events_update.php</code><code>POST api/events_delete.php</code></div>
      <p>GET returns event JSON arrays. POST sends JSON with an X-CSRF-Token header. Successful responses return 200/201; invalid inputs 422; invalid sessions 401; conflicting deletes 409.</p>
    </details>
    <p class="p13-footer">Practical 13 · JSON APIs · Fetch/AJAX CRUD · Prepared MySQLi Queries · 300 ms Debounced Search · Status Codes · No Page Reload</p>
  </main>
  <footer><p>© 2026 StudentHub Portal · Practical 13</p></footer>
</body>
</html>
