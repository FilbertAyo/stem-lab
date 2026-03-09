<x-app-layout>
    @php($subjects = $subjects ?? collect())
    @php($smileyTutorCredential = $smileyTutorCredential ?? null)

    {{-- Same sidebar as index: All Experiments + subject links + Mathematics --}}
    <div class="col-12">
        <div class="row">
            <div class="col-lg-3 dashboard-sidebar mb-4 mb-lg-0">
                <ul class="category-list">
                    <li>
                        <a href="{{ route('client.vr-experiments.index', request()->only(['search', 'sort'])) }}"
                           class="">
                            All Experiments <iconify-icon icon="lucide:chevron-right"></iconify-icon>
                        </a>
                    </li>
                    @foreach($subjects as $subject)
                        <li>
                            <a href="{{ route('client.vr-experiments.index', array_merge(request()->only(['search', 'sort']), ['subject' => $subject->id])) }}"
                               class="">
                                {{ $subject->subject_name }} <iconify-icon icon="lucide:chevron-right"></iconify-icon>
                            </a>
                        </li>
                    @endforeach
                    <li>
                        <a href="{{ route('client.vr-experiments.math') }}"
                           class="active">
                            Mathematics <iconify-icon icon="lucide:chevron-right"></iconify-icon>
                        </a>
                    </li>
                </ul>
            </div>

            <div class="col-lg-9">
                <div class="dashboard-content-header mb-4">
                    <h3 class="mb-2">Mathematics</h3>
                    <p class="text-muted mb-0">Practice math with SmileyTutor and earn rewards.</p>
                </div>

                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body p-4">
                        <div class=" mb-4">
                            <img src="{{ asset('images/tutorweb-logo.png') }}" alt="SmileyTutor" class="img-fluid rounded mb-3" style="max-height: 80px; width: auto;">
                        
                            <p class="text-muted mb-0">We extend our mathematics content through SmileyTutor so you can learn and earn.</p>
                        </div>

                        <h5 class="fw-semibold mb-3">How learners do mathematics</h5>
                        <p class="mb-3">
                            Our mathematics offering is powered by a partnership with <strong>SmileyTutor</strong>. Learners practice mathematics in an <strong>interactive way</strong> on the SmileyTutor platform: work through drills, build skills, and stay engaged with instant feedback.
                        </p>
                        <p class="mb-3">
                            As you complete exercises and master topics, you earn <strong>smiley-like tokens</strong>. These tokens can be <strong>converted to money</strong> and add up over time. When you accumulate a significant amount, you become eligible for <strong>gifts</strong>—including devices like <strong>tablets</strong>—to support your learning journey.
                        </p>
                        <p class="mb-4">
                            SmileyTutor provides courses and tutorials across the math department (from high school mathematics to calculus and beyond), so you can continue from where you left off or explore something new.
                        </p>

                        @if($smileyTutorCredential)
                            <div class="alert alert-light border mb-4">
                                <h6 class="alert-heading fw-semibold mb-2">
                                    <i class="bi bi-key me-2"></i>Your SmileyTutor login
                                </h6>
                                <p class="mb-2 small text-muted">Use these credentials when SmileyTutor asks for username and password (e.g. after clicking <strong>Drills</strong>).</p>
                                <div class="row g-2 small">
                                    <div class="col-auto">
                                        <span class="text-muted">Username:</span>
                                        <code class="ms-1">{{ $smileyTutorCredential->username }}</code>
                                    </div>
                                    <div class="col-auto">
                                        <span class="text-muted">Password:</span>
                                        <code class="ms-1">{{ $smileyTutorCredential->password }}</code>
                                    </div>
                                </div>
                            </div>
                        @else
                            <div class="alert alert-info border mb-4 small" id="no-credential-alert">
                                <i class="bi bi-info-circle me-2"></i>
                                SmileyTutor login credentials are assigned when you create an account. If you did not receive credentials, you can request them below.
                                <div class="mt-3">
                                    <button type="button" class="badge bg-dark" id="request-credential-btn">Request credentials</button>
                                    <p class="mb-0 mt-2 text-success small d-none" id="credential-request-message">
                                        You will receive your credentials in a moment.
                                    </p>
                                </div>
                            </div>
                        @endif

                        <div class="d-flex flex-wrap gap-2 align-items-center">
                            <a href="{{ route('client.vr-experiments.math.practice') }}" class="btn btn-primary">
                                <i class="bi bi-play-circle me-2"></i>Practice on SmileyTutor
                            </a>
                            <span class="text-muted small">On the practice page, click <strong>Drills</strong> and sign in with your SmileyTutor username and password above.</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if(!$smileyTutorCredential)
    <script>
        document.getElementById('request-credential-btn')?.addEventListener('click', function () {
            document.getElementById('credential-request-message')?.classList.remove('d-none');
        });
    </script>
    @endif
</x-app-layout>
