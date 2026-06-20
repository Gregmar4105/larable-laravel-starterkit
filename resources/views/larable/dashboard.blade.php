@extends('larable.layout')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/larable.css') }}?v={{ filemtime(public_path('css/larable.css')) }}">
<style>
/* ─── SQL Query Console ────────────────────────────────────────────── */
.db-query-console {
    display: flex;
    flex-direction: column;
    gap: 1rem;
    height: 100%;
    padding: 1.5rem;
}

.db-query-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 0.5rem;
}

.db-query-header h3 {
    font-size: 1.25rem;
    font-weight: 700;
    color: var(--accent);
}

.db-connection-status {
    display: flex;
    align-items: center;
    gap: 0.4rem;
    font-size: 0.75rem;
    color: var(--text-muted);
}

.status-dot {
    width: 8px;
    height: 8px;
    background-color: var(--success);
    border-radius: 50%;
    display: inline-block;
}

.query-editor-container {
    border: 1px solid var(--border);
    border-radius: var(--radius);
    overflow: hidden;
    background: var(--bg-input);
    position: relative;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
}

.query-textarea {
    width: 100%;
    min-height: 180px;
    padding: 1.25rem;
    background: transparent;
    border: none;
    resize: vertical;
    color: var(--text);
    font-family: var(--mono);
    font-size: 0.875rem;
    line-height: 1.6;
    outline: none;
}

.query-actions {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.75rem 1.25rem;
    background: var(--bg-card);
    border-top: 1px solid var(--border);
}

.query-hints {
    font-size: 0.75rem;
    color: var(--text-muted);
}

.query-hints kbd {
    background: var(--bg-input);
    border: 1px solid var(--border);
    border-radius: 3px;
    padding: 1px 4px;
    font-size: 0.6875rem;
    font-family: var(--mono);
}

.query-results-wrapper {
    margin-top: 1.25rem;
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
    flex: 1;
}

.query-results-meta {
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 0.75rem;
    color: var(--text-muted);
    border-bottom: 1px solid var(--border);
    padding-bottom: 0.5rem;
}

.query-results-meta .meta-right {
    display: flex;
    gap: 1rem;
}

.query-success-msg {
    padding: 1rem;
    background: rgba(16, 185, 129, 0.1);
    border: 1px solid rgba(16, 185, 129, 0.2);
    border-radius: var(--radius);
    color: var(--success);
    font-size: 0.8125rem;
}

.query-error-msg {
    padding: 1rem;
    background: rgba(239, 68, 68, 0.1);
    border: 1px solid rgba(239, 68, 68, 0.2);
    border-radius: var(--radius);
    color: var(--error);
    font-family: var(--mono);
    font-size: 0.8125rem;
    white-space: pre-wrap;
    line-height: 1.5;
}

/* ─── Resizers ────────────────────────────────────────────────────── */
.resizer-left, .resizer-right {
    width: 6px;
    background: transparent; /* Make it visually transparent to avoid thick outline borders */
    cursor: col-resize;
    position: relative;
    user-select: none;
    flex-shrink: 0;
    transition: background 0.15s;
    z-index: 10;
    margin: 0 -3px; /* Overlap the existing 1px border */
}
.resizer-left:hover, .resizer-right:hover, .resizer-active {
    background: var(--accent);
}

/* ─── Sidebar Table Details Dropdown ────────────────────────────── */
.header-click-area {
    display: flex;
    align-items: center;
    gap: 0.4rem;
    flex: 1;
}
.table-dropdown-toggle {
    background: none;
    border: none;
    cursor: pointer;
    padding: 0.25rem;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 4px;
    color: var(--text-muted);
    transition: all 0.2s;
}
.table-dropdown-toggle:hover {
    color: var(--text);
    background: var(--bg-glass);
}
.dropdown-chevron {
    transition: transform 0.2s var(--ease);
}
.table-item.expanded .dropdown-chevron {
    transform: rotate(180deg);
}
.table-item.expanded .table-columns-preview,
.table-item.expanded .table-fk-preview {
    display: none;
}
.table-details-dropdown {
    margin-top: 0.5rem;
    margin-left: 1.15rem;
    padding: 0.5rem 0.75rem;
    background: var(--bg-input);
    border: 1px solid var(--border);
    border-radius: 6px;
}
.details-columns-list {
    display: flex;
    flex-direction: column;
    gap: 0.35rem;
}
.detail-col-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 0.75rem;
    font-family: var(--mono);
}
.detail-col-name {
    color: var(--text-dim);
}
.detail-col-type {
    color: var(--accent-cyan);
    font-size: 0.6875rem;
}
.detail-pk .detail-col-name {
    color: var(--accent);
    font-weight: 600;
}
.detail-fk .detail-col-name {
    color: var(--warning);
}

/* ─── Pagination styles ─────────────────────────────────────────── */
.db-pagination-bar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0.75rem 0;
    margin-top: 0.5rem;
    border-top: 1px solid var(--border);
}
.pagination-info {
    font-size: 0.75rem;
    color: var(--text-muted);
}
.data-table-wrapper {
    overflow-x: auto !important;
    width: 100%;
}
.data-table {
    width: 100%;
    min-width: max-content !important; /* Force scroll behavior if columns overflow */
}
</style>
@endsection

