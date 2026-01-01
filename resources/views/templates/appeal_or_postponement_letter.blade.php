{{-- Template pour Lettre de Recours ou de Report --}}
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
        'OFFICIAL' => 'En espérant que mes arguments retiendront votre attention, je vous prie d\'agréer, Monsieur/Madame, l\'expression de mes salutations distinguées.',
        'VERY_OFFICIAL' => 'En espérant que mes très respectueux arguments retiendront votre haute attention, je vous prie d\'agréer, Excellence, l\'expression de ma très haute considération.',
        default => 'En espérant que mes arguments retiendront votre attention, je vous prie d\'agréer, Monsieur/Madame, l\'expression de mes salutations distinguées.'
    };
@endphp

{{ $appellant_name ?? '' }}
{{ $appellant_address ?? '' }}
{{ $appellant_city ?? '' }}
@if(!empty($appellant_phone))
Téléphone : {{ $appellant_phone }}
@endif
@if(!empty($appellant_email))
Email : {{ $appellant_email }}
@endif


{{ $recipient_title ?? 'Monsieur/Madame le/la' }} {{ $recipient_name ?? '' }}
{{ $recipient_organization ?? '' }}
@if(!empty($recipient_address))
{{ $recipient_address }}
@endif
@if(!empty($recipient_city))
{{ $recipient_city }}
@endif


{{ $appellant_city ?? '' }}, le {{ \Carbon\Carbon::parse($date ?? now())->format($dateFormat) }}


Objet : {{ $subject ?? 'Recours gracieux contre décision administrative' }}

@if(!empty($reference_number))
Référence : {{ $reference_number }}
@endif


{{ $politeness_formula_opening ?? $openingFormula }}

@if($tone_level === 'VERY_OFFICIAL')
J'ai l'insigne honneur de former un recours gracieux contre la décision mentionnée ci-après, conformément aux dispositions légales en vigueur.
@elseif($tone_level === 'SIMPLE')
Je forme un recours contre la décision mentionnée ci-après.
@else
J'ai l'honneur de former un recours gracieux contre la décision mentionnée ci-après, conformément aux dispositions légales en vigueur.
@endif

RÉFÉRENCE DE LA DÉCISION CONTESTÉE :
{{ $original_decision_reference ?? '' }}

@if(!empty($original_decision_date))
Date de la décision : {{ \Carbon\Carbon::parse($original_decision_date)->format($dateFormat) }}
@endif

@if(!empty($decision_authority))
Autorité ayant pris la décision : {{ $decision_authority }}
@endif

DESCRIPTION DE LA DÉCISION :
{{ $decision_description ?? '' }}

@if(!empty($notification_date))
Date de notification : {{ \Carbon\Carbon::parse($notification_date)->format($dateFormat) }}
@endif

@if(!empty($notification_method))
Mode de notification : {{ $notification_method }}
@endif

MOTIFS DU RECOURS :
{{ $appeal_grounds ?? '' }}

@if(!empty($legal_basis))
BASE LÉGALE :
{{ $legal_basis }}
@endif

@if(!empty($factual_arguments))
ARGUMENTS FACTUELS :
{{ $factual_arguments }}
@endif

@if(!empty($procedural_irregularities))
IRRÉGULARITÉS DE PROCÉDURE :
{{ $procedural_irregularities }}
@endif

@if(!empty($new_evidence))
NOUVEAUX ÉLÉMENTS DE PREUVE :
{{ $new_evidence }}
@endif

@if(!empty($circumstances_change))
CHANGEMENT DE CIRCONSTANCES :
{{ $circumstances_change }}
@endif

RÉSULTAT DEMANDÉ :
{{ $requested_outcome ?? 'Je sollicite l\'annulation de la décision contestée.' }}

@if(!empty($alternative_solution))
SOLUTION ALTERNATIVE :
{{ $alternative_solution }}
@endif

@if(!empty($deadline_considerations))
CONSIDÉRATIONS DE DÉLAI :
{{ $deadline_considerations }}
@endif

@if(!empty($interim_measures))
MESURES PROVISOIRES DEMANDÉES :
{{ $interim_measures }}
@endif

{{ $politeness_formula_closing ?? $closingFormula }}


{{ $appellant_name ?? '' }}
@if(!empty($appellant_signature))
{{ $appellant_signature }}
@endif


Pièces jointes :
{{ $attachments ?? 'Copie de la décision contestée, Pièces justificatives, Éléments de preuve' }}