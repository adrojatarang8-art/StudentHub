/* Practical 13: Fetch-based CRUD. Never call location.reload() or submit an HTML form. */
'use strict';
(() => {
  const $ = (id) => document.getElementById(id);
  const api = {
    list: 'api/events_list.php', stats: 'api/event_stats.php',
    create: 'api/events_create.php', update: 'api/events_update.php', delete: 'api/events_delete.php'
  };
  const token = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
  let loadedEvents = [];
  let searchTimer;
  let requestController;
  let listVersion = 0;
  let busy = false;

  function message(text, type = 'success') {
    const el = $('message');
    el.hidden = false;
    el.textContent = text;
    el.dataset.type = type;
  }
  function setLoading(active) {
    $('loadingIndicator').hidden = !active;
    if (active) $('apiState').textContent = 'Loading…';
    $('apiState').dataset.loading = String(active);
  }
  async function request(path, options = {}, signal = undefined) {
    const response = await fetch(path, {credentials:'same-origin', cache:'no-store', ...options, signal});
    $('lastRequest').textContent = `${options.method || 'GET'} · HTTP ${response.status}`;
    const mime = response.headers.get('content-type') || '';
    if (!mime.includes('application/json')) {
      throw new Error('API did not return JSON. Check your session or PHP configuration.');
    }
    const body = await response.json();
    if (!response.ok || !body.success) {
      const e = new Error(body.error?.message || `Request failed (${response.status}).`);
      e.status = response.status;
      throw e;
    }
    return body;
  }
  function node(tag, className, value) {
    const el = document.createElement(tag);
    if (className) el.className = className;
    if (value != null) el.textContent = String(value); // textContent avoids XSS from database text
    return el;
  }
  function statusError(error) {
    if (error?.name === 'AbortError') return;
    $('apiState').textContent = 'Error';
    message(error?.message || 'Unexpected error. Check Apache, MySQL and the Network tab.', 'error');
  }
  function formatDate(iso) {
    if (!/^\d{4}-\d{2}-\d{2}$/.test(iso)) return iso;
    return iso;
  }
  function row(event) {
    const tr = node('tr');
    const id = node('td', 'p13-col-id', `#${event.event_id}`);
    tr.append(id);
    const desc = node('td');
    desc.append(node('strong', '', event.title));
    desc.append(node('small', '', event.description));
    tr.append(desc);
    const location = node('td');
    location.append(node('span', '', formatDate(event.event_date)));
    location.append(node('small', '', event.venue));
    tr.append(location);
    const category = node('td');
    category.append(node('span','p13-tag',event.category));
    tr.append(category);
    const actions = node('td');
    const group = node('div', 'p13-row-actions');
    const edit = node('button','p13-edit-btn','Edit');
    edit.type = 'button';
    edit.addEventListener('click', () => beginEdit(event));
    const del = node('button', 'p13-delete-btn', 'Delete');
    del.type = 'button';
    del.addEventListener('click', () => deleteEvent(event));
    group.append(edit,del);
    actions.append(group);
    tr.append(actions);
    return tr;
  }
  function render(events, total) {
    const body = $('eventsBody');
    body.replaceChildren();
    if (!events.length) {
      const tr = node('tr');
      const cell = node('td','p13-empty','No events match the selected search/filter.');
      cell.colSpan=5;
      tr.append(cell);
      body.append(tr);
    } else {
      const frag = document.createDocumentFragment();
      events.forEach((event) => frag.append(row(event)));
      body.append(frag);
    }
    $('filteredEvents').textContent = String(events.length);
    $('resultsBadge').textContent = `${events.length} result(s)`;
    $('resultInfo').textContent = `Displaying ${events.length} of ${total} records (maximum 250 per request).`;
  }
  async function loadList({quiet=false}={}) {
    const version = ++listVersion;
    requestController?.abort();
    requestController = new AbortController();
    setLoading(true);
    const params = new URLSearchParams({q:$('eventSearch').value.trim(),category:$('categoryFilter').value});
    try {
      const data = await request(`${api.list}?${params}`, {}, requestController.signal);
      if (version !== listVersion) return;
      loadedEvents = data.data;
      render(loadedEvents, data.meta.total);
      if (!quiet) $('apiState').textContent='Connected';
    } catch (error) {
      if (version !== listVersion || error.name === 'AbortError') return;
      render([],0);
      statusError(error);
    } finally {
      if (version === listVersion) setLoading(false);
    }
  }
  async function loadStats() {
    try {
      const data = await request(api.stats);
      $('totalEvents').textContent = String(data.data.total);
      $('upcomingEvents').textContent = String(data.data.upcoming);
    } catch (error) { statusError(error); }
  }
  function resetForm() {
    $('eventForm').reset();
    $('eventId').value='';
    $('editorTitle').textContent='Add New Event';
    $('editBadge').hidden=true;
    $('saveBtn').textContent='+ Add Event';
    $('cancelEditBtn').hidden=true;
    $('saveBtn').disabled=false;
  }
  function beginEdit(event) {
    $('eventId').value=event.event_id;
    ['title','event_date','venue','category','description'].forEach((key) => { $(key).value=event[key]; });
    if ($('category').value !== event.category) {
      const option = document.createElement('option');
      option.value=event.category;
      option.textContent=event.category;
      $('category').append(option);
      $('category').value=event.category;
    }
    $('editorTitle').textContent=`Edit Event #${event.event_id}`;
    $('editBadge').hidden=false;
    $('saveBtn').textContent='Save Changes';
    $('cancelEditBtn').hidden=false;
    $('editor').scrollIntoView({behavior:'smooth',block:'start'});
    $('title').focus({preventScroll:true});
  }
  function collectFields() {
    const value = {};
    for (const field of ['title','event_date','venue','category','description']) {
      value[field]=$(field).value.trim();
    }
    return value;
  }
  function validate(data) {
    for (const field of ['title','venue','category','description']) {
      if (data[field].length < 2) throw new Error(`Enter at least 2 characters for ${field}.`);
    }
    if (!/^\d{4}-\d{2}-\d{2}$/.test(data.event_date)) throw new Error('Select a valid event date.');
    return data;
  }
  async function write(path, payload) {
    return request(path, {method:'POST',headers:{'Content-Type':'application/json','X-CSRF-Token':token},body:JSON.stringify(payload)});
  }
  async function submit(event) {
    event.preventDefault();
    if (busy) return;
    let data;
    try { data = validate(collectFields()); }
    catch (error) { message(error.message,'error'); return; }
    const id = $('eventId').value;
    const updating = id !== '';
    if (updating) data.event_id=Number(id);
    busy = true;
    $('saveBtn').disabled=true;
    $('saveBtn').textContent = updating ? 'Updating…' : 'Saving…';
    message('Sending JSON to PHP API…', 'info');
    try {
      const response = await write(updating ? api.update : api.create, data);
      resetForm();
      message(response.message,'success');
      await Promise.all([loadList({quiet:true}),loadStats()]);
    } catch (error) { statusError(error); }
    finally { busy=false; $('saveBtn').disabled=false; if ($('eventId').value==='') $('saveBtn').textContent='+ Add Event'; }
  }
  async function deleteEvent(event) {
    if (busy) return;
    if (!window.confirm(`Delete "${event.title}" (ID ${event.event_id})?\nEvents with registrations cannot be deleted.`)) return;
    busy=true;
    message('Deleting event through JSON API…', 'info');
    try {
      const response = await write(api.delete,{event_id:Number(event.event_id)});
      if (String($('eventId').value)===String(event.event_id)) resetForm();
      message(response.message,'success');
      await Promise.all([loadList({quiet:true}),loadStats()]);
    } catch(error) { statusError(error); }
    finally { busy=false; }
  }
  $('eventForm').addEventListener('submit', submit);
  $('cancelEditBtn').addEventListener('click',resetForm);
  $('newEventBtn').addEventListener('click',()=>{resetForm();$('editor').scrollIntoView({behavior:'smooth'});});
  $('refreshBtn').addEventListener('click',()=>{loadList();loadStats();});
  $('clearBtn').addEventListener('click',()=>{$('eventSearch').value='';$('categoryFilter').value='';loadList();});
  $('categoryFilter').addEventListener('change',()=>loadList());
  $('eventSearch').addEventListener('input',()=>{
    clearTimeout(searchTimer);
    searchTimer=setTimeout(()=>loadList(),300);
  });
  loadList();
  loadStats();
})();
