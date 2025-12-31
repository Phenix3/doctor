{{-- Template pour Courrier Administratif Générique --}}
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

Objet : {{ $subject ?? 'Correspondance administrative' }}

{{ $politeness_formula_opening ?? 'Monsieur/Madame,' }}

{{ $correspondence_content ?? '' }}

{{ $main_message ?? '' }}

{{ $supporting_details ?? '' }}

{{ $requested_action ?? '' }}

{{ $follow_up_information ?? '' }}

{{ $politeness_formula_closing ?? 'Je vous prie d\'agréer, Monsieur/Madame, l\'expression de mes salutations distinguées.' }}

{{ $sender_name ?? '' }}
{{ $sender_signature ?? '' }}

Pièces jointes :
{{ $attachments ?? 'Selon objet' }}