@section('content')
<!-- ─── Left Sidebar ─────────────────────────────────────────────── -->
<aside class="sidebar" id="sidebar">
    <!-- Sidebar Tabs -->
    <div class="sidebar-tabs">
        <button class="sidebar-tab active" data-tab="api" onclick="switchSidebarTab('api')">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/></svg>
            API
        </button>
        <button class="sidebar-tab" data-tab="database" onclick="switchSidebarTab('database')">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><ellipse cx="12" cy="5" rx="9" ry="3"/><path d="M21 12c0 1.66-4 3-9 3s-9-1.34-9-3"/><path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5"/></svg>
            DB
        </button>
        <button class="sidebar-tab" data-tab="email" onclick="switchSidebarTab('email')">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
            Mail
        </button>
        <button class="sidebar-tab" data-tab="graph" onclick="switchSidebarTab('graph')">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><circle cx="18" cy="18" r="3"/><circle cx="6" cy="6" r="3"/><circle cx="18" cy="6" r="3"/><line x1="8.59" y1="7.41" x2="15.42" y2="14.59"/><line x1="15.41" y1="7.41" x2="8.59" y2="14.59"/></svg>
            Graph
        </button>
    </div>

    <!-- API Endpoints Tab -->
    <div class="sidebar-content active" id="tab-api">
        <div class="sidebar-search">
            <input type="text" id="endpoint-search" placeholder="Search endpoints..." oninput="filterEndpoints(this.value)">
        </div>
        <div class="endpoint-list" id="endpoint-list">
            @php
                $grouped = collect($endpoints)->groupBy('group');
            @endphp
            @foreach($grouped as $group => $groupEndpoints)
                <div class="endpoint-group">
                    <div class="group-header" onclick="toggleGroup(this)">
                        <svg class="group-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><polyline points="6 9 12 15 18 9"/></svg>
                        <span>{{ ucfirst($group) }}</span>
                        <span class="group-count">{{ count($groupEndpoints) }}</span>
                    </div>
                    <div class="group-items">
                        @foreach($groupEndpoints as $endpoint)
                            <button class="endpoint-item" onclick="selectEndpoint({{ json_encode($endpoint) }})" data-uri="{{ $endpoint['uri'] }}">
                                <span class="method-badge method-{{ strtolower($endpoint['method']) }}">{{ $endpoint['method'] }}</span>
                                <span class="endpoint-path">{{ preg_replace('#^/api/v\d+#', '', $endpoint['uri']) ?: '/' }}</span>
                                @if($endpoint['requires_auth'])
                                    <svg class="auth-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="12" height="12"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg>
                                @endif
                            </button>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Database Tab -->
    <div class="sidebar-content" id="tab-database">
        <div class="sidebar-search">
            <input type="text" id="table-search" placeholder="Search tables..." oninput="filterTables(this.value)">
        </div>
        <div class="db-actions">
            <button class="db-action-btn active" onclick="switchDbView('structure')" id="btn-structure">Structure</button>
            <button class="db-action-btn" onclick="switchDbView('schema')" id="btn-schema">ER Diagram</button>
            <button class="db-action-btn" onclick="switchDbView('query')" id="btn-query">SQL Console</button>
        </div>
        <div id="table-list" class="table-list">
            <div class="loading-indicator">Loading tables...</div>
        </div>
    </div>

    <!-- Email Tab -->
    <div class="sidebar-content" id="tab-email">
        <div class="email-actions">
            <button class="btn-sm btn-primary-sm" onclick="showEmailComposer()">+ Compose</button>
            <button class="btn-sm btn-secondary-sm" onclick="refreshInbox()">↻ Refresh</button>
            <button class="btn-sm btn-danger-sm" onclick="clearInbox()">✕ Clear</button>
        </div>
        <div id="email-inbox" class="email-inbox">
            <div class="loading-indicator">Loading inbox...</div>
        </div>
    </div>

    <!-- Graph Tab -->
    <div class="sidebar-content" id="tab-graph">
        <div class="graph-info">
            <p class="sidebar-hint">Obsidian-style documentation graph. Click nodes to view content.</p>
        </div>
        <div id="graph-file-list" class="graph-file-list">
            <div class="loading-indicator">Loading docs...</div>
        </div>
    </div>
</aside>
<div class="resizer-left" id="resizer-left"></div>

<!-- ─── Center Panel (Playground) ────────────────────────────────── -->
<main class="center-panel" id="center-panel">
    <div class="welcome-screen" id="welcome-screen">
        <div class="welcome-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" width="64" height="64"><path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/></svg>
        </div>
        <h2>Larable API Playground</h2>
        <p>Select an endpoint from the sidebar to begin testing, or explore the database and documentation sections.</p>
        <div class="welcome-stats">
            <div class="welcome-stat">
                <span class="welcome-stat-value">{{ count($endpoints) }}</span>
                <span class="welcome-stat-label">API Endpoints</span>
            </div>
            <div class="welcome-stat">
                <span class="welcome-stat-value">v1</span>
                <span class="welcome-stat-label">API Version</span>
            </div>
        </div>
    </div>

    <!-- API Playground -->
    <div class="playground" id="playground" style="display:none">
        <div class="playground-header">
            <span class="method-badge method-get" id="pg-method">GET</span>
            <input type="text" class="url-input" id="pg-url" value="" readonly>
            <button class="btn-send" id="pg-send" onclick="executeRequest()">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
                Send
            </button>
        </div>

        <!-- Request Tabs -->
        <div class="playground-tabs">
            <button class="pg-tab active" onclick="switchPlaygroundTab('body')">Body</button>
            <button class="pg-tab" onclick="switchPlaygroundTab('headers')">Headers</button>
            <button class="pg-tab" onclick="switchPlaygroundTab('auth')">Auth</button>
        </div>

        <!-- Request Body -->
        <div class="pg-tab-content active" id="pg-body">
            <div id="body-fields" class="body-fields">
                <p class="no-body-msg">No body parameters for this endpoint.</p>
            </div>
        </div>

        <!-- Headers -->
        <div class="pg-tab-content" id="pg-headers">
            <div class="header-row">
                <span class="header-key">Content-Type</span>
                <span class="header-value">application/json</span>
            </div>
            <div class="header-row">
                <span class="header-key">Accept</span>
                <span class="header-value">application/json</span>
            </div>
            <div class="header-row" id="idempotency-header" style="display:none">
                <span class="header-key">Idempotency-Key</span>
                <span class="header-value" id="idempotency-value">auto-generated</span>
            </div>
        </div>

        <!-- Auth -->
        <div class="pg-tab-content" id="pg-auth">
            <div class="auth-config">
                <label for="bearer-token">Bearer Token</label>
                <input type="text" id="bearer-token" placeholder="Paste your Sanctum token here..." class="token-input">
                <p class="auth-hint">Get a token by calling POST /api/v1/auth/login first.</p>
            </div>
        </div>

        <!-- Response -->
        <div class="response-section" id="response-section" style="display:none">
            <div class="response-header">
                <span class="response-title">Response</span>
                <div class="response-meta">
                    <span class="response-status" id="resp-status"></span>
                    <span class="response-time" id="resp-time"></span>
                    <span class="response-size" id="resp-size"></span>
                </div>
            </div>
            <pre class="response-body" id="resp-body"></pre>
        </div>
    </div>

    <!-- Database View -->
    <div class="db-view" id="db-view" style="display:none">
        <div id="db-structure-view">
            <div class="db-table-header" id="db-table-header"></div>
            <div class="db-table-data" id="db-table-data"></div>
        </div>
        <div id="db-schema-view" style="display:none">
            <div class="er-diagram" id="er-diagram">
                <canvas id="er-canvas"></canvas>
            </div>
        </div>
        <div id="db-query-view" style="display:none">
            <div class="db-query-console">
                <div class="db-query-header">
                    <h3>SQL Query Console</h3>
                    <div class="db-connection-status">
                        <span class="status-dot"></span>
                        <span>Connected to PostgreSQL</span>
                    </div>
                </div>
                <div class="query-editor-container">
                    <textarea id="db-query-input" class="query-textarea" placeholder="-- Write your SQL query here&#10;SELECT * FROM users LIMIT 10;"></textarea>
                    <div class="query-actions">
                        <span class="query-hints">Press <kbd>Ctrl</kbd> + <kbd>Enter</kbd> to run</span>
                        <button class="btn-send" id="btn-run-query" onclick="executeSqlQuery()">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><polygon points="5 3 19 12 5 21 5 3"/></svg>
                            Run Query
                        </button>
                    </div>
                </div>
                <div class="query-results-wrapper" id="query-results-wrapper" style="display:none">
                    <div class="query-results-meta">
                        <span id="query-status">Success</span>
                        <div class="meta-right">
                            <span id="query-time">0 ms</span>
                            <span id="query-count">0 rows</span>
                        </div>
                    </div>
                    <div id="query-results-output"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Email Composer -->
    <div class="email-view" id="email-view" style="display:none">
        <div class="email-composer" id="email-composer">
            <h3>Send Test Email</h3>
            <div class="form-group-gui">
                <label>To</label>
                <input type="email" id="email-to" value="test@example.com" class="gui-input">
            </div>
            <div class="form-group-gui">
                <label>Subject</label>
                <input type="text" id="email-subject" value="Test Email from Larable" class="gui-input">
            </div>
            <div class="form-group-gui">
                <label>Body</label>
                <textarea id="email-body" rows="6" class="gui-input gui-textarea">This is a test email sent from the Larable Backend GUI.

