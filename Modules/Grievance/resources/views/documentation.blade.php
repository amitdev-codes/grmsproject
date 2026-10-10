<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="color-scheme" content="light">
    <title>{{ $applicationName }} — System Guide</title>
    <style>
        :root { color-scheme: light; --ink:#16243a; --muted:#62718a; --line:#e1e8f0; --paper:#fff; --canvas:#f3f6fb; --blue:#1647a5; --green:#08784d; --amber:#a34f08; }
        * { box-sizing:border-box; }
        body { margin:0; color:var(--ink); background:var(--canvas); font:15px/1.65 Inter,ui-sans-serif,system-ui,-apple-system,"Segoe UI",sans-serif; }
        a { color:var(--blue); text-decoration:none; }
        a:hover { text-decoration:underline; }
        .hero { padding:64px max(24px,calc((100vw - 1120px)/2)); color:white; background:radial-gradient(ellipse at 90% 0,#3575c4 0,transparent 40%),linear-gradient(125deg,#102954,#1647a5 70%,#2175a5); }
        .eyebrow { text-transform:uppercase; letter-spacing:.15em; font-size:12px; font-weight:700; opacity:.8; }
        h1 { max-width:820px; margin:10px 0; font-size:clamp(34px,6vw,58px); line-height:1.1; letter-spacing:-.04em; }
        .hero p { max-width:760px; color:#e4efff; font-size:17px; }
        .hero-meta { display:flex; flex-wrap:wrap; gap:10px; margin-top:26px; }
        .hero-meta span { padding:5px 12px; border:1px solid #ffffff55; border-radius:999px; color:#f0f6ff; font-size:13px; }
        .layout { display:grid; grid-template-columns:230px minmax(0,1fr); gap:32px; max-width:1168px; margin:32px auto; padding:0 24px; align-items:start; }
        nav { position:sticky; top:24px; padding:18px; border:1px solid var(--line); border-radius:16px; background:var(--paper); }
        nav strong { display:block; margin-bottom:10px; }
        nav a { display:block; padding:5px 0; color:var(--muted); font-size:13px; }
        main { min-width:0; }
        section,.summary-card { margin-bottom:20px; padding:24px; border:1px solid var(--line); border-radius:18px; background:var(--paper); box-shadow:0 8px 30px #182a4610; }
        h2 { margin:0 0 12px; font-size:23px; letter-spacing:-.02em; }
        h3 { margin:0 0 5px; font-size:16px; }
        .lead,.muted { color:var(--muted); }
        .summary { display:grid; grid-template-columns:repeat(3,minmax(0,1fr)); gap:12px; margin-bottom:20px; }
        .summary-card { margin:0; padding:18px; }
        .summary-card b { display:block; font-size:26px; line-height:1.2; }
        .summary-card span { color:var(--muted); font-size:13px; }
        .steps { display:grid; gap:12px; counter-reset:step; }
        .step { position:relative; padding:16px 18px 16px 64px; border:1px solid var(--line); border-radius:14px; }
        .step:before { counter-increment:step; content:counter(step); position:absolute; top:17px; left:17px; display:grid; place-items:center; width:32px; height:32px; border-radius:10px; color:#fff; background:var(--blue); font-weight:700; }
        .step p { margin:3px 0 0; color:var(--muted); }
        .tags { display:flex; flex-wrap:wrap; gap:7px; margin-top:10px; }
        .tag { display:inline-block; padding:3px 9px; border-radius:999px; color:#1a458d; background:#edf3ff; font-size:12px; }
        .tag.final { color:var(--green); background:#e9f8f0; }
        .tag.reject { color:var(--amber); background:#fff4e8; }
        .table-wrap { overflow-x:auto; }
        table { width:100%; border-collapse:collapse; text-align:left; font-size:13px; }
        th,td { padding:10px 12px; border-bottom:1px solid var(--line); vertical-align:top; }
        th { color:var(--muted); font-size:11px; text-transform:uppercase; letter-spacing:.08em; }
        code { padding:2px 5px; border-radius:5px; background:#f0f3f8; font-size:12px; overflow-wrap:anywhere; }
        .api-list { display:grid; gap:11px; }
        .api { display:grid; grid-template-columns:65px minmax(180px,1fr) 2fr; gap:12px; align-items:center; padding:12px; border:1px solid var(--line); border-radius:12px; }
        .method { font-size:11px; font-weight:800; text-align:center; padding:4px 7px; border-radius:6px; color:#075c3c; background:#e8f7ef; }
        .method.post { color:#174797; background:#edf3ff; }
        .callout { padding:13px 15px; border-left:3px solid var(--blue); border-radius:5px; color:#344866; background:#f2f6ff; }
        footer { max-width:1168px; margin:0 auto; padding:0 24px 38px; color:var(--muted); font-size:12px; }
        @media(max-width:760px) { .layout { grid-template-columns:1fr; gap:16px; margin-top:16px; } nav { position:static; } nav div { display:grid; grid-template-columns:repeat(2,1fr); gap:0 12px; } .summary { grid-template-columns:1fr; } .api { grid-template-columns:56px 1fr; } .api span:last-child { grid-column:2; } .hero { padding-top:42px; padding-bottom:42px; } section { padding:19px; } }
    </style>
</head>
<body>
<header class="hero">
    <div class="eyebrow">Living system guide · Generated from current configuration</div>
    <h1>{{ $applicationName }}</h1>
    <p>{{ $applicationDescription ?: 'A practical guide to roles, public grievance intake, case routing, and resolution.' }}</p>
    <div class="hero-meta"><span>Operations handbook</span><span>Public API reference</span><span>Updated {{ $generatedAt->format('d M Y, H:i') }}</span></div>
</header>
<div class="layout">
    <nav aria-label="Guide contents">
        <strong>In this guide</strong>
        <div>
            <a href="#overview">Overview</a>
            <a href="#intake">Registration process</a>
            <a href="#workflow">Configured workflow</a>
            <a href="#roles">Roles and access</a>
            <a href="#channels">Intake channels</a>
            <a href="#api">Mobile APIs</a>
            <a href="#security">Privacy and safety</a>
            <a href="#maintenance">Keeping this guide current</a>
        </div>
    </nav>
    <main>
        <div class="summary" id="overview">
            <div class="summary-card"><b>{{ $roles->count() }}</b><span>configured roles</span></div>
            <div class="summary-card"><b>{{ $roles->sum('active_users') }}</b><span>active role assignments</span></div>
            <div class="summary-card"><b>{{ $categoryCount }}</b><span>active grievance categories</span></div>
        </div>
        <section id="intake">
            <h2>How grievance registration works</h2>
            <p class="lead">Grievances enter through the configured public channels or are recorded by authorized staff. Public submissions receive a reference number immediately and then enter the routing and notification process.</p>
            <div class="steps">
                <article class="step"><h3>Receive and validate</h3><p>Collect the description, category, location, contact preference, and optional evidence. Required fields and upload limits are validated before a grievance is stored.</p></article>
                <article class="step"><h3>Protect the public intake</h3><p>Submission traffic is checked against configured IP blocks and rate limits. Bot verification and recent duplicate checks run before the grievance is accepted.</p></article>
                <article class="step"><h3>Create and acknowledge</h3><p>The registration service generates a tracking reference, records the intake channel and submission history, attaches evidence, applies service deadlines, and initiates routing.</p></article>
                <article class="step"><h3>Review, investigate, and communicate</h3><p>Authorized staff progress the grievance, record decisions in its status history, and communicate updates. Resolution and closure are recorded against the case.</p></article>
            </div>
        </section>
        <section id="workflow">
            <h2>Configured review workflow</h2>
            <p class="lead">This list is read live from the active default workflow configuration.</p>
            @forelse($workflowSteps as $step)
                <article class="step">
                    <h3>Level {{ $step->step_number }} · {{ $step->name }}</h3>
                    <p>{{ $step->description ?: 'No additional instructions configured.' }}</p>
                    <div class="tags">
                        <span class="tag">{{ $step->role_name ?: ($step->approver_user_id ? 'Specific user assigned' : 'No approver configured') }}</span>
                        <span class="tag">{{ $step->approval_action === 'resolve' ? 'Approval resolves grievance' : 'Approval advances to next level' }}</span>
                        @if($step->rejection_action === 'return_to_step')
                            <span class="tag reject">Rejection returns to Level {{ $step->rejection_target_step }}</span>
                        @else
                            <span class="tag reject">Rejection rejects grievance</span>
                        @endif
                        @if($step->is_final_approval)<span class="tag final">Final approval</span>@endif
                    </div>
                </article>
            @empty
                <div class="callout">No active default workflow steps are configured.</div>
            @endforelse
            <div class="callout" style="margin-top:16px">Workflow configuration is maintained from <strong>Grievances → Workflow</strong>. Application routing currently uses the existing Director → Division Director → Section Manager → Helpdesk Officer flow; editing these workflow rows does not yet change case routing or approval behavior.</div>
        </section>
        <section id="roles">
            <h2>Roles and access</h2>
            <p class="lead">Role names, active user counts, and assigned permissions are generated from the current role configuration.</p>
            <div class="table-wrap"><table><thead><tr><th>Role</th><th>Active users</th><th>Permissions</th></tr></thead><tbody>
                @foreach($roles as $role)
                    <tr><td><strong>{{ $role['name'] }}</strong></td><td>{{ $role['active_users'] }}</td><td>@forelse($role['permissions'] as $permission)<code>{{ $permission }}</code>@if(!$loop->last) @endif @empty<span class="muted">No explicit permissions</span>@endforelse</td></tr>
                @endforeach
            </tbody></table></div>
        </section>
        <section id="channels">
            <h2>Grievance intake channels</h2>
            <p class="lead">These channels are active in the current grievance channel register.</p>
            <div class="tags">@forelse($channels as $channel)<span class="tag">{{ $channel->name }} · <code>{{ $channel->code }}</code></span>@empty<span class="muted">No intake channels configured.</span>@endforelse</div>
        </section>
        <section id="api">
            <h2>Mobile API reference</h2>
            <p class="lead">These public endpoints do not require a user login. Use Swagger UI for interactive request examples and response schemas.</p>
            <div class="callout" style="margin-bottom:14px">Interactive API explorer: <a href="/api/documentation"><strong>/api/documentation</strong></a></div>
            <div class="api-list">
                <div class="api"><span class="method">GET</span><code>/api/v1/mobile/landing</code><span>Public summary, categories, districts, and divisions.</span></div>
                <div class="api"><span class="method">GET</span><code>/api/v1/mobile/reports/summary</code><span>Aggregate counts and monthly report data.</span></div>
                <div class="api"><span class="method">GET</span><code>/api/v1/mobile/captcha</code><span>Get the currently configured Cloudflare or local captcha challenge.</span></div>
                <div class="api"><span class="method post">POST</span><code>/api/v1/mobile/grievances</code><span>Lodge a grievance; retain the returned reference number.</span></div>
                <div class="api"><span class="method post">POST</span><code>/api/v1/mobile/grievances/track</code><span>Track using a reference and the contact used for named submissions.</span></div>
            </div>
        </section>
        <section id="security">
            <h2>Privacy and safety</h2>
            <p>Named grievances require the registered phone number or email address to track case details. Anonymous cases can be tracked with their reference number. Public reports contain aggregate statistics, not individual case records.</p>
            <p>Current settings: up to <strong>{{ $intakeSecurity['requests_per_minute'] }} submissions per IP per minute</strong>; duplicate checks cover the last <strong>{{ $intakeSecurity['duplicate_window_hours'] }} hours</strong> (zero disables them); captcha uses <strong>{{ $intakeSecurity['captcha_provider'] === 'cloudflare_turnstile' ? 'Cloudflare Turnstile' : 'the built-in math challenge' }}</strong>.</p>
            <p>Intake rate limits, IP lists, duplicate detection, and captcha provider are configured in <strong>Settings → Security</strong>. Cloudflare Turnstile secrets are stored encrypted. Staff management APIs remain protected by Sanctum and application permissions.</p>
        </section>
        <section id="maintenance">
            <h2>Keeping this guide current</h2>
            <p>This page is assembled from live roles and permissions, active intake channels, categories, application details, and workflow rows each time it is opened. It does not need a redeploy when that reference data changes.</p>
            <p>Safe cache and optimization commands are available under <strong>Settings → Optimize App</strong> to Super Admin users.</p>
        </section>
    </main>
</div>
<footer>Generated from the current GRMS configuration · <a href="/api/documentation">Open the interactive API documentation</a></footer>
</body>
</html>
