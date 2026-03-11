@extends('layouts.app')

@section('title', 'Privacy Policy — Curevia')
@section('meta_description', 'Read Curevia\'s Privacy Policy to understand how we collect, use, and protect your personal information.')

@section('content')
<section style="padding:7rem 0 5rem;">
<div style="max-width:800px;margin:0 auto;padding:0 1.5rem;">

    <div style="margin-bottom:3rem;">
        <span style="font-size:0.75rem;font-weight:700;text-transform:uppercase;letter-spacing:.12em;color:var(--accent-cyan);">Legal</span>
        <h1 style="font-size:clamp(2rem,5vw,3rem);font-weight:900;color:var(--text-primary);margin:0.5rem 0 1rem;line-height:1.1;">Privacy Policy</h1>
        <p style="color:var(--text-muted);font-size:0.88rem;">Last updated: March 1, 2026</p>
    </div>

    <div class="legal-body">
        @php $sections = [
            ['Introduction', 'Welcome to Curevia ("we," "us," or "our"). We are committed to protecting your personal privacy. This Privacy Policy explains how we collect, use, disclose, and safeguard your information when you visit curevia.com. Please read this policy carefully. If you disagree with its terms, please discontinue use of the site.'],
            ['Information We Collect', "We may collect information about you in the following ways:\n\n**Account Information** — When you register, we collect your name, email address, and a hashed password. We never store passwords in plain text.\n\n**Usage Data** — We automatically collect information about how you interact with Curevia, including pages visited, search queries, time spent on articles, and device/browser information. This helps us improve the experience.\n\n**Cookies** — We use essential session cookies to maintain your login state, and analytics cookies (if consent is given) to understand usage patterns. You can disable cookies in your browser settings."],
            ['How We Use Your Information', "We use collected information for:\n\n• Providing, operating, and improving Curevia\n• Personalizing your experience (e.g., saved bookmarks)\n• Sending transactional emails (e.g., password reset)\n• Analyzing usage trends to improve content quality\n• Preventing fraud and ensuring security\n\nWe do NOT sell your personal data to third parties, ever."],
            ['Information Sharing', "We share your information only in the following limited circumstances:\n\n**Service Providers** — Trusted third-party services (e.g., hosting, analytics) that help operate Curevia. These parties are contractually bound to confidentiality.\n\n**Legal Requirements** — When required by law, court order, or to protect rights and safety.\n\n**Business Transfer** — In the event of a merger or acquisition, your data may transfer as a business asset. You will be notified in advance."],
            ['Data Retention', 'We retain your personal information for as long as your account is active or as needed to provide services. You may request deletion of your account and associated data at any time by contacting us at privacy@curevia.com. Anonymized analytics data may be retained indefinitely.'],
            ['Your Rights', "Depending on your jurisdiction, you may have the right to:\n\n• Access the personal data we hold about you\n• Correct inaccurate or incomplete data\n• Request deletion of your data (right to be forgotten)\n• Object to or restrict processing\n• Data portability\n\nTo exercise these rights, contact privacy@curevia.com. We will respond within 30 days."],
            ['Security', 'We implement industry-standard security measures including HTTPS encryption, hashed passwords (bcrypt), and regular security audits. However, no internet transmission is 100% secure. We encourage you to use a strong, unique password for your Curevia account.'],
            ['Children\'s Privacy', 'Curevia is not directed to children under 13. We do not knowingly collect personal information from children under 13. If you believe we have inadvertently collected such information, please contact us immediately.'],
            ['Changes to This Policy', 'We may update this Privacy Policy periodically. When we do, we will update the "Last updated" date above and notify registered users by email for material changes. Continued use after changes constitutes acceptance.'],
            ['Contact Us', "For privacy questions or requests:\n\n**Email:** privacy@curevia.com\n**Address:** Curevia Knowledge Platform\n\nWe aim to respond to all privacy inquiries within 5 business days."],
        ]; @endphp

        @foreach($sections as $idx => [$title, $body])
        <div style="margin-bottom:2.5rem;">
            <h2 style="font-size:1.15rem;font-weight:800;color:var(--text-primary);margin-bottom:0.75rem;display:flex;align-items:center;gap:0.75rem;">
                <span style="width:28px;height:28px;border-radius:50%;background:rgba(34,242,226,0.1);border:1px solid rgba(34,242,226,0.2);display:inline-flex;align-items:center;justify-content:center;font-size:0.7rem;font-weight:900;color:var(--accent-cyan);flex-shrink:0;">{{ $idx + 1 }}</span>
                {{ $title }}
            </h2>
            <div style="color:var(--text-secondary);line-height:1.85;font-size:0.95rem;padding-left:2.75rem;">
                @foreach(explode("\n\n", $body) as $para)
                <p style="margin-bottom:1rem;">{{ $para }}</p>
                @endforeach
            </div>
        </div>
        @if($idx < count($sections) - 1)
        <hr style="border:none;border-top:1px solid var(--border-subtle);margin-bottom:2.5rem;">
        @endif
        @endforeach
    </div>

    <div style="background:rgba(124,108,255,0.04);border:1px solid rgba(124,108,255,0.15);border-radius:1.25rem;padding:1.75rem;display:flex;align-items:center;gap:1.5rem;margin-top:3rem;flex-wrap:wrap;">
        <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="var(--accent-violet)" stroke-width="1.5" style="flex-shrink:0;"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
        <div>
            <div style="font-size:0.95rem;font-weight:700;color:var(--text-primary);margin-bottom:0.25rem;">Your privacy is our priority</div>
            <div style="font-size:0.85rem;color:var(--text-secondary);">Questions? <a href="{{ route('contact') }}" style="color:var(--accent-cyan);text-decoration:none;">Contact our privacy team</a> anytime.</div>
        </div>
    </div>

</div>
</section>
@endsection