It was delivered through Mailpit for testing purposes.</textarea>
            </div>
            <button class="btn-send" onclick="sendTestEmail()">Send Email</button>
            <div id="email-result" class="email-result" style="display:none"></div>
        </div>
        <div class="email-preview" id="email-preview" style="display:none"></div>
    </div>

    <!-- Graph View -->
    <div class="graph-view" id="graph-view" style="display:none">
        <canvas id="graph-canvas"></canvas>
        <div class="graph-tooltip" id="graph-tooltip" style="display:none"></div>
    </div>

    <!-- Doc Viewer -->
    <div class="doc-viewer" id="doc-viewer" style="display:none">
        <div class="doc-header" id="doc-header"></div>
        <div class="doc-content" id="doc-content"></div>
    </div>
</main>
<div class="resizer-right" id="resizer-right"></div>

<!-- ─── Right Panel (Documentation) ──────────────────────────────── -->
<aside class="right-panel" id="right-panel">
    <div class="right-panel-content" id="right-panel-content">
        <div class="info-welcome">
            <h3>Documentation</h3>
            <p>Select an API endpoint to see its documentation, expected request/response format, and authentication requirements.</p>

            <div class="info-section">
                <h4>Quick Guide</h4>
                <ul>
                    <li><span class="method-badge method-get">GET</span> Retrieve data</li>
                    <li><span class="method-badge method-post">POST</span> Create or submit</li>
                    <li><span class="method-badge method-put">PUT</span> Update entire resource</li>
                    <li><span class="method-badge method-patch">PATCH</span> Partial update</li>
                    <li><span class="method-badge method-delete">DELETE</span> Remove resource</li>
                </ul>
            </div>

            <div class="info-section">
                <h4>Authentication</h4>
                <p>Endpoints marked with 🔒 require a Bearer token. Get one by calling <code>POST /api/v1/auth/login</code>.</p>
            </div>

            <div class="info-section">
                <h4>Idempotency</h4>
                <p>POST/PUT/PATCH requests support the <code>Idempotency-Key</code> header. The playground auto-generates this for each request.</p>
            </div>
        </div>
    </div>
</aside>
@endsection

@section('scripts')
<script>
// ═══════════════════════════════════════════════════════════════════
// Larable Backend GUI — JavaScript
// ═══════════════════════════════════════════════════════════════════

const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]').content;
let currentEndpoint = null;
let currentDbView = 'structure';
let sqlConsoleResult = null;
let sqlConsolePage = 1;
const sqlConsolePerPage = 10;

// ─── Sidebar Tab Switching ──────────────────────────────────────
function switchSidebarTab(tab) {
    document.querySelectorAll('.sidebar-tab').forEach(t => t.classList.remove('active'));
    document.querySelectorAll('.sidebar-content').forEach(c => c.classList.remove('active'));
    document.querySelector(`[data-tab="${tab}"]`).classList.add('active');
    document.getElementById(`tab-${tab}`).classList.add('active');

    // Load data for tabs
    if (tab === 'database') loadTables();
    if (tab === 'email') refreshInbox();
    if (tab === 'graph') loadGraph();

    // Show appropriate center view
    hideAllViews();
    if (tab === 'api') {
        if (currentEndpoint) {
            document.getElementById('playground').style.display = 'block';
        } else {
            document.getElementById('welcome-screen').style.display = 'flex';
        }
    } else if (tab === 'database') {
        document.getElementById('db-view').style.display = 'block';
    } else if (tab === 'email') {
        document.getElementById('email-view').style.display = 'block';
    } else if (tab === 'graph') {
        document.getElementById('graph-view').style.display = 'block';
        setTimeout(() => renderGraph(), 100);
    }
}

function hideAllViews() {
    ['welcome-screen', 'playground', 'db-view', 'email-view', 'graph-view', 'doc-viewer'].forEach(id => {
        const el = document.getElementById(id);
        if (el) el.style.display = 'none';
    });
}

// ─── Endpoint Group Toggle ──────────────────────────────────────
function toggleGroup(header) {
    const group = header.closest('.endpoint-group');
    group.classList.toggle('collapsed');
}

// ─── Endpoint Search ────────────────────────────────────────────
function filterEndpoints(query) {
    query = query.toLowerCase();
    document.querySelectorAll('.endpoint-item').forEach(item => {
        const uri = item.dataset.uri.toLowerCase();
        item.style.display = uri.includes(query) ? '' : 'none';
    });
}

// ─── Select Endpoint ────────────────────────────────────────────
function selectEndpoint(endpoint) {
    currentEndpoint = endpoint;

    // Update active state
    document.querySelectorAll('.endpoint-item').forEach(i => i.classList.remove('active'));
    document.querySelector(`[data-uri="${endpoint.uri}"]`)?.classList.add('active');

    // Show playground
    hideAllViews();
    document.getElementById('playground').style.display = 'block';

    // Update playground header
    const methodBadge = document.getElementById('pg-method');
    methodBadge.textContent = endpoint.method;
    methodBadge.className = `method-badge method-${endpoint.method.toLowerCase()}`;
    document.getElementById('pg-url').value = endpoint.uri;

    // Show/hide idempotency header
    const showIdempotency = ['POST', 'PUT', 'PATCH'].includes(endpoint.method);
    document.getElementById('idempotency-header').style.display = showIdempotency ? '' : 'none';

    // Build body fields
    const bodyFieldsEl = document.getElementById('body-fields');
    if (endpoint.body_keys && endpoint.body_keys.length > 0) {
        let html = '';
        endpoint.body_keys.forEach(field => {
            const requiredBadge = field.required ? '<span class="required-badge">required</span>' : '<span class="optional-badge">optional</span>';
            const inputType = field.type === 'boolean' ? 'checkbox' : (field.key.includes('password') ? 'password' : 'text');

            if (field.type === 'boolean') {
                html += `
                    <div class="body-field">
                        <div class="field-meta">
                            <span class="field-key">"${field.key}"</span>
                            <span class="field-type">${field.type}</span>
                            ${requiredBadge}
                        </div>
                        <label class="checkbox-field">
                            <input type="checkbox" data-field="${field.key}" ${field.example ? 'checked' : ''}>
                            <span>${field.key}</span>
                        </label>
                    </div>`;
            } else {
                html += `
                    <div class="body-field">
                        <div class="field-meta">
                            <span class="field-key">"${field.key}"</span>
                            <span class="field-type">${field.type}</span>
                            ${requiredBadge}
                        </div>
                        <input type="${inputType}" class="field-input" data-field="${field.key}" placeholder="${field.example || ''}" value="${field.example || ''}">
                    </div>`;
            }
        });
        bodyFieldsEl.innerHTML = html;
    } else {
        bodyFieldsEl.innerHTML = '<p class="no-body-msg">No body parameters for this endpoint.</p>';
    }

    // Update right panel documentation
    updateDocumentation(endpoint);

    // Hide response
    document.getElementById('response-section').style.display = 'none';
}

