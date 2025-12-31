{{-- Template pour Lettre de Plainte --}}
@php
    $currentDate = now()->format('d/m/Y');
@endphp

{{ $complainant_name ?? '' }}
{{ $complainant_address ?? '' }}
{{ $complainant_city ?? '' }}
{{ $complainant_phone ?? '' }}
{{ $complainant_email ?? '' }}

{{ $recipient_title ?? '' }}
{{ $recipient_name ?? '' }}
{{ $recipient_organization ?? '' }}
{{ $recipient_address ?? '' }}
{{ $recipient_city ?? '' }}

{{ $complainant_city ?? '' }}, le {{ $currentDate }}

Objet : {{ $subject ?? 'Plainte' }}

{{ $politeness_formula_opening ?? 'Monsieur/Madame,' }}

{{ $incident_description ?? '' }}

{{ $incident_date ?? '' }}

{{ $incident_location ?? '' }}

{{ $parties_involved ?? '' }}

{{ $damages_or_prejudice ?? '' }}

{{ $evidence_description ?? '' }}

{{ $previous_attempts ?? '' }}

{{ $requested_action ?? '' }}

{{ $legal_references ?? '' }}

{{ $politeness_formula_closing ?? 'En espérant une suite favorable à ma plainte, je vous prie d\'agréer, Monsieur/Madame, l\'expression de mes salutations distinguées.' }}

{{ $complainant_name ?? '' }}
{{ $complainant_signature ?? '' }}

Pièces jointes :
{{ $attachments ?? 'Preuves, Témoignages, Documents justificatifs' }}