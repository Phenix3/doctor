{{-- Template pour Lettre de Motivation --}}
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
        'VERY_OFFICIAL' => 'Excellence, Monsieur/Madame le/la ' . ($recipient_title ?? 'Directeur'),
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


Objet : {{ $subject ?? 'Candidature pour le poste de' }} {{ $position_title ?? '' }}

@if(!empty($reference_number))
Référence : {{ $reference_number }}
@endif


{{ $politeness_formula_opening ?? $openingFormula }}

@if($tone_level === 'VERY_OFFICIAL')
{{ $introduction ?? 'J\'ai l\'insigne honneur de vous adresser ma candidature pour le poste mentionné en objet.' }}
@elseif($tone_level === 'SIMPLE')
{{ $introduction ?? 'Je vous adresse ma candidature pour le poste mentionné en objet.' }}
@else
{{ $introduction ?? 'J\'ai l\'honneur de vous adresser ma candidature pour le poste mentionné en objet.' }}
@endif

PARCOURS PROFESSIONNEL :
@if(!empty($professional_experience))
{{ $professional_experience }}
@else
Mon expérience professionnelle me permet d'apporter une contribution significative à votre organisation.
@endif

COMPÉTENCES ET QUALIFICATIONS :
@if(!empty($skills_and_qualifications))
{{ $skills_and_qualifications }}
@else
Mes compétences techniques et relationnelles sont adaptées aux exigences du poste.
@endif

MOTIVATION POUR LE POSTE :
{{ $motivation_content ?? 'Ma motivation pour ce poste repose sur mon intérêt pour vos activités et ma volonté de contribuer à vos objectifs.' }}

@if(!empty($contribution_expectations))
MA CONTRIBUTION À VOTRE ORGANISATION :
{{ $contribution_expectations }}
@endif

@if(!empty($career_objectives))
OBJECTIFS DE CARRIÈRE :
{{ $career_objectives }}
@endif

@if(!empty($availability))
DISPONIBILITÉ :
{{ $availability }}
@endif

@if(!empty($salary_expectations))
PRÉTENTIONS SALARIALES :
{{ $salary_expectations }}
@endif

@if($tone_level === 'VERY_OFFICIAL')
Dans l'attente d'une réponse favorable, {{ $politeness_formula_closing ?? $closingFormula }}
@elseif($tone_level === 'SIMPLE')
En espérant une réponse positive, {{ $politeness_formula_closing ?? $closingFormula }}
@else
Dans l'attente d'une réponse favorable, {{ $politeness_formula_closing ?? $closingFormula }}
@endif


{{ $applicant_name ?? '' }}
@if(!empty($applicant_signature))
{{ $applicant_signature }}
@endif


Pièces jointes :
{{ $attachments ?? 'CV, Diplômes, Certificats de travail' }}