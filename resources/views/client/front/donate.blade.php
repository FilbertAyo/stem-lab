@extends('layouts.landing')

@section('title', config('app.name') . ' | Donate')
@section('page', 'donate')

@section('content')
<main>



    <!-- ===== Donate Form Start ===== -->
    <section class="gj do ir hj sp jr i pg">
        <div class="bb ze ki xn ">
            <div class="tc uf sn tn un gg">
                <div class="animate_left to/2">
                    <div class="animate_top">
                        <h2 class="fk vj zp pr kk wm">Make a Donation</h2>
                        <p class="fq">
                            Choose an amount or enter a custom donation. Every contribution makes a difference in
                            empowering the next generation of STEM leaders.
                        </p>
                    </div>

                    <div class="animate_bottom">
                        <form action="#!" method="post" class="mt-8">
                            <!-- Donation Amount Selection -->
                            <div class="mb-6">
                                <label class="kk wm tj ec mb-3 block">Select Amount</label>
                                <div class="tc wf gg gap-4 mb-4">
                                    <button type="button" class="ek pk xl rg il vc _d _l donation-amount" data-amount="10000">
                                        TZS 10,000
                                    </button>
                                    <button type="button" class="ek pk xl rg il vc _d _l donation-amount" data-amount="50000">
                                        TZS 50,000
                                    </button>
                                    <button type="button" class="ek pk xl rg il vc _d _l donation-amount" data-amount="100000">
                                        TZS 100,000
                                    </button>
                                    <button type="button" class="ek pk xl rg il vc _d _l donation-amount" data-amount="500000">
                                        TZS 500,000
                                    </button>
                                </div>
                                <div class="i">
                                    <input
                                        type="number"
                                        id="custom-amount"
                                        name="amount"
                                        placeholder="Enter custom amount (TZS)"
                                        class="vd sm _g ch pm vk xm rg gm dm dn gi mi w-full"
                                        step="1000"
                                    />
                                </div>
                            </div>

                            <!-- Donor Information -->
                            <div class="mb-6">
                                <h3 class="kk wm tj ec mb-4">Your Information</h3>
                                <div class="tc wf gg gap-4 mb-4">
                                    <div class="to/2">
                                        <label for="first-name" class="kk wm tj ec mb-2 block">First Name</label>
                                        <input
                                            type="text"
                                            id="first-name"
                                            name="first_name"
                                            required
                                            class="vd sm _g ch pm vk xm rg gm dm dn gi mi w-full"
                                        />
                                    </div>
                                    <div class="to/2">
                                        <label for="last-name" class="kk wm tj ec mb-2 block">Last Name</label>
                                        <input
                                            type="text"
                                            id="last-name"
                                            name="last_name"
                                            required
                                            class="vd sm _g ch pm vk xm rg gm dm dn gi mi w-full"
                                        />
                                    </div>
                                </div>
                                <div class="mb-4">
                                    <label for="email" class="kk wm tj ec mb-2 block">Email Address</label>
                                    <input
                                        type="email"
                                        id="email"
                                        name="email"
                                        required
                                        class="vd sm _g ch pm vk xm rg gm dm dn gi mi w-full"
                                    />
                                </div>
                                <div class="mb-4">
                                    <label for="phone" class="kk wm tj ec mb-2 block">Phone Number</label>
                                    <input
                                        type="tel"
                                        id="phone"
                                        name="phone"
                                        placeholder="+255 XXX XXX XXX"
                                        class="vd sm _g ch pm vk xm rg gm dm dn gi mi w-full"
                                    />
                                </div>
                            </div>

                            <!-- Payment Method -->
                            <div class="mb-6">
                                <h3 class="kk wm tj ec mb-4">Payment Method</h3>
                                <div class="tc wf gg gap-4">
                                    <label class="rc ab i p-4 border rounded-lg cursor-pointer hover:border-[#0077b6] transition">
                                        <input type="radio" name="payment_method" value="mobile_money" class="pf vd yc uk h r za ab" checked />
                                        <span class="ml-2 kk wm">Mobile Money (M-Pesa, Tigo Pesa, Airtel Money)</span>
                                    </label>
                                    <label class="rc ab i p-4 border rounded-lg cursor-pointer hover:border-[#0077b6] transition">
                                        <input type="radio" name="payment_method" value="bank_transfer" class="pf vd yc uk h r za ab" />
                                        <span class="ml-2 kk wm">Bank Transfer</span>
                                    </label>
                                </div>
                            </div>

                            <!-- Additional Message -->
                            <div class="mb-6">
                                <label for="message" class="kk wm tj ec mb-2 block">Message (Optional)</label>
                                <textarea
                                    id="message"
                                    name="message"
                                    rows="4"
                                    placeholder="Leave a message or dedication..."
                                    class="vd sm _g ch pm vk xm rg gm dm dn gi mi w-full"
                                ></textarea>
                            </div>

                            <!-- Submit Button -->
                            <div class="tc wf">
                                <button type="submit" class="ek jk lk gh gi hi rg ml il vc _d _l">
                                    Complete Donation
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Impact Information -->
                <div class="animate_right bf">
                    <div class="p-6 bg-gray-50 dark:bg-gray-800 rounded-lg">
                        <h3 class="kk wm tj ec mb-4">Your Impact</h3>
                        <ul class="space-y-3">
                            <li class="tc wf">
                                <svg class="vh ul cl il text-green-600" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M20 6L9 17L4 12" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                                <span class="ml-2">TZS 10,000 provides VR equipment for one student</span>
                            </li>
                            <li class="tc wf">
                                <svg class="vh ul cl il text-green-600" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M20 6L9 17L4 12" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                                <span class="ml-2">TZS 50,000 supports a month of STEM lab access</span>
                            </li>
                            <li class="tc wf">
                                <svg class="vh ul cl il text-green-600" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M20 6L9 17L4 12" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                                <span class="ml-2">TZS 100,000 funds educational resources for a classroom</span>
                            </li>
                            <li class="tc wf">
                                <svg class="vh ul cl il text-green-600" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M20 6L9 17L4 12" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                                <span class="ml-2">TZS 500,000 enables a full STEM program for a school</span>
                            </li>
                        </ul>

                        <div class="mt-6 p-4 bg-[#0077b6] text-white rounded-lg">
                            <p class="kk wm mb-2">Contact Us</p>
                            <p class="text-sm">For large donations or corporate partnerships, please contact us:</p>
                            <p class="text-sm mt-2">
                                <strong>Phone:</strong> +255 689 780 000<br>
                                <strong>Email:</strong> info@adilisha.org
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    @include('client.front.partials.cta')

</main>
@endsection

@push('scripts')
<script>
    // Handle donation amount selection
    document.addEventListener('DOMContentLoaded', function() {
        const amountButtons = document.querySelectorAll('.donation-amount');
        const customAmountInput = document.getElementById('custom-amount');

        amountButtons.forEach(button => {
            button.addEventListener('click', function() {
                // Remove active class from all buttons
                amountButtons.forEach(btn => {
                    btn.classList.remove('gh', 'sh');
                    btn.classList.add('pk');
                });

                // Add active class to clicked button
                this.classList.remove('pk');
                this.classList.add('gh', 'sh');

                // Set the amount in the input
                const amount = this.getAttribute('data-amount');
                customAmountInput.value = amount;
            });
        });

        // Clear button selection when custom amount is entered
        customAmountInput.addEventListener('input', function() {
            amountButtons.forEach(btn => {
                btn.classList.remove('gh', 'sh');
                btn.classList.add('pk');
            });
        });
    });
</script>
@endpush

