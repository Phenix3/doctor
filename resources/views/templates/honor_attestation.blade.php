{{-- Template pour Attestation sur l'Honneur --}}
@php
    $currentDate = now()->format('d/m/Y');
@endphp

ATTESTATION SUR L'HONNEUR

Je soussigné(e), {{ $declarant_name ?? '' }}, né(e) le {{ $birth_date ?? '' }} à {{ $birth_place ?? '' }}, de nationalité {{ $nationality ?? '' }}, demeurant {{ $address ?? '' }}, titulaire de la pièce d'identité {{ $id_type ?? '' }} n° {{ $id_number ?? '' }}, délivrée le {{ $id_issue_date ?? '' }} par {{ $id_issuing_authority ?? '' }}.

ATTESTE SUR L'HONNEUR

{{ $attestation_content ?? '' }}

{{ $specific_declarations ?? '' }}

Je déclare que les informations fournies dans la présente attestation sont exactes et complètes. Je suis conscient(e) que toute fausse déclaration m'expose aux sanctions prévues par la loi.

{{ $additional_commitments ?? '' }}

Fait à {{ $declaration_city ?? '' }}, le {{ $currentDate }}

{{ $declarant_name ?? '' }}
{{ $declarant_signature ?? '' }}

Lu et approuvé