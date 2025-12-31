{{-- Template pour Demande d'Acte Administratif --}}
@php
    $currentDate = now()->format('d/m/Y');
@endphp

{{ $requester_name ?? '' }}
{{ $requester_address ?? '' }}
{{ $requester_city ?? '' }}
{{ $requester_phone ?? '' }}
{{ $requester_email ?? '' }}

{{ $recipient_title ?? '' }}
{{ $recipient_name ?? '' }}
{{ $recipient_organization ?? '' }}
{{ $recipient_address ?? '' }}
{{ $recipient_city ?? '' }}

{{ $requester_city ?? '' }}, le {{ $currentDate }}

Objet : {{ $subject ?? 'Demande d\'acte administratif' }}

{{ $politeness_formula_opening ?? 'Monsieur/Madame,' }}

{{ $request_purpose ?? '' }}

{{ $document_type_requested ?? '' }}

{{ $person_concerned ?? '' }}

{{ $relationship_to_person ?? '' }}

{{ $specific_details ?? '' }}

{{ $urgency_justification ?? '' }}

{{ $delivery_method ?? '' }}

{{ $payment_information ?? '' }}

{{ $politeness_formula_closing ?? 'Je vous prie d\'agréer, Monsieur/Madame, l\'expression de mes salutations distinguées.' }}

{{ $requester_name ?? '' }}
{{ $requester_signature ?? '' }}

Pièces jointes :
{{ $attachments ?? 'Pièce d\'identité, Justificatifs' }}