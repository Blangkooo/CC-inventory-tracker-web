@php
    use App\Models\User;

    // ── Placeholder fallbacks ─────────────────────────────────────────
    if (!isset($branches) || $branches->isEmpty()) {
        $branches = collect([
            (object)['id'=>1,'name'=>'QC Main Branch', 'location'=>'Quezon City'],
            (object)['id'=>2,'name'=>'Makati Outlet',  'location'=>'Makati City'],
            (object)['id'=>3,'name'=>'BGC Branch',     'location'=>'Taguig City'],
            (object)['id'=>4,'name'=>'Cebu City Branch','location'=>'Cebu City'],
        ]);
    }
    if (!isset($workers) || $workers->isEmpty()) {
        $workers = collect([
            (object)['id'=>1, 'name'=>'Maria Santos',    'role'=>User::ROLE_STAFF, 'branch_id'=>1],
            (object)['id'=>2, 'name'=>'Juan dela Cruz',  'role'=>User::ROLE_STAFF, 'branch_id'=>1],
            (object)['id'=>3, 'name'=>'Ana Reyes',       'role'=>User::ROLE_STAFF, 'branch_id'=>2],
            (object)['id'=>4, 'name'=>'Pedro Gonzales',  'role'=>User::ROLE_STAFF, 'branch_id'=>2],
            (object)['id'=>5, 'name'=>'Luisa Tan',       'role'=>User::ROLE_STAFF, 'branch_id'=>3],
        ]);
    }

    // ── Resolve selected worker ──────────────────────────────────────
    $reqWorkerId = request()->integer('worker', 0);
    $selectedUser = null;
    if ($reqWorkerId) {
        $selectedUser = $workers->firstWhere('id', $reqWorkerId);
    }
    if (!$selectedUser) {
        $selectedUser = $workers->first();
    }

    // ── Build detailed profile from DB + fallback extras ──────────
    $profileFallbacks = [
        'Maria Santos' => [
            'number' => '+63 912 345 6789', 'address' => '123 Katipunan Ave, Quezon City', 'birthday' => 'March 15, 1998',
            'senior_high' => 'Quezon City Science HS', 'college' => 'UP Diliman — BS Nutrition',
            'partner_contact' => '+63 917 654 3210', 'mother_contact' => '+63 908 777 8888',
            'skills' => ['Barista', 'Chef', 'Marketing'], 'note' => 'Allergies: Pollen — Severe',
            'schedule' => ['Mon'=>'10:00 AM — 8:00 PM','Tue'=>'10:00 AM — 8:00 PM','Wed'=>'10:00 AM — 8:00 PM','Thu'=>'10:00 AM — 8:00 PM','Fri'=>'10:00 AM — 8:00 PM'],
            'performance' => ['Always on time','Good customer service','Fast worker','Team player','High accuracy on orders'],
        ],
        'Juan dela Cruz' => [
            'number' => '+63 923 456 7890', 'address' => '456 Shaw Blvd, Mandaluyong City', 'birthday' => 'June 10, 1997',
            'senior_high' => 'Mandaluyong Science HS', 'college' => 'UST — BS Hotel & Restaurant Mgmt',
            'partner_contact' => '+63 927 111 2222', 'mother_contact' => '+63 902 333 4444',
            'skills' => ['Chef', 'Baking', 'Inventory'], 'note' => 'Food handling cert. expires Dec 2026',
            'schedule' => ['Mon'=>'10:00 AM — 8:00 PM','Tue'=>'10:00 AM — 8:00 PM','Wed'=>'10:00 AM — 8:00 PM','Thu'=>'10:00 AM — 8:00 PM','Fri'=>'10:00 AM — 8:00 PM'],
            'performance' => ['Excellent cook','Good inventory management','Team player'],
        ],
        'Ana Reyes' => [
            'number' => '+63 934 567 8901', 'address' => '789 Paseo de Roxas, Makati City', 'birthday' => 'September 22, 1999',
            'senior_high' => 'Makati Science HS', 'college' => 'DLSU — BS Accountancy',
            'partner_contact' => '+63 917 555 6666', 'mother_contact' => '+63 905 777 8888',
            'skills' => ['Cashiering', 'Customer Service', 'Basic Barista'], 'note' => 'Cash bond on file: ₱5,000',
            'schedule' => ['Mon'=>'10:00 AM — 8:00 PM','Tue'=>'10:00 AM — 8:00 PM','Wed'=>'10:00 AM — 8:00 PM','Thu'=>'10:00 AM — 8:00 PM','Fri'=>'10:00 AM — 8:00 PM'],
            'performance' => ['Very reliable','Good with customers','Fast cashier'],
        ],
        'Pedro Gonzales' => [
            'number' => '+63 945 678 9012', 'address' => '321 Ayala Ave, Makati City', 'birthday' => 'January 5, 1996',
            'senior_high' => 'Ateneo de Manila SHS', 'college' => 'ADMU — BS Marketing',
            'partner_contact' => '+63 927 888 9999', 'mother_contact' => '+63 908 111 2222',
            'skills' => ['Marketing', 'Social Media', 'Photography'], 'note' => 'Handles all branch social media accounts',
            'schedule' => ['Mon'=>'9:00 AM — 6:00 PM','Tue'=>'9:00 AM — 6:00 PM','Wed'=>'9:00 AM — 6:00 PM','Thu'=>'9:00 AM — 6:00 PM','Fri'=>'9:00 AM — 6:00 PM'],
            'performance' => ['Creative campaigns','Good social media presence','Team player'],
        ],
        'Luisa Tan' => [
            'number' => '+63 956 789 0123', 'address' => '654 Bonifacio High St, BGC', 'birthday' => 'November 12, 2000',
            'senior_high' => 'BGC International SHS', 'college' => 'UP BGC — BS Business Admin',
            'partner_contact' => '+63 917 444 5555', 'mother_contact' => '+63 905 666 7777',
            'skills' => ['Barista', 'Latte Art', 'Pastry'], 'note' => 'Brewing competition finalist 2025',
            'schedule' => ['Mon'=>'10:00 AM — 8:00 PM','Tue'=>'10:00 AM — 8:00 PM','Wed'=>'10:00 AM — 8:00 PM','Thu'=>'10:00 AM — 8:00 PM','Fri'=>'10:00 AM — 8:00 PM'],
            'performance' => ['Award-winning barista','Fast worker','Great sales upsell'],
        ],
    ];

    $name = $selectedUser->name ?? 'Worker';
    $profile = $selectedUser->profile ?? null;

    // Read from DB profile first, fall back to hardcoded demo data
    $fallback = $profileFallbacks[$name] ?? [
        'number' => '—', 'address' => '—', 'birthday' => '—',
        'senior_high' => '—', 'college' => '—',
        'partner_contact' => '—', 'mother_contact' => '—',
        'skills' => [], 'note' => 'No notes on file.',
        'schedule' => ['Mon'=>'10:00 AM — 8:00 PM','Tue'=>'10:00 AM — 8:00 PM','Wed'=>'10:00 AM — 8:00 PM','Thu'=>'10:00 AM — 8:00 PM','Fri'=>'10:00 AM — 8:00 PM'],
        'performance' => ['No performance data yet.'],
    ];

    $selectedWorker = (object) [
        'id'              => $selectedUser->id ?? 0,
        'name'            => $name,
        'role'            => $selectedUser->role ?? 'staff',
        'role_label'      => $selectedUser->role === User::ROLE_STAFF ? 'Staff' : 'Manager',
        'number'          => $profile?->phone          ?? $fallback['number'],
        'email'           => $selectedUser->email       ?? '—',
        'address'         => $profile?->address         ?? $fallback['address'],
        'birthday'        => $profile?->birthday        ?? $fallback['birthday'],
        'senior_high'     => $profile?->senior_high     ?? $fallback['senior_high'],
        'college'         => $profile?->college         ?? $fallback['college'],
        'partner_contact' => $profile?->partner_contact ?? $fallback['partner_contact'],
        'mother_contact'  => $profile?->mother_contact  ?? $fallback['mother_contact'],
        'skills'          => $profile?->skills          ?? $fallback['skills'],
        'note'            => $profile?->notes           ?? $fallback['note'],
        'schedule'        => $profile?->work_schedule   ?? $fallback['schedule'],
        'performance'     => $profile?->performance_metrics ?? $fallback['performance'],
        'rating'          => $profile?->rating ?? 0,
        'profile_id'      => $profile?->id,
    ];

    $initials = fn($name) => collect(explode(' ', $name))->map(fn($w) => strtoupper($w[0]))->take(2)->implode('');

    // ── Active branch for sidebar ─────────────────────────────────────
    $activeBranch = $branches->firstWhere('id', $selectedUser?->branch_id) ?? $branches->first();

    $openShiftUserIds = $openShiftUserIds ?? [];

    // ── Workers data for JS (pre-computed to avoid @json parsing issues) ──
    $workersJsData = $workers->map(fn($w) => [
        'id'         => $w->id,
        'name'       => $w->name,
        'email'      => $w->email,
        'role'       => $w->role,
        'branch_id'  => $w->branch_id,
        'branch_name'=> $w->branch?->name ?? '',
        'is_on_shift'=> in_array($w->id, $openShiftUserIds),
    ])->values();
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Workers — NITA</title>
@include('partials._shared-styles')

    <style>
        :root {
            --terra-dk: #A8523E;
            --border-light: rgba(92,45,27,.08);
            --shadow-md: 0 2px 8px rgba(92,45,27,.1), 0 8px 24px rgba(92,45,27,.07);
            --radius-sm: 8px;
        }

        /* ── WORKSPACE LAYOUT ── */
        .workspace {
            max-width: 1400px; margin: 0 auto; padding: 24px 32px;
            display: flex; gap: 20px;
        }

        /* ── LEFT SIDEBAR (Branch Selector + Employees) ── */
        .left-sidebar {
            width: 220px; flex-shrink: 0;
            display: flex; flex-direction: column; gap: 16px;
        }

        .branch-card {
            position: relative;
            background: linear-gradient(135deg, #2d1810 0%, #4a2a1e 100%);
            border-radius: var(--radius);
            box-shadow: var(--shadow-md);
            overflow: hidden;
            min-height: 100px;
        }

        .branch-card__pattern {
            position: absolute; inset: 0;
            opacity: .15;
            background-image:
                radial-gradient(circle at 20% 30%, rgba(255,255,255,.3) 1px, transparent 1px),
                radial-gradient(circle at 70% 60%, rgba(255,255,255,.2) 1px, transparent 1px),
                radial-gradient(circle at 40% 80%, rgba(255,255,255,.15) 2px, transparent 2px);
            background-size: 40px 40px, 30px 30px, 50px 50px;
        }

        .branch-card__content {
            position: relative; z-index: 1;
            padding: 16px;
        }

        .branch-card__pill {
            display: inline-block;
            padding: 4px 12px; border-radius: 999px;
            background: rgba(255,255,255,.18); backdrop-filter: blur(4px);
            font-size: 10px; font-weight: 700; color: #fff;
            text-transform: uppercase; letter-spacing: .04em;
            margin-bottom: 10px;
        }

        .branch-card__name {
            font-size: 15px; font-weight: 800; color: #fff; line-height: 1.2;
        }

        .branch-card__sub {
            font-size: 10px; font-weight: 600; color: rgba(255,255,255,.6);
            text-transform: uppercase; letter-spacing: .03em; margin-top: 4px;
        }

        .employees-card {
            background: #fff;
            border: 1px solid var(--border);
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            overflow: hidden;
            display: flex; flex-direction: column;
        }

        .employees-card__head {
            padding: 14px 16px 10px;
            border-bottom: 1px solid var(--border);
        }

        .employees-card__title {
            font-size: 11px; font-weight: 700; text-transform: uppercase;
            letter-spacing: .06em; opacity: .5;
        }

        .employees-list {
            display: flex; flex-direction: column;
            padding: 4px 6px;
        }

        .employee-row {
            display: flex; align-items: center; gap: 10px;
            padding: 9px 10px; border-radius: var(--radius-sm);
            cursor: pointer; transition: all .12s ease;
            text-decoration: none; color: var(--brown);
        }

        .employee-row:hover { background: rgba(92,45,27,.04); }
        .employee-row.is-active { background: rgba(188,97,75,.1); }

        .employee-avatar {
            width: 34px; height: 34px; border-radius: 50%;
            background: var(--terra); color: #fff;
            display: flex; align-items: center; justify-content: center;
            font-size: 12px; font-weight: 700; flex-shrink: 0;
            letter-spacing: .02em;
        }

        .employee-info { flex: 1; min-width: 0; }

        .employee-name {
            font-size: 13px; font-weight: 600;
            white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
        }

        .employee-role {
            font-size: 10px; font-weight: 600; opacity: .5;
            text-transform: uppercase; letter-spacing: .04em;
        }

        .employees-card__add {
            display: block; margin: 6px 12px 12px;
            padding: 9px 0; text-align: center;
            background: #fff; color: var(--brown);
            border: 1.5px solid var(--border); border-radius: 999px;
            font-size: 12px; font-weight: 600; cursor: pointer;
            font-family: var(--font); text-decoration: none;
            transition: all .15s ease;
        }

        .employees-card__add:hover { background: var(--terra); color: #fff; border-color: var(--terra); }

        /* ── MAIN CONTENT ── */
        .main-content { flex: 1; min-width: 0; display: flex; flex-direction: column; gap: 16px; }

        .content-head {
            display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px;
        }

        .content-head__title-wrap { display: flex; align-items: baseline; gap: 10px; }
        .content-head__title { font-size: 22px; font-weight: 800; letter-spacing: .02em; }
        .content-head__role { font-size: 15px; font-weight: 400; opacity: .5; }

        .sub-tabs { display: flex; gap: 6px; }

        .sub-tab {
            display: flex; align-items: center; gap: 6px;
            padding: 7px 16px; border-radius: 999px; font-size: 12px; font-weight: 600;
            border: 1.5px solid var(--border); background: #fff; color: var(--brown);
            text-decoration: none; transition: all .15s ease; cursor: pointer;
        }

        .sub-tab:hover { border-color: var(--terra); color: var(--terra); }
        .sub-tab.is-active { background: var(--terra); color: #fff; border-color: var(--terra); }
        .sub-tab.is-active svg { stroke: #fff; }

        .profile-card {
            background: #fff;
            border: 1.5px solid var(--border);
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            overflow: hidden;
        }

        .profile-card__header {
            display: flex; align-items: center; gap: 16px;
            padding: 20px 24px;
            border-bottom: 1px solid var(--border);
        }

        .profile-avatar {
            width: 56px; height: 56px; border-radius: 50%;
            background: var(--terra); color: #fff;
            display: flex; align-items: center; justify-content: center;
            font-size: 20px; font-weight: 700; flex-shrink: 0;
            border: 3px solid rgba(188,97,75,.2);
        }

        .profile-name-wrap {
            flex: 1; min-width: 140px;
            word-break: keep-all;
        }

        .profile-name { font-size: 18px; font-weight: 800; }
        .profile-role-tag {
            display: inline-block; margin-top: 4px;
            padding: 3px 12px; border-radius: 999px;
            background: rgba(188,97,75,.1); color: var(--terra);
            font-size: 11px; font-weight: 600;
        }

        .profile-actions-bar {
            display: flex; align-items: center; gap: 6px;
            margin-left: auto; flex-shrink: 0;
        }

        .profile-actions-bar .act-btn {
            padding: 7px 12px; border-radius: var(--radius-sm);
            font-size: 11px; font-weight: 600; font-family: var(--font);
            cursor: pointer; transition: all .12s ease; border: 1.5px solid var(--border);
            background: #fff; color: var(--brown); display: flex; align-items: center; gap: 5px;
            white-space: nowrap;
        }

        .profile-actions-bar .act-btn:hover { border-color: var(--terra); color: var(--terra); }
        .profile-actions-bar .act-btn--danger:hover { border-color: #dc2626; color: #dc2626; }
        .profile-actions-bar .act-btn--clock {
            background: var(--terra); color: #fff; border-color: var(--terra);
        }
        .profile-actions-bar .act-btn--clock:hover { background: var(--terra-dk); border-color: var(--terra-dk); }
        .profile-actions-bar .act-btn--clock.is-active {
            background: #16a34a; border-color: #16a34a;
        }

        .profile-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 0;
        }

        .profile-col {
            padding: 20px 24px;
        }

        .profile-col:not(:last-child) { border-right: 1px solid var(--border); }

        .profile-col__title {
            font-size: 10px; font-weight: 700; text-transform: uppercase;
            letter-spacing: .06em; opacity: .5; margin-bottom: 14px;
        }

        .profile-field {
            display: flex; flex-direction: column; gap: 2px;
            margin-bottom: 14px;
        }

        .profile-field:last-child { margin-bottom: 0; }

        .profile-field__label {
            font-size: 10px; font-weight: 600; text-transform: uppercase;
            letter-spacing: .04em; opacity: .4;
        }

        .profile-field__value {
            font-size: 13px; font-weight: 600; color: var(--brown);
            word-break: keep-all; overflow-wrap: break-word;
        }

        .profile-field__value a { color: var(--terra); text-decoration: none; }
        .profile-field__value a:hover { text-decoration: underline; }

        .profile-footer {
            display: grid; grid-template-columns: 1fr 1fr; gap: 0;
            border-top: 1px solid var(--border);
            padding: 16px 24px;
        }

        .profile-skills { display: flex; flex-direction: column; gap: 8px; }

        .profile-skills__title {
            font-size: 10px; font-weight: 700; text-transform: uppercase;
            letter-spacing: .06em; opacity: .5;
        }

        .skills-pills { display: flex; gap: 6px; flex-wrap: wrap; }

        .skill-pill {
            padding: 4px 12px; border-radius: 999px;
            background: rgba(188,97,75,.08); color: var(--terra);
            font-size: 11px; font-weight: 600;
        }

        .profile-note { display: flex; flex-direction: column; gap: 6px; }

        .profile-note__title {
            font-size: 10px; font-weight: 700; text-transform: uppercase;
            letter-spacing: .06em; opacity: .5;
        }

        .profile-note__body {
            font-size: 12px; font-weight: 500; color: var(--brown);
            background: rgba(253,245,214,.6);
            padding: 12px 16px; border-radius: var(--radius-sm);
            border: 1px solid rgba(92,45,27,.08);
            line-height: 1.6; width: 100%;
        }

        .two-up { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }

        .shift-card {
            background: #fff;
            border: 1.5px solid var(--border);
            border-top: 3px solid var(--terra);
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            padding: 20px;
        }

        .shift-card__title {
            font-size: 11px; font-weight: 700; text-transform: uppercase;
            letter-spacing: .08em; opacity: .6; margin-bottom: 16px;
            color: var(--brown);
        }

        .shift-schedule {
            display: grid;
            grid-template-columns: 48px 1fr;
            gap: 2px 12px;
        }

        .shift-row {
            display: contents;
        }

        .shift-day {
            font-size: 12px; font-weight: 700; letter-spacing: .03em;
            padding: 7px 0;
            border-bottom: 1px solid var(--border-light);
            color: var(--brown);
        }

        .shift-hours {
            font-size: 12px; font-weight: 500;
            padding: 7px 0;
            border-bottom: 1px solid var(--border-light);
            color: var(--brown);
            opacity: .75;
        }

        .shift-row:last-child .shift-day,
        .shift-row:last-child .shift-hours {
            border-bottom: none;
        }

        .shift-card__footer {
            display: flex; align-items: center; justify-content: space-between;
            margin-top: 14px; padding-top: 12px;
            border-top: 1px solid var(--border);
        }

        .shift-card__badge {
            display: inline-block;
            padding: 4px 12px; border-radius: 999px;
            background: rgba(188,97,75,.08); color: var(--terra);
            font-size: 10px; font-weight: 700; text-transform: uppercase;
            letter-spacing: .04em;
        }

        .shift-card__edit-btn {
            padding: 5px 14px; border-radius: 6px;
            border: 1.5px solid var(--border); background: #fff;
            color: var(--brown); cursor: pointer;
            font-size: 11px; font-weight: 600; font-family: var(--font);
            transition: all .12s ease;
        }
        .shift-card__edit-btn:hover { border-color: var(--terra); color: var(--terra); }

        .perf-card {
            background: #fff;
            border: 1.5px solid var(--border);
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            padding: 20px;
        }

        .perf-card__head {
            display: flex; align-items: center; justify-content: space-between;
            margin-bottom: 14px;
            padding-bottom: 12px;
            border-bottom: 1px solid var(--border);
        }

        .perf-card__title {
            font-size: 11px; font-weight: 700; text-transform: uppercase;
            letter-spacing: .06em; opacity: .6;
        }

        .perf-card__score {
            display: flex; align-items: center; gap: 6px;
        }

        .perf-rating-pill {
            display: inline-flex; align-items: center; gap: 4px;
            padding: 4px 12px; border-radius: 999px;
            background: rgba(22,163,74,.1); color: #16a34a;
            font-size: 14px; font-weight: 800;
        }

        .perf-rating-pill .stars {
            display: inline-flex; gap: 1px;
            color: #16a34a; font-size: 11px;
        }

        .perf-card__score-label {
            font-size: 9px; font-weight: 600; text-transform: uppercase;
            letter-spacing: .04em; opacity: .4; color: var(--brown);
            margin-right: 2px;
        }

        .perf-list { display: flex; flex-direction: column; gap: 0; }

        .perf-list {
            display: flex; flex-direction: column; gap: 6px;
        }

        .perf-item {
            display: flex; align-items: center; gap: 10px;
            padding: 10px 14px;
            background: rgba(22,163,74,.04);
            border: 1px solid rgba(22,163,74,.12);
            border-radius: var(--radius-sm);
            font-size: 13px; font-weight: 500;
        }

        .perf-item:last-child { border-bottom: none; }

        .perf-dot {
            width: 8px; height: 8px; border-radius: 50%;
            background: #16a34a; flex-shrink: 0;
        }

        .perf-item__text { flex: 1; }

        /* ── RESPONSIVE ── */
        @media (max-width: 1100px) {
            .left-sidebar { width: 180px; }
            .profile-grid { grid-template-columns: 1fr; }
            .profile-col:first-child { border-right: none; border-bottom: 1px solid var(--border); }
            .profile-col:not(:last-child) { border-right: none; border-bottom: 1px solid var(--border); }
            .profile-footer { grid-template-columns: 1fr; gap: 16px; }
        }

        @media (max-width: 880px) {
            .profile-actions-bar { flex-wrap: wrap; justify-content: flex-end; }
        }

        @media (max-width: 880px) {
            .workspace { flex-direction: column; padding: 16px; }
            .left-sidebar { width: 100%; flex-direction: row; gap: 12px; }
            .branch-card { flex: 1; min-height: 80px; }
            .employees-card { flex: 2; }
            .two-up { grid-template-columns: 1fr; }
            .sub-tabs { flex-wrap: wrap; }
        }

        @media (max-width: 640px) {
            .left-sidebar { flex-direction: column; }
            .nav__inner { padding: 0 16px; }
        }

        /* ── MODALS ── */
        .modal-overlay {
            position: fixed; inset: 0; z-index: 999;
            background: rgba(44,24,16,.6); backdrop-filter: blur(4px);
            display: none; align-items: center; justify-content: center;
            padding: 20px;
        }
        .modal-overlay.is-open { display: flex; }

        .modal {
            background: #fff; border-radius: var(--radius);
            box-shadow: 0 16px 48px rgba(44,24,16,.25);
            width: 100%; max-width: 460px; max-height: 90vh; overflow-y: auto;
            padding: 28px 32px 24px;
        }

        .modal__head {
            display: flex; align-items: center; justify-content: space-between;
            margin-bottom: 20px;
        }
        .modal__title { font-size: 17px; font-weight: 800; }
        .modal__close {
            width: 32px; height: 32px; border-radius: 8px;
            background: transparent; border: none; color: var(--brown);
            cursor: pointer; display: flex; align-items: center; justify-content: center;
            transition: background .12s ease; font-size: 18px;
        }
        .modal__close:hover { background: rgba(92,45,27,.07); }

        .modal__body { display: flex; flex-direction: column; gap: 14px; }

        .form-group { display: flex; flex-direction: column; gap: 4px; }
        .form-group label {
            font-size: 11px; font-weight: 700; text-transform: uppercase;
            letter-spacing: .04em; opacity: .6;
        }
        .form-group label .required { color: #dc2626; }
        .form-control {
            padding: 10px 14px; border: 1.5px solid var(--border);
            border-radius: var(--radius-sm); font-size: 13px; font-weight: 500;
            font-family: var(--font); color: var(--brown); background: #fff;
            transition: border-color .15s ease; outline: none;
        }
        .form-control:focus { border-color: var(--terra); }
        .form-control.error { border-color: #dc2626; }
        select.form-control { cursor: pointer; appearance: auto; }
        .form-error { font-size: 11px; color: #dc2626; font-weight: 600; display: none; }
        .form-error.is-visible { display: block; }

        .modal__footer {
            display: flex; gap: 10px; justify-content: flex-end;
            margin-top: 20px; padding-top: 16px;
            border-top: 1px solid var(--border);
        }

        .btn {
            padding: 9px 20px; border-radius: var(--radius-sm);
            font-size: 13px; font-weight: 600; font-family: var(--font);
            cursor: pointer; transition: all .12s ease; border: none;
        }
        .btn--secondary {
            background: transparent; color: var(--brown);
            border: 1.5px solid var(--border);
        }
        .btn--secondary:hover { background: rgba(92,45,27,.04); }
        .btn--primary { background: var(--terra); color: #fff; }
        .btn--primary:hover { background: var(--terra-dk); }
        .btn--danger { background: #dc2626; color: #fff; }
        .btn--danger:hover { background: #b91c1c; }
        .btn:disabled { opacity: .5; cursor: not-allowed; }

        /* ── Toast Notifications ── */
        .toast-container {
            position: fixed; top: 20px; right: 20px; z-index: 1000;
            display: flex; flex-direction: column; gap: 8px;
        }
        .toast {
            padding: 12px 20px; border-radius: var(--radius-sm);
            font-size: 13px; font-weight: 600; color: #fff;
            box-shadow: var(--shadow-md); animation: toast-in .25s ease;
            max-width: 360px;
        }
        .toast--success { background: #16a34a; }
        .toast--error { background: #dc2626; }
        @keyframes toast-in {
            from { opacity: 0; transform: translateX(40px); }
            to { opacity: 1; transform: translateX(0); }
        }



        /* ── Attendance / Timesheet ── */
        .badge {
            display: inline-block; padding: 3px 10px; border-radius: 999px;
            font-size: 11px; font-weight: 600;
        }
        .badge-open  { background: rgba(22,163,74,.1); color: #16a34a; }
        .badge-closed { background: rgba(92,45,27,.06); color: rgba(92,45,27,.5); }

        /* ── Empty state ── */
        .empty-state {
            text-align: center; padding: 24px 20px;
        }
        .empty-state svg {
            display: block; margin: 0 auto 8px;
            opacity: .25; color: var(--brown);
        }
        .empty-state__text {
            font-weight: 600; opacity: .45; font-size: 13px;
        }
        .btn--xs { padding: 4px 10px; font-size: 11px; border-radius: 6px; }
        .btn--xs svg { display: inline; vertical-align: middle; }

        /* ── Summary table re-use ── */
        .summary-table-wrap {
            background: #fff; border: 1px solid var(--border);
            border-radius: var(--radius); box-shadow: var(--shadow);
            overflow: hidden;
        }
        .summary-table-wrap__head {
            padding: 14px 20px; border-bottom: 1px solid var(--border);
            display: flex; align-items: center; gap: 10px;
        }
        .summary-table-wrap__head svg { opacity: .7; flex-shrink: 0; }
        .summary-table-wrap__title { font-size: 13px; font-weight: 700; }
        .summary-table {
            width: 100%; border-collapse: collapse;
        }
        .summary-table th {
            padding: 10px 20px; font-size: 10px; font-weight: 800;
            text-transform: uppercase; letter-spacing: .06em; opacity: .65;
            text-align: left; border-bottom: 1px solid var(--border);
            background: rgba(253,245,214,.5);
            color: var(--brown);
        }
        .summary-table td {
            padding: 10px 20px; font-size: 13px; font-weight: 500;
            border-bottom: 1px solid rgba(92,45,27,.05);
        }
        .summary-table tr:last-child td { border-bottom: none; }
        .summary-table tr:hover td { background: rgba(188,97,75,.04); }

        /* ── Filter bar ── */
        .form-control-sm {
            padding: 7px 10px; border: 1.5px solid var(--border);
            border-radius: 6px; font-size: 12px; font-weight: 500;
            font-family: var(--font); color: var(--brown); background: #fff;
            outline: none; transition: border-color .15s ease;
        }
        .form-control-sm:focus { border-color: var(--terra); }
    </style>
</head>
<body>

<nav class="nav">
    <div class="nav__inner">
        <div class="nav__left">
            <a href="{{ url('/dashboard') }}" class="nav__logo">
                <img src="{{ asset('images/logo.svg') }}" alt="NITA">
            </a>
            <div class="nav__pills">
                <a href="{{ url('/dashboard') }}"        class="nav__pill">Dashboard</a>
                <a href="{{ url('/business/workers') }}"  class="nav__pill is-active">Businesses</a>
                <a href="{{ url('/logistics') }}"         class="nav__pill">Logistics</a>
            </div>
        </div>
        <div class="nav__right">
            <a href="{{ url('/alerts') }}" class="nav__icon" title="Alerts">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/>
                </svg>
            </a>
            <a href="{{ url('/alerts') }}" class="nav__icon nav__icon--box" title="Messages">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="2" y="4" width="20" height="16" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/>
                </svg>
            </a>
            <a href="{{ url('/settings') }}" class="nav__icon" title="Settings">
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="3"/>
                    <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/>
                </svg>
            </a>
            <div class="nav__sep"></div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="nav__logout">Logout</button>
            </form>
        </div>
    </div>
</nav>

<div class="workspace">

    {{-- LEFT SIDEBAR --}}
    <div class="left-sidebar">
        {{-- Branch Selector --}}
        <div class="branch-card">
            <div class="branch-card__pattern"></div>
            <div class="branch-card__content">
                <span class="branch-card__pill">Coffee Shop</span>
                <div class="branch-card__name">{{ $activeBranch->name }}</div>
                <div class="branch-card__sub">{{ $activeBranch->location ?? 'N/A' }}</div>
            </div>
        </div>

        {{-- Employees List --}}
        <div class="employees-card">
            <div class="employees-card__head">
                <div class="employees-card__title">Employees</div>
            </div>
            {{-- Search + Filters --}}
            <div style="padding:8px 10px;border-bottom:1px solid var(--border);display:flex;flex-direction:column;gap:6px;">
                <input type="text" id="searchInput" class="form-control" placeholder="Search by name…" style="padding:7px 10px;font-size:12px;border-radius:6px;">
                <div style="display:flex;gap:4px;">
                    <select id="filterBranch" class="form-control" style="flex:1;padding:5px 8px;font-size:11px;border-radius:6px;">
                        <option value="">All branches</option>
                        @foreach ($branches as $branch)
                            <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                        @endforeach
                    </select>
                    <select id="filterRole" class="form-control" style="flex:1;padding:5px 8px;font-size:11px;border-radius:6px;">
                        <option value="">All roles</option>
                        <option value="staff">Staff</option>
                        <option value="manager">Manager</option>
                    </select>
                </div>
            </div>
            <div class="employees-list" id="employeesList">
                @forelse ($workers as $worker)
                    <a href="{{ url('/business/workers') }}?worker={{ $worker->id }}" class="employee-row {{ $selectedWorker->id === $worker->id ? 'is-active' : '' }}" data-worker-name="{{ strtolower($worker->name) }}" data-worker-branch="{{ $worker->branch_id }}" data-worker-role="{{ $worker->role }}">
                        <div class="employee-avatar">{{ $initials($worker->name) }}</div>
                        <div class="employee-info">
                            <div class="employee-name">{{ $worker->name }}</div>
                            <div class="employee-role">
                                @php
                                    $roleLabel = $worker->role === \App\Models\User::ROLE_MANAGER ? 'Manager' : ($worker->role === \App\Models\User::ROLE_STAFF ? 'Staff' : ucfirst($worker->role));
                                @endphp
                                {{ $roleLabel }}
                            </div>
                        </div>
                    </a>
                @empty
                    <div style="padding:20px 16px;text-align:center;font-size:12px;opacity:.5;font-weight:600;">
                        No workers yet. Click "Add new employee" to get started.
                    </div>
                @endforelse
            </div>
            <button type="button" class="employees-card__add">
                <span style="display:flex;align-items:center;justify-content:center;gap:6px;">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
                    </svg>
                    Add new employee
                </span>
            </button>
        </div>
    </div>

    {{-- MAIN CONTENT --}}
    <div class="main-content">

        {{-- Page Header --}}
        <div class="content-head">
            <div class="content-head__title-wrap">
                <h1 class="content-head__title">Businesses</h1>
                <span class="content-head__role">/ Owner</span>
            </div>
            <div class="sub-tabs">
                <a href="{{ url('/business/summary') }}" class="sub-tab">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/>
                    </svg>
                    Summary
                </a>
                <a href="{{ url('/business/recipes') }}" class="sub-tab">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/>
                        <line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/>
                    </svg>
                    Recipe
                </a>
                <a href="{{ url('/business/workers') }}" class="sub-tab is-active">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>
                    </svg>
                    Workers
                </a>
                <a href="{{ url('/business/verification') }}" class="sub-tab">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="20 6 9 17 4 12"/>
                    </svg>
                    Verification
                </a>
            </div>
        </div>

        {{-- Worker Profile Card --}}
        <div class="profile-card">
            <div class="profile-card__header">
                <div class="profile-avatar">{{ $initials($selectedWorker->name) }}</div>
                <div class="profile-name-wrap">
                    <div class="profile-name">{{ $selectedWorker->name }}</div>
                    <span class="profile-role-tag">{{ $selectedWorker->role }}</span>
                </div>
                @if ($selectedUser)
                @php
                    $isOnShift = in_array($selectedUser->id, $openShiftUserIds);
                @endphp
                <div class="profile-actions-bar">
                    <div id="onShiftIndicator" style="display:{{ $isOnShift ? 'flex' : 'none' }};align-items:center;gap:5px;padding:4px 10px;border-radius:999px;background:rgba(22,163,74,.1);color:#16a34a;font-size:10px;font-weight:700;">
                        <span style="width:6px;height:6px;border-radius:50%;background:#16a34a;"></span>
                        On Shift
                    </div>
                    <button type="button" class="act-btn act-btn--clock {{ $isOnShift ? 'is-active' : '' }}" id="clockBtn"
                        data-worker-id="{{ $selectedWorker->id }}"
                        data-on-shift="{{ $isOnShift ? 'true' : 'false' }}"
                        onclick="toggleClock(this)">
                        <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>
                        </svg>
                        <span id="clockBtnText">{{ $isOnShift ? 'Clock Out' : 'Clock In' }}</span>
                    </button>
                    <button type="button" class="act-btn" onclick="openProfileModal({{ $selectedWorker->id }}, {{ $selectedWorker->profile_id ?? 'null' }})" title="Edit profile">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>
                        </svg>
                        Profile
                    </button>
                    <button type="button" class="act-btn" onclick="openEditModal({{ $selectedWorker->id }})" title="Edit worker">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                            <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                        </svg>
                        Edit
                    </button>
                    <button type="button" class="act-btn act-btn--danger" onclick="openDeleteModal({{ $selectedWorker->id }}, '{{ addslashes($selectedWorker->name) }}')" title="Delete worker">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="3 6 5 6 21 6"/>
                            <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
                        </svg>
                        Delete
                    </button>
                </div>
                @endif
            </div>

            <div class="profile-grid">
                {{-- Col 1: Contact Info --}}
                <div class="profile-col">
                    <div class="profile-col__title">Contact Info</div>
                    <div class="profile-field">
                        <span class="profile-field__label">Number</span>
                        <span class="profile-field__value">{{ $selectedWorker->number }}</span>
                    </div>
                    <div class="profile-field">
                        <span class="profile-field__label">Email</span>
                        <span class="profile-field__value">
                            <a href="mailto:{{ $selectedWorker->email }}">{{ $selectedWorker->email }}</a>
                        </span>
                    </div>
                    <div class="profile-field">
                        <span class="profile-field__label">Address</span>
                        <span class="profile-field__value">{{ $selectedWorker->address }}</span>
                    </div>
                    <div class="profile-field">
                        <span class="profile-field__label">Birthday</span>
                        <span class="profile-field__value">{{ $selectedWorker->birthday }}</span>
                    </div>
                </div>
                {{-- Col 2: Education & Emergency --}}
                <div class="profile-col">
                    <div class="profile-col__title">Education &amp; Emergency</div>
                    <div class="profile-field">
                        <span class="profile-field__label">Senior High</span>
                        <span class="profile-field__value">{{ $selectedWorker->senior_high }}</span>
                    </div>
                    <div class="profile-field">
                        <span class="profile-field__label">College</span>
                        <span class="profile-field__value">{{ $selectedWorker->college }}</span>
                    </div>
                    <div class="profile-field">
                        <span class="profile-field__label">Partner's Contact</span>
                        <span class="profile-field__value">{{ $selectedWorker->partner_contact }}</span>
                    </div>
                    <div class="profile-field">
                        <span class="profile-field__label">Mother's Contact</span>
                        <span class="profile-field__value">{{ $selectedWorker->mother_contact }}</span>
                    </div>
                </div>
            </div>

            {{-- Skills & Notes Footer --}}
            <div class="profile-footer">
                <div class="profile-skills">
                    <span class="profile-skills__title">Skills</span>
                    <div class="skills-pills">
                        @foreach ($selectedWorker->skills as $skill)
                            <span class="skill-pill">{{ $skill }}</span>
                        @endforeach
                    </div>
                </div>
                <div class="profile-note">
                    <span class="profile-note__title">Note</span>
                    <div class="profile-note__body">{{ $selectedWorker->note }}</div>
                </div>
            </div>
        </div>

        {{-- Bottom Two-Up: Work Shift + Performance --}}
        <div class="two-up">
            <div class="shift-card">
                <div class="shift-card__title">Work Shift</div>
                <div class="shift-schedule" id="scheduleDisplay">
                    @foreach ($selectedWorker->schedule as $day => $hours)
                        <div class="shift-row">
                            <span class="shift-day">{{ $day }}</span>
                            <span class="shift-hours">{{ $hours }}</span>
                        </div>
                    @endforeach
                </div>
                <div class="shift-card__footer">
                    <span class="shift-card__badge">Weekends — Off</span>
                    <button type="button" class="shift-card__edit-btn" onclick="openScheduleModal({{ $selectedWorker->id }})">Edit</button>
                </div>
            </div>
            <div class="perf-card">
                <div class="perf-card__head">
                    <span class="perf-card__title">Performance</span>
                    <div style="display:flex;align-items:center;gap:12px;">
                        <div class="perf-card__score">
                            <span class="perf-rating-pill">
                                <span class="stars">
                                    @php
                                        $fullStars = floor($selectedWorker->rating);
                                        $halfStar = $selectedWorker->rating - $fullStars >= 0.5;
                                        $emptyStars = 5 - $fullStars - ($halfStar ? 1 : 0);
                                    @endphp
                                    @for ($i = 0; $i < $fullStars; $i++)&#9733;@endfor
                                    @if ($halfStar)&#9734;@endif
                                    @for ($i = 0; $i < $emptyStars; $i++)&#9734;@endfor
                                </span>
                                {{ number_format($selectedWorker->rating, 1) }}
                            </span>
                        </div>
                        <button type="button" class="btn--xs" onclick="openPerfModal({{ $selectedWorker->id }})" style="border:1.5px solid var(--border);background:#fff;color:var(--brown);cursor:pointer;padding:4px 12px;border-radius:6px;font-size:11px;font-weight:600;transition:all .12s ease;">Record</button>
                    </div>
                </div>
                <div class="perf-list">
                    @forelse ($selectedWorker->performance as $metric)
                        <div class="perf-item">
                            <span class="perf-dot"></span>
                            <span class="perf-item__text">{{ $metric }}</span>
                        </div>
                    @empty
                        <div class="perf-item">
                            <span class="perf-item__text" style="opacity:.4;">No performance data recorded yet.</span>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- ════════════════════════════════════════════════
             ACTIVITY HISTORY (Transactions, Shifts, Discrepancies)
            ════════════════════════════════════════════════ --}}
        <div class="summary-table-wrap" id="activitySection">
            <div class="summary-table-wrap__head">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/>
                </svg>
                <span class="summary-table-wrap__title">Activity — <span id="activityName">{{ $selectedWorker->name }}</span></span>
                <div style="margin-left:auto;display:flex;gap:4px;">
                    <button type="button" class="sub-tab is-active" id="actTabTransactions" onclick="switchActivity({{ $selectedWorker->id }}, 'transactions')" style="padding:4px 12px;font-size:11px;">Orders</button>
                    <button type="button" class="sub-tab" id="actTabShifts" onclick="switchActivity({{ $selectedWorker->id }}, 'shifts')" style="padding:4px 12px;font-size:11px;">Shifts</button>
                    <button type="button" class="sub-tab" id="actTabDiscrepancies" onclick="switchActivity({{ $selectedWorker->id }}, 'discrepancies')" style="padding:4px 12px;font-size:11px;">Variances</button>
                </div>
            </div>
            <div id="activityBody">
                <table class="summary-table" id="activityTable">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Item / Shift</th>
                            <th>Detail</th>
                            <th>Amount</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody id="activityTableBody">
                        <tr><td colspan="5" style="text-align:center;padding:24px 20px;">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" style="display:block;margin:0 auto 8px;opacity:.25;color:#5C2D1B;">
                    <circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>
                </svg>
                <span style="font-weight:600;opacity:.45;font-size:13px;">Loading activity data…</span>
            </td></tr>
                    </tbody>
                </table>
            </div>
        </div>

        {{-- ════════════════════════════════════════════════
             ATTENDANCE TIMESHEET
            ════════════════════════════════════════════════ --}}
        <div class="summary-table-wrap" id="attendanceSection">
            <div class="summary-table-wrap__head">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>
                </svg>
                <span class="summary-table-wrap__title">Attendance &mdash; <span id="attendanceName">{{ $selectedWorker->name }}</span></span>
                <button type="button" class="btn btn--secondary btn--xs" onclick="loadAttendance({{ $selectedWorker->id }})" style="margin-left:auto;padding:5px 12px;font-size:11px;">
                    <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="display:inline;vertical-align:middle;margin-right:4px;">
                        <polyline points="23 4 23 10 17 10"/><path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"/>
                    </svg>
                    Refresh
                </button>
            </div>
            <table class="summary-table" id="attendanceTable">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Clock In</th>
                        <th>Clock Out</th>
                        <th>Duration</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody id="attendanceBody">
                    <tr>
                        <td colspan="5" style="text-align:center;padding:24px 20px;">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" style="display:block;margin:0 auto 8px;opacity:.25;color:#5C2D1B;">
                                <circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>
                            </svg>
                            <span style="font-weight:600;opacity:.45;font-size:13px;">Loading attendance data…</span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

    </div>
</div>

{{-- Toast container --}}
<div class="toast-container" id="toastContainer"></div>

{{-- ═══════════════════════════════════════════════════════════════
     ADD EMPLOYEE MODAL
    ═══════════════════════════════════════════════════════════════ --}}
<div class="modal-overlay" id="addModal">
    <div class="modal">
        <div class="modal__head">
            <h2 class="modal__title">Add New Worker</h2>
            <button type="button" class="modal__close" onclick="closeModal('addModal')">&times;</button>
        </div>
        <form id="addForm" onsubmit="return submitAdd(event)">
            @csrf
            <div class="modal__body">
                <div class="form-group">
                    <label>Name <span class="required">*</span></label>
                    <input type="text" name="name" class="form-control" placeholder="e.g. Juan dela Cruz" required>
                    <span class="form-error" id="addErrorName"></span>
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" class="form-control" placeholder="e.g. juan@nita.com">
                    <span class="form-error" id="addErrorEmail"></span>
                </div>
                <div class="form-group">
                    <label>PIN <span class="required">*</span></label>
                    <input type="text" name="pin" class="form-control" placeholder="4-8 digit PIN" maxlength="8" required>
                    <span class="form-error" id="addErrorPin"></span>
                </div>
                <div class="form-group">
                    <label>Branch <span class="required">*</span></label>
                    <select name="branch_id" class="form-control" required>
                        <option value="">Select a branch…</option>
                        @foreach ($branches as $branch)
                            <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                        @endforeach
                    </select>
                    <span class="form-error" id="addErrorBranch"></span>
                </div>
                <div class="form-group">
                    <label>Role</label>
                    <select name="role" class="form-control">
                        <option value="staff">Staff</option>
                        <option value="manager">Manager</option>
                    </select>
                </div>
            </div>
            <div class="modal__footer">
                <button type="button" class="btn btn--secondary" onclick="closeModal('addModal')">Cancel</button>
                <button type="submit" class="btn btn--primary" id="addSubmitBtn">Add Worker</button>
            </div>
        </form>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════════════
     EDIT EMPLOYEE MODAL
    ═══════════════════════════════════════════════════════════════ --}}
<div class="modal-overlay" id="editModal">
    <div class="modal">
        <div class="modal__head">
            <h2 class="modal__title">Edit Worker</h2>
            <button type="button" class="modal__close" onclick="closeModal('editModal')">&times;</button>
        </div>
        <form id="editForm" onsubmit="return submitEdit(event)">
            @csrf
            @method('PUT')
            <input type="hidden" name="id" id="editId">
            <div class="modal__body">
                <div class="form-group">
                    <label>Name <span class="required">*</span></label>
                    <input type="text" name="name" id="editName" class="form-control" required>
                    <span class="form-error" id="editErrorName"></span>
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" id="editEmail" class="form-control">
                    <span class="form-error" id="editErrorEmail"></span>
                </div>
                <div class="form-group">
                    <label>New PIN <span style="font-size:10px;opacity:.4;font-weight:400">(leave blank to keep current)</span></label>
                    <input type="text" name="pin" class="form-control" placeholder="4-8 digit PIN" maxlength="8">
                    <span class="form-error" id="editErrorPin"></span>
                </div>
                <div class="form-group">
                    <label>Branch <span class="required">*</span></label>
                    <select name="branch_id" id="editBranch" class="form-control" required>
                        <option value="">Select a branch…</option>
                        @foreach ($branches as $branch)
                            <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                        @endforeach
                    </select>
                    <span class="form-error" id="editErrorBranch"></span>
                </div>
                <div class="form-group">
                    <label>Role</label>
                    <select name="role" id="editRole" class="form-control">
                        <option value="staff">Staff</option>
                        <option value="manager">Manager</option>
                    </select>
                </div>
            </div>
            <div class="modal__footer">
                <button type="button" class="btn btn--secondary" onclick="closeModal('editModal')">Cancel</button>
                <button type="submit" class="btn btn--primary" id="editSubmitBtn">Save Changes</button>
            </div>
        </form>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════════════
     DELETE CONFIRMATION MODAL
    ═══════════════════════════════════════════════════════════════ --}}
<div class="modal-overlay" id="deleteModal">
    <div class="modal">
        <div class="modal__head">
            <h2 class="modal__title">Delete Worker</h2>
            <button type="button" class="modal__close" onclick="closeModal('deleteModal')">&times;</button>
        </div>
        <div class="modal__body">
            <p style="font-size:14px;line-height:1.6;opacity:.8">
                Are you sure you want to delete
                <strong id="deleteName"></strong>?
                This action cannot be undone.
            </p>
        </div>
        <div class="modal__footer">
            <button type="button" class="btn btn--secondary" onclick="closeModal('deleteModal')">Cancel</button>
            <button type="button" class="btn btn--danger" id="deleteConfirmBtn">Delete</button>
        </div>
        <form id="deleteForm" method="POST" style="display:none">
            @csrf
            @method('DELETE')
        </form>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════════════
     SCHEDULE EDITOR MODAL
    ═══════════════════════════════════════════════════════════════ --}}
<div class="modal-overlay" id="scheduleModal">
    <div class="modal" style="max-width:500px">
        <div class="modal__head">
            <h2 class="modal__title">Edit Work Schedule</h2>
            <button type="button" class="modal__close" onclick="closeModal('scheduleModal')">&times;</button>
        </div>
        <form id="scheduleForm" onsubmit="return submitSchedule(event)">
            @csrf
            @method('PUT')
            <input type="hidden" id="schedWorkerId" value="{{ $selectedWorker->id }}">
            <div class="modal__body">
                @php $days = ['Mon','Tue','Wed','Thu','Fri','Sat','Sun']; @endphp
                @foreach ($days as $day)
                    <div class="form-group" style="display:flex;flex-direction:row;align-items:center;gap:12px;">
                        <label style="min-width:40px;margin:0;font-size:13px;text-transform:none;letter-spacing:normal;opacity:.8;">{{ $day }}</label>
                        <input type="text" class="form-control sched-input" data-day="{{ $day }}" placeholder="e.g. 10:00 AM — 8:00 PM" value="{{ $selectedWorker->schedule[$day] ?? '' }}" style="flex:1;">
                    </div>
                @endforeach
                <div class="form-group">
                    <label>Weekend Note</label>
                    <input type="text" class="form-control" id="schedWeekend" placeholder="e.g. Weekends — Off">
                </div>
            </div>
            <div class="modal__footer">
                <button type="button" class="btn btn--secondary" onclick="closeModal('scheduleModal')">Cancel</button>
                <button type="submit" class="btn btn--primary" id="schedSubmitBtn">Save Schedule</button>
            </div>
        </form>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════════════
     RECORD PERFORMANCE MODAL
    ═══════════════════════════════════════════════════════════════ --}}
<div class="modal-overlay" id="perfModal">
    <div class="modal" style="max-width:500px">
        <div class="modal__head">
            <h2 class="modal__title">Record Performance</h2>
            <button type="button" class="modal__close" onclick="closeModal('perfModal')">&times;</button>
        </div>
        <form id="perfForm" onsubmit="return submitPerformance(event)">
            @csrf
            @method('PUT')
            <input type="hidden" id="perfWorkerId" value="{{ $selectedWorker->id }}">
            <div class="modal__body">
                <div class="form-group">
                    <label>Rating <span style="font-weight:400;opacity:.5;font-size:10px;">(out of 5)</span></label>
                    <div style="display:flex;align-items:center;gap:12px;">
                        <input type="range" id="perfRating" min="0" max="5" step="0.1" value="{{ $selectedWorker->rating ?: 4.5 }}" oninput="document.getElementById('perfRatingDisplay').textContent = this.value" style="flex:1;">
                        <span id="perfRatingDisplay" style="font-size:16px;font-weight:800;min-width:30px;">{{ $selectedWorker->rating ?: 4.5 }}</span>
                    </div>
                </div>
                <div class="form-group">
                    <label>Metrics <span style="font-weight:400;opacity:.5;font-size:10px;">(one per line)</span></label>
                    <textarea class="form-control" id="perfMetrics" rows="5" placeholder="Always on time&#10;Good customer service&#10;Fast worker" style="resize:vertical;">{{ implode("\n", $selectedWorker->performance) }}</textarea>
                </div>
                <div class="form-group">
                    <label>Notes</label>
                    <textarea class="form-control" id="perfNotes" rows="2" placeholder="Optional notes on this review…" style="resize:vertical;"></textarea>
                </div>
            </div>
            <div class="modal__footer">
                <button type="button" class="btn btn--secondary" onclick="closeModal('perfModal')">Cancel</button>
                <button type="submit" class="btn btn--primary" id="perfSubmitBtn">Save Performance</button>
            </div>
        </form>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════════════
     PROFILE EDIT MODAL
    ═══════════════════════════════════════════════════════════════ --}}
<div class="modal-overlay" id="profileModal">
    <div class="modal" style="max-width:560px">
        <div class="modal__head">
            <h2 class="modal__title">Edit Profile</h2>
            <button type="button" class="modal__close" onclick="closeModal('profileModal')">&times;</button>
        </div>
        <form id="profileForm" onsubmit="return submitProfile(event)">
            @csrf
            @method('PUT')
            <input type="hidden" name="worker_id" id="profileWorkerId">
            <div class="modal__body" style="display:grid;grid-template-columns:1fr 1fr;gap:14px">
                {{-- Left column --}}
                <div style="display:flex;flex-direction:column;gap:14px">
                    <div class="form-group">
                        <label>Phone</label>
                        <input type="text" name="phone" id="pfPhone" class="form-control" placeholder="+63 912 345 6789">
                    </div>
                    <div class="form-group">
                        <label>Address</label>
                        <input type="text" name="address" id="pfAddress" class="form-control" placeholder="123 Main St, City">
                    </div>
                    <div class="form-group">
                        <label>Birthday</label>
                        <input type="text" name="birthday" id="pfBirthday" class="form-control" placeholder="March 15, 1998">
                    </div>
                    <div class="form-group">
                        <label>Senior High</label>
                        <input type="text" name="senior_high" id="pfSeniorHigh" class="form-control" placeholder="School name">
                    </div>
                    <div class="form-group">
                        <label>College</label>
                        <input type="text" name="college" id="pfCollege" class="form-control" placeholder="University — Course">
                    </div>
                </div>
                {{-- Right column --}}
                <div style="display:flex;flex-direction:column;gap:14px">
                    <div class="form-group">
                        <label>Partner's Contact</label>
                        <input type="text" name="partner_contact" id="pfPartnerContact" class="form-control" placeholder="+63 917 654 3210">
                    </div>
                    <div class="form-group">
                        <label>Mother's Contact</label>
                        <input type="text" name="mother_contact" id="pfMotherContact" class="form-control" placeholder="+63 908 777 8888">
                    </div>
                    <div class="form-group">
                        <label>Skills <span style="font-size:10px;opacity:.4">(comma-separated)</span></label>
                        <input type="text" name="skills" id="pfSkills" class="form-control" placeholder="Barista, Chef, Marketing">
                    </div>
                    <div class="form-group" style="grid-column:1/-1">
                        <label>Notes</label>
                        <textarea name="notes" id="pfNotes" class="form-control" rows="3" placeholder="Notes about this worker…"></textarea>
                    </div>
                </div>
            </div>
            <div class="modal__footer">
                <button type="button" class="btn btn--secondary" onclick="closeModal('profileModal')">Cancel</button>
                <button type="submit" class="btn btn--primary" id="profileSubmitBtn">Save Profile</button>
            </div>
        </form>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════════════
     JAVASCRIPT — Modal & AJAX Logic
    ═══════════════════════════════════════════════════════════════ --}}
