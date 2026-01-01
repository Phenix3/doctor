{{-- Template pour Attestation sur l'Honneur --}}
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
    
    // Country-specific legal references
    $legalReference = match($country) {
        'CI' => 'les dispositions du Code pénal ivoirien relatives au faux et usage de faux',
        'SN' => 'les dispositions du Code pénal sénégalais relatives au faux et usage de faux',
        'ML' => 'les dispositions du Code pénal malien relatives au faux et usage de faux',
        'BF' => 'les dispositions du Code pénal burkinabé relatives au faux et usage de faux',
        default => 'les articles du Code pénal relatifs au faux et usage de faux'
    };
@endphp

ATTESTATION SUR L'HONNEUR

@if(!empty($attestation_title))
{{ strtoupper($attestation_title) }}
@endif


Je soussigné(e), {{ $declarant_name ?? '' }}, né(e) le {{ \Carbon\Carbon::parse($birth_date ?? now())->format($dateFormat) }} à {{ $birth_place ?? '' }}, de nationalité {{ $nationality ?? '' }}, demeurant {{ $declarant_address ?? '' }}, titulaire de la pièce d'identité {{ $identity_document_type ?? '' }} n° {{ $identity_document_number ?? '' }}@if(!empty($id_issue_date)), délivrée le {{ \Carbon\Carbon::parse($id_issue_date)->format($dateFormat) }}@endif@if(!empty($id_issuing_authority)) par {{ $id_issuing_authority }}@endif.

@if(!empty($profession))
Profession : {{ $profession }}
@endif

@if(!empty($declarant_phone))
Téléphone : {{ $declarant_phone }}
@endif

@if(!empty($declarant_email))
Email : {{ $declarant_email }}
@endif


ATTESTE SUR L'HONNEUR


{{ $declaration_content ?? '' }}

@if(!empty($specific_declarations))
DÉCLARATIONS SPÉCIFIQUES :
{{ $specific_declarations }}
@endif

@if(!empty($factual_statements))
ÉLÉMENTS FACTUELS :
{{ $factual_statements }}
@endif

@if(!empty($period_concerned))
PÉRIODE CONCERNÉE :
{{ $period_concerned }}
@endif

@if(!empty($witnesses))
TÉMOINS :
{{ $witnesses }}
@endif

Je déclare que les informations fournies dans la présente attestation sont exactes et complètes. Je suis conscient(e) que toute fausse déclaration m'expose aux sanctions prévues par {{ $legalReference }}.

@if(!empty($additional_commitments))
ENGAGEMENTS ADDITIONNELS :
{{ $additional_commitments }}
@endif

@if(!empty($legal_consequences))
CONSÉQUENCES JURIDIQUES :
Je reconnais être informé(e) des conséquences juridiques de cette déclaration.
@endif


Fait à {{ $declaration_city ?? '' }}, le {{ \Carbon\Carbon::parse($date ?? now())->format($dateFormat) }}

En foi de quoi, j'ai établi la présente attestation pour servir et valoir ce que de droit.


{{ $declarant_name ?? '' }}
@if(!empty($declarant_signature))
{{ $declarant_signature }}
@endif


Lu et approuvé

@if(!empty($witness_signature))
Témoin :
{{ $witness_signature }}
@endif