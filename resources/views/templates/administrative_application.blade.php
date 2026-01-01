{{-- Template pour Demande Administrative --}}
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

{{ $applicant_name ?? '' }}
{{ $applicant_address ?? '' }}
{{ $applicant_city ?? '' }}
@if(!empty($applicant_phone))
Téléphone : {{ $applicant_phone }}
@endif
@if(!empty($applicant_email))
Email : {{ $applicant_email }}
@endif


{{ $recipient_title ?? 'Monsieur/Madame le/la' }} {{ $recipient_name ?? '' }}
{{ $recipient_organization ?? '' }}
@if(!empty($recipient_address))
{{ $recipient_address }}
@endif
@if(!empty($recipient_city))
{{ $recipient_city }}
@endif


{{ $applicant_city ?? '' }}, le {{ \Carbon\Carbon::parse($date ?? now())->format($dateFormat) }}


Objet : {{ $subject ?? 'Demande administrative' }}

@if(!empty($reference_number))
Référence : {{ $reference_number }}
@endif


{{ $politeness_formula_opening ?? $openingFormula }}

@if($tone_level === 'VERY_OFFICIAL')
J'ai l'insigne honneur de venir très respectueusement auprès de votre haute bienveillance pour solliciter {{ $request_context ?? 'votre intervention dans le cadre de ma demande administrative' }}.
@elseif($tone_level === 'SIMPLE')
Je me permets de vous adresser cette demande concernant {{ $request_context ?? 'une démarche administrative' }}.
@else
J'ai l'honneur de venir auprès de votre haute bienveillance pour solliciter {{ $request_context ?? 'votre intervention dans le cadre de ma demande administrative' }}.
@endif

CONTEXTE DE LA DEMANDE :
{{ $request_details ?? '' }}

@if(!empty($justification))
JUSTIFICATION :
Les motifs de ma demande se justifient par les éléments suivants :
{{ $justification }}
@endif

@if(!empty($urgency_reason))
CARACTÈRE D'URGENCE :
{{ $urgency_reason }}
@endif

@if(!empty($expected_outcome))
RÉSULTAT ATTENDU :
À l'issue de cette démarche, j'espère obtenir {{ $expected_outcome }}.
@endif

@if(!empty($deadline))
DÉLAI SOUHAITÉ :
{{ $deadline }}
@endif

@if(!empty($contact_preference))
MODALITÉS DE CONTACT :
{{ $contact_preference }}
@endif

@if($tone_level === 'VERY_OFFICIAL')
Dans l'attente d'une suite favorable à ma très respectueuse demande, {{ $politeness_formula_closing ?? $closingFormula }}
@elseif($tone_level === 'SIMPLE')
En espérant une réponse favorable, {{ $politeness_formula_closing ?? $closingFormula }}
@else
Dans l'attente d'une suite favorable à ma demande, {{ $politeness_formula_closing ?? $closingFormula }}
@endif


{{ $applicant_name ?? '' }}
@if(!empty($applicant_signature))
{{ $applicant_signature }}
@endif


Pièces jointes :
{{ $attachments ?? 'Néant' }}