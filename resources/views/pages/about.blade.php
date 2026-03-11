@extends('layouts.app')

@section('title', 'About Curevia — The Ocean of Knowledge')
@section('meta_description', 'Learn about Curevia — our mission to make the universe of knowledge freely accessible to every curious mind on Earth.')

@section('content')
<section style="padding:7rem 0 5rem;">
<div style="max-width:900px;margin:0 auto;padding:0 1.5rem;">

    {{-- Hero --}}
    <div style="text-align:center;margin-bottom:4rem;">
        <div style="display:inline-flex;align-items:center;gap:0.5rem;background:rgba(34,242,226,0.08);border:1px solid rgba(34,242,226,0.2);border-radius:100px;padding:0.4rem 1.25rem;font-size:0.78rem;font-weight:700;color:var(--accent-cyan);text-transform:uppercase;letter-spacing:.12em;margin-bottom:1.5rem;">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4M12 8h.01"/></svg>
            About Us
        </div>
        <h1 style="font-size:clamp(2.5rem,6vw,4rem);font-weight:900;color:var(--text-primary);line-height:1.1;letter-spacing:-0.03em;margin-bottom:1.5rem;">The Ocean of <span style="background:linear-gradient(135deg,var(--accent-cyan),var(--accent-violet));-webkit-background-clip:text;-webkit-text-fill-color:transparent;">Knowledge</span></h1>
        <p style="font-size:1.15rem;color:var(--text-secondary);line-height:1.8;max-width:600px;margin:0 auto;">Curevia is a free, open knowledge encyclopedia designed for the endlessly curious — where science, history, nature, and technology converge.</p>
    </div>

    {{-- Mission --}}
    <div style="background:linear-gradient(135deg,rgba(34,242,226,0.04),rgba(124,108,255,0.04));border:1px solid var(--border-subtle);border-radius:1.5rem;padding:2.5rem;margin-bottom:2.5rem;">
        <h2 style="font-size:1.5rem;font-weight:800;color:var(--text-primary);margin-bottom:1rem;display:flex;align-items:center;gap:0.75rem;">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="var(--accent-cyan)" stroke-width="2"><circle cx="12" cy="12" r="10"/><polygon points="10 8 16 12 10 16 10 8" fill="var(--accent-cyan)"/></svg>
            Our Mission
        </h2>
        <p style="color:var(--text-secondary);line-height:1.9;font-size:1rem;margin:0;">We believe knowledge should be free, beautiful, and accessible to everyone. Curevia was built to be the most engaging and trustworthy encyclopedia on the internet — combining rigorous accuracy with a reading experience that feels as immersive as great storytelling. Whether you're a student, researcher, teacher, or simply someone who never stopped asking "why," Curevia is your home.</p>
    </div>

    {{-- Stats --}}
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:1.5rem;margin-bottom:3rem;">
        @foreach([['20+','Encyclopedia Articles'],['12+','Product Categories'],['7','Story Series'],['5+','Knowledge Domains']] as [$n,$l])
        <div style="background:rgba(17,24,39,0.6);border:1px solid var(--border-subtle);border-radius:1.25rem;padding:1.75rem 1.5rem;text-align:center;">
            <div style="font-size:2.5rem;font-weight:900;background:linear-gradient(135deg,var(--accent-cyan),var(--accent-violet));-webkit-background-clip:text;-webkit-text-fill-color:transparent;line-height:1;margin-bottom:0.5rem;">{{ $n }}</div>
            <div style="font-size:0.8rem;color:var(--text-muted);font-weight:500;">{{ $l }}</div>
        </div>
        @endforeach
    </div>

    {{-- Values --}}
    <h2 style="font-size:1.5rem;font-weight:800;color:var(--text-primary);margin-bottom:1.5rem;">Our Values</h2>
    <div style="display:grid;gap:1.25rem;margin-bottom:3rem;">
        @foreach([
            ['🔬','Scientific Accuracy','Every article is authored by experts and reviewed for factual accuracy. We cite primary sources and update articles as knowledge evolves.'],
            ['🌍','Universal Access','All encyclopedia content is free, forever. Knowledge should not be gated behind paywalls.'],
            ['✨','Beautiful Design','We believe a great reading experience deepens comprehension. Curevia is designed to make learning feel wonderful.'],
            ['🤝','Community','Our contributors are educators, scientists, writers, and passionate experts from around the world.'],
        ] as [$icon,$title,$desc])
        <div style="display:flex;gap:1.25rem;background:rgba(17,24,39,0.4);border:1px solid var(--border-subtle);border-radius:1.25rem;padding:1.5rem;">
            <div style="width:48px;height:48px;border-radius:0.875rem;background:rgba(34,242,226,0.06);display:flex;align-items:center;justify-content:center;font-size:1.4rem;flex-shrink:0;">{{ $icon }}</div>
            <div>
                <h3 style="font-size:1rem;font-weight:700;color:var(--text-primary);margin-bottom:0.35rem;">{{ $title }}</h3>
                <p style="font-size:0.88rem;color:var(--text-secondary);line-height:1.7;margin:0;">{{ $desc }}</p>
            </div>
        </div>
        @endforeach
    </div>

    {{-- Team --}}
    <div style="background:rgba(124,108,255,0.04);border:1px solid rgba(124,108,255,0.15);border-radius:1.5rem;padding:2.5rem;text-align:center;">
        <h2 style="font-size:1.4rem;font-weight:800;color:var(--text-primary);margin-bottom:0.75rem;">Built by Curators of Knowledge</h2>
        <p style="color:var(--text-secondary);line-height:1.8;max-width:600px;margin:0 auto 1.5rem;">Curevia is a passion project by a team that believes the internet deserves better encyclopedias. We're a small team with big ambitions — and we're just getting started.</p>
        <a href="{{ route('contact') }}" class="btn-primary" style="display:inline-flex;align-items:center;gap:0.5rem;padding:0.75rem 2rem;font-size:0.9rem;text-decoration:none;">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 4H4c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
            Get in Touch
        </a>
    </div>

</div>
</section>
@endsection
