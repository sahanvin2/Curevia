@extends('layouts.app')

@section('title', 'Advertise on Curevia — Reach Curious Minds')
@section('meta_description', 'Advertise on Curevia and reach a highly engaged audience of curious learners, students, and knowledge-seekers worldwide.')

@section('content')
<section style="padding:7rem 0 5rem;">
<div style="max-width:960px;margin:0 auto;padding:0 1.5rem;">

    {{-- Hero --}}
    <div style="text-align:center;margin-bottom:5rem;">
        <div style="display:inline-flex;align-items:center;gap:0.5rem;background:rgba(245,158,11,0.08);border:1px solid rgba(245,158,11,0.25);border-radius:100px;padding:0.4rem 1.25rem;font-size:0.78rem;font-weight:700;color:#F59E0B;text-transform:uppercase;letter-spacing:.12em;margin-bottom:1.5rem;">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 12h-4l-3 9L9 3l-3 9H2"/></svg>
            Advertise
        </div>
        <h1 style="font-size:clamp(2.5rem,6vw,4rem);font-weight:900;color:var(--text-primary);line-height:1.1;letter-spacing:-0.03em;margin-bottom:1.5rem;">Reach <span style="background:linear-gradient(135deg,#F59E0B,var(--accent-cyan));-webkit-background-clip:text;-webkit-text-fill-color:transparent;">Curious Minds</span></h1>
        <p style="font-size:1.15rem;color:var(--text-secondary);line-height:1.8;max-width:620px;margin:0 auto;">Curevia connects your brand with an engaged audience of students, educators, researchers, and lifelong learners who are actively seeking knowledge.</p>
    </div>

    {{-- Audience Stats --}}
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(170px,1fr));gap:1.5rem;margin-bottom:4rem;">
        @foreach([['67%','College-educated audience'],['8.2 min','Average time per visit'],['Top 3','Science, History, Tech interests'],['Mobile-first','60%+ mobile readers']] as [$n,$l])
        <div style="background:rgba(17,24,39,0.7);border:1px solid var(--border-subtle);border-radius:1.25rem;padding:1.75rem 1.5rem;text-align:center;">
            <div style="font-size:1.8rem;font-weight:900;background:linear-gradient(135deg,#F59E0B,var(--accent-cyan));-webkit-background-clip:text;-webkit-text-fill-color:transparent;line-height:1.2;margin-bottom:0.5rem;">{{ $n }}</div>
            <div style="font-size:0.78rem;color:var(--text-muted);font-weight:500;line-height:1.4;">{{ $l }}</div>
        </div>
        @endforeach
    </div>

    {{-- Ad Options --}}
    <h2 style="font-size:1.5rem;font-weight:800;color:var(--text-primary);margin-bottom:1.75rem;text-align:center;">Advertising Options</h2>
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:1.5rem;margin-bottom:4rem;">
        @foreach([
            ['Standard Display','From $299/month','Banner ads placed throughout encyclopedia articles and knowledge pages. Includes desktop and mobile formats.','#22F2E2',['300×250 sidebar banners','728×90 leaderboard','Mobile 320×50','Frequency capping']],
            ['Sponsored Content','From $799/month','Sponsored knowledge articles written by our editorial team that align with your brand values and audience interests.','#7C6CFF',['Clearly labelled sponsored','Written by Curevia editors','Permanent placement','Social sharing included']],
            ['Premium Partnership','Custom Pricing','Full integration with co-branded experiences, newsletter features, and first-look access to new knowledge sections.','#F59E0B',['Co-branded encyclopedia category','Newsletter sponsorship','Custom landing page','Priority support']],
        ] as [$name,$price,$desc,$color,$bullets])
        <div style="background:rgba(17,24,39,0.5);border:1px solid var(--border-subtle);border-radius:1.5rem;padding:2rem;display:flex;flex-direction:column;">
            <div style="font-size:0.7rem;font-weight:800;text-transform:uppercase;letter-spacing:.12em;color:{{ $color }};margin-bottom:0.75rem;">{{ $name }}</div>
            <div style="font-size:1.75rem;font-weight:900;color:var(--text-primary);margin-bottom:0.75rem;">{{ $price }}</div>
            <p style="font-size:0.875rem;color:var(--text-secondary);line-height:1.7;margin-bottom:1.25rem;flex:1;">{{ $desc }}</p>
            <ul style="list-style:none;padding:0;margin-bottom:1.5rem;">
                @foreach($bullets as $b)
                <li style="display:flex;align-items:center;gap:0.5rem;font-size:0.83rem;color:var(--text-secondary);padding:0.3rem 0;">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="{{ $color }}" stroke-width="2.5"><path d="M20 6L9 17l-5-5"/></svg>
                    {{ $b }}
                </li>
                @endforeach
            </ul>
            <a href="{{ route('contact') }}?subject=advertising" style="display:block;text-align:center;padding:0.7rem;background:rgba(34,242,226,0.06);border:1px solid rgba(34,242,226,0.15);border-radius:0.75rem;color:var(--accent-cyan);font-size:0.85rem;font-weight:700;text-decoration:none;transition:all .2s;" onmouseover="this.style.background='rgba(34,242,226,0.12)'" onmouseout="this.style.background='rgba(34,242,226,0.06)'">Get Started →</a>
        </div>
        @endforeach
    </div>

    {{-- Why Curevia --}}
    <div style="background:linear-gradient(135deg,rgba(34,242,226,0.04),rgba(124,108,255,0.04));border:1px solid var(--border-subtle);border-radius:1.5rem;padding:2.5rem;margin-bottom:3rem;">
        <h2 style="font-size:1.3rem;font-weight:800;color:var(--text-primary);margin-bottom:1.5rem;">Why Advertise on Curevia?</h2>
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:1.5rem;">
            @foreach([
                ['🎯','Intent-rich audience','Readers are actively seeking knowledge — highly receptive to relevant brands.'],
                ['✅','Brand-safe environment','All content is curated and moderated. No harmful or controversial content.'],
                ['📊','Detailed analytics','Real-time reporting on impressions, clicks, and engagement.'],
                ['🚀','Growing platform','Expanding content library means growing traffic and fresh audiences.'],
            ] as [$icon,$title,$desc])
            <div>
                <div style="font-size:1.5rem;margin-bottom:0.5rem;">{{ $icon }}</div>
                <div style="font-size:0.9rem;font-weight:700;color:var(--text-primary);margin-bottom:0.35rem;">{{ $title }}</div>
                <div style="font-size:0.82rem;color:var(--text-muted);line-height:1.6;">{{ $desc }}</div>
            </div>
            @endforeach
        </div>
    </div>

    {{-- CTA --}}
    <div style="text-align:center;padding:3rem 2rem;background:rgba(17,24,39,0.6);border:1px solid var(--border-subtle);border-radius:1.5rem;">
        <h2 style="font-size:1.5rem;font-weight:800;color:var(--text-primary);margin-bottom:0.75rem;">Ready to advertise?</h2>
        <p style="color:var(--text-secondary);margin-bottom:2rem;max-width:480px;margin-left:auto;margin-right:auto;font-size:0.95rem;line-height:1.7;">Contact our advertising team for a custom proposal. We respond within 1 business day.</p>
        <a href="{{ route('contact') }}" class="btn-primary" style="display:inline-flex;align-items:center;gap:0.5rem;padding:0.85rem 2.5rem;font-size:0.95rem;text-decoration:none;">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 4H4c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
            Contact Advertising Team
        </a>
    </div>

</div>
</section>
@endsection
