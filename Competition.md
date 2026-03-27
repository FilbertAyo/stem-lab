Overview of the System Context

You are extending an existing platform — Adilisha STEM Labs — to host and manage a national competition. The system must support over 1,000 schools (which are the auth users) – means teachers and each teacher represent the one school, and each school should have up to 75 registered student members (of which these students have their table hence not authenticated), meaning you are designing for a potential user base of 75,000+ participants. The architecture, database design, and every feature must be built with that scale in mind from day one. Nothing should be lightweight or provisional.
School Registration & Eligibility Engine
Registration is not just a sign-up form. It is a gated eligibility process with hard requirements that must be enforced by the system, not by manual checking.
When a school registers(so the one teacher is the one register to represent school hence his account will be created and along with it , the system must capture the school details name, region , and other details as on db and confirmation of the competition terms. The school account is created immediately but is placed in a pending state (hence admins will approve since we will consider some issue like should be public secondary school, ..).
The teacher -school account only becomes eligible — meaning it can submit ideas — once it has met the 75-student registration threshold(70% of those should be girls) . The system must track this count in real time and prevent the school from advancing to any competition stage until this number is met. This is a hard gate, not a warning. The teacher dashboard must clearly display the current count and how many more students are needed. (and after the number is met then teacher select only 10 students from the list to represent others but 70% of those should be girls) –NB: so boys school only wont have eligibiliy and registration have end date
Competition Stage Management
The entire competition runs through eight (make flexible since other stages maybe will add and become 9 or 10 ,…) sequential stages. The system must have a stage engine — a backend-controlled state machine that governs which features are active, which forms are open, and what each user can see and do at any given point in time.
The Adilisha admin controls which stage is currently active. When a stage closes, submissions for that stage are locked. Users cannot submit late entries. The transition between stages is not automatic (or put option if automatic or option) — it is admin-triggered, but the system must enforce the rules of each stage strictly once set.
Each stage must have a visible status on every relevant dashboard — whether it is upcoming, open, closed, or results-pending. No user should ever be confused about what phase the competition is in.
 
Idea Submission Module
Once a school is eligible and the idea submission stage is active, the teacher or designated team member can submit the school's idea.( school can see end of idea submission so will be end date if idea submission)
The submission form must capture the community problem being addressed, the proposed solution, the STEM discipline it applies to (with a selectable category), the region and community the problem exists in, and supporting documents or images if any. The form must support file uploads — PDFs and images at minimum.
Each school is allowed exactly one idea submission. The system must enforce this. If a submission is already in draft, the form reopens that draft. It cannot create a second one. The submission should support a draft and save workflow, meaning teams can fill it progressively and submit only when ready before the deadline.
Once submitted, the status changes to "Submitted" and the form is locked from further editing unless an admin explicitly reopens it.
 
Regional Shortlisting & Review System
After the idea submission phase closes, the system moves into the shortlisting phase. This is where judges come in.
The system must have a judge assignment engine. The admin assigns judges to regions. Each judge only sees the submissions from their assigned region. Judges cannot see each other's scores. Submissions must be presented to judges in a clean review interface with all the team's submitted content clearly displayed.
Each judge scores submissions against the judging criteria — STEM-based approach, tangible product potential, community relevance, creativity, and local material use. The system must calculate aggregate scores and rank submissions automatically per region.
The admin then reviews the ranked list and marks the top 2 to 5 teams per region as shortlisted. This action triggers automatic notifications to the shortlisted schools and locks out the non-shortlisted ones from further competition progression while still keeping their accounts active.
Prototype Submission Module
Shortlisted teams enter the prototype phase. The system must present a new submission form to shortlisted teams only, completely separate from the idea submission.
This form captures a description of the prototype, a breakdown of materials used and their origin (local vs. imported), photos or video of the prototype, a self-assessment of functionality, and any documentation about how the prototype addresses the community problem. The 60% local materials requirement should be captured here explicitly(like creativity will be also judged on use of local materials), with the team declaring the proportion of local materials and providing evidence.
Same rules apply — one prototype submission per school, draft-and-save supported, locked after final submission.
Mentor Assignment & Communication System
After prototype shortlisting, national finalists are assigned university mentors. This is managed entirely by the Adilisha admin.
The admin sees a list of all national finalist teams and a list of available mentors (registered on the platform by university). The admin matches mentors to teams. Once matched, both the mentor and the team are notified.
The mentor gains access to that team's full submission history — idea and prototype — and can see the team's profile. The system must include a structured communication thread between mentor and team. This is not a general chat; it is a per-assignment, logged conversation thread attached to the team's competition record. Every message is stored, timestamped, and visible to the Adilisha admin for oversight.
Mentors can leave structured feedback on specific prototype elements in addition to free-text communication.
Pitch Event Registration & Management
When teams advance to the national pitch, the system must manage logistics data. Each qualifying team must confirm their attendance, provide updated transport details if needed, and the system must track who has confirmed and who hasn't so the organizing team can follow up.
The pitch schedule — teams, time slots, assigned judges — must be manageable from the admin panel and visible to relevant users in read-only mode.
Notifications & Communication Infrastructure
With 1,000+ users, notifications are critical and must be reliable. The system needs a notification layer that operates both in-app and via email at minimum.
Every stage transition triggers automated notifications to affected user groups. Shortlisting outcomes trigger immediate notification to teachers of both shortlisted and non-shortlisted schools. Mentor assignments trigger notifications to mentors and teams. Deadline reminders should go out automatically a set number of days before each stage closes. Admin must be able to send broadcast announcements to all registered schools, to a specific region, or to specific roles. (But put option on the dashboard if email is sent or not)
Admin Dashboard & Control Panel
The Adilisha admin needs a comprehensive operational dashboard. This is mission-critical.
The dashboard must show real-time registration statistics — total schools registered, total students registered, breakdown by region and gender, how many schools have met the 75-student threshold and how many haven't. It must show submission counts by stage. It must show the current competition stage and allow the admin to advance it with confirmation.
The admin must be able to search for any school, view their full competition record, manually override a submission status where required, add notes to a school's record, export data to CSV or Excel at any point, and generate a summary report by region.
Bulk actions are needed — for example, sending a reminder to all schools that have not yet reached 75 students.
Scalability & Performance Considerations
Because you are expecting 1,000+ user accounts and concurrent activity during key phases — especially registration peaks and submission deadlines — the system must be built with this in mind at the infrastructure level.
Database queries involving submission counts, user lookups, and regional aggregations must be indexed properly. File uploads (prototype photos, PDFs) must go to object storage, not the server filesystem. Any heavy operations like report generation or bulk notifications must be handled asynchronously through a queue system, not run synchronously on the request thread. The system must not hang or time out when 500 schools are submitting on the last day of a deadline.
Session management must handle concurrent logins gracefully. Role-based access checks must happen at the API level, not just the frontend.
Data Integrity & Audit Trail
Every significant action in the system — a submission being made, a stage being advanced, a team being shortlisted, a mentor being assigned, a score being submitted — must be recorded in an audit log with a timestamp and the user who performed the action. This is non-negotiable for a government-backed national competition where disputes, re-checks, and reporting are expected.
No data should ever be hard-deleted. Submissions, registrations, and user records should be soft-deleted and retained. This ensures evidence for post-competition policy reporting to COSTECH and PO-RALG.
And tables should not use /1/edit NO use uld like /SGHKJBN&VSH87/edit for security issue
Impact & Reporting Module
At the end of the competition, the system must be able to generate a structured impact report pulling from all the data collected. This includes total participants by gender, by region, by school type, number of ideas submitted, prototype quality scores, mentor engagement rates, and winner profiles. This data feeds directly into the policy evidence outputs required by the framework.
The reporting module does not need to be complex but it must be accurate, exportable, and reflect the full competition lifecycle from registration to winner.

