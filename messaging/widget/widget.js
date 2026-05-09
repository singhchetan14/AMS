// Floating chat widget: bubble + popup panel powered by the messaging API.
(function () {
  // Stop early if the widget markup wasn't included on this page.
  const root = document.getElementById('ams-msg-widget');
  if (!root) return;

  // Base URL of the API folder, set on the widget element by widget.php.
  const API = root.dataset.api;
  const $ = (id) => document.getElementById(id);

  // Cache references to all the widget DOM elements.
  const bubble     = $('ams-msg-bubble');
  const panel      = $('ams-msg-panel');
  const closeBtn   = $('ams-msg-close');
  const backBtn    = $('ams-msg-back');
  const title      = $('ams-msg-title');
  const badge      = $('ams-msg-badge');
  const search     = $('ams-msg-search');
  const list       = $('ams-msg-list');
  const viewList   = $('ams-msg-view-list');
  const viewThread = $('ams-msg-view-thread');
  const thread     = $('ams-msg-thread');
  const form       = $('ams-msg-form');
  const input      = $('ams-msg-input');

  // Currently open thread + polling timers.
  let activeUser  = null;
  let listTimer   = null;
  let threadTimer = null;
  let searchTimer = null;

  function escapeHtml(s) {
    return String(s == null ? '' : s).replace(/[&<>"']/g, (c) => ({
      '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'
    }[c]));
  }

  function initials(name, email) {
    const src = (name || email || '?').trim();
    return src.split(/\s+/).map((p) => p[0]).slice(0, 2).join('').toUpperCase();
  }

  async function callApi(url, opts) {
    let res;
    try {
      res = await fetch(url, Object.assign({ credentials: 'same-origin' }, opts || {}));
    } catch (e) {
      console.error('[messaging] network failure for', url, e);
      throw e;
    }
    const text = await res.text();
    let data;
    try { data = JSON.parse(text); }
    catch (e) {
      console.error('[messaging] non-JSON response from', url, '\nstatus:', res.status, '\nbody:', text);
      throw new Error('Invalid JSON from ' + url);
    }
    if (!res.ok || data.error) {
      console.error('[messaging] API error from', url, data);
    }
    return data;
  }

  const get = (url) => callApi(url);
  const post = (url, data) => {
    const fd = new FormData();
    Object.keys(data).forEach((k) => fd.append(k, data[k]));
    return callApi(url, { method: 'POST', body: fd });
  };

  // Show / hide the red unread-count badge on the bubble.
  function showBadge(n) {
    if (n > 0) { badge.hidden = false; badge.textContent = n > 99 ? '99+' : String(n); }
    else { badge.hidden = true; }
  }

  // Pull the current unread count from the server and update the badge.
  async function refreshUnread() {
    try {
      const r = await get(API + 'unread_count.php');
      if (r && r.ok) showBadge(r.count);
    } catch (_) {}
  }

  function renderList(items, emptyMsg) {
    if (!items || !items.length) {
      list.innerHTML = '<div class="ams-msg-empty">' + escapeHtml(emptyMsg) + '</div>';
      return;
    }
    list.innerHTML = items.map((it) => {
      const display = it.full_name || it.email || ('User #' + it.id);
      const snippet = it.last_body ? it.last_body : (it.email || '');
      return (
        '<div class="ams-msg-item" data-id="' + it.id + '" data-name="' + escapeHtml(display) + '">' +
          '<div class="ams-msg-avatar">' + escapeHtml(initials(it.full_name, it.email)) + '</div>' +
          '<div class="ams-msg-meta">' +
            '<div class="ams-msg-name">' + escapeHtml(display) +
              (it.role ? '<span class="ams-msg-role">' + escapeHtml(it.role) + '</span>' : '') +
            '</div>' +
            '<div class="ams-msg-snippet">' + escapeHtml(snippet) + '</div>' +
          '</div>' +
          (it.unread > 0 ? '<span class="ams-msg-unread">' + it.unread + '</span>' : '') +
        '</div>'
      );
    }).join('');

    list.querySelectorAll('.ams-msg-item').forEach((el) => {
      el.addEventListener('click', () => openThread(el.dataset.id, el.dataset.name));
    });
  }

  async function loadConversations() {
    list.innerHTML = '<div class="ams-msg-empty">Loading…</div>';
    try {
      const r = await get(API + 'conversations.php');
      renderList((r && r.conversations) || [], 'No conversations yet. Search to start one.');
    } catch (e) {
      list.innerHTML = '<div class="ams-msg-empty">Couldn’t load conversations. Check the console.</div>';
    }
  }

  async function searchUsers(q) {
    list.innerHTML = '<div class="ams-msg-empty">Searching…</div>';
    try {
      const r = await get(API + 'contacts.php?q=' + encodeURIComponent(q));
      renderList((r && r.results) || [], 'No users found for “' + q + '”.');
    } catch (e) {
      list.innerHTML = '<div class="ams-msg-empty">Search failed. Check the console.</div>';
    }
  }

  async function loadThread() {
    if (!activeUser) return;
    try {
      const r = await get(API + 'fetch.php?with=' + activeUser);
      if (!r || !r.ok) return;
      const me = parseInt(r.me_id, 10);
      const html = (r.messages || []).map((m) => {
        const who = parseInt(m.sender_id, 10) === me ? 'me' : 'them';
        return '<div class="ams-msg-bubble-msg ' + who + '">' + escapeHtml(m.body) + '</div>';
      }).join('');
      thread.innerHTML = html || '<div class="ams-msg-empty">No messages yet — say hi.</div>';
      thread.scrollTop = thread.scrollHeight;
      refreshUnread();
    } catch (_) {
      thread.innerHTML = '<div class="ams-msg-empty">Couldn’t load messages.</div>';
    }
  }

  // Switch the panel from the contact list into a one-on-one chat view.
  function openThread(userId, name) {
    activeUser = userId;
    title.textContent = name;
    backBtn.hidden = false;
    viewList.hidden = true;
    viewThread.hidden = false;
    loadThread();
    // Refresh the thread every 5 seconds so new messages show up automatically.
    if (threadTimer) clearInterval(threadTimer);
    threadTimer = setInterval(loadThread, 5000);
    setTimeout(() => input.focus(), 50);
  }

  function backToList() {
    activeUser = null;
    backBtn.hidden = true;
    title.textContent = 'Messages';
    viewThread.hidden = true;
    viewList.hidden = false;
    if (threadTimer) { clearInterval(threadTimer); threadTimer = null; }
    search.value = '';
    loadConversations();
    refreshUnread();
  }

  function openPanel() {
    panel.hidden = false;
    bubble.style.display = 'none';
    loadConversations();
    setTimeout(() => search.focus(), 50);
    if (listTimer) clearInterval(listTimer);
    listTimer = setInterval(() => {
      if (!activeUser && (search.value.trim() === '')) loadConversations();
      refreshUnread();
    }, 10000);
  }

  function closePanel() {
    panel.hidden = true;
    bubble.style.display = '';
    if (listTimer)   { clearInterval(listTimer);   listTimer = null; }
    if (threadTimer) { clearInterval(threadTimer); threadTimer = null; }
    activeUser = null;
    backBtn.hidden = true;
    title.textContent = 'Messages';
    viewThread.hidden = true;
    viewList.hidden = false;
    search.value = '';
  }

  // ── Wire up events ─────────────────────────────────────────────────
  bubble.addEventListener('click', openPanel);
  closeBtn.addEventListener('click', closePanel);
  backBtn.addEventListener('click', backToList);

  search.addEventListener('input', () => {
    clearTimeout(searchTimer);
    const q = search.value.trim();
    searchTimer = setTimeout(() => {
      if (q === '') loadConversations();
      else searchUsers(q);
    }, 250);
  });

  // Handle the "Send" button on the chat form.
  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    const body = input.value.trim();
    if (!body || !activeUser) return;
    input.value = '';
    try {
      const r = await post(API + 'send.php', { receiver_id: activeUser, body });
      // Reload thread on success; restore the text on failure so the user can retry.
      if (r && r.ok) loadThread();
      else input.value = body;
    } catch (_) {
      input.value = body;
    }
  });

  // Background unread polling so the badge stays fresh even when panel is closed.
  refreshUnread();
  setInterval(refreshUnread, 30000);
})();