// ─── Update Documentation Panel ─────────────────────────────────
function updateDocumentation(endpoint) {
    const panel = document.getElementById('right-panel-content');
    let html = `
        <div class="endpoint-doc">
            <div class="doc-method-uri">
                <span class="method-badge method-${endpoint.method.toLowerCase()}">${endpoint.method}</span>
                <code>${endpoint.uri}</code>
            </div>
            <p class="doc-description">${endpoint.description}</p>

            <div class="info-section">
                <h4>Authentication</h4>
                <p>${endpoint.requires_auth
                    ? '🔒 <strong>Required</strong> — Include a Bearer token in the Authorization header.'
                    : '🔓 <strong>Public</strong> — No authentication required.'
                }</p>
            </div>

            <div class="info-section">
                <h4>Route Name</h4>
                <code>${endpoint.name || 'unnamed'}</code>
            </div>

            <div class="info-section">
                <h4>Middleware</h4>
                <div class="middleware-tags">
                    ${endpoint.middleware.map(m => `<span class="middleware-tag">${m}</span>`).join('')}
                </div>
            </div>`;

    if (endpoint.body_keys && endpoint.body_keys.length > 0) {
        html += `
            <div class="info-section">
                <h4>Request Body</h4>
                <pre class="doc-json">{
${endpoint.body_keys.map(f => `  "${f.key}": ${f.type === 'boolean' ? 'false' : `"${f.example || ''}"`}`).join(',\n')}
}</pre>
            </div>`;
    }

    html += `
            <div class="info-section">
                <h4>Idempotency</h4>
                <p>${['POST', 'PUT', 'PATCH'].includes(endpoint.method)
                    ? 'Supports <code>Idempotency-Key</code> header. Duplicate requests with the same key return cached responses for 24 hours.'
                    : 'Not applicable for ' + endpoint.method + ' requests.'
                }</p>
            </div>
        </div>`;

    panel.innerHTML = html;
}

// ─── Playground Tab Switching ───────────────────────────────────
function switchPlaygroundTab(tab) {
    document.querySelectorAll('.pg-tab').forEach(t => t.classList.remove('active'));
    document.querySelectorAll('.pg-tab-content').forEach(c => c.classList.remove('active'));
    event.target.classList.add('active');
    document.getElementById(`pg-${tab}`).classList.add('active');
}

// ─── Execute API Request ────────────────────────────────────────
async function executeRequest() {
    if (!currentEndpoint) return;

    const sendBtn = document.getElementById('pg-send');
    sendBtn.disabled = true;
    sendBtn.innerHTML = '<span class="spinner-sm"></span> Sending...';

    // Build body from fields
    const body = {};
    document.querySelectorAll('#body-fields [data-field]').forEach(input => {
        const key = input.dataset.field;
        if (input.type === 'checkbox') {
            body[key] = input.checked;
        } else if (input.value) {
            body[key] = input.value;
        }
    });

    const bearerToken = document.getElementById('bearer-token').value;

    try {
        const response = await fetch('/larable/playground/execute', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': CSRF_TOKEN,
            },
            body: JSON.stringify({
                method: currentEndpoint.method,
                url: currentEndpoint.uri,
                body: Object.keys(body).length > 0 ? body : null,
                bearer_token: bearerToken || null,
            }),
        });

        const result = await response.json();

        // Show response
        const responseSection = document.getElementById('response-section');
        responseSection.style.display = 'block';

        const statusEl = document.getElementById('resp-status');
        statusEl.textContent = `${result.status} ${result.status_text}`;
        statusEl.className = `response-status status-${Math.floor(result.status / 100)}xx`;

        document.getElementById('resp-time').textContent = `${result.duration_ms}ms`;
        document.getElementById('resp-size').textContent = formatBytes(result.size_bytes);
        document.getElementById('resp-body').textContent = JSON.stringify(result.body, null, 2);

        // Syntax highlight
        highlightJson(document.getElementById('resp-body'));

    } catch (err) {
        document.getElementById('response-section').style.display = 'block';
        document.getElementById('resp-status').textContent = 'Error';
        document.getElementById('resp-status').className = 'response-status status-5xx';
        document.getElementById('resp-body').textContent = err.message;
    } finally {
        sendBtn.disabled = false;
        sendBtn.innerHTML = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg> Send';
    }
}

// ─── Database Functions ─────────────────────────────────────────
let tablesData = [];

async function loadTables() {
    try {
        const resp = await fetch('/larable/database/tables');
        tablesData = await resp.json();
        renderTableList();
    } catch (err) {
        document.getElementById('table-list').innerHTML = '<p class="error-msg">Failed to load tables</p>';
    }
}

function renderTableList() {
    const container = document.getElementById('table-list');
    let html = '';

    tablesData.forEach(table => {
        html += `
            <div class="table-item" id="table-item-${table.name}">
                <div class="table-item-header">
                    <div class="header-click-area" onclick="loadTableData('${table.name}')" title="Load Table Data">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><rect x="3" y="3" width="18" height="18" rx="2"/><line x1="3" y1="9" x2="21" y2="9"/><line x1="3" y1="15" x2="21" y2="15"/><line x1="9" y1="3" x2="9" y2="21"/></svg>
                        <span class="table-name">${table.name}</span>
                        <span class="table-count">${table.row_count} rows</span>
                    </div>
                    <button class="table-dropdown-toggle" onclick="toggleTableDropdown(event, '${table.name}')" title="Show Columns">
                        <svg class="dropdown-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="12" height="12"><polyline points="6 9 12 15 18 9"/></svg>
                    </button>
                </div>
                <div class="table-columns-preview">
                    ${table.columns.slice(0, 4).map(c => `<span class="col-preview">${c.name}</span>`).join('')}
                    ${table.columns.length > 4 ? `<span class="col-preview col-more">+${table.columns.length - 4}</span>` : ''}
                </div>
                ${table.foreign_keys.length > 0 ? `
                    <div class="table-fk-preview">
                        ${table.foreign_keys.map(fk => `
                            <span class="fk-badge" title="${fk.column} → ${fk.foreign_table}.${fk.foreign_column}">
                                🔗 ${fk.column} → ${fk.foreign_table}
                            </span>
                        `).join('')}
                    </div>
                ` : ''}
                <div class="table-details-dropdown" id="details-${table.name}" style="display: none;">
                    <div class="details-columns-list">
                        ${table.columns.map(col => {
                            const isPk = col.auto_increment || (table.indexes && table.indexes.find(idx => idx.primary && idx.columns.includes(col.name)));
                            const fk = table.foreign_keys.find(f => f.column === col.name);
                            return `
                                <div class="detail-col-row ${isPk ? 'detail-pk' : ''} ${fk ? 'detail-fk' : ''}">
                                    <span class="detail-col-name">${isPk ? '🔑 ' : (fk ? '🔗 ' : '')}${col.name}</span>
                                    <span class="detail-col-type">${col.type_name}</span>
                                </div>
                            `;
                        }).join('')}
                    </div>
                </div>
            </div>`;
    });

    container.innerHTML = html || '<p class="no-data-msg">No tables found</p>';
}

