@php
    use App\Models\User;

    $reqWorkerId = request()->integer('worker', 0);
    $selectedUser = null;
    if ($reqWorkerId) $selectedUser = $workers->firstWhere('id', $reqWorkerId);
    if (!$selectedUser) $selectedUser = $workers->first();

    $name = $selectedUser->name ?? 'Worker';
    $profile = $selectedUser->profile ?? null;

    $selectedWorker = (object) [
        'id' => $selectedUser->id ?? 0,
        'name' => $name,
        'role' => $selectedUser->role ?? 'staff',
        'role_label' => $selectedUser->role === User::ROLE_STAFF ? 'Staff' : 'Manager',
        'worker_id' => 'Worker' . str_pad($selectedUser->id ?? 0, 4, '0', STR_PAD_LEFT),
        'number' => $profile?->phone ?? '0900 000 0000',
        'email' => $selectedUser->email ?? 'worker.name@gmail.com',
        'address' => $profile?->address ?? 'Makati, Manila, Philippines',
        'birthday' => $profile?->birthday ? \Carbon\Carbon::parse($profile->birthday)->format('F j, Y') : 'November 09, 1994',
        'age' => $profile?->birthday ? \Carbon\Carbon::parse($profile->birthday)->age : 32,
        'senior_high' => $profile?->senior_high ?? 'Manila National High',
        'college' => $profile?->college ?? 'University of the Philippines',
        'partner_contact' => $profile?->partner_contact ?? '0900 000 0000',
        'mother_contact' => $profile?->mother_contact ?? '0900 000 0000',
        'skills' => $profile?->skills ?? ['Barista', 'Chef', 'Marketing'],
        'note' => $profile?->notes ?? 'Allergies: Pollen - Severe',
        'schedule' => $profile?->work_schedule ?? [
            'MONDAY' => '10:00 AM - 8:00 PM',
            'TUESDAY' => '10:00 AM - 8:00 PM',
            'WEDNESDAY' => '10:00 AM - 8:00 PM',
            'THURSDAY' => '10:00 AM - 8:00 PM',
            'FRIDAY' => '10:00 AM - 8:00 PM',
            'SATURDAY' => '8:00 AM - 8:00 PM',
        ],
        'performance' => $profile?->performance_metrics ?? [],
        'rating' => $profile?->rating ?? 0,
        'profile_id' => $profile?->id,
    ];

    $initials = fn($name) => collect(explode(' ', $name))->map(fn($w) => strtoupper($w[0]))->take(2)->implode('');
    $activeBranch = $branches->firstWhere('id', $selectedUser?->branch_id) ?? $branches->first();
    $openShiftUserIds = $openShiftUserIds ?? [];

    // Calculate stats
    $totalEmployees = $workers->count();
    $managers = $workers->filter(fn($w) => $w->role === User::ROLE_MANAGER);
    $staff = $workers->filter(fn($w) => $w->role === User::ROLE_STAFF);
@endphp

@extends('layouts.app')

@section('title', 'owner employees')

