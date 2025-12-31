{{-- Template pour Demande de Stage --}}
@php
    $currentDate = now()->format('d/m/Y');
@endphp

{{ $sender_name ?? '' }}
{{ $sender_address ?? '' }}
{{ $sender_city ?? '' }}
{{ $sender_phone ?? '' }}
{{ $sender_email ?? '' }}

{{ $recipient_title ?? '' }}
{{ $recipient_name ?? '' }}
{{ $recipient_organization ?? '' }}
{{ $recipient_address ?? '' }}
{{ $recipient_city ?? '' }}

{{ $sender_city ?? '' }}, le {{ $currentDate }}

Objet : {{ $subject ?? 'Demande de stage' }}

{{ $politeness_formula_opening ?? 'Monsieur/Madame,' }}

{{ $student_status ?? '' }}

{{ $academic_program ?? '' }}

{{ $internship_duration ?? '' }}

{{ $internship_objectives ?? '' }}

{{ $organization_interest ?? '' }}

{{ $skills_contribution ?? '' }}

{{ $supervision_requirements ?? '' }}

{{ $politeness_formula_closing ?? 'En espérant que ma demande retiendra votre attention, je vous prie d\'agréer, Monsieur/Madame, l\'expression de mes salutations respectueuses.' }}

{{ $sender_name ?? '' }}
{{ $sender_signature ?? '' }}

Pièces jointes :
{{ $attachments ?? 'CV, Lettre de recommandation, Attestation d\'inscription' }}