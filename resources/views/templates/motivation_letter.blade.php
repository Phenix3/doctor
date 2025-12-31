{{-- Template pour Lettre de Motivation --}}
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

Objet : {{ $subject ?? 'Lettre de motivation' }}

{{ $politeness_formula_opening ?? 'Monsieur/Madame,' }}

{{ $introduction ?? '' }}

{{ $professional_experience ?? '' }}

{{ $skills_and_qualifications ?? '' }}

{{ $motivation_reasons ?? '' }}

{{ $contribution_expectations ?? '' }}

{{ $availability ?? '' }}

{{ $politeness_formula_closing ?? 'Dans l\'attente d\'une réponse favorable, je vous prie d\'agréer, Monsieur/Madame, l\'expression de mes salutations distinguées.' }}

{{ $sender_name ?? '' }}
{{ $sender_signature ?? '' }}

Pièces jointes :
{{ $attachments ?? 'CV, Diplômes, Certificats' }}