async function loadTableData(tableName, page = 1) {
    hideAllViews();
    document.getElementById('db-view').style.display = 'block';
    document.getElementById('db-structure-view').style.display = 'block';
    document.getElementById('db-schema-view').style.display = 'none';

    try {
        const resp = await fetch(`/larable/database/table/${tableName}?page=${page}`);
        const data = await resp.json();
        renderTableData(data);
    } catch (err) {
        document.getElementById('db-table-data').innerHTML = '<p class="error-msg">Failed to load table data</p>';
    }
}

function renderTableData(data) {
    // Header
    const headerEl = document.getElementById('db-table-header');
    headerEl.innerHTML = `
        <h3>${data.table}</h3>
        <div class="table-meta-info">
            <span>${data.columns.length} columns</span>
            <span>${data.pagination.total} rows</span>
            <span>Page ${data.pagination.current_page}/${data.pagination.last_page}</span>
        </div>
    `;

    // Column structure
    let html = '<div class="column-structure">';
    data.columns.forEach(col => {
        const isPk = col.auto_increment;
        const fk = data.foreign_keys.find(f => f.column === col.name);
        html += `
            <div class="column-row ${isPk ? 'pk-row' : ''} ${fk ? 'fk-row' : ''}">
                <span class="col-name">${isPk ? '🔑 ' : ''}${col.name}</span>
                <span class="col-type">${col.type_name}</span>
                <span class="col-nullable">${col.nullable ? 'NULL' : 'NOT NULL'}</span>
                ${col.default !== null ? `<span class="col-default">= ${col.default}</span>` : ''}
                ${fk ? `<span class="col-fk" title="References ${fk.foreign_table}.${fk.foreign_column}" onmouseenter="highlightFk(this, '${fk.foreign_table}', '${fk.foreign_column}')" onmouseleave="unhighlightFk()">→ ${fk.foreign_table}.${fk.foreign_column}</span>` : ''}
            </div>`;
    });
    html += '</div>';

    // Data table
    if (data.data.length > 0) {
        html += '<div class="data-table-wrapper"><table class="data-table"><thead><tr>';
        data.columns.forEach(col => {
            html += `<th>${col.name}</th>`;
        });
        html += '</tr></thead><tbody>';
        data.data.forEach(row => {
            html += '<tr>';
            data.columns.forEach(col => {
                const val = row[col.name];
                const fk = data.foreign_keys.find(f => f.column === col.name);
                if (fk && val !== null) {
                    html += `<td><a class="fk-link" onclick="loadTableData('${fk.foreign_table}')" title="Go to ${fk.foreign_table}">${val}</a></td>`;
                } else {
                    html += `<td>${val !== null ? val : '<span class="null-val">NULL</span>'}</td>`;
                }
            });
            html += '</tr>';
        });
        html += '</tbody></table></div>';

        // Table Pagination Controls
        if (data.pagination.last_page > 1) {
            html += `
                <div class="db-pagination-bar">
                    <button class="btn-sm btn-secondary-sm" onclick="loadTableData('${data.table}', ${data.pagination.current_page - 1})" ${data.pagination.current_page === 1 ? 'disabled' : ''}>← Previous</button>
                    <span class="pagination-info">Showing ${(data.pagination.current_page - 1) * data.pagination.per_page + 1} - ${Math.min(data.pagination.current_page * data.pagination.per_page, data.pagination.total)} of ${data.pagination.total}</span>
                    <button class="btn-sm btn-secondary-sm" onclick="loadTableData('${data.table}', ${data.pagination.current_page + 1})" ${data.pagination.current_page === data.pagination.last_page ? 'disabled' : ''}>Next →</button>
                </div>
            `;
        }
    } else {
        html += '<p class="no-data-msg">No data in this table</p>';
    }

    document.getElementById('db-table-data').innerHTML = html;
}

function switchDbView(view) {
    currentDbView = view;
    document.getElementById('btn-structure').classList.toggle('active', view === 'structure');
    document.getElementById('btn-schema').classList.toggle('active', view === 'schema');
    document.getElementById('btn-query').classList.toggle('active', view === 'query');

    document.getElementById('db-structure-view').style.display = view === 'structure' ? 'block' : 'none';
    document.getElementById('db-schema-view').style.display = view === 'schema' ? 'block' : 'none';
    document.getElementById('db-query-view').style.display = view === 'query' ? 'block' : 'none';

    if (view === 'schema') {
        hideAllViews();
        document.getElementById('db-view').style.display = 'block';
        loadERDiagram();
    } else if (view === 'query') {
        hideAllViews();
        document.getElementById('db-view').style.display = 'block';
        document.getElementById('db-query-input').focus();
    }
}

// ─── ER Diagram ─────────────────────────────────────────────────
let erData = null;

async function loadERDiagram() {
    try {
        const resp = await fetch('/larable/database/schema');
        erData = await resp.json();
        renderERDiagram();
    } catch (err) {
        document.getElementById('er-diagram').innerHTML = '<p class="error-msg">Failed to load schema</p>';
    }
}