<script>
    // ── CSRF Token Setup ──
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    const workersData = @json($workersJsData);

    // ── Toast Notification ──
    function showToast(message, type) {
        const container = document.getElementById('toastContainer');
        const toast = document.createElement('div');
        toast.className = 'toast toast--' + type;
        toast.textContent = message;
        container.appendChild(toast);
        setTimeout(() => {
            toast.style.opacity = '0';
            toast.style.transition = 'opacity .3s ease';
            setTimeout(() => toast.remove(), 300);
        }, 3500);
    }

    // ── Modal Helpers ──
    function openModal(id) {
        document.getElementById(id).classList.add('is-open');
    }

    function closeModal(id) {
        document.getElementById(id).classList.remove('is-open');
        document.querySelectorAll('#' + id + ' .form-error').forEach(el => {
            el.classList.remove('is-visible');
            el.textContent = '';
        });
        document.querySelectorAll('#' + id + ' .form-control').forEach(el => {
            el.classList.remove('error');
        });
    }

    // Close modals on backdrop click
    document.querySelectorAll('.modal-overlay').forEach(overlay => {
        overlay.addEventListener('click', function(e) {
            if (e.target === this) {
                this.classList.remove('is-open');
            }
        });
    });

    // ── Live Search & Filter ──
    function filterEmployees() {
        const query = document.getElementById('searchInput').value.toLowerCase().trim();
        const branchVal = document.getElementById('filterBranch').value;
        const roleVal = document.getElementById('filterRole').value;

        document.querySelectorAll('#employeesList .employee-row').forEach(row => {
            const name = row.getAttribute('data-worker-name') || '';
            const branch = row.getAttribute('data-worker-branch') || '';
            const role = row.getAttribute('data-worker-role') || '';

            const matchName = !query || name.includes(query);
            const matchBranch = !branchVal || branch === branchVal;
            const matchRole = !roleVal || role === roleVal;

            row.style.display = (matchName && matchBranch && matchRole) ? '' : 'none';
        });
    }

    document.getElementById('searchInput')?.addEventListener('input', filterEmployees);
    document.getElementById('filterBranch')?.addEventListener('change', filterEmployees);
    document.getElementById('filterRole')?.addEventListener('change', filterEmployees);

    // ── Clock In / Clock Out ──
    async function toggleClock(btn) {
        const indicator = document.getElementById('onShiftIndicator');
        const btnText = document.getElementById('clockBtnText');

        const currentlyOnShift = btn.getAttribute('data-on-shift') === 'true';
        const workerId = parseInt(btn.getAttribute('data-worker-id'));

        btn.disabled = true;
        btn.style.opacity = '.6';

        const endpoint = currentlyOnShift
            ? '{{ url('/business/workers') }}/' + workerId + '/clock-out'
            : '{{ url('/business/workers') }}/' + workerId + '/clock-in';

        try {
            const response = await fetch(endpoint, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
            });

            const data = await response.json();

            if (!response.ok) {
                showToast(data.message || 'Failed to toggle shift.', 'error');
                btn.disabled = false;
                btn.style.opacity = '1';
                return;
            }

            showToast(data.message, 'success');

            // Toggle state visually without page reload
            const newOnShift = !currentlyOnShift;
            if (newOnShift) {
                indicator.style.display = 'flex';
                btn.style.background = '#16a34a';
                btn.style.color = '#fff';
                btn.style.borderColor = '#16a34a';
                btnText.textContent = 'Clock Out';
                btn.setAttribute('data-on-shift', 'true');
            } else {
                indicator.style.display = 'none';
                btn.style.background = '#fff';
                btn.style.color = 'var(--brown)';
                btn.style.borderColor = 'var(--border)';
                btnText.textContent = 'Clock In';
                btn.setAttribute('data-on-shift', 'false');
            }

            // Reload attendance list
            loadAttendance(workerId);

            btn.disabled = false;
            btn.style.opacity = '1';
        } catch (err) {
            showToast('Network error. Please try again.', 'error');
            btn.disabled = false;
            btn.style.opacity = '1';
        }
    }

    // ── Load Attendance History ──
    async function loadAttendance(workerId) {
        const tbody = document.getElementById('attendanceBody');
        const nameSpan = document.getElementById('attendanceName');

        tbody.innerHTML = `
            <tr>
                <td colspan="5" style="text-align:center;opacity:.4;padding:20px;">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" style="display:block;margin:0 auto 8px;opacity:.3">
                        <circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>
                    </svg>
                    Loading attendance…
                </td>
            </tr>`;

        try {
            const response = await fetch('{{ url('/business/workers') }}/' + workerId + '/attendance', {
                headers: { 'Accept': 'application/json' },
            });

            const data = await response.json();

            if (!response.ok || !data.shifts) {
                tbody.innerHTML = `
                    <tr><td colspan="5" style="text-align:center;opacity:.4;padding:20px;">
                        No attendance data available.
                    </td></tr>`;
                return;
            }

            if (nameSpan) {
                const worker = workersData.find(w => w.id === workerId);
                if (worker) nameSpan.textContent = worker.name;
            }

            if (data.shifts.length === 0) {
                tbody.innerHTML = `
                    <tr><td colspan="5" style="text-align:center;opacity:.4;padding:20px;">
                        No shifts recorded yet.
                    </td></tr>`;
                return;
            }

            tbody.innerHTML = data.shifts.map(s => `
                <tr>
                    <td style="font-weight:600">${s.date || '—'}</td>
                    <td>${s.clock_in || '—'}</td>
                    <td>${s.clock_out || '—'}</td>
                    <td>${s.duration || '—'}</td>
                    <td>
                        <span class="badge ${s.status === 'open' ? 'badge-open' : 'badge-closed'}">
                            ${s.status === 'open' ? 'On Shift' : 'Completed'}
                        </span>
                    </td>
                </tr>
            `).join('');
        } catch (err) {
            tbody.innerHTML = `
                <tr><td colspan="5" style="text-align:center;opacity:.4;padding:20px;">
                    Failed to load attendance data.
                </td></tr>`;
        }
    }

    // ── Load attendance on page load for the selected worker ──
    document.addEventListener('DOMContentLoaded', function() {
        const selectedId = {{ $selectedWorker->id }};
        if (selectedId > 0) {
            loadAttendance(selectedId);
        }
    });

    // ── Open Add Modal ──
    document.querySelector('.employees-card__add')?.addEventListener('click', function() {
        openModal('addModal');
    });

    // ── Add Form Submission ──
    async function submitAdd(event) {
        event.preventDefault();
        const form = document.getElementById('addForm');
        const btn = document.getElementById('addSubmitBtn');
        btn.disabled = true;
        btn.textContent = 'Adding…';

        try {
            const formData = new FormData(form);
            const response = await fetch('{{ route('business.workers.store') }}', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                body: formData,
            });

            const data = await response.json();

            if (!response.ok) {
                if (data.errors) {
                    for (const [field, messages] of Object.entries(data.errors)) {
                        const errorEl = document.getElementById('addError' + field.charAt(0).toUpperCase() + field.slice(1));
                        const inputEl = form.querySelector('[name="' + field + '"]');
                        if (errorEl) {
                            errorEl.textContent = messages[0];
                            errorEl.classList.add('is-visible');
                        }
                        if (inputEl) inputEl.classList.add('error');
                    }
                } else {
                    showToast(data.message || 'An error occurred.', 'error');
                }
                btn.disabled = false;
                btn.textContent = 'Add Worker';
                return false;
            }

            showToast(data.message || 'Worker added successfully.', 'success');
            closeModal('addModal');
            setTimeout(() => location.reload(), 800);
        } catch (err) {
            showToast('Network error. Please try again.', 'error');
            btn.disabled = false;
            btn.textContent = 'Add Worker';
        }

        return false;
    }

    // ── Profile Data Store (fetched from the current page state) ──
    const currentProfile = {
        phone: '{{ addslashes($selectedWorker->number) }}',
        address: '{{ addslashes($selectedWorker->address) }}',
        birthday: '{{ addslashes($selectedWorker->birthday) }}',
        senior_high: '{{ addslashes($selectedWorker->senior_high) }}',
        college: '{{ addslashes($selectedWorker->college) }}',
        partner_contact: '{{ addslashes($selectedWorker->partner_contact) }}',
        mother_contact: '{{ addslashes($selectedWorker->mother_contact) }}',
        skills: @json($selectedWorker->skills ?? []),
        notes: '{{ addslashes($selectedWorker->note) }}',
    };

    // ── Open Profile Edit Modal ──
    function openProfileModal(workerId, profileId) {
        document.getElementById('profileWorkerId').value = workerId;
        document.getElementById('pfPhone').value = currentProfile.phone !== '—' ? currentProfile.phone : '';
        document.getElementById('pfAddress').value = currentProfile.address !== '—' ? currentProfile.address : '';
        document.getElementById('pfBirthday').value = currentProfile.birthday !== '—' ? currentProfile.birthday : '';
        document.getElementById('pfSeniorHigh').value = currentProfile.senior_high !== '—' ? currentProfile.senior_high : '';
        document.getElementById('pfCollege').value = currentProfile.college !== '—' ? currentProfile.college : '';
        document.getElementById('pfPartnerContact').value = currentProfile.partner_contact !== '—' ? currentProfile.partner_contact : '';
        document.getElementById('pfMotherContact').value = currentProfile.mother_contact !== '—' ? currentProfile.mother_contact : '';
        document.getElementById('pfSkills').value = Array.isArray(currentProfile.skills) ? currentProfile.skills.join(', ') : '';
        document.getElementById('pfNotes').value = currentProfile.notes !== 'No notes on file.' ? currentProfile.notes : '';
        openModal('profileModal');
    }

    // ── Profile Form Submission ──
    async function submitProfile(event) {
        event.preventDefault();
        const btn = document.getElementById('profileSubmitBtn');
        const workerId = document.getElementById('profileWorkerId').value;
        btn.disabled = true;
        btn.textContent = 'Saving…';

        try {
            // Build form data with skills as array
            const formData = new FormData();
            formData.append('phone', document.getElementById('pfPhone').value);
            formData.append('address', document.getElementById('pfAddress').value);
            formData.append('birthday', document.getElementById('pfBirthday').value);
            formData.append('senior_high', document.getElementById('pfSeniorHigh').value);
            formData.append('college', document.getElementById('pfCollege').value);
            formData.append('partner_contact', document.getElementById('pfPartnerContact').value);
            formData.append('mother_contact', document.getElementById('pfMotherContact').value);
            formData.append('notes', document.getElementById('pfNotes').value);

            // Split skills by comma
            const skillsRaw = document.getElementById('pfSkills').value;
            const skillsArr = skillsRaw.split(',').map(s => s.trim()).filter(s => s.length > 0);
            skillsArr.forEach((skill, i) => formData.append('skills[' + i + ']', skill));

            formData.append('_method', 'PUT');

            const response = await fetch('{{ url('/business/workers') }}/' + workerId + '/profile', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                body: formData,
            });

            const data = await response.json();

            if (!response.ok) {
                showToast(data.message || 'Failed to save profile.', 'error');
                btn.disabled = false;
                btn.textContent = 'Save Profile';
                return;
            }

            showToast('Profile saved successfully.', 'success');
            closeModal('profileModal');
            setTimeout(() => location.reload(), 800);
        } catch (err) {
            showToast('Network error. Please try again.', 'error');
            btn.disabled = false;
            btn.textContent = 'Save Profile';
        }

        return false;
    }

    // ── Open Edit Modal ──
    function openEditModal(workerId) {
        const worker = workersData.find(w => w.id === workerId);
        if (!worker) return;

        document.getElementById('editId').value = worker.id;
        document.getElementById('editName').value = worker.name;
        document.getElementById('editEmail').value = worker.email || '';
        document.getElementById('editBranch').value = worker.branch_id;
        document.getElementById('editRole').value = worker.role;

        openModal('editModal');
    }

    // ── Edit Form Submission ──
    async function submitEdit(event) {
        event.preventDefault();
        const form = document.getElementById('editForm');
        const btn = document.getElementById('editSubmitBtn');
        const workerId = document.getElementById('editId').value;
        btn.disabled = true;
        btn.textContent = 'Saving…';

        try {
            const formData = new FormData(form); // @method('PUT') hidden input already included
            const response = await fetch('{{ url('/business/workers') }}/' + workerId, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                body: formData,
            });

            const data = await response.json();

            if (!response.ok) {
                if (data.errors) {
                    for (const [field, messages] of Object.entries(data.errors)) {
                        const errorEl = document.getElementById('editError' + field.charAt(0).toUpperCase() + field.slice(1));
                        const inputEl = form.querySelector('[name="' + field + '"]');
                        if (errorEl) {
                            errorEl.textContent = messages[0];
                            errorEl.classList.add('is-visible');
                        }
                        if (inputEl) inputEl.classList.add('error');
                    }
                } else {
                    showToast(data.message || 'An error occurred.', 'error');
                }
                btn.disabled = false;
                btn.textContent = 'Save Changes';
                return false;
            }

            showToast(data.message || 'Worker updated successfully.', 'success');
            closeModal('editModal');
            setTimeout(() => location.reload(), 800);
        } catch (err) {
            showToast('Network error. Please try again.', 'error');
            btn.disabled = false;
            btn.textContent = 'Save Changes';
        }

        return false;
    }

    // ── Open Delete Modal ──
    function openDeleteModal(workerId, workerName) {
        document.getElementById('deleteName').textContent = workerName;
        const confirmBtn = document.getElementById('deleteConfirmBtn');

        // Remove old handler and attach new one
        const newBtn = confirmBtn.cloneNode(true);
        confirmBtn.parentNode.replaceChild(newBtn, confirmBtn);

        newBtn.addEventListener('click', async function() {
            newBtn.disabled = true;
            newBtn.textContent = 'Deleting…';

            try {
                const response = await fetch('{{ url('/business/workers') }}/' + workerId, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                    },
                });

                const data = await response.json();

                if (!response.ok) {
                    showToast(data.message || 'An error occurred.', 'error');
                    newBtn.disabled = false;
                    newBtn.textContent = 'Delete';
                    return;
                }

                showToast(data.message || 'Worker deleted successfully.', 'success');
                closeModal('deleteModal');
                setTimeout(() => location.reload(), 800);
            } catch (err) {
                showToast('Network error. Please try again.', 'error');
                newBtn.disabled = false;
                newBtn.textContent = 'Delete';
            }
        });

        openModal('deleteModal');
    }

    // ── Schedule Editor ──
    function openScheduleModal(workerId) {
        document.getElementById('schedWorkerId').value = workerId;
        openModal('scheduleModal');
    }

    async function submitSchedule(event) {
        event.preventDefault();
        const workerId = document.getElementById('schedWorkerId').value;
        const btn = document.getElementById('schedSubmitBtn');
        btn.disabled = true; btn.textContent = 'Saving…';

        const schedule = {};
        document.querySelectorAll('.sched-input').forEach(inp => {
            schedule[inp.getAttribute('data-day')] = inp.value.trim();
        });

        try {
            const res = await fetch('{{ url("/business/workers") }}/' + workerId + '/profile', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrfToken, 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-HTTP-Method-Override': 'PUT' },
                body: JSON.stringify({ work_schedule: schedule }),
            });
            const data = await res.json();
            if (!res.ok) { throw new Error(data.message || 'Save failed'); }
            showToast('Schedule saved! Refreshing…', 'success');
            closeModal('scheduleModal');
            setTimeout(() => location.reload(), 800);
        } catch (err) {
            showToast(err.message, 'error');
            btn.disabled = false; btn.textContent = 'Save Schedule';
        }
    }

    // ── Performance Recording ──
    function openPerfModal(workerId) {
        document.getElementById('perfWorkerId').value = workerId;
        openModal('perfModal');
    }

    async function submitPerformance(event) {
        event.preventDefault();
        const workerId = document.getElementById('perfWorkerId').value;
        const btn = document.getElementById('perfSubmitBtn');
        btn.disabled = true; btn.textContent = 'Saving…';

        const rating = parseFloat(document.getElementById('perfRating').value);
        const metricsRaw = document.getElementById('perfMetrics').value;
        const metrics = metricsRaw.split('\n').map(s => s.trim()).filter(s => s.length > 0);
        const notes = document.getElementById('perfNotes').value.trim();

        const payload = { rating, performance_metrics: metrics };
        if (notes) payload.notes = notes;

        try {
            const res = await fetch('{{ url("/business/workers") }}/' + workerId + '/profile', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrfToken, 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-HTTP-Method-Override': 'PUT' },
                body: JSON.stringify(payload),
            });
            const data = await res.json();
            if (!res.ok) { throw new Error(data.message || 'Save failed'); }
            showToast('Performance saved! Refreshing…', 'success');
            closeModal('perfModal');
            setTimeout(() => location.reload(), 800);
        } catch (err) {
            showToast(err.message, 'error');
            btn.disabled = false; btn.textContent = 'Save Performance';
        }
    }

    // ── Activity History Tabs ──
    let currentActivityWorker = {{ $selectedWorker->id }};
    let currentActivityType = 'transactions';

    function switchActivity(workerId, type) {
        currentActivityWorker = workerId;
        currentActivityType = type;

        document.querySelectorAll('[id^="actTab"]').forEach(t => t.classList.remove('is-active'));
        const tabId = 'actTab' + type.charAt(0).toUpperCase() + type.slice(1);
        const tabEl = document.getElementById(tabId);
        if (tabEl) tabEl.classList.add('is-active');

        const thead = document.querySelector('#activityTable thead tr');
        if (type === 'shifts') {
            thead.innerHTML = '<th>Date</th><th>Clock In</th><th>Clock Out</th><th>Duration</th><th>Status</th>';
        } else if (type === 'discrepancies') {
            thead.innerHTML = '<th>Date</th><th>Ingredient</th><th>Expected</th><th>Actual</th><th>Variance</th>';
        } else {
            thead.innerHTML = '<th>Date</th><th>Item / Shift</th><th>Detail</th><th>Amount</th><th>Status</th>';
        }
        loadActivity(workerId, type);
    }

    async function loadActivity(workerId, type) {
        const tbody = document.getElementById('activityTableBody');
        tbody.innerHTML = '<tr><td colspan="5" style="text-align:center;opacity:.4;padding:20px;">Loading…</td></tr>';
        try {
            const res = await fetch('{{ url("/business/workers") }}/' + workerId + '/activity?type=' + type, {
                headers: { 'Accept': 'application/json' }
            });
            const json = await res.json();
            const data = json.data || [];
            if (data.length === 0) {
                tbody.innerHTML = '<tr><td colspan="5" style="text-align:center;opacity:.3;padding:20px;">No records found.</td></tr>';
                return;
            }
            if (type === 'shifts') {
                tbody.innerHTML = data.map(s => `<tr><td>${s.date}</td><td>${s.clock_in}</td><td>${s.clock_out}</td><td>${s.duration}</td><td><span class="badge ${s.status === 'open' ? 'badge-open' : 'badge-closed'}">${s.status}</span></td></tr>`).join('');
            } else if (type === 'discrepancies') {
                tbody.innerHTML = data.map(d => `<tr><td>${d.date}</td><td>${d.ingredient}</td><td>${d.expected}</td><td>${d.actual}</td><td style="color:${d.severity === 'severe' ? '#dc2626' : d.severity === 'moderate' ? '#d97706' : '#16a34a'};font-weight:700;">${d.variance > 0 ? '+' : ''}${d.variance}</td></tr>`).join('');
            } else {
                tbody.innerHTML = data.map(t => `<tr><td>${t.date}</td><td>${t.product}</td><td>Qty: ${t.quantity}</td><td>₱${parseFloat(t.total).toFixed(2)}</td><td>${t.sync_status || 'synced'}</td></tr>`).join('');
            }
        } catch (err) {
            tbody.innerHTML = '<tr><td colspan="5" style="text-align:center;color:#dc2626;padding:20px;">Failed to load data.</td></tr>';
        }
    }

    // ── Auto-load activity on page load ──
    // (Note: loadAttendance is already called by the existing DOMContentLoaded listener)
    document.addEventListener('DOMContentLoaded', function () {
        if ({{ $selectedWorker->id }} > 0) {
            loadActivity({{ $selectedWorker->id }}, 'transactions');
        }
    });
</script>

</body>
</html>
