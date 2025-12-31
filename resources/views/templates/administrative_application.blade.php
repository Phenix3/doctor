{{-- Template pour Demande Administrative --}}
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

Objet : {{ $subject ?? 'Demande administrative' }}

{{ $politeness_formula_opening ?? 'Monsieur/Madame,' }}

{{ $request_context ?? '' }}

{{ $request_details ?? '' }}

{{ $justification ?? '' }}

{{ $expected_outcome ?? '' }}

{{ $politeness_formula_closing ?? 'Je vous prie d\'agréer, Monsieur/Madame, l\'expression de mes salutations distinguées.' }}

{{ $sender_name ?? '' }}
{{ $sender_signature ?? '' }}

Pièces jointes :
{{ $attachments ?? 'Néant' }}