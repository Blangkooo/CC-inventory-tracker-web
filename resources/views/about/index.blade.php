@extends('layouts.app')

@section('title', 'owner about')

@section('content')
<div class="max-w-[1000px] mx-auto overflow-hidden mt-6">

    {{-- ═══ CONTENT TABS ═══ --}}
    <div class="flex gap-5 mb-6 flex-wrap">
        <button class="content-tab active px-3 py-2 text-[13px] font-semibold text-[#5C2D1B] opacity-60 border-b-2 border-transparent transition-all hover:opacity-100 flex items-center gap-1.5" onclick="switchTab('about-nita', this)">
            About NITA
        </button>
        <button class="content-tab px-3 py-2 text-[13px] font-semibold text-[#5C2D1B] opacity-60 border-b-2 border-transparent transition-all hover:opacity-100 flex items-center gap-1.5" onclick="switchTab('about-owner', this)">
            About Owner Account
        </button>
        <button class="content-tab px-3 py-2 text-[13px] font-semibold text-[#5C2D1B] opacity-60 border-b-2 border-transparent transition-all hover:opacity-100 flex items-center gap-1.5" onclick="switchTab('privacy', this)">
            Privacy Policy
        </button>
        <button class="content-tab px-3 py-2 text-[13px] font-semibold text-[#5C2D1B] opacity-60 border-b-2 border-transparent transition-all hover:opacity-100 flex items-center gap-1.5" onclick="switchTab('terms', this)">
            Terms & Conditions
        </button>
    </div>

    {{-- ═══ ABOUT NITA ═══ --}}
    <div id="about-nita" class="bg-white border border-[rgba(28,25,23,.12)] rounded-2xl p-7">
        <span class="inline-block px-3 py-1 rounded-full bg-[#FDF5D6] text-[11px] font-semibold text-[#5C2D1B] mb-5">About NITA</span>

        <h2 class="text-lg font-bold text-[#B45353] mb-3">What is Nita?</h2>
        <p class="text-sm leading-relaxed text-[#5C2D1B] mb-4 break-words">
            Nita is an all-in-one, multi-branch inventory tracker and operational copilot designed to bridge the gap between remote business owners and their physical stores. Nita turns chaotic spreadsheets and manual logs into a single, cohesive dashboard that tracks inventory, logs transactions, and monitors employee attendance in real time.
        </p>
        <p class="text-sm leading-relaxed text-[#5C2D1B] mb-5 break-words">
            By calculating expected sales versus actual performance, Nita shines a light on operational "leakage" (theft, damage, or undocumented giveaways) so business owners can make data-driven decisions from anywhere in the world.
        </p>

        <div class="mt-5">
            <h3 class="text-[15px] font-bold text-[#B45353] mb-2">Who it is Created For?</h3>
            <ul class="ml-5 mb-4 space-y-1.5">
                <li class="text-sm leading-relaxed text-[#5C2D1B] break-words"><strong class="text-[#B45353]">For Business Owners:</strong> The ultimate remote-monitoring tool. It gives owners peace of mind, clear performance metrics, historical trends, and instant visibility into multi-location operations without requiring them to be physically present.</li>
                <li class="text-sm leading-relaxed text-[#5C2D1B] break-words"><strong class="text-[#B45353]">For Store Workers:</strong> A straightforward, easy-to-use tool to log their shifts (time-in/time-out), check live inventory, and process transactions without cumbersome paperwork.</li>
            </ul>
        </div>

        <div class="mt-5">
            <h3 class="text-[15px] font-bold text-[#B45353] mb-2">Services Offered:</h3>
            <ul class="ml-5 mb-4 space-y-1.5">
                <li class="text-sm leading-relaxed text-[#5C2D1B] break-words"><strong class="text-[#B45353]">Multi-Branch Inventory Management:</strong> Real-time stock tracking across various locations.</li>
                <li class="text-sm leading-relaxed text-[#5C2D1B] break-words"><strong class="text-[#B45353]">Transactional POS Logging:</strong> Seamless recording of daily shop sales.</li>
                <li class="text-sm leading-relaxed text-[#5C2D1B] break-words"><strong class="text-[#B45353]">Employee Time & Attendance:</strong> Quick clock-in/clock-out tracking for staff on-site.</li>
                <li class="text-sm leading-relaxed text-[#5C2D1B] break-words"><strong class="text-[#B45353]">Leakage & Discrepancy Detection:</strong> Smart algorithms that compare expected inventory and expected sales against actual physical counts to flag losses.</li>
                <li class="text-sm leading-relaxed text-[#5C2D1B] break-words"><strong class="text-[#B45353]">Analytics & Historical Trends:</strong> Deep-dive reporting on store performance, high-performing products, and seasonal sales trends.</li>
            </ul>
        </div>

        <div class="mt-5">
            <h3 class="text-[15px] font-bold text-[#B45353] mb-2">Our Limits (What Nita Is Not)</h3>
        <p class="text-sm leading-relaxed text-[#5C2D1B] break-words">
            <strong>Important Boundary:</strong> While Nita is an incredibly powerful monitoring tool, it is not a physical security system. It cannot physically prevent theft or inventory damage—it simply flags the discrepancies so you can take action. Furthermore, Nita is an operational tracker, not a certified accounting or tax-filing software. Owners should still consult with certified professionals for tax and legal financial reporting.
        </p>
        </div>
    </div>

    {{-- ═══ ABOUT OWNER ACCOUNT ═══ --}}
    <div id="about-owner" class="bg-white border border-[rgba(28,25,23,.12)] rounded-2xl p-7 hidden">
        <span class="inline-block px-3 py-1 rounded-full bg-[#FDF5D6] text-[11px] font-semibold text-[#5C2D1B] mb-5">About Owner Account</span>

        <div class="border-2 border-dashed border-[#B45353] rounded-xl p-6">
            <h2 class="text-base font-bold text-[#B45353] mb-3">Owner Account</h2>
            <p class="text-sm leading-relaxed text-[#5C2D1B] mb-3 break-words">
                The Owner Account is the highest level of administrative access within Nita. It is designed for the business proprietor or top-level executives who need bird's-eye visibility across the entire enterprise, as well as the ultimate authority to configure how the application computes financial and operational data.
            </p>

            <p class="text-sm font-semibold text-[#5C2D1B] mb-3">
                <strong class="text-[#B45353]">Scope of Access:</strong> Enterprise-wide (All registered brands, businesses, and branches).
            </p>

            <h3 class="text-[15px] font-bold text-[#B45353] mb-2">Key Authorities & Features:</h3>
            <ul class="ml-5 space-y-1.5">
                <li class="text-sm leading-relaxed text-[#5C2D1B] break-words"><strong class="text-[#B45353]">Global Architecture:</strong> Add new business entities, register new physical branches, or "disown"/archive a business that is no longer operational.</li>
                <li class="text-sm leading-relaxed text-[#5C2D1B] break-words"><strong class="text-[#B45353]">Global HR Management:</strong> Ultimate hiring and termination authority; can invite, assign, or remove Managers and Store Workers across any branch.</li>
                <li class="text-sm leading-relaxed text-[#5C2D1B] break-words"><strong class="text-[#B45353]">Global Configuration & Formulas:</strong> Complete control over the backend logic. Owners set the specific mathematical formulas used by Nita's algorithms to detect inventory and sales discrepancies.</li>
                <li class="text-sm leading-relaxed text-[#5C2D1B] break-words"><strong class="text-[#B45353]">Recipe & Cost Management:</strong> Create and edit product "recipes" (e.g., specifying that selling 1 cup of coffee subtracts 30g of coffee beans and 1 paper cup from inventory) to ensure accurate tracking.</li>
                <li class="text-sm leading-relaxed text-[#5C2D1B] break-words"><strong class="text-[#B45353]">Consolidated Analytics:</strong> View real-time inventory trackers, live transaction streams, and historical performance trends across all branches simultaneously for cross-location comparison.</li>
            </ul>
        </div>
    </div>

    {{-- ═══ PRIVACY POLICY ═══ --}}
    <div id="privacy" class="bg-white border border-[rgba(28,25,23,.12)] rounded-2xl p-7 hidden">
        <span class="inline-block px-3 py-1 rounded-full bg-[#FDF5D6] text-[11px] font-semibold text-[#5C2D1B] mb-5">Privacy Policy</span>

        <p class="text-sm font-bold text-[#B45353] mb-4">Effective Date: July 16, 2026</p>            <p class="text-sm leading-relaxed text-[#5C2D1B] mb-5 border-l-[3px] border-[#B45353] pl-3 break-words">
            At Nita, we respect your privacy and are committed to protecting the data of both business owners and their employees. This Privacy Policy explains how we collect, use, and safeguard your information.
        </p>

        <div class="mb-5">
            <h3 class="text-[15px] font-bold text-[#B45353] mb-2">1. Information We Collect</h3>
            <p class="text-sm leading-relaxed text-[#5C2D1B] mb-2">To provide our inventory-tracking services, we collect:</p>
            <ul class="ml-5 space-y-1.5">
                <li class="text-sm leading-relaxed text-[#5C2D1B] break-words"><strong class="text-[#B45353]">Account Information:</strong> Names, email addresses, phone numbers, and business details.</li>
                <li class="text-sm leading-relaxed text-[#5C2D1B] break-words"><strong class="text-[#B45353]">Employee Data:</strong> Names, shift logs (time-in/time-out), and (if authorized by the employer) location data at the time of clocking in/out to verify on-site presence.</li>
                <li class="text-sm leading-relaxed text-[#5C2D1B] break-words"><strong class="text-[#B45353]">Transactional & Inventory Data:</strong> Product lists, stock levels, sales figures, and pricing data uploaded to the platform.</li>
                <li class="text-sm leading-relaxed text-[#5C2D1B] break-words"><strong class="text-[#B45353]">Technical Data:</strong> IP addresses, browser types, and device information used to access the Nita platform.</li>
            </ul>
        </div>

        <div class="mb-5">
            <h3 class="text-[15px] font-bold text-[#B45353] mb-2">2. How We Use Your Information</h3>
            <p class="text-sm leading-relaxed text-[#5C2D1B] mb-2">We use the collected data to:</p>
            <ul class="ml-5 space-y-1.5">
                <li class="text-sm leading-relaxed text-[#5C2D1B]">Operate, maintain, and improve the Nita platform.</li>
                <li class="text-sm leading-relaxed text-[#5C2D1B]">Calculate sales trends, inventory forecasts, and identify operational leakage.</li>
                <li class="text-sm leading-relaxed text-[#5C2D1B]">Provide real-time dashboards to authorized business owners.</li>
                <li class="text-sm leading-relaxed text-[#5C2D1B]">Send system alerts, updates, and customer support messages.</li>
            </ul>
        </div>

        <div class="mb-5">
            <h3 class="text-[15px] font-bold text-[#B45353] mb-2">3. Data Sharing and Disclosure</h3>
            <ul class="ml-5 space-y-1.5">
                <li class="text-sm leading-relaxed text-[#5C2D1B] break-words"><strong class="text-[#B45353]">We do not sell your data.</strong></li>
                <li class="text-sm leading-relaxed text-[#5C2D1B] break-words"><strong class="text-[#B45353]">Owner Access:</strong> Employee attendance and transactional data logged at a specific branch are visible to the registered owner/administrator of that business account.</li>
                <li class="text-sm leading-relaxed text-[#5C2D1B] break-words"><strong class="text-[#B45353]">Service Providers:</strong> We may share data with secure, trusted third-party hosting or database providers essential to running our application, all of whom are bound by strict confidentiality agreements.</li>
            </ul>
        </div>

        <div class="mb-5">
            <h3 class="text-[15px] font-bold text-[#B45353] mb-2">4. Data Security</h3>
            <p class="text-sm leading-relaxed text-[#5C2D1B] break-words">
                We employ industry-standard encryption (SSL/TLS) to protect data in transit and at rest. However, no method of transmission over the internet is 100% secure, and we cannot guarantee absolute security.
            </p>
        </div>

        <div>
            <h3 class="text-[15px] font-bold text-[#B45353] mb-2">5. Your Rights</h3>
            <p class="text-sm leading-relaxed text-[#5C2D1B] break-words">
                Depending on your location, you and your employees may have the right to access, correct, or request the deletion of personal data. Please contact us at <strong>nitasupport@gmail.com</strong> for any data privacy inquiries.
            </p>
        </div>
    </div>

    {{-- ═══ TERMS & CONDITIONS ═══ --}}
    <div id="terms" class="bg-white border border-[rgba(28,25,23,.12)] rounded-2xl p-7 hidden">
        <span class="inline-block px-3 py-1 rounded-full bg-[#FDF5D6] text-[11px] font-semibold text-[#5C2D1B] mb-5">Terms & Conditions</span>

        <p class="text-sm font-bold text-[#B45353] mb-4">Last Updated: July 16, 2026</p>
        <p class="text-sm leading-relaxed text-[#5C2D1B] mb-5 border-l-[3px] border-[#B45353] pl-3 break-words">
            Welcome to Nita! By accessing or using our platform, you agree to comply with and be bound by the following Terms and Conditions.
        </p>

        <div class="mb-5">
            <h3 class="text-[15px] font-bold text-[#B45353] mb-2">1. Account Registration and Security</h3>
            <ul class="ml-5 space-y-1.5">
                <li class="text-sm leading-relaxed text-[#5C2D1B] break-words"><strong class="text-[#B45353]">Accuracy:</strong> You must provide accurate and complete information when registering your business and branches.</li>
                <li class="text-sm leading-relaxed text-[#5C2D1B] break-words"><strong class="text-[#B45353]">Credentials:</strong> You are responsible for safeguarding your login credentials and for all activities that occur under your account, including actions taken by your employees.</li>
            </ul>
        </div>

        <div class="mb-5">
            <h3 class="text-[15px] font-bold text-[#B45353] mb-2">2. Employee Consent and Compliance</h3>
            <p class="text-sm leading-relaxed text-[#5C2D1B] break-words">
                <strong>Compliance Notice:</strong> Business owners are solely responsible for obtaining all necessary consents and authorizations from their employees before tracking their attendance, work hours, or location through Nita, in compliance with local labor and privacy laws.
            </p>
        </div>

        <div class="mb-5">
            <h3 class="text-[15px] font-bold text-[#B45353] mb-2">3. Acceptable Use</h3>
            <p class="text-sm leading-relaxed text-[#5C2D1B] mb-2">You agree not to use Nita to:</p>
            <ul class="ml-5 space-y-1.5">
                <li class="text-sm leading-relaxed text-[#5C2D1B]">Upload any fraudulent transaction or inventory data.</li>
                <li class="text-sm leading-relaxed text-[#5C2D1B]">Attempt to reverse-engineer, disrupt, or compromise the security of the platform.</li>
                <li class="text-sm leading-relaxed text-[#5C2D1B]">Use the platform for any illegal activities or unauthorized tracking.</li>
            </ul>
        </div>

        <div class="mb-5">
            <h3 class="text-[15px] font-bold text-[#B45353] mb-2">4. Disclaimers & Limitation of Liability</h3>
            <ul class="ml-5 space-y-1.5">
                <li class="text-sm leading-relaxed text-[#5C2D1B] break-words"><strong class="text-[#B45353]">&ldquo;As-Is&rdquo; Service:</strong> Nita is provided on an &ldquo;as-is&rdquo; and &ldquo;as-available&rdquo; basis. We do not warrant that the platform will be entirely error-free or uninterrupted.</li>
                <li class="text-sm leading-relaxed text-[#5C2D1B] break-words"><strong class="text-[#B45353]">Data Accuracy:</strong> Nita relies on the data entered by you and your employees. We are not liable for business losses, inventory discrepancies, or inaccurate financial calculations resulting from manual data entry errors.</li>
                <li class="text-sm leading-relaxed text-[#5C2D1B] break-words"><strong class="text-[#B45353]">Limitation of Damages:</strong> To the maximum extent permitted by law, Nita shall not be liable for any indirect, incidental, or consequential damages (including loss of profits or inventory leakage) arising out of your use of the platform.</li>
            </ul>
        </div>

        <div class="mb-5">
            <h3 class="text-[15px] font-bold text-[#B45353] mb-2">5. Fees and Termination</h3>
            <ul class="ml-5 space-y-1.5">
                <li class="text-sm leading-relaxed text-[#5C2D1B]"><strong class="text-[#B45353]">Subscriptions:</strong> Certain features of Nita may require a paid subscription. All fees are non-refundable unless stated otherwise.</li>
                <li class="text-sm leading-relaxed text-[#5C2D1B]"><strong class="text-[#B45353]">Termination:</strong> We reserve the right to suspend or terminate your account if you violate these Terms, fail to pay subscription fees, or engage in fraudulent activity.</li>
            </ul>
        </div>

        <div>
            <h3 class="text-[15px] font-bold text-[#B45353] mb-2">6. Governing Law</h3>
            <p class="text-sm leading-relaxed text-[#5C2D1B] break-words">
                These Terms shall be governed by and construed in accordance with the laws of the Philippines, without regard to its conflict of law principles.
            </p>
        </div>
    </div>
</div>

<script>
function switchTab(tabId, el) {
    // Update tabs
    document.querySelectorAll('.content-tab').forEach(t => {
        t.classList.remove('active');
        t.style.opacity = '0.6';
        t.style.borderBottomColor = 'transparent';
    });
    el.classList.add('active');
    el.style.opacity = '1';
    el.style.borderBottomColor = '#B45353';

    // Hide all sections
    document.getElementById('about-nita').classList.add('hidden');
    document.getElementById('about-owner').classList.add('hidden');
    document.getElementById('privacy').classList.add('hidden');
    document.getElementById('terms').classList.add('hidden');

    // Show selected section
    document.getElementById(tabId).classList.remove('hidden');
}
</script>
@endsection
