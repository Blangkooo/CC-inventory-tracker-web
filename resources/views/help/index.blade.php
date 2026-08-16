@extends('layouts.sidebar')

@section('title', 'Help Center')
@section('subtitle', 'Frequently asked questions and support.')

@section('content')
<div class="max-w-[1000px] mx-auto space-y-6">

    {{-- ═══ FAQ SECTION ═══ --}}
    <div class="bg-white border border-[rgba(28,25,23,.12)] rounded-2xl p-6">
        <span class="inline-block px-3 py-1 rounded-full bg-[#FDF5D6] text-[11px] font-semibold text-[#5C2D1B] mb-5">Frequently Asked Questions</span>

        <div class="faq-item border-b border-[rgba(28,25,23,.12)] py-4 active" data-faq>
            <div class="faq-question flex items-center justify-between cursor-pointer hover:text-[#B45353] transition-colors" onclick="toggleFaq(this)">
                <span class="text-[15px] font-semibold text-[#B45353]">How much is the monthly fee?</span>
                <span class="faq-icon w-6 h-6 rounded-full bg-[#B45353] text-white flex items-center justify-center text-sm font-bold">×</span>
            </div>
            <div class="faq-answer max-h-0 overflow-hidden transition-all duration-300">
                <p class="text-sm leading-relaxed text-[#5C2D1B] opacity-80 pt-3">The monthly fee is $100. This includes the Owner account and Manager accounts.</p>
            </div>
        </div>

        <div class="faq-item border-b border-[rgba(28,25,23,.12)] py-4" data-faq>
            <div class="faq-question flex items-center justify-between cursor-pointer hover:text-[#B45353] transition-colors" onclick="toggleFaq(this)">
                <span class="text-[15px] font-semibold text-[#B45353]">How do I hire new employees?</span>
                <span class="faq-icon w-6 h-6 rounded-full bg-[#FDF5D6] text-[#B45353] flex items-center justify-center text-sm font-bold">+</span>
            </div>
            <div class="faq-answer max-h-0 overflow-hidden transition-all duration-300">
                <p class="text-sm leading-relaxed text-[#5C2D1B] opacity-80 pt-3">You can hire new employees by going to the Employees page and clicking on "Open Positions". From there, you can create a new job posting or accept suggestions from your managers.</p>
            </div>
        </div>

        <div class="faq-item border-b border-[rgba(28,25,23,.12)] py-4" data-faq>
            <div class="faq-question flex items-center justify-between cursor-pointer hover:text-[#B45353] transition-colors" onclick="toggleFaq(this)">
                <span class="text-[15px] font-semibold text-[#B45353]">How do I add a new business?</span>
                <span class="faq-icon w-6 h-6 rounded-full bg-[#FDF5D6] text-[#B45353] flex items-center justify-center text-sm font-bold">+</span>
            </div>
            <div class="faq-answer max-h-0 overflow-hidden transition-all duration-300">
                <p class="text-sm leading-relaxed text-[#5C2D1B] opacity-80 pt-3">To add a new business, go to the Businesses page and click the "Add Business" button. Fill in the required information including business name, description, and location.</p>
            </div>
        </div>

        <div class="faq-item py-4" data-faq>
            <div class="faq-question flex items-center justify-between cursor-pointer hover:text-[#B45353] transition-colors" onclick="toggleFaq(this)">
                <span class="text-[15px] font-semibold text-[#B45353]">Are the legal papers really necessary?</span>
                <span class="faq-icon w-6 h-6 rounded-full bg-[#FDF5D6] text-[#B45353] flex items-center justify-center text-sm font-bold">+</span>
            </div>
            <div class="faq-answer max-h-0 overflow-hidden transition-all duration-300">
                <p class="text-sm leading-relaxed text-[#5C2D1B] opacity-80 pt-3">Yes, legal papers are essential for operating your business legally. They include DTI registration, SEC registration, BIR registration, and LGU permits. These documents protect your business and ensure compliance with local regulations.</p>
            </div>
        </div>
    </div>

    {{-- ═══ BOTTOM SECTION ═══ --}}
    <div class="grid grid-cols-1 md:grid-cols-[1.5fr_1fr] gap-5">
        {{-- Question Form --}}
        <div class="bg-white border border-[rgba(28,25,23,.12)] rounded-2xl p-6">
            <span class="inline-block px-3 py-1 rounded-full bg-[#FDF5D6] text-[11px] font-semibold text-[#5C2D1B] mb-4">Question</span>
            <h2 class="text-lg font-bold text-[#5C2D1B] mb-5">Have a specific question? Ask us.</h2>

            <form action="#" method="POST">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-[13px] font-semibold text-[#B45353] mb-1.5">Email Address</label>
                        <input type="email" name="email" class="w-full px-3.5 py-2.5 border border-[rgba(28,25,23,.12)] rounded-lg text-[13px] focus:outline-none focus:border-[#B45353] transition-colors" placeholder="johnmychel33@gmail.com" value="{{ auth()->user()->email ?? '' }}">
                    </div>
                    <div>
                        <label class="block text-[13px] font-semibold text-[#B45353] mb-1.5">Position</label>
                        <select name="position" class="w-full px-3.5 py-2.5 border border-[rgba(28,25,23,.12)] rounded-lg text-[13px] focus:outline-none focus:border-[#B45353] transition-colors">
                            <option value="">Are you an Owner or Manager?</option>
                            <option value="owner">Owner</option>
                            <option value="manager">Manager</option>
                        </select>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="block text-[13px] font-semibold text-[#B45353] mb-1.5">Question</label>
                    <textarea name="question" class="w-full px-3.5 py-2.5 border border-[rgba(28,25,23,.12)] rounded-lg text-[13px] focus:outline-none focus:border-[#B45353] transition-colors min-h-[80px] resize-y" placeholder="Write your question..."></textarea>
                </div>

                <button type="submit" class="px-6 py-2.5 rounded-lg text-[13px] font-semibold text-white bg-green-600 border-none cursor-pointer hover:bg-green-700 transition-colors">Submit Question</button>
            </form>
        </div>

        {{-- Contacts --}}
        <div class="bg-white border border-[rgba(28,25,23,.12)] rounded-2xl p-6">
            <span class="inline-block px-3 py-1 rounded-full bg-[#FDF5D6] text-[11px] font-semibold text-[#5C2D1B] mb-4">Contacts</span>
            <h2 class="text-lg font-bold text-[#5C2D1B] mb-4">Reach out to us!</h2>

            <div class="space-y-2.5">
                <div class="text-sm text-[#B45353] cursor-pointer hover:opacity-70 transition-opacity">nitasupport@gmail.com</div>
                <div class="text-sm text-[#B45353] cursor-pointer hover:opacity-70 transition-opacity">nitaofficial@gmail.com</div>
                <div class="text-sm text-[#B45353] cursor-pointer hover:opacity-70 transition-opacity">408 - 317 - 3645</div>
            </div>
        </div>
    </div>
</div>

<script>
function toggleFaq(el) {
    const faqItem = el.closest('.faq-item');
    const icon = faqItem.querySelector('.faq-icon');
    const isActive = faqItem.classList.contains('active');

    // Close all FAQ items
    document.querySelectorAll('.faq-item').forEach(item => {
        item.classList.remove('active');
        item.querySelector('.faq-icon').textContent = '+';
        item.querySelector('.faq-icon').classList.remove('bg-[#B45353]', 'text-white');
        item.querySelector('.faq-icon').classList.add('bg-[#FDF5D6]', 'text-[#B45353]');
    });

    // Toggle clicked item
    if (!isActive) {
        faqItem.classList.add('active');
        icon.textContent = '×';
        icon.classList.remove('bg-[#FDF5D6]', 'text-[#B45353]');
        icon.classList.add('bg-[#B45353]', 'text-white');
    }
}
</script>

<style>
.faq-item.active .faq-answer {
    max-height: 200px;
}
</style>
@endsection
