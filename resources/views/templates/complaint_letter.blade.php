{{-- Template pour Lettre de Plainte --}}
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
        'OFFICIAL' => 'En espérant une suite favorable à ma plainte, je vous prie d\'agréer, Monsieur/Madame, l\'expression de mes salutations distinguées.',
        'VERY_OFFICIAL' => 'En espérant une suite favorable à ma très respectueuse plainte, je vous prie d\'agréer, Excellence, l\'expression de ma très haute considération.',
        default => 'En espérant une suite favorable à ma plainte, je vous prie d\'agréer, Monsieur/Madame, l\'expression de mes salutations distinguées.'
    };
@endphp

{{ $complainant_name ?? '' }}
{{ $complainant_address ?? '' }}
{{ $complainant_city ?? '' }}
@if(!empty($complainant_phone))
Téléphone : {{ $complainant_phone }}
@endif
@if(!empty($complainant_email))
Email : {{ $complainant_email }}
@endif


{{ $recipient_title ?? 'Monsieur/Madame le/la' }} {{ $recipient_name ?? '' }}
{{ $recipient_organization ?? '' }}
@if(!empty($recipient_address))
{{ $recipient_address }}
@endif
@if(!empty($recipient_city))
{{ $recipient_city }}
@endif


{{ $complainant_city ?? '' }}, le {{ \Carbon\Carbon::parse($date ?? now())->format($dateFormat) }}


Objet : {{ $complaint_subject ?? 'Plainte formelle' }}

@if(!empty($reference_number))
Référence : {{ $reference_number }}
@endif


{{ $politeness_formula_opening ?? $openingFormula }}

@if($tone_level === 'VERY_OFFICIAL')
J'ai l'insigne honneur de porter à votre haute connaissance les faits suivants et de déposer une plainte formelle auprès de vos services.
@elseif($tone_level === 'SIMPLE')
Je porte à votre connaissance les faits suivants et dépose une plainte auprès de vos services.
@else
J'ai l'honneur de porter à votre connaissance les faits suivants et de déposer une plainte formelle auprès de vos services.
@endif

DESCRIPTION DE L'INCIDENT :
{{ $complaint_details ?? '' }}

@if(!empty($incident_date))
Date de l'incident : {{ \Carbon\Carbon::parse($incident_date)->format($dateFormat) }}
@endif

@if(!empty($incident_time))
Heure de l'incident : {{ $incident_time }}
@endif

@if(!empty($incident_location))
Lieu de l'incident : {{ $incident_location }}
@endif

@if(!empty($parties_involved))
PARTIES IMPLIQUÉES :
{{ $parties_involved }}
@endif

@if(!empty($witnesses))
TÉMOINS :
{{ $witnesses }}
@endif

@if(!empty($damages_or_prejudice))
DOMMAGES OU PRÉJUDICES SUBIS :
{{ $damages_or_prejudice }}
@endif

@if(!empty($financial_impact))
IMPACT FINANCIER :
{{ $financial_impact }}
@endif

@if(!empty($evidence_description))
ÉLÉMENTS DE PREUVE :
{{ $evidence_description }}
@endif

@if(!empty($previous_attempts))
TENTATIVES DE RÉSOLUTION ANTÉRIEURES :
{{ $previous_attempts }}
@endif

@if(!empty($urgency_justification))
CARACTÈRE D'URGENCE :
{{ $urgency_justification }}
@endif

ACTION DEMANDÉE :
{{ $desired_resolution ?? 'Je sollicite votre intervention pour résoudre cette situation.' }}

@if(!empty($compensation_requested))
RÉPARATION DEMANDÉE :
{{ $compensation_requested }}
@endif

@if(!empty($deadline_expectation))
DÉLAI SOUHAITÉ POUR LA RÉPONSE :
{{ $deadline_expectation }}
@endif

{{ $politeness_formula_closing ?? $closingFormula }}


{{ $complainant_name ?? '' }}
@if(!empty($complainant_signature))
{{ $complainant_signature }}
@endif


Pièces jointes :
{{ $attachments ?? 'Preuves, Témoignages, Documents justificatifs, Photos' }}