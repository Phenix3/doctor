{{-- Template pour Demande de Stage --}}
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
        'OFFICIAL' => 'En espérant que ma demande retiendra votre attention, je vous prie d\'agréer, Monsieur/Madame, l\'expression de mes salutations respectueuses.',
        'VERY_OFFICIAL' => 'En espérant que ma très respectueuse demande retiendra votre haute attention, je vous prie d\'agréer, Excellence, l\'expression de ma très haute considération.',
        default => 'En espérant que ma demande retiendra votre attention, je vous prie d\'agréer, Monsieur/Madame, l\'expression de mes salutations respectueuses.'
    };
@endphp

{{ $student_name ?? '' }}
{{ $student_address ?? '' }}
{{ $student_city ?? '' }}
@if(!empty($student_phone))
Téléphone : {{ $student_phone }}
@endif
@if(!empty($student_email))
Email : {{ $student_email }}
@endif


{{ $recipient_title ?? 'Monsieur/Madame le/la' }} {{ $recipient_name ?? '' }}
{{ $recipient_organization ?? '' }}
@if(!empty($recipient_address))
{{ $recipient_address }}
@endif
@if(!empty($recipient_city))
{{ $recipient_city }}
@endif


{{ $student_city ?? '' }}, le {{ \Carbon\Carbon::parse($date ?? now())->format($dateFormat) }}


Objet : {{ $subject ?? 'Demande de stage académique' }}

@if(!empty($reference_number))
Référence : {{ $reference_number }}
@endif


{{ $politeness_formula_opening ?? $openingFormula }}

@if($tone_level === 'VERY_OFFICIAL')
J'ai l'insigne honneur de solliciter de votre haute bienveillance l'octroi d'un stage au sein de votre prestigieuse organisation.
@elseif($tone_level === 'SIMPLE')
Je me permets de vous adresser une demande de stage au sein de votre organisation.
@else
J'ai l'honneur de solliciter de votre haute bienveillance l'octroi d'un stage au sein de votre organisation.
@endif

STATUT ÉTUDIANT :
{{ $student_status ?? 'Étudiant' }} à {{ $institution_name ?? '' }}

PROGRAMME ACADÉMIQUE :
{{ $study_program ?? '' }}
@if(!empty($academic_level))
Niveau : {{ $academic_level }}
@endif

DURÉE ET PÉRIODE DU STAGE :
Durée souhaitée : {{ $internship_duration ?? '' }}
Période : {{ $internship_period ?? '' }}

OBJECTIFS PÉDAGOGIQUES :
{{ $internship_objectives ?? 'Ce stage s\'inscrit dans le cadre de ma formation et vise à acquérir une expérience pratique dans le domaine.' }}

@if(!empty($organization_interest))
INTÉRÊT POUR VOTRE ORGANISATION :
{{ $organization_interest }}
@endif

@if(!empty($skills_contribution))
MA CONTRIBUTION POTENTIELLE :
{{ $skills_contribution }}
@endif

@if(!empty($supervision_requirements))
BESOINS D'ENCADREMENT :
{{ $supervision_requirements }}
@endif

@if(!empty($academic_supervisor))
ENCADREMENT ACADÉMIQUE :
Superviseur académique : {{ $academic_supervisor }}
@if(!empty($supervisor_contact))
Contact : {{ $supervisor_contact }}
@endif
@endif

@if(!empty($evaluation_requirements))
MODALITÉS D'ÉVALUATION :
{{ $evaluation_requirements }}
@endif

{{ $politeness_formula_closing ?? $closingFormula }}


{{ $student_name ?? '' }}
@if(!empty($student_signature))
{{ $student_signature }}
@endif


Pièces jointes :
{{ $attachments ?? 'CV, Lettre de recommandation, Attestation d\'inscription, Convention de stage' }}