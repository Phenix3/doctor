{{-- Template pour Lettre de Recours ou de Report --}}
@php
    $currentDate = now()->format('d/m/Y');
@endphp

{{ $appellant_name ?? '' }}
{{ $appellant_address ?? '' }}
{{ $appellant_city ?? '' }}
{{ $appellant_phone ?? '' }}
{{ $appellant_email ?? '' }}

{{ $recipient_title ?? '' }}
{{ $recipient_name ?? '' }}
{{ $recipient_organization ?? '' }}
{{ $recipient_address ?? '' }}
{{ $recipient_city ?? '' }}

{{ $appellant_city ?? '' }}, le {{ $currentDate }}

Objet : {{ $subject ?? 'Recours/Report' }}

{{ $politeness_formula_opening ?? 'Monsieur/Madame,' }}

{{ $original_decision_reference ?? '' }}

{{ $decision_date ?? '' }}

{{ $decision_description ?? '' }}

{{ $appeal_grounds ?? '' }}

{{ $legal_basis ?? '' }}

{{ $factual_arguments ?? '' }}

{{ $new_evidence ?? '' }}

{{ $requested_outcome ?? '' }}

{{ $deadline_considerations ?? '' }}

{{ $politeness_formula_closing ?? 'En espérant que mes arguments retiendront votre attention, je vous prie d\'agréer, Monsieur/Madame, l\'expression de mes salutations distinguées.' }}

{{ $appellant_name ?? '' }}
{{ $appellant_signature ?? '' }}

Pièces jointes :
{{ $attachments ?? 'Copie de la décision contestée, Pièces justificatives' }}