function renderERDiagram() {
    if (!erData) return;
    const canvas = document.getElementById('er-canvas');
    const ctx = canvas.getContext('2d');
    const container = document.getElementById('er-diagram');

    canvas.width = container.offsetWidth || 800;
    canvas.height = Math.max(container.offsetHeight, 600);

    ctx.clearRect(0, 0, canvas.width, canvas.height);

    const theme = document.documentElement.getAttribute('data-theme') || 'light';
    const isDark = theme === 'dark';

    // Theme values
    const cardBg = isDark ? 'rgba(26, 26, 46, 0.9)' : 'rgba(255, 255, 255, 0.95)';
    const cardBorder = isDark ? 'rgba(255, 255, 255, 0.08)' : 'rgba(0, 0, 0, 0.18)';
    const headerBg = isDark ? 'rgba(108, 92, 231, 0.15)' : 'rgba(108, 92, 231, 0.1)';
    const headerText = isDark ? '#e8e8f0' : '#1e1e2f';
    const columnText = isDark ? '#8b8ba3' : '#3f3f5c';
    const typeText = isDark ? '#5a5a72' : '#5a5a7a';
    const edgeColor = isDark ? 'rgba(108, 92, 231, 0.4)' : 'rgba(108, 92, 231, 0.55)';
    const edgeLabel = isDark ? 'rgba(139, 139, 163, 0.8)' : 'rgba(63, 63, 92, 0.9)';

    const nodes = erData.nodes;
    const edges = erData.edges;

    // Position nodes in a grid
    const cols = Math.ceil(Math.sqrt(nodes.length));
    const cellW = canvas.width / (cols + 1);
    const cellH = 200;

    const positions = {};
    nodes.forEach((node, i) => {
        const col = i % cols;
        const row = Math.floor(i / cols);
        positions[node.id] = {
            x: cellW * (col + 0.5),
            y: cellH * (row + 0.5) + 40,
            node: node,
        };
    });

    // Resize canvas height based on rows
    const rows = Math.ceil(nodes.length / cols);
    canvas.height = Math.max(cellH * (rows + 1), 400);

    // Draw edges first
    edges.forEach(edge => {
        const from = positions[edge.from];
        const to = positions[edge.to];
        if (!from || !to) return;

        ctx.beginPath();
        ctx.strokeStyle = edgeColor;
        ctx.lineWidth = 1.5;
        ctx.setLineDash([4, 4]);
        ctx.moveTo(from.x, from.y + 20);
        ctx.lineTo(to.x, to.y + 20);
        ctx.stroke();
        ctx.setLineDash([]);

        // Arrow
        const angle = Math.atan2(to.y - from.y, to.x - from.x);
        const arrowX = to.x - Math.cos(angle) * 30;
        const arrowY = to.y + 20 - Math.sin(angle) * 30;
        ctx.beginPath();
        ctx.fillStyle = edgeColor;
        ctx.moveTo(arrowX, arrowY);
        ctx.lineTo(arrowX - 8 * Math.cos(angle - 0.5), arrowY - 8 * Math.sin(angle - 0.5));
        ctx.lineTo(arrowX - 8 * Math.cos(angle + 0.5), arrowY - 8 * Math.sin(angle + 0.5));
        ctx.fill();

        // Label
        const midX = (from.x + to.x) / 2;
        const midY = (from.y + to.y) / 2 + 20;
        ctx.fillStyle = edgeLabel;
        ctx.font = '10px Inter';
        ctx.textAlign = 'center';
        ctx.fillText(`${edge.from_column} → ${edge.to_column}`, midX, midY - 8);
    });

    // Draw nodes
    nodes.forEach(node => {
        const pos = positions[node.id];
        if (!pos) return;

        const w = 180;
        const headerH = 32;
        const colH = 18;
        const h = headerH + node.columns.length * colH + 8;
        const x = pos.x - w / 2;
        const y = pos.y;

        // Card background
        ctx.fillStyle = cardBg;
        ctx.strokeStyle = cardBorder;
        ctx.lineWidth = 1;
        ctx.beginPath();
        ctx.roundRect(x, y, w, h, 8);
        ctx.fill();
        ctx.stroke();

        // Header
        ctx.fillStyle = headerBg;
        ctx.beginPath();
        ctx.roundRect(x, y, w, headerH, [8, 8, 0, 0]);
        ctx.fill();

        ctx.fillStyle = headerText;
        ctx.font = 'bold 12px Inter';
        ctx.textAlign = 'left';
        ctx.fillText(node.label, x + 10, y + 21);

        // Columns
        node.columns.forEach((col, ci) => {
            const cy = y + headerH + ci * colH + 14;
            ctx.fillStyle = col.auto_increment ? '#6c5ce7' : columnText;
            ctx.font = '11px JetBrains Mono';
            ctx.fillText(col.name, x + 10, cy);
            ctx.fillStyle = typeText;
            ctx.font = '10px JetBrains Mono';
            ctx.textAlign = 'right';
            ctx.fillText(col.type_name, x + w - 10, cy);
            ctx.textAlign = 'left';
        });
    });
}

function highlightFk(el, table, column) {
    el.style.background = 'rgba(108, 92, 231, 0.3)';
}

function unhighlightFk() {
    document.querySelectorAll('.col-fk').forEach(el => el.style.background = '');
}

// ─── Email Functions ────────────────────────────────────────────
function showEmailComposer() {
    hideAllViews();
    document.getElementById('email-view').style.display = 'block';
    document.getElementById('email-composer').style.display = 'block';
    document.getElementById('email-preview').style.display = 'none';
}

async function sendTestEmail() {
    const to = document.getElementById('email-to').value;
    const subject = document.getElementById('email-subject').value;
    const body = document.getElementById('email-body').value;

    try {
        const resp = await fetch('/larable/email/send', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN },
            body: JSON.stringify({ to, subject, body }),
        });
        const result = await resp.json();
        const resultEl = document.getElementById('email-result');
        resultEl.style.display = 'block';
        resultEl.className = `email-result ${result.success ? 'success' : 'error'}`;
        resultEl.textContent = result.message;
        if (result.success) refreshInbox();
    } catch (err) {
        const resultEl = document.getElementById('email-result');
        resultEl.style.display = 'block';
        resultEl.className = 'email-result error';
        resultEl.textContent = 'Failed to send email.';
    }
}

async function refreshInbox() {
    try {
        const resp = await fetch('/larable/email/inbox');
        const data = await resp.json();
        const container = document.getElementById('email-inbox');

        if (data.messages && data.messages.length > 0) {
            container.innerHTML = data.messages.map(msg => `
                <div class="email-item" onclick="viewEmail('${msg.ID}')">
                    <div class="email-from">${msg.From?.Address || 'Unknown'}</div>
                    <div class="email-subject-line">${msg.Subject || '(no subject)'}</div>
                    <div class="email-date">${new Date(msg.Created).toLocaleString()}</div>
                </div>
            `).join('');
        } else {
            container.innerHTML = '<p class="no-data-msg">Inbox is empty. Send a test email!</p>';
        }
    } catch (err) {
        document.getElementById('email-inbox').innerHTML = '<p class="error-msg">Cannot reach Mailpit. Is it running?</p>';
    }
}

async function viewEmail(id) {
    try {
        const resp = await fetch(`/larable/email/message/${id}`);
        const msg = await resp.json();

        hideAllViews();
        document.getElementById('email-view').style.display = 'block';
        document.getElementById('email-composer').style.display = 'none';
        document.getElementById('email-preview').style.display = 'block';
        document.getElementById('email-preview').innerHTML = `
            <div class="email-detail-header">
                <h3>${msg.Subject || '(no subject)'}</h3>
                <p><strong>From:</strong> ${msg.From?.Address || 'Unknown'}</p>
                <p><strong>To:</strong> ${msg.To?.map(t => t.Address).join(', ') || 'Unknown'}</p>
                <p><strong>Date:</strong> ${new Date(msg.Created).toLocaleString()}</p>
            </div>
            <div class="email-detail-body">${msg.Text || msg.HTML || 'No content'}</div>
            <button class="btn-sm btn-secondary-sm" onclick="showEmailComposer()">← Back to Composer</button>
        `;
    } catch (err) {
        console.error('Failed to load email:', err);
    }
}

