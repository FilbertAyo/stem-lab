<div class="col-lg-3 dashboard-sidebar mb-4 mb-lg-0">
    <ul class="category-list">
        @if(Auth::user()->role === 'teacher')
            <li>
                <a href="{{ route('client.my-students.index') }}"
                   class="{{ request()->routeIs('client.my-students.*') ? 'active' : '' }}">
                    My Students <iconify-icon icon="lucide:chevron-right"></iconify-icon>
                </a>
            </li>
            <li>
                <a href="{{ route('client.weekly-sessions.index') }}"
                   class="{{ request()->routeIs('client.weekly-sessions.*') ? 'active' : '' }}">
                    Weekly Sessions <iconify-icon icon="lucide:chevron-right"></iconify-icon>
                </a>
            </li>
            <li>
                <a href="{{ route('client.clubs.index') }}"
                   class="{{ request()->routeIs('client.clubs.*') ? 'active' : '' }}">
                    Clubs <iconify-icon icon="lucide:chevron-right"></iconify-icon>
                </a>
            </li>
            <li>
                <a href="{{ route('client.competitions.index') }}"
                   class="{{ request()->routeIs('client.competitions.*') ? 'active' : '' }}">
                    Competitions <iconify-icon icon="lucide:chevron-right"></iconify-icon>
                </a>
            </li>
        @endif
        @if(Auth::user()->role === 'student')
            <li>
                <a href="{{ route('client.student-club.index') }}"
                   class="{{ request()->routeIs('client.student-club.*') ? 'active' : '' }}">
                    My Club <iconify-icon icon="lucide:chevron-right"></iconify-icon>
                </a>
            </li>
        @endif
        <li>
            <a href="{{ route('client.vr-assessments.index') }}"
               class="{{ request()->routeIs('client.vr-assessments.*') ? 'active' : '' }}">
                VR Quizzes <iconify-icon icon="lucide:chevron-right"></iconify-icon>
            </a>
        </li>
       
    </ul>
</div>
