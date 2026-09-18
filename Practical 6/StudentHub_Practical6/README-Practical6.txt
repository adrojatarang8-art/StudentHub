StudentHub - Practical 6
========================

WHAT WAS ADDED
--------------
1. events.json   - 15 event records
2. students.json - 15 student records
3. faqs.json     - 15 FAQ records
4. js/dataService.js - reusable Fetch API + localStorage cache service
5. js/practical6.js  - rendering, search, filter, sorting, pagination and dependent dropdowns
6. events.html - Practical 6 user interface
7. css/style.css - Practical 6 styling added to the existing stylesheet

HOW TO RUN
----------
1. Open this StudentHub_Practical6 folder in VS Code.
2. Install/open the Live Server extension.
3. Right-click index.html or events.html -> Open with Live Server.
4. Open the Events page from the navigation menu.

IMPORTANT
---------
Do not test Fetch API by double-clicking events.html directly.
Use Live Server because external JSON fetching should run through HTTP.

ARRAY METHODS USED
------------------
map()    -> converts JSON records into HTML cards.
filter() -> performs search and category/course filtering.
sort()   -> sorts date, title, name, enrollment, question or category.
slice()  -> displays only 5 records for the current page.
Set       -> creates unique filter dropdown values.

LOADING / ERROR HANDLING
------------------------
- "Loading..." status is shown before fetch completes.
- response.ok is checked before parsing JSON.
- try/catch handles fetch and JSON errors.
- Successful JSON is cached in localStorage.
- If network loading fails, latest cached data is shown when available.
- If network and cache both fail, a clear error message is rendered.

MODULARITY
----------
- dataService.js handles fetching and cache logic.
- practical6.js handles UI state, rendering, controls and pagination.
- JSON data remains separate from JavaScript and HTML.

VIVA SHORT ANSWERS
------------------
Q1. How is JSON fetched, parsed and rendered?
A. fetch() requests the JSON file, response.json() parses it into JavaScript data, and map() creates HTML cards that are inserted using innerHTML.

Q2. Which array methods are used?
A. filter() for search/filter, sort() for sorting, slice() for pagination, and map() for rendering.

Q3. How are loading and error states handled?
A. A loading message is shown before fetch. try/catch handles errors. If fetch fails, localStorage cache is tried; otherwise an error message is shown.

Q4. How is modularity maintained?
A. Fetch/cache code is in dataService.js, UI logic is in practical6.js, and data is stored in separate JSON files.

EXTENSIONS
----------
Intermediate: Country -> State -> City dependent dropdowns.
Advanced: Latest successful JSON responses are cached in localStorage for offline-like fallback.
