{{-- Template pour Demande d'Acte Administratif --}}
@php
    $currentDate = isset($date) ? \Carbon\Carbon::parse($date)->format('d/m/Y') : now()->format('d/m/Y');
    $country = $country ?? 'SN';
    $tone_level = $tone_level ?? 'OFFICIAL';
    
    // Country-specific adaptations
    $dateFormat = match($country) {
        'CI' => 'd/m/Y',
        'SN' => 'd/m/Y',
        'ML' => 'd/m/Y',
        'BF' => 'd/m/Y',
        default => 'd/m/Y'
    };
    
    // Tone-specific formulas
    $openingFormula = match($tone_level) {
        'SIMPLE' => 'Monsieur/Madame,',
        'OFFICIAL' => 'Monsieur/Madame,',
        'VERY_OFFICIAL' => 'Excellence, Monsieur/Madame le/la ' . ($recipient_title ?? 'Ministre'),
        default => 'Monsieur/Madame,'
    };
    
    $closingFormula = match($tone_level) {
        'SIMPLE' => 'Cordialement,',
        'OFFICIAL' => 'Je vous prie d\'agréer, Monsieur/Madame, l\'expression de mes salutations distinguées.',
        'VERY_OFFICIAL' => 'Je vous prie d\'agréer, Excellence, l\'expression de ma très haute considération.',
        default => 'Je vous prie d\'agréer, Monsieur/Madame, l\'expression de mes salutations distinguées.'
    };
@endphp

{{ $requester_name ?? '' }}
{{ $requester_address ?? '' }}
{{ $requester_city ?? '' }}
@if(!empty($requester_phone))
Téléphone : {{ $requester_phone }}
@endif
@if(!empty($requester_email))
Email : {{ $requester_email }}
@endif


{{ $recipient_title ?? 'Monsieur/Madame le/la' }} {{ $recipient_name ?? '' }}
{{ $recipient_organization ?? '' }}
@if(!empty($recipient_address))
{{ $recipient_address }}
@endif
@if(!empty($recipient_city))
{{ $recipient_city }}
@endif


{{ $requester_city ?? '' }}, le {{ \Carbon\Carbon::parse($date ?? now())->format($dateFormat) }}


Objet : {{ $subject ?? 'Demande de délivrance d\'acte administratif' }}

@if(!empty($reference_number))
Référence : {{ $reference_number }}
@endif


{{ $politeness_formula_opening ?? $openingFormula }}

@if($tone_level === 'VERY_OFFICIAL')
J'ai l'insigne honneur de solliciter de votre haute bienveillance la délivrance d'un acte administratif dans les conditions précisées ci-après.
@elseif($tone_level === 'SIMPLE')
Je sollicite la délivrance d'un acte administratif dans les conditions précisées ci-après.
@else
J'ai l'honneur de solliciter de votre haute bienveillance la délivrance d'un acte administratif dans les conditions précisées ci-après.
@endif

OBJET DE LA DEMANDE :
{{ $request_purpose ?? '' }}

TYPE DE DOCUMENT DEMANDÉ :
{{ $document_type_requested ?? '' }}

PERSONNE CONCERNÉE PAR L'ACTE :
Nom et prénoms : {{ $person_concerned ?? '' }}
@if(!empty($person_birth_date))
Date de naissance : {{ \Carbon\Carbon::parse($person_birth_date)->format($dateFormat) }}
@endif
@if(!empty($person_birth_place))
Lieu de naissance : {{ $person_birth_place }}
@endif

@if(!empty($relationship_to_person))
LIEN AVEC LA PERSONNE CONCERNÉE :
{{ $relationship_to_person }}
@endif

@if(!empty($specific_details))
DÉTAILS SPÉCIFIQUES :
{{ $specific_details }}
@endif

@if(!empty($period_concerned))
PÉRIODE CONCERNÉE :
{{ $period_concerned }}
@endif

JUSTIFICATION DE LA DEMANDE :
{{ $justification ?? 'Cette demande est motivée par des besoins administratifs légitimes.' }}

@if(!empty($urgency_justification))
JUSTIFICATION DE L'URGENCE :
{{ $urgency_justification }}
@endif

@if(!empty($delivery_method))
MODE DE LIVRAISON SOUHAITÉ :
{{ $delivery_method }}
@endif

@if(!empty($delivery_address))
ADRESSE DE LIVRAISON :
{{ $delivery_address }}
@endif

@if(!empty($payment_information))
INFORMATIONS DE PAIEMENT :
{{ $payment_information }}
@endif

@if(!empty($contact_person))
PERSONNE À CONTACTER :
{{ $contact_person }}
@if(!empty($contact_phone))
Téléphone : {{ $contact_phone }}
@endif
@endif

{{ $politeness_formula_closing ?? $closingFormula }}


{{ $requester_name ?? '' }}
@if(!empty($requester_signature))
{{ $requester_signature }}
@endif


Pièces jointes :
{{ $attachments ?? 'Pièce d\'identité, Justificatifs de domicile, Procuration (le cas échéant)' }}