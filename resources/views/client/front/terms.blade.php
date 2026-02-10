@extends('layouts.landing')

@section('title', config('app.name') . ' | Terms & Conditions')
@section('page', 'terms-conditions')

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
            <h1 class="mb-0 fs-16 text-white lh-1">Terms & Conditions</h1>
            <a href="javascript:void(0)" class="p-1 ps-7 bg-primary rounded-pill">
              <span class="bg-white round-52 rounded-circle d-flex align-items-center justify-content-center">
                <iconify-icon icon="lucide:arrow-up-right" class="fs-8 text-dark"></iconify-icon>
              </span>
            </a>
          </div>
        </div>
      </div>
    </section>

    <!--  Terms & Conditions Section -->
    <section class="terms-conditions py-5 py-lg-11">
      <div class="container">
        <p class="fs-5 text-dark fw-medium">This Terms of Service <span class="fw-bold">("Agreement")</span> is entered into by and between <strong>Adilisha STEM Labs</strong> <span class="fw-bold">("Adilisha", "we", "us", "our")</span> and the educational institution, school, or individual accessing or using our Services <span class="fw-bold">("User", "School", "you")</span>. This Agreement governs your use of Adilisha STEM Labs platform, including Adilisha XR headsets, virtual laboratory ecosystem, educational content, and related services (collectively, the "Services").</p>
        
        <p class="fs-5 text-dark fw-medium">By accessing, using, or registering for our Services, you agree to be bound by these Terms and Conditions. If you do not agree to these terms, please do not use our Services. We may modify this Agreement from time to time, and continued use of the Services after such modifications constitutes acceptance of the updated terms.</p>
        
        <h4 class="my-4">1. Definitions</h4>
        <p class="fs-5 text-dark fw-medium"><span class="fw-bold">1.1 "Adilisha XR"</span> means our proprietary extended reality (XR) headset hardware designed for educational use in classroom environments.</p>
        <p class="fs-5 text-dark fw-medium"><span class="fw-bold">1.2 "Virtual Laboratories"</span> means the immersive science laboratory experiences, experiments, simulations, and educational content accessible through Adilisha XR and our platform.</p>
        <p class="fs-5 text-dark fw-medium"><span class="fw-bold">1.3 "Platform"</span> means the Adilisha STEM Labs ecosystem, including web portals, cloud services, teacher dashboards, student interfaces, and all associated software and applications.</p>
        <p class="fs-5 text-dark fw-medium"><span class="fw-bold">1.4 "Services"</span> means collectively the Adilisha XR hardware, virtual laboratory content, platform access, educational resources, support services, and any related applications or tools provided by Adilisha STEM Labs.</p>
        <p class="fs-5 text-dark fw-medium"><span class="fw-bold">1.5 "User"</span> means any individual who accesses or uses the Services, including students, educators, administrators, or school personnel.</p>
        <p class="fs-5 text-dark fw-medium"><span class="fw-bold">1.6 "School"</span> means the educational institution, organization, or entity that has entered into an agreement with Adilisha STEM Labs to provide Services to its students and staff.</p>
        <p class="fs-5 text-dark fw-medium"><span class="fw-bold">1.7 "Student Data"</span> means any information collected, generated, or stored about students in connection with their use of the Services, including academic progress, experiment completion, and learning analytics.</p>
        <p class="fs-5 text-dark fw-medium"><span class="fw-bold">1.8 "Educational Content"</span> means all laboratory experiments, simulations, educational materials, documentation, and resources made available through the Services.</p>

        <h4 class="my-4">2. Use of Services</h4>
        <p class="fs-5 text-dark fw-medium"><span class="fw-bold">2.1 License Grant</span> Subject to your compliance with these Terms, Adilisha STEM Labs grants you a limited, non-exclusive, non-transferable license to access and use the Services solely for educational purposes within your authorized school or institution.</p>
        <p class="fs-5 text-dark fw-medium"><span class="fw-bold">2.2 Authorized Use</span> The Services are intended for educational use only. You may not use the Services for any commercial purpose, reverse engineering, or any activity that violates applicable laws or regulations. Adilisha XR headsets must be used in accordance with provided documentation and safety guidelines.</p>
        <p class="fs-5 text-dark fw-medium"><span class="fw-bold">2.3 Account Responsibility</span> You are responsible for maintaining the confidentiality of your account credentials and for all activities that occur under your account. Schools are responsible for managing student accounts and ensuring appropriate use of the Services by their students and staff.</p>
        <p class="fs-5 text-dark fw-medium"><span class="fw-bold">2.4 Age Requirements</span> Students under the age of 18 must have parental or guardian consent and school authorization to use the Services. Schools are responsible for obtaining necessary consents and ensuring compliance with applicable child protection laws.</p>

        <h4 class="my-4">3. Hardware and Equipment</h4>
        <p class="fs-5 text-dark fw-medium"><span class="fw-bold">3.1 Adilisha XR Ownership</span> Adilisha XR headsets remain the property of Adilisha STEM Labs or the purchasing school, as specified in your agreement. You are responsible for proper care, maintenance, and safe use of the hardware.</p>
        <p class="fs-5 text-dark fw-medium"><span class="fw-bold">3.2 Damage and Loss</span> Schools are responsible for any damage to or loss of Adilisha XR headsets while in their possession, except for normal wear and tear or defects covered under warranty.</p>
        <p class="fs-5 text-dark fw-medium"><span class="fw-bold">3.3 Safety</span> Users must follow all safety guidelines provided with Adilisha XR. The headsets should not be used by individuals with certain medical conditions, and appropriate supervision is required for student use.</p>

        <h4 class="my-4">4. Educational Content and Intellectual Property</h4>
        <p class="fs-5 text-dark fw-medium"><span class="fw-bold">4.1 Content Ownership</span> All educational content, virtual laboratory experiences, software, and materials provided through the Services are the intellectual property of Adilisha STEM Labs or its licensors and are protected by copyright and other intellectual property laws.</p>
        <p class="fs-5 text-dark fw-medium"><span class="fw-bold">4.2 Limited Use Rights</span> You may access and use the educational content solely for educational purposes within your authorized institution. You may not copy, modify, distribute, or create derivative works from the content without express written permission.</p>
        <p class="fs-5 text-dark fw-medium"><span class="fw-bold">4.3 Student Work</span> Students retain ownership of work products created using the Services, subject to Adilisha STEM Labs' right to use anonymized, aggregated data for platform improvement and educational research.</p>

        <h4 class="my-4">5. Student Data and Privacy</h4>
        <p class="fs-5 text-dark fw-medium"><span class="fw-bold">5.1 Data Collection</span> We collect student data necessary to provide educational services, track learning progress, and improve our platform. Our collection and use of student data is governed by our Privacy Policy and applicable educational data protection laws.</p>
        <p class="fs-5 text-dark fw-medium"><span class="fw-bold">5.2 Data Security</span> We implement industry-standard security measures to protect student data. However, no system is completely secure, and schools should ensure appropriate data protection measures are in place.</p>
        <p class="fs-5 text-dark fw-medium"><span class="fw-bold">5.3 Parental Rights</span> Parents and guardians have the right to access, review, and request deletion of their child's data in accordance with applicable laws and our Privacy Policy.</p>

        <h4 class="my-4">6. Prohibited Activities</h4>
        <p class="fs-5 text-dark fw-medium">You agree not to:</p>
        <ul class="fs-5 text-dark fw-medium">
          <li>Use the Services for any illegal or unauthorized purpose</li>
          <li>Attempt to hack, reverse engineer, or compromise the security of the platform or Adilisha XR hardware</li>
          <li>Share account credentials or allow unauthorized access to the Services</li>
          <li>Remove, modify, or tamper with any hardware, software, or content provided by Adilisha STEM Labs</li>
          <li>Use the Services in a manner that could harm, disable, or impair the platform or other users</li>
          <li>Collect or harvest information about other users without authorization</li>
        </ul>

        <h4 class="my-4">7. Limitation of Liability</h4>
        <p class="fs-5 text-dark fw-medium">To the maximum extent permitted by law, Adilisha STEM Labs shall not be liable for any indirect, incidental, special, or consequential damages arising from your use of the Services. Our total liability shall not exceed the amount paid by your school for the Services in the twelve months preceding the claim.</p>

        <h4 class="my-4">8. Termination</h4>
        <p class="fs-5 text-dark fw-medium"><span class="fw-bold">8.1 Termination by You</span> You may stop using the Services at any time. Schools may terminate their agreement in accordance with the terms of their specific service agreement.</p>
        <p class="fs-5 text-dark fw-medium"><span class="fw-bold">8.2 Termination by Us</span> We may suspend or terminate your access to the Services if you violate these Terms or engage in activities that harm the platform or other users.</p>
        <p class="fs-5 text-dark fw-medium"><span class="fw-bold">8.3 Effect of Termination</span> Upon termination, your right to access and use the Services will immediately cease. We may delete your account and data in accordance with our data retention policies.</p>

        <h4 class="my-4">9. Modifications to Terms</h4>
        <p class="fs-5 text-dark fw-medium">We reserve the right to modify these Terms at any time. We will notify users of material changes through the platform or via email. Continued use of the Services after such modifications constitutes acceptance of the updated Terms.</p>

        <h4 class="my-4">10. Contact Information</h4>
        <p class="fs-5 text-dark fw-medium mb-0">If you have questions about these Terms and Conditions, please contact us at: <a class="fw-bold text-primary" href="mailto:legal@adilisha.org">legal@adilisha.org</a> or through your school administrator.</p>
        <p class="fs-5 text-dark fw-medium mt-3 mb-0"><strong>Last Updated:</strong> {{ now()->format('F d, Y') }}</p>
      </div>
    </section>
</main>
@endsection
