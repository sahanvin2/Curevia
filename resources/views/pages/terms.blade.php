@extends('layouts.app')

@section('title', 'Terms of Use — Curevia')
@section('meta_description', 'Read Curevia\'s Terms of Use. By using our platform you agree to these terms governing access and use of our knowledge services.')

@section('content')
<section style="padding:7rem 0 5rem;">
<div style="max-width:800px;margin:0 auto;padding:0 1.5rem;">

    <div style="margin-bottom:3rem;">
        <span style="font-size:0.75rem;font-weight:700;text-transform:uppercase;letter-spacing:.12em;color:var(--accent-violet);">Legal</span>
        <h1 style="font-size:clamp(2rem,5vw,3rem);font-weight:900;color:var(--text-primary);margin:0.5rem 0 1rem;line-height:1.1;">Terms of Use</h1>
        <p style="color:var(--text-muted);font-size:0.88rem;">Last updated: March 1, 2026 · Effective immediately upon accessing Curevia</p>
    </div>

    <div style="background:rgba(245,158,11,0.06);border:1px solid rgba(245,158,11,0.2);border-radius:1rem;padding:1.25rem 1.5rem;margin-bottom:2.5rem;display:flex;gap:1rem;">
        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#F59E0B" stroke-width="2" style="flex-shrink:0;margin-top:2px;"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
        <p style="color:var(--text-secondary);font-size:0.88rem;line-height:1.7;margin:0;">By accessing or using Curevia, you agree to be bound by these Terms of Use. If you do not agree, please do not use our services.</p>
    </div>

    @php $sections = [
        ['Acceptance of Terms', 'These Terms of Use ("Terms") constitute a legally binding agreement between you and Curevia ("Company," "we," "us"). You must be at least 13 years old to use Curevia. By creating an account or using any part of the service, you represent that you meet this requirement.'],
        ['Use of the Service', "You agree to use Curevia only for lawful, personal, non-commercial purposes. You must not:\n\n• Copy, distribute, or modify encyclopedia content without attribution\n• Use automated bots or scrapers to harvest content\n• Attempt to gain unauthorized access to systems\n• Post harmful, deceptive, or illegal content\n• Harass, intimidate, or harm other users\n• Use the service to distribute spam or malware"],
        ['Intellectual Property', "All encyclopedia articles, images, designs, and code on Curevia are either owned by Curevia or used with permission. Article text is available under Creative Commons Attribution 4.0 (CC BY 4.0) — you may share and adapt it with proper attribution to Curevia.\n\nThe Curevia name, logo, and visual design are proprietary trademarks and may not be used without written permission."],
        ['User Accounts', "When you create an account, you are responsible for:\n\n• Maintaining the confidentiality of your password\n• All activities that occur under your account\n• Notifying us immediately of any unauthorized use\n\nWe reserve the right to suspend accounts that violate these Terms or engage in harmful behavior."],
        ['User Content', 'By submitting comments, reviews, or other content, you grant Curevia a worldwide, royalty-free license to use, display, and distribute that content as part of the service. You retain ownership of your content and may request removal at any time.'],
        ['Shop & Purchases', "Products listed in the Curevia Shop are for educational and informational purposes. We act as an affiliate or reseller and do not manufacture products directly. Product availability, pricing, and specifications are subject to change without notice. Purchases are subject to our Refund Policy available at checkout."],
        ['Disclaimers', "Curevia provides educational content in good faith but makes NO WARRANTIES regarding accuracy, completeness, or fitness for a particular purpose. Encyclopedia articles are for general knowledge purposes only and should not be used as a substitute for professional advice (medical, legal, financial, etc.).\n\nTHE SERVICE IS PROVIDED \"AS IS\" WITHOUT WARRANTY OF ANY KIND."],
        ['Limitation of Liability', 'To the maximum extent permitted by applicable law, Curevia shall not be liable for any indirect, incidental, consequential, or punitive damages arising from your use of the service, even if we have been advised of the possibility of such damages.'],
        ['Termination', 'We reserve the right to terminate or suspend your account at our discretion, with or without notice, for violation of these Terms. Upon termination, your right to use the service ceases immediately.'],
        ['Changes to Terms', 'We may update these Terms periodically. Material changes will be communicated via email to registered users. Continued use of Curevia after changes constitutes acceptance of the revised Terms.'],
        ['Governing Law', 'These Terms are governed by applicable law. Any disputes shall be resolved through good-faith negotiation before pursuing legal remedies.'],
        ['Contact', 'For questions about these Terms, contact us at legal@curevia.com or through our Contact page.'],
    ]; @endphp

    @foreach($sections as $idx => [$title, $body])
    <div style="margin-bottom:2.25rem;">
        <h2 style="font-size:1.1rem;font-weight:800;color:var(--text-primary);margin-bottom:0.75rem;display:flex;align-items:center;gap:0.75rem;">
            <span style="width:26px;height:26px;border-radius:50%;background:rgba(124,108,255,0.1);border:1px solid rgba(124,108,255,0.2);display:inline-flex;align-items:center;justify-content:center;font-size:0.68rem;font-weight:900;color:var(--accent-violet);flex-shrink:0;">{{ $idx + 1 }}</span>
            {{ $title }}
        </h2>
        <div style="color:var(--text-secondary);line-height:1.85;font-size:0.93rem;padding-left:2.5rem;">
            @foreach(explode("\n\n", $body) as $para)
            <p style="margin-bottom:0.85rem;">{{ $para }}</p>
            @endforeach
        </div>
    </div>
    @if($idx < count($sections) - 1)
    <hr style="border:none;border-top:1px solid var(--border-subtle);margin-bottom:2.25rem;">
    @endif
    @endforeach

    <div style="background:rgba(34,242,226,0.04);border:1px solid rgba(34,242,226,0.12);border-radius:1.25rem;padding:1.75rem;display:flex;align-items:center;gap:1.5rem;margin-top:2.5rem;flex-wrap:wrap;">
        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="var(--accent-cyan)" stroke-width="1.5" style="flex-shrink:0;"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
        <div>
            <div style="font-size:0.9rem;font-weight:700;color:var(--text-primary);margin-bottom:0.25rem;">Have questions about these terms?</div>
            <div style="font-size:0.83rem;color:var(--text-secondary);">We're happy to clarify. <a href="{{ route('contact') }}" style="color:var(--accent-cyan);text-decoration:none;">Contact our team →</a></div>
        </div>
    </div>

</div>
</section>
@endsection
