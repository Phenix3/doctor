<?php

namespace App\Enums;

use Spatie\Enum\Enum;

/**
 * @method static self ADMINISTRATIVE_APPLICATION()
 * @method static self MOTIVATION_LETTER()
 * @method static self INTERNSHIP_REQUEST()
 * @method static self HONOR_ATTESTATION()
 * @method static self COMPLAINT_LETTER()
 * @method static self ADMINISTRATIVE_ACT_REQUEST()
 * @method static self APPEAL_OR_POSTPONEMENT_LETTER()
 * @method static self GENERIC_ADMINISTRATIVE_CORRESPONDENCE()
 */
class DocumentType extends Enum
{
    protected static function values(): array
    {
        return [
            'ADMINISTRATIVE_APPLICATION' => 'administrative_application',
            'MOTIVATION_LETTER' => 'motivation_letter',
            'INTERNSHIP_REQUEST' => 'internship_request',
            'HONOR_ATTESTATION' => 'honor_attestation',
            'COMPLAINT_LETTER' => 'complaint_letter',
            'ADMINISTRATIVE_ACT_REQUEST' => 'administrative_act_request',
            'APPEAL_OR_POSTPONEMENT_LETTER' => 'appeal_or_postponement_letter',
            'GENERIC_ADMINISTRATIVE_CORRESPONDENCE' => 'generic_administrative_correspondence',
        ];
    }

    protected static function labels(): array
    {
        return [
            'ADMINISTRATIVE_APPLICATION' => 'Demande Administrative',
            'MOTIVATION_LETTER' => 'Lettre de Motivation',
            'INTERNSHIP_REQUEST' => 'Demande de Stage',
            'HONOR_ATTESTATION' => 'Attestation sur l\'Honneur',
            'COMPLAINT_LETTER' => 'Lettre de Plainte',
            'ADMINISTRATIVE_ACT_REQUEST' => 'Demande d\'Acte Administratif',
            'APPEAL_OR_POSTPONEMENT_LETTER' => 'Lettre de Recours ou de Report',
            'GENERIC_ADMINISTRATIVE_CORRESPONDENCE' => 'Courrier Administratif Générique',
        ];
    }
}