async function clearInbox() {
    if (!confirm('Clear all emails in Mailpit?')) return;
    try {
        await fetch('/larable/email/clear', { method: 'DELETE', headers: { 'X-CSRF-TOKEN': CSRF_TOKEN } });
        refreshInbox();
    } catch (err) {
        console.error('Failed to clear inbox:', err);
    }
}

function filterTables(query) {
    query = query.toLowerCase();
    document.querySelectorAll('.table-item').forEach(item => {
        const name = item.querySelector('.table-name').textContent.toLowerCase();
        item.style.display = name.includes(query) ? '' : 'none';
    });
}

// ─── Graph Functions ────────────────────────────────────────────
let graphData = null;
let graphNodes = [];

async function loadGraph() {
    try {
        const resp = await fetch('/larable/graph');
        graphData = await resp.json();
        renderGraphFileList();
    } catch (err) {
        document.getElementById('graph-file-list').innerHTML = '<p class="error-msg">Failed to load docs</p>';
    }
}

function renderGraphFileList() {
    if (!graphData) return;
    const container = document.getElementById('graph-file-list');

    if (graphData.nodes.length === 0) {
        container.innerHTML = '<p class="no-data-msg">No documentation files found in docs/ folder.</p>';
        return;
    }

    container.innerHTML = graphData.nodes.map(node => `
        <div class="graph-file-item" onclick="viewDocFile('${node.path}')">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
            <span>${node.label}</span>
            <span class="graph-connections">${node.connections} links</span>
        </div>
    `).join('');
}

function renderGraph() {
    if (!graphData || graphData.nodes.length === 0) return;

    const canvas = document.getElementById('graph-canvas');
    const ctx = canvas.getContext('2d');
    const container = document.getElementById('graph-view');

    canvas.width = container.offsetWidth || 800;
    canvas.height = container.offsetHeight || 500;

    const theme = document.documentElement.getAttribute('data-theme') || 'light';
    const isDark = theme === 'dark';
    const labelColor = isDark ? '#e8e8f0' : '#1e1e2f';

    // Simple force-directed layout
    const centerX = canvas.width / 2;
    const centerY = canvas.height / 2;
    const radius = Math.min(canvas.width, canvas.height) * 0.35;

    // Position nodes in a circle
    graphNodes = graphData.nodes.map((node, i) => {
        const angle = (2 * Math.PI * i) / graphData.nodes.length;
        return {
            ...node,
            x: centerX + radius * Math.cos(angle),
            y: centerY + radius * Math.sin(angle),
            r: 8 + Math.min(node.connections * 3, 20),
        };
    });

    ctx.clearRect(0, 0, canvas.width, canvas.height);

    // Draw edges
    graphData.edges.forEach(edge => {
        const source = graphNodes.find(n => n.id === edge.source);
        const target = graphNodes.find(n => n.id === edge.target);
        if (!source || !target) return;

        ctx.beginPath();
        ctx.strokeStyle = isDark ? 'rgba(108, 92, 231, 0.2)' : 'rgba(108, 92, 231, 0.5)';
        ctx.lineWidth = 1.5;
        ctx.moveTo(source.x, source.y);
        ctx.lineTo(target.x, target.y);
        ctx.stroke();
    });

    // Draw nodes
    graphNodes.forEach(node => {
        // Glow
        const gradient = ctx.createRadialGradient(node.x, node.y, 0, node.x, node.y, node.r * 2);
        gradient.addColorStop(0, isDark ? 'rgba(108, 92, 231, 0.3)' : 'rgba(108, 92, 231, 0.12)');
        gradient.addColorStop(1, 'rgba(108, 92, 231, 0)');
        ctx.fillStyle = gradient;
        ctx.beginPath();
        ctx.arc(node.x, node.y, node.r * 2, 0, Math.PI * 2);
        ctx.fill();

        // Node circle
        ctx.beginPath();
        ctx.fillStyle = '#6c5ce7';
        ctx.arc(node.x, node.y, node.r, 0, Math.PI * 2);
        ctx.fill();

        // Label
        ctx.fillStyle = labelColor;
        ctx.font = '11px Inter';
        ctx.textAlign = 'center';
        ctx.fillText(node.label, node.x, node.y + node.r + 16);
    });
}

async function viewDocFile(path) {
    try {
        const resp = await fetch(`/larable/graph/file/${path}`);
        const data = await resp.json();

        hideAllViews();
        document.getElementById('doc-viewer').style.display = 'block';
        document.getElementById('doc-header').innerHTML = `<h3>${data.title}</h3><span class="doc-path">${data.path}</span>`;
        document.getElementById('doc-content').innerHTML = `<pre class="doc-markdown">${escapeHtml(data.content)}</pre>`;
    } catch (err) {
        console.error('Failed to load doc:', err);
    }
}

// ─── Utility Functions ──────────────────────────────────────────
function formatBytes(bytes) {
    if (bytes === 0) return '0 B';
    const k = 1024;
    const sizes = ['B', 'KB', 'MB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(1)) + ' ' + sizes[i];
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function highlightJson(pre) {
    let html = pre.textContent;
    html = html.replace(/"([^"]+)":/g, '<span class="json-key">"$1"</span>:');
    html = html.replace(/: "([^"]*)"/g, ': <span class="json-string">"$1"</span>');
    html = html.replace(/: (\d+)/g, ': <span class="json-number">$1</span>');
    html = html.replace(/: (true|false)/g, ': <span class="json-bool">$1</span>');
    html = html.replace(/: (null)/g, ': <span class="json-null">$1</span>');
    pre.innerHTML = html;
}