@section('content')
<div class="space-y-6">

    {{-- ═══ BUSINESS TABS ═══ --}}
    <div class="flex gap-1 flex-wrap items-center border-b border-[rgba(28,25,23,.12)]">
        <button class="business-tab {{ !($selectedBranchId ?? null) ? 'active' : '' }} px-4 py-2.5 text-[13px] font-semibold border-b-2 border-transparent opacity-50 transition-all hover:opacity-80" onclick="switchView('all', this)">
            All
        </button>
        @foreach($branches as $branch)
            <button class="business-tab {{ ($selectedBranchId ?? null) == $branch->id ? 'active' : '' }} px-4 py-2.5 text-[13px] font-semibold border-b-2 border-transparent opacity-50 transition-all hover:opacity-80" onclick="switchView('branch-{{ $branch->id }}', this)">
                {{ $branch->name }}
            </button>
        @endforeach
        <button class="ml-auto px-4 py-2 rounded-lg text-[13px] font-semibold border border-[#B45353] bg-[#B45353] text-white transition-all hover:bg-[#9F4242]" onclick="switchView('positions', this)">
            Open Positions
        </button>
    </div>

    {{-- ═══ EMPLOYEES LIST VIEW ═══ --}}
    <div id="employeesListView">
        {{-- ═══ STATS ROW ═══ --}}
        <div class="grid grid-cols-2 md:grid-cols-5 bg-white border border-[rgba(28,25,23,.12)] rounded-xl overflow-hidden mb-6">
            <div class="p-5 text-center border-r border-[rgba(28,25,23,.12)]">
                <div class="text-[12px] font-semibold text-[#1C1917] mb-2">Total Employees</div>
                <div class="text-[40px] font-extrabold text-[#B45353] leading-none mb-2">{{ $totalEmployees }}</div>
                <div class="text-[12px] font-semibold text-green-600">5% ↑ <span class="opacity-60">vs last month</span></div>
            </div>
            <div class="p-5 text-center border-r border-[rgba(28,25,23,.12)]">
                <div class="text-[12px] font-semibold text-[#1C1917] mb-2">New Employee</div>
                <div class="text-[40px] font-extrabold text-[#B45353] leading-none mb-2">3</div>
                <div class="text-[12px] font-semibold text-green-600">83% ↑ <span class="opacity-60">vs last month</span></div>
            </div>
            <div class="p-5 text-center border-r border-[rgba(28,25,23,.12)]">
                <div class="text-[12px] font-semibold text-[#1C1917] mb-2">Full Time Employee</div>
                <div class="text-[40px] font-extrabold text-[#B45353] leading-none mb-2">10</div>
                <div class="text-[12px] font-semibold text-green-600">90% ↑ <span class="opacity-60">vs last month</span></div>
            </div>
            <div class="p-5 text-center border-r border-[rgba(28,25,23,.12)]">
                <div class="text-[12px] font-semibold text-[#1C1917] mb-2">Part Time Employee</div>
                <div class="text-[40px] font-extrabold text-[#B45353] leading-none mb-2">5</div>
                <div class="text-[12px] font-semibold text-green-600">60% ↑ <span class="opacity-60">vs last month</span></div>
            </div>
            <div class="p-5 text-center">
                <div class="text-[12px] font-semibold text-[#1C1917] mb-2">Resigned Employee</div>
                <div class="text-[40px] font-extrabold text-[#B45353] leading-none mb-2">8</div>
                <div class="text-[12px] font-semibold text-red-600">20% ↓ <span class="opacity-60">vs last month</span></div>
            </div>
        </div>

        {{-- ═══ EMPLOYEES GRID ═══ --}}
        <div class="grid grid-cols-1 md:grid-cols-3 bg-white border border-[rgba(28,25,23,.12)] rounded-xl overflow-hidden">
            {{-- Branch Managers --}}
            <div class="p-6 border-r border-[rgba(28,25,23,.12)]">
                <div class="text-base font-bold mb-5 pb-3 border-b border-[rgba(28,25,23,.12)]">Branch Managers</div>
                @foreach($branches as $branch)
                    @php $branchManagers = $workers->filter(fn($w) => $w->branch_id === $branch->id && $w->role === User::ROLE_MANAGER); @endphp
                    @if($branchManagers->isNotEmpty())
                        <div class="mb-5">
                            <span class="inline-block px-3 py-1 rounded-full bg-[#FDF5D6] text-[11px] font-semibold text-[#5C2D1B] mb-3">{{ $branch->name }}</span>
                            @foreach($branchManagers as $worker)
                                <div class="py-2 border-b border-[rgba(28,25,23,.12)] cursor-pointer hover:opacity-70 transition-opacity" onclick="viewProfile({{ $worker->id }})">
                                    <div class="text-sm font-semibold">{{ $worker->name }}</div>
                                    <div class="text-xs opacity-60">{{ $branch->name }} Branch</div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                @endforeach
            </div>

            {{-- Full Time Employees --}}
            <div class="p-6 border-r border-[rgba(28,25,23,.12)]">
                <div class="text-base font-bold mb-5 pb-3 border-b border-[rgba(28,25,23,.12)]">Full Time Employees</div>
                @foreach($branches as $branch)
                    @php $branchStaff = $workers->filter(fn($w) => $w->branch_id === $branch->id && $w->role === User::ROLE_STAFF); @endphp
                    @if($branchStaff->isNotEmpty())
                        <div class="mb-5">
                            <span class="inline-block px-3 py-1 rounded-full bg-[#FDF5D6] text-[11px] font-semibold text-[#5C2D1B] mb-2">{{ $branch->name }}</span>
                            <div class="text-sm font-bold text-[#B45353] mb-2">{{ $branch->name }} Branch</div>
                            @foreach($branchStaff->take(3) as $worker)
                                <div class="py-2 border-b border-[rgba(28,25,23,.12)] cursor-pointer hover:opacity-70 transition-opacity" onclick="viewProfile({{ $worker->id }})">
                                    <div class="text-sm font-semibold">{{ $worker->name }}</div>
                                    <div class="text-xs opacity-60">{{ $worker->role === User::ROLE_MANAGER ? 'Manager' : 'Cashier' }}</div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                @endforeach
            </div>

            {{-- Part Time Employees --}}
            <div class="p-6">
                <div class="text-base font-bold mb-5 pb-3 border-b border-[rgba(28,25,23,.12)]">Part Time Employees</div>
                @foreach($branches as $branch)
                    @php $branchStaff = $workers->filter(fn($w) => $w->branch_id === $branch->id && $w->role === User::ROLE_STAFF); @endphp
                    @if($branchStaff->isNotEmpty())
                        <div class="mb-5">
                            <span class="inline-block px-3 py-1 rounded-full bg-[#FDF5D6] text-[11px] font-semibold text-[#5C2D1B] mb-2">{{ $branch->name }}</span>
                            <div class="text-sm font-bold text-[#B45353] mb-2">{{ $branch->name }} Branch</div>
                            @foreach($branchStaff->skip(3)->take(2) as $worker)
                                <div class="py-2 border-b border-[rgba(28,25,23,.12)] cursor-pointer hover:opacity-70 transition-opacity" onclick="viewProfile({{ $worker->id }})">
                                    <div class="text-sm font-semibold">{{ $worker->name }}</div>
                                    <div class="text-xs opacity-60">Server</div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                @endforeach
            </div>
        </div>
    </div>

    {{-- ═══ EMPLOYEE PROFILE VIEW ═══ --}}
    <div id="employeeProfileView" style="display: none;">
        <div class="bg-white border border-[rgba(28,25,23,.12)] rounded-xl overflow-hidden">
            <div class="flex items-center gap-4 p-5 border-b border-[rgba(28,25,23,.12)]">
                <div class="w-14 h-14 rounded-full bg-[#B45353] text-white flex items-center justify-center text-xl font-bold flex-shrink-0">{{ $initials($selectedWorker->name) }}</div>
                <div class="flex-1">
                    <div class="text-lg font-extrabold">{{ $selectedWorker->name }}</div>
                    <div class="text-xs opacity-60 mt-0.5">{{ $selectedWorker->role_label }} | {{ $activeBranch->name ?? 'Branch' }}</div>
                </div>
                <span class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full text-xs font-semibold bg-green-50 text-green-600">Clocked In</span>
                <span class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full text-xs font-semibold bg-gray-100 text-gray-700">Clocked out</span>
                <button class="ml-auto px-5 py-2 rounded-lg text-[13px] font-semibold text-white bg-red-600 border border-red-600 cursor-pointer hover:bg-red-700 transition-colors">Fire Employee</button>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-0 p-5 border-b border-[rgba(28,25,23,.12)]">
                <div class="pr-4">
                    <div class="text-[11px] font-semibold opacity-50 mb-1">WorkerID: {{ $selectedWorker->worker_id }}</div>
                    <div class="text-[11px] font-semibold opacity-50 mb-1">Number: {{ $selectedWorker->number }}</div>
                    <div class="text-[11px] font-semibold opacity-50 mb-1">Email: {{ $selectedWorker->email }}</div>
                    <div class="text-[11px] font-semibold opacity-50 mb-3">Address: {{ $selectedWorker->address }}</div>
                    <div class="text-[11px] font-bold text-[#B45353] mb-2">SKILLS</div>
                    <div class="flex gap-1.5 flex-wrap">
                        @foreach($selectedWorker->skills as $skill)
                            <span class="text-xs">{{ $skill }}</span>
                        @endforeach
                    </div>
                </div>

                <div class="px-4 border-x border-[rgba(28,25,23,.12)]">
                    <div class="text-xs font-bold text-[#B45353] mb-3">EDUCATION</div>
                    <div class="text-[11px] font-semibold opacity-50 mb-1">Senior High: {{ $selectedWorker->senior_high }}</div>
                    <div class="text-[11px] font-semibold opacity-50 mb-4">College: {{ $selectedWorker->college }}</div>
                    <div class="text-xs font-bold text-[#B45353] mb-3">EMERGENCY CONTACT</div>
                    <div class="text-[11px] font-semibold opacity-50 mb-1">Partner: {{ $selectedWorker->partner_contact }}</div>
                    <div class="text-[11px] font-semibold opacity-50">Mother: {{ $selectedWorker->mother_contact }}</div>
                </div>

                <div class="pl-4">
                    <div class="text-[11px] font-semibold opacity-50 mb-1">Birthday: {{ $selectedWorker->birthday }}</div>
                    <div class="text-[11px] font-semibold opacity-50 mb-4">Age: {{ $selectedWorker->age }}</div>
                    <div class="text-xs font-bold text-[#B45353] mb-3">NOTE</div>
                    <div class="text-[11px] font-semibold opacity-50">{{ $selectedWorker->note }}</div>
                </div>
            </div>

            <div class="text-right px-6 py-3 border-b border-[rgba(28,25,23,.12)]">
                <a href="#" class="text-[#B45353] text-[13px] font-semibold no-underline hover:underline">Employment Contract.pdf</a>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-5 p-5">
                {{-- Work Shift --}}
                <div class="bg-white border border-[rgba(28,25,23,.12)] rounded-xl p-5">
                    <div class="text-sm font-bold mb-4">Work Shift</div>
                    @foreach($selectedWorker->schedule as $day => $hours)
                        <div class="flex justify-between py-2 border-b border-[rgba(28,25,23,.12)] text-[13px]">
                            <span class="font-bold text-[11px]">{{ $day }}</span>
                            <span class="opacity-70">{{ $hours }}</span>
                        </div>
                    @endforeach
                    <div class="text-center mt-3 pt-3 border-t border-[rgba(28,25,23,.12)]">
                        <a href="#" class="text-[#B45353] text-xs font-semibold no-underline">Edit Schedule</a>
                    </div>
                </div>

                {{-- Performance & Goals --}}
                <div class="bg-white border border-[rgba(28,25,23,.12)] rounded-xl p-5">
                    <div class="text-sm font-bold mb-4">Performance</div>
                    <div class="w-20 h-20 rounded-full mx-auto mb-3 flex items-center justify-center" style="background: conic-gradient(#B45353 0deg, #B45353 {{ ($selectedWorker->rating > 0 ? ($selectedWorker->rating / 5 * 100) : 80) * 3.6 }}deg, #eee {{ ($selectedWorker->rating > 0 ? ($selectedWorker->rating / 5 * 100) : 80) * 3.6 }}deg)">
                        <div class="w-[60px] h-[60px] rounded-full bg-white flex flex-col items-center justify-center">
                            <div class="text-lg font-extrabold text-[#B45353]">{{ $selectedWorker->rating > 0 ? number_format($selectedWorker->rating * 20, 0) : 80 }}%</div>
                            <div class="text-[9px] opacity-50">monthly goal</div>
                        </div>
                    </div>

                    <div class="text-sm font-bold mt-5 mb-4">Goals</div>
                    <ol class="list-none space-y-1">
                        @foreach(['Maintain Voluntary Turnover Rate', 'Achieve Employee Net Promoter Score', 'Maintain Manager Effectiveness Rating', 'Reduce Training Day Delivery', 'Maintain Quality Metrics', 'Promotion & Mobility', 'Maintain Promotion Rate', 'Skill Coverage', 'Observe Budget Adherence', 'Hiring Efficiency', 'Process Improvement'] as $i => $goal)
                            <li class="text-xs opacity-80">{{ $i + 1 }}. {{ $goal }}</li>
                        @endforeach
                    </ol>
                </div>

                {{-- Peer Review & Reports --}}
                <div class="bg-white border border-[rgba(28,25,23,.12)] rounded-xl p-5">
                    <div class="text-sm font-bold mb-4">Peer Review</div>
                    <ul class="list-none space-y-1.5 mb-5">
                        <li class="text-[13px]">1. Gives good directions</li>
                        <li class="text-[13px]">2. Supports the whole crew</li>
                        <li class="text-[13px]">3. Is very nice and considerate</li>
                    </ul>

                    <div class="text-sm font-bold mb-4">Reports</div>
                    <div class="space-y-2">
                        <div class="flex justify-between items-center py-2.5 border-b border-[rgba(28,25,23,.12)] text-[13px]">
                            <span class="text-[#B45353] font-semibold">FlagReport.pdf</span>
                            <span class="opacity-60">07/ 28/ 2026</span>
                        </div>
                        <div class="flex justify-between items-center py-2.5 border-b border-[rgba(28,25,23,.12)] text-[13px]">
                            <span class="text-[#B45353] font-semibold">PerformanceReport.pdf</span>
                            <span class="opacity-60">07/ 28/ 2026</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ═══ OPEN POSITIONS VIEW ═══ --}}
    <div id="openPositionsView" style="display: none;">
        <div class="grid grid-cols-1 md:grid-cols-[1fr_1.5fr] gap-6">
            {{-- Positions List --}}
            <div class="bg-white border border-[rgba(28,25,23,.12)] rounded-xl p-6">
                <span class="inline-block px-3 py-1 rounded-full bg-[#FDF5D6] text-[11px] font-semibold text-[#5C2D1B] mb-3">To be filled in</span>
                <div class="text-lg font-bold mb-5">Open Positions</div>

                <div class="mb-5 pb-5 border-b border-[rgba(28,25,23,.12)]">
                    <span class="inline-block px-3 py-1 rounded-full bg-[#FDF5D6] text-[11px] font-semibold text-[#5C2D1B] mb-2">Coffee Shop</span>
                    <div class="text-base font-bold">Senior Barista</div>
                    <div class="text-xs opacity-60">QC - Branch</div>
                    <div class="text-xs text-[#B45353] font-semibold">Full Time Position</div>
                </div>

                <div class="mb-5 pb-5 border-b border-[rgba(28,25,23,.12)]">
                    <span class="inline-block px-3 py-1 rounded-full bg-[#FDF5D6] text-[11px] font-semibold text-[#5C2D1B] mb-2">Coffee Shop</span>
                    <div class="text-base font-bold">Server</div>
                    <div class="text-xs opacity-60">UST - Branch</div>
                    <div class="text-xs text-[#B45353] font-semibold">Full Time & Part Time Position</div>
                </div>

                <div class="mb-5">
                    <span class="inline-block px-3 py-1 rounded-full bg-[#FDF5D6] text-[11px] font-semibold text-[#5C2D1B] mb-2">Printing Shop</span>
                    <div class="text-base font-bold">Manager</div>
                    <div class="text-xs opacity-60">UP - Branch</div>
                    <div class="text-xs text-[#B45353] font-semibold">Full Time Position</div>
                </div>
            </div>

            {{-- Add Position Form --}}
            <div>
                <div class="bg-white border border-[rgba(28,25,23,.12)] rounded-xl p-6">
                    <div class="text-lg font-bold mb-5">Open another position</div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block text-xs font-semibold text-[#B45353] mb-1">Position</label>
                            <input type="text" class="w-full px-3.5 py-2.5 border border-[rgba(28,25,23,.12)] rounded-lg text-[13px] focus:outline-none focus:border-[#B45353] transition-colors" placeholder="What is the role?">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-[#B45353] mb-1">Employment Type</label>
                            <input type="text" class="w-full px-3.5 py-2.5 border border-[rgba(28,25,23,.12)] rounded-lg text-[13px] focus:outline-none focus:border-[#B45353] transition-colors" placeholder="Full time, Part time, or both?">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-4">
                        <div>
                            <label class="block text-xs font-semibold text-[#B45353] mb-1">Business</label>
                            <input type="text" class="w-full px-3.5 py-2.5 border border-[rgba(28,25,23,.12)] rounded-lg text-[13px] focus:outline-none focus:border-[#B45353] transition-colors" placeholder="For what business?">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-[#B45353] mb-1">Branch</label>
                            <input type="text" class="w-full px-3.5 py-2.5 border border-[rgba(28,25,23,.12)] rounded-lg text-[13px] focus:outline-none focus:border-[#B45353] transition-colors" placeholder="For which branch?">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-[#B45353] mb-1">Salary</label>
                            <input type="text" class="w-full px-3.5 py-2.5 border border-[rgba(28,25,23,.12)] rounded-lg text-[13px] focus:outline-none focus:border-[#B45353] transition-colors" placeholder="What is the expected salary?">
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="block text-xs font-semibold text-[#B45353] mb-1">Requirements</label>
                        <textarea class="w-full px-3.5 py-2.5 border border-[rgba(28,25,23,.12)] rounded-lg text-[13px] focus:outline-none focus:border-[#B45353] transition-colors min-h-[80px] resize-y" placeholder="What are the requirements?&#10;• Requirement 1&#10;• Requirement 2"></textarea>
                    </div>

                    <div class="flex justify-end">
                        <button class="px-6 py-2.5 rounded-lg text-[13px] font-semibold text-white bg-green-600 border-none cursor-pointer hover:bg-green-700 transition-colors">Open position</button>
                    </div>
                </div>

                {{-- Suggested Positions --}}
                <div class="bg-white border border-[rgba(28,25,23,.12)] rounded-xl p-6 mt-6">
                    <div class="flex items-center gap-3 mb-4">
                        <span class="inline-block px-3 py-1 rounded-full bg-[#FDF5D6] text-[11px] font-semibold text-[#5C2D1B]">Suggested Positions</span>
                        <span class="text-sm font-bold">What positions did managers suggest for?</span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="border border-[#B45353] bg-[rgba(180,83,83,.08)] rounded-xl p-4 cursor-pointer transition-all">
                            <div class="flex justify-between items-center mb-2">
                                <span class="inline-block px-3 py-1 rounded-full bg-[#FDF5D6] text-[11px] font-semibold text-[#5C2D1B]">Burger Shop</span>
                                <span class="text-xs opacity-60">07/ 22/ 2026</span>
                            </div>
                            <div class="text-sm font-bold">Cook</div>
                            <div class="text-xs opacity-60">Makati - Branch</div>
                            <div class="text-xs text-[#B45353] font-semibold">Full Time or Part Time Position</div>
                        </div>

                        <div class="border border-[rgba(28,25,23,.12)] rounded-xl p-4 cursor-pointer transition-all hover:border-[#B45353]">
                            <div class="flex justify-between items-center mb-2">
                                <span class="inline-block px-3 py-1 rounded-full bg-[#FDF5D6] text-[11px] font-semibold text-[#5C2D1B]">Bakery</span>
                                <span class="text-xs opacity-60">07/ 22/ 2026</span>
                            </div>
                            <div class="text-sm font-bold">Baker</div>
                            <div class="text-xs opacity-60">QC - Branch</div>
                            <div class="text-xs text-[#B45353] font-semibold">Full Time Position</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// ══ VIEW SWITCHING ═════════════════════════════════════════════════════
function switchView(view, el) {
    if (view === 'positions') {
        // Open positions doesn't use branch filtering
        document.querySelectorAll('.business-tab').forEach(t => {
            t.classList.remove('active');
            t.style.opacity = '0.5';
            t.style.borderBottomColor = 'transparent';
        });
        if (el) {
            el.classList.add('active');
            el.style.opacity = '1';
            el.style.borderBottomColor = '#B45353';
        }
        document.getElementById('employeesListView').style.display = 'none';
        document.getElementById('employeeProfileView').style.display = 'none';
        document.getElementById('openPositionsView').style.display = 'block';
        return;
    }

    // Update URL without reload
    var url = new URL(window.location.href);
    if (view === 'all') {
        url.searchParams.delete('branch_id');
    } else if (view.startsWith('branch-')) {
        var branchId = view.replace('branch-', '');
        url.searchParams.set('branch_id', branchId);
    }
    history.pushState({}, '', url.toString());

    // Update active tab styling
    document.querySelectorAll('.business-tab').forEach(t => {
        t.classList.remove('active');
        t.style.opacity = '0.5';
        t.style.borderBottomColor = 'transparent';
    });
    if (el) {
        el.classList.add('active');
        el.style.opacity = '1';
        el.style.borderBottomColor = '#B45353';
    }

    // Show loading state
    document.getElementById('employeesListView').style.opacity = '0.4';
    document.getElementById('employeesListView').style.display = 'block';
    document.getElementById('employeeProfileView').style.display = 'none';
    document.getElementById('openPositionsView').style.display = 'none';

    // Determine branch_id for AJAX
    var branchParam = '';
    if (view.startsWith('branch-')) {
        branchParam = view.replace('branch-', '');
    }

    // Fetch new data via AJAX
    fetch('/ajax/workers?branch_id=' + branchParam, {
        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        renderWorkersData(data);
        document.getElementById('employeesListView').style.opacity = '1';
    })
    .catch(function() {
        document.getElementById('employeesListView').style.opacity = '1';
    });
}

function renderWorkersData(data) {
    if (!data.workers) return;
    var workers = data.workers;
    var branches = data.branchs || [];

    // Update stats
    var statsEls = document.querySelectorAll('#employeesListView .grid-cols-2 > div');
    if (statsEls.length >= 1) {
        statsEls[0].querySelector('.text-\\[40px\\]') || null;
        // Update total employees count
        var totalEl = statsEls[0];
        if (totalEl) {
            var countEl = totalEl.querySelector('[class*="text-[40px]"]');
            if (countEl) countEl.textContent = workers.length;
        }
    }

    // Rebuild workers grid
    var managers = workers.filter(function(w) { return w.role === 'manager'; });
    var staff = workers.filter(function(w) { return w.role === 'staff'; });

    var gridContainer = document.querySelector('#employeesListView .grid.grid-cols-1.md\\:grid-cols-3');
    if (!gridContainer) return;

    // Get branch names from workers
    var branchMap = {};
    workers.forEach(function(w) {
        if (w.branch && !branchMap[w.branch_id]) {
            branchMap[w.branch_id] = w.branch.name;
        }
    });

    // Managers column
    var managersHtml = '<div class="p-6 border-r border-[rgba(28,25,23,.12)]"><div class="text-base font-bold mb-5 pb-3 border-b border-[rgba(28,25,23,.12)]">Branch Managers</div>';
    var branchIds = Object.keys(branchMap);
    branchIds.forEach(function(bid) {
        var bManagers = managers.filter(function(w) { return w.branch_id == bid; });
        if (bManagers.length > 0) {
            managersHtml += '<div class="mb-5"><span class="inline-block px-3 py-1 rounded-full bg-[#FDF5D6] text-[11px] font-semibold text-[#5C2D1B] mb-3">' + branchMap[bid] + '</span>';
            bManagers.forEach(function(w) {
                managersHtml += '<div class="py-2 border-b border-[rgba(28,25,23,.12)] cursor-pointer hover:opacity-70 transition-opacity" onclick="viewProfile(' + w.id + ')"><div class="text-sm font-semibold">' + w.name + '</div><div class="text-xs opacity-60">' + branchMap[bid] + ' Branch</div></div>';
            });
            managersHtml += '</div>';
        }
    });
    managersHtml += '</div>';

    // Full time column
    var fullHtml = '<div class="p-6 border-r border-[rgba(28,25,23,.12)]"><div class="text-base font-bold mb-5 pb-3 border-b border-[rgba(28,25,23,.12)]">Full Time Employees</div>';
    branchIds.forEach(function(bid) {
        var bStaff = staff.filter(function(w) { return w.branch_id == bid; }).slice(0, 3);
        if (bStaff.length > 0) {
            fullHtml += '<div class="mb-5"><span class="inline-block px-3 py-1 rounded-full bg-[#FDF5D6] text-[11px] font-semibold text-[#5C2D1B] mb-2">' + branchMap[bid] + '</span>';
            fullHtml += '<div class="text-sm font-bold text-[#B45353] mb-2">' + branchMap[bid] + ' Branch</div>';
            bStaff.forEach(function(w) {
                fullHtml += '<div class="py-2 border-b border-[rgba(28,25,23,.12)] cursor-pointer hover:opacity-70 transition-opacity" onclick="viewProfile(' + w.id + ')"><div class="text-sm font-semibold">' + w.name + '</div><div class="text-xs opacity-60">' + (w.role === 'manager' ? 'Manager' : 'Cashier') + '</div></div>';
            });
            fullHtml += '</div>';
        }
    });
    fullHtml += '</div>';

    // Part time column
    var partHtml = '<div class="p-6"><div class="text-base font-bold mb-5 pb-3 border-b border-[rgba(28,25,23,.12)]">Part Time Employees</div>';
    branchIds.forEach(function(bid) {
        var bStaff = staff.filter(function(w) { return w.branch_id == bid; }).slice(3);
        if (bStaff.length > 0) {
            partHtml += '<div class="mb-5"><span class="inline-block px-3 py-1 rounded-full bg-[#FDF5D6] text-[11px] font-semibold text-[#5C2D1B] mb-2">' + branchMap[bid] + '</span>';
            partHtml += '<div class="text-sm font-bold text-[#B45353] mb-2">' + branchMap[bid] + ' Branch</div>';
            bStaff.forEach(function(w) {
                partHtml += '<div class="py-2 border-b border-[rgba(28,25,23,.12)] cursor-pointer hover:opacity-70 transition-opacity" onclick="viewProfile(' + w.id + ')"><div class="text-sm font-semibold">' + w.name + '</div><div class="text-xs opacity-60">Server</div></div>';
            });
            partHtml += '</div>';
        }
    });
    partHtml += '</div>';

    gridContainer.innerHTML = managersHtml + fullHtml + partHtml;
}

// ══ VIEW PROFILE ═══════════════════════════════════════════════════════
function viewProfile(workerId) {
    document.getElementById('employeesListView').style.display = 'none';
    document.getElementById('openPositionsView').style.display = 'none';
    document.getElementById('employeeProfileView').style.display = 'block';
    console.log('View profile for worker:', workerId);
}

// ══ SUGGESTED ITEMS ═══════════════════════════════════════════════════
document.querySelectorAll('.suggested-item').forEach(item => {
    item.addEventListener('click', function() {
        document.querySelectorAll('.suggested-item').forEach(i => i.classList.remove('selected'));
        this.classList.add('selected');
    });
});
</script>
@endsection
