@extends('layouts.landing')

@section('title', config('app.name') . ' | Privacy Policy')
@section('page', 'privacy-policy')

@section('content')

<main>
    <section class="banner-section banner-inner-section position-relative overflow-hidden d-flex align-items-end"
      style="background-image: url({{ asset('client/assets/images/backgrounds/privacy-policy-banner.jpg') }});">
      <div class="container">
        <div class="d-flex flex-column gap-4 pb-5 pb-xl-10 position-relative z-1">
          <div class="row align-items-center">
            <div class="col-xl-4">
              <div class="d-flex align-items-center gap-4" data-aos="fade-up" data-aos-delay="100"
                data-aos-duration="1000">
                <img src="{{ asset('client/assets/images/svgs/primary-leaf.svg') }}" alt="" class="img-fluid animate-spin">
                <p class="mb-0 text-white fs-5 text-opacity-70">Understand the <span class="text-primary">Rules &
                    Guidelines</span> Before Using Our Services</p>
              </div>
            </div>
          </div>
          <div class="d-flex align-items-end gap-3" data-aos="fade-up" data-aos-delay="200" data-aos-duration="1000">
            <h1 class="mb-0 fs-16 text-white lh-1">Privacy Policy</h1>
            <a href="javascript:void(0)" class="p-1 ps-7 bg-primary rounded-pill">
              <span class="bg-white round-52 rounded-circle d-flex align-items-center justify-content-center">
                <iconify-icon icon="lucide:arrow-up-right" class="fs-8 text-dark"></iconify-icon>
              </span>
            </a>
          </div>
        </div>
      </div>
    </section>

    <!--  Privacy Policy Section -->
    <section class="privacy-policy py-5 py-lg-11">
      <div class="container">
        <p class="fs-5 text-dark fw-medium">This Privacy Statement explains how personal information about students, educators, schools, and other individuals using our services is collected, used, and disclosed by <strong>Adilisha STEM Labs</strong> ("us", "we", "our"). This Privacy Statement describes our privacy practices in relation to the use of our platform, including the Adilisha XR headset, virtual laboratory ecosystem, educational portals, and related applications, services, and programs offered by us (the "Services").
        </p>
        <p class="fs-5 text-dark fw-medium">By using our Services, including accessing Adilisha STEM Labs virtual laboratories, using Adilisha XR headsets, or creating an account on our platform, you consent to the collection, usage, storage, and disclosure of your information as described in this Privacy Statement.
        </p>
        <p class="fs-5 text-dark fw-medium">Our Services may contain links to external websites or educational resources. The privacy practices and content of such sites or services will be governed by their respective privacy policies. We may update this Privacy Statement periodically. If changes occur, we will notify you by updating the date at the top of the statement and providing additional notifications (such as a statement on our homepage or an email notification). We encourage you to review this Privacy Statement whenever you use our Services to stay informed about how we handle your information.
        </p>
        
        <h4 class="my-4">Information We Collect</h4>
        <p class="fs-5 text-dark fw-medium">While using our Services, we may collect information necessary to provide educational experiences and support learning outcomes. This includes:</p>
        <ul class="fs-5 text-dark fw-medium">
          <li><strong>Student Information:</strong> Name, age, grade level, school affiliation, academic progress, experiment completion data, and learning analytics generated through virtual laboratory sessions.</li>
          <li><strong>Educator Information:</strong> Name, email address, school or institution affiliation, teaching credentials, and classroom management data.</li>
          <li><strong>School Information:</strong> Institution name, location, student enrollment data, device usage statistics, and implementation metrics.</li>
          <li><strong>XR Device Data:</strong> Adilisha XR usage statistics, session duration, experiment interactions, and technical performance metrics (collected anonymously for platform improvement).</li>
          <li><strong>Account Information:</strong> Username, email address, password (encrypted), profile preferences, and account activity logs.</li>
        </ul>
        
        <h4 class="my-4">How We Use Your Information</h4>
        <p class="fs-5 text-dark fw-medium">We use the information we collect to:</p>
        <ul class="fs-5 text-dark fw-medium">
          <li>Provide and improve our virtual laboratory experiences and educational content</li>
          <li>Track student progress and learning outcomes for educational assessment</li>
          <li>Enable teachers to monitor classroom engagement and provide personalized instruction</li>
          <li>Optimize Adilisha XR performance and develop new educational features</li>
          <li>Communicate with schools, educators, and students about platform updates and educational opportunities</li>
          <li>Conduct research and analysis to improve STEM education delivery (with anonymized data)</li>
          <li>Ensure platform security and prevent unauthorized access</li>
        </ul>
        
        <h4 class="my-4">Student Data Protection</h4>
        <p class="fs-5 text-dark fw-medium">We are committed to protecting student privacy and comply with applicable educational data protection laws. Student data is collected and used solely for educational purposes. We do not sell student information to third parties. Student data may be shared with authorized school administrators and educators for educational assessment purposes only.
        </p>
        
        <h4 class="my-4">Data Security and Storage</h4>
        <p class="fs-5 text-dark fw-medium">We implement industry-standard security measures to protect your information, including encryption, secure data transmission, and restricted access controls. Data is stored on secure servers and may be processed in facilities located within Africa or internationally, always in compliance with applicable data protection regulations.
        </p>
        
        <h4 class="my-4">Third-Party Services</h4>
        <p class="fs-5 text-dark fw-medium">We may use third-party service providers to support our platform operations, including cloud hosting, analytics, and educational content delivery. These providers are contractually obligated to protect your information and use it only for specified purposes related to our Services.
        </p>
        
        <h4 class="my-4">Your Rights</h4>
        <p class="fs-5 text-dark fw-medium">You have the right to access, update, or request deletion of your personal information. Students and parents may request access to student data through their school administrators. To exercise these rights, please contact us using the information provided below.
        </p>
        
        <h4 class="my-4">Contact Us</h4>
        <p class="fs-5 text-dark fw-medium mb-0">If you have questions about this Privacy Statement or our data practices, please contact us at: <a class="fw-bold" href="mailto:support@adilishastemlabs.com" class="text-primary">support@adilishastemlabs.com</a> or through your school administrator.
        </p>
        <p class="fs-5 text-dark fw-medium mt-3 mb-0"><strong>Last Updated:</strong> {{ now()->format('F d, Y') }}</p>
      </div>
    </section>
</main>
@endsection