// ─── SQL Query Console Functions ────────────────────────────────
async function executeSqlQuery() {
    const queryInput = document.getElementById('db-query-input');
    const query = queryInput.value.trim();

    if (!query) return;

    const runBtn = document.getElementById('btn-run-query');
    runBtn.disabled = true;
    runBtn.innerHTML = '<span class="spinner-sm"></span> Running...';

    const resultsWrapper = document.getElementById('query-results-wrapper');
    const statusEl = document.getElementById('query-status');
    const timeEl = document.getElementById('query-time');
    const countEl = document.getElementById('query-count');
    const outputEl = document.getElementById('query-results-output');

    resultsWrapper.style.display = 'none';

    try {
        const response = await fetch('/larable/database/query', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': CSRF_TOKEN,
            },
            body: JSON.stringify({ query }),
        });

        const result = await response.json();

        resultsWrapper.style.display = 'flex';
        timeEl.textContent = `${result.duration_ms} ms`;

        if (!response.ok) {
            statusEl.textContent = 'Error';
            statusEl.className = 'response-status status-5xx';
            countEl.style.display = 'none';
            outputEl.innerHTML = `<div class="query-error-msg">${escapeHtml(result.error)}</div>`;
            return;
        }

        statusEl.textContent = 'Success';
        statusEl.className = 'response-status status-2xx';
        countEl.style.display = '';

        if (result.type === 'select') {
            sqlConsoleResult = {
                columns: result.columns,
                data: result.data,
                total: result.affected_rows,
                last_page: Math.ceil(result.affected_rows / sqlConsolePerPage)
            };
            sqlConsolePage = 1;
            renderSqlConsolePage();
        } else {
            sqlConsoleResult = null;
            countEl.textContent = `${result.affected_rows} rows affected`;
            outputEl.innerHTML = `<div class="query-success-msg">Query executed successfully. ${result.affected_rows} rows affected.</div>`;
        }
    } catch (err) {
        resultsWrapper.style.display = 'flex';
        statusEl.textContent = 'Error';
        statusEl.className = 'response-status status-5xx';
        countEl.style.display = 'none';
        timeEl.textContent = '--';
        outputEl.innerHTML = `<div class="query-error-msg">${escapeHtml(err.message)}</div>`;
    } finally {
        runBtn.disabled = false;
        runBtn.innerHTML = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><polygon points="5 3 19 12 5 21 5 3"/></svg> Run Query';
    }
}

function renderSqlConsolePage() {
    if (!sqlConsoleResult) return;

    const outputEl = document.getElementById('query-results-output');
    const countEl = document.getElementById('query-count');
    const startIdx = (sqlConsolePage - 1) * sqlConsolePerPage;
    const endIdx = Math.min(startIdx + sqlConsolePerPage, sqlConsoleResult.total);
    const pageData = sqlConsoleResult.data.slice(startIdx, endIdx);

    countEl.textContent = `${sqlConsoleResult.total} rows`;

    if (sqlConsoleResult.total > 0) {
        let html = '<div class="data-table-wrapper"><table class="data-table"><thead><tr>';
        sqlConsoleResult.columns.forEach(col => {
            html += `<th>${col}</th>`;
        });
        html += '</tr></thead><tbody>';
        pageData.forEach(row => {
            html += '<tr>';
            sqlConsoleResult.columns.forEach(col => {
                const val = row[col];
                html += `<td>${val !== null ? escapeHtml(String(val)) : '<span class="null-val">NULL</span>'}</td>`;
            });
            html += '</tr>';
        });
        html += '</tbody></table></div>';

        // SQL Console Pagination Controls
        if (sqlConsoleResult.last_page > 1) {
            html += `
                <div class="db-pagination-bar">
                    <button class="btn-sm btn-secondary-sm" onclick="changeSqlConsolePage(${sqlConsolePage - 1})" ${sqlConsolePage === 1 ? 'disabled' : ''}>← Previous</button>
                    <span class="pagination-info">Showing ${startIdx + 1} - ${endIdx} of ${sqlConsoleResult.total}</span>
                    <button class="btn-sm btn-secondary-sm" onclick="changeSqlConsolePage(${sqlConsolePage + 1})" ${sqlConsolePage === sqlConsoleResult.last_page ? 'disabled' : ''}>Next →</button>
                </div>
            `;
        }
        outputEl.innerHTML = html;
    } else {
        outputEl.innerHTML = '<div class="no-data-msg">Query returned 0 rows.</div>';
    }
}

function changeSqlConsolePage(page) {
    if (!sqlConsoleResult || page < 1 || page > sqlConsoleResult.last_page) return;
    sqlConsolePage = page;
    renderSqlConsolePage();
}

// Add shortcut key handler and resizer drags on document ready
document.addEventListener('DOMContentLoaded', () => {
    const queryInput = document.getElementById('db-query-input');
    if (queryInput) {
        queryInput.addEventListener('keydown', (e) => {
            if ((e.ctrlKey || e.metaKey) && e.key === 'Enter') {
                e.preventDefault();
                executeSqlQuery();
            }
        });
    }

    // Sidebar Resizers Drag Logic
    const resizerLeft = document.getElementById('resizer-left');
    const sidebar = document.getElementById('sidebar');
    let isResizingLeft = false;

    resizerLeft.addEventListener('mousedown', (e) => {
        isResizingLeft = true;
        resizerLeft.classList.add('resizer-active');
        document.body.style.cursor = 'col-resize';
        document.body.style.userSelect = 'none';
    });

    const resizerRight = document.getElementById('resizer-right');
    const rightPanel = document.getElementById('right-panel');
    let isResizingRight = false;

    resizerRight.addEventListener('mousedown', (e) => {
        isResizingRight = true;
        resizerRight.classList.add('resizer-active');
        document.body.style.cursor = 'col-resize';
        document.body.style.userSelect = 'none';
    });

    document.addEventListener('mousemove', (e) => {
        if (isResizingLeft) {
            const newWidth = Math.min(Math.max(200, e.clientX), 500);
            sidebar.style.width = `${newWidth}px`;
            sidebar.style.minWidth = `${newWidth}px`;
        }
        if (isResizingRight) {
            const newWidth = Math.min(Math.max(200, window.innerWidth - e.clientX), 600);
            rightPanel.style.width = `${newWidth}px`;
            rightPanel.style.minWidth = `${newWidth}px`;
        }
    });

    document.addEventListener('mouseup', () => {
        if (isResizingLeft) {
            isResizingLeft = false;
            resizerLeft.classList.remove('resizer-active');
            document.body.style.cursor = '';
            document.body.style.userSelect = '';
            window.dispatchEvent(new Event('resize'));
        }
        if (isResizingRight) {
            isResizingRight = false;
            resizerRight.classList.remove('resizer-active');
            document.body.style.cursor = '';
            document.body.style.userSelect = '';
            window.dispatchEvent(new Event('resize'));
        }
    });
});

// Toggle Sidebar Table Details Dropdown
function toggleTableDropdown(event, tableName) {
    event.stopPropagation();
    const tableItem = document.getElementById(`table-item-${tableName}`);
    const details = document.getElementById(`details-${tableName}`);
    if (!tableItem || !details) return;

    const isExpanded = tableItem.classList.toggle('expanded');
    details.style.display = isExpanded ? 'block' : 'none';
}

// ─── Window Resize Handler ──────────────────────────────────────
window.addEventListener('resize', () => {
    if (document.getElementById('graph-view').style.display !== 'none') renderGraph();
    if (document.getElementById('db-schema-view').style.display !== 'none') renderERDiagram();
});
window.addEventListener('larable-theme-changed', () => {
    if (document.getElementById('graph-view').style.display !== 'none') renderGraph();
    if (document.getElementById('db-schema-view').style.display !== 'none') renderERDiagram();
});
</script>
@endsection
