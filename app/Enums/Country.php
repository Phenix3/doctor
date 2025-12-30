<?php

namespace App\Enums;

use Spatie\Enum\Enum;

/**
 * @method static self SENEGAL()
 * @method static self IVORY_COAST()
 * @method static self BURKINA_FASO()
 * @method static self MALI()
 * @method static self NIGER()
 * @method static self GUINEA()
 * @method static self BENIN()
 * @method static self TOGO()
 * @method static self MAURITANIA()
 * @method static self CAMEROON()
 * @method static self CHAD()
 * @method static self CENTRAL_AFRICAN_REPUBLIC()
 * @method static self GABON()
 * @method static self EQUATORIAL_GUINEA()
 * @method static self REPUBLIC_OF_CONGO()
 * @method static self DEMOCRATIC_REPUBLIC_OF_CONGO()
 * @method static self MADAGASCAR()
 * @method static self COMOROS()
 * @method static self DJIBOUTI()
 * @method static self BURUNDI()
 * @method static self RWANDA()
 */
class Country extends Enum
{
    protected static function values(): array
    {
        return [
            'SENEGAL' => 'SN',
            'IVORY_COAST' => 'CI',
            'BURKINA_FASO' => 'BF',
            'MALI' => 'ML',
            'NIGER' => 'NE',
            'GUINEA' => 'GN',
            'BENIN' => 'BJ',
            'TOGO' => 'TG',
            'MAURITANIA' => 'MR',
            'CAMEROON' => 'CM',
            'CHAD' => 'TD',
            'CENTRAL_AFRICAN_REPUBLIC' => 'CF',
            'GABON' => 'GA',
            'EQUATORIAL_GUINEA' => 'GQ',
            'REPUBLIC_OF_CONGO' => 'CG',
            'DEMOCRATIC_REPUBLIC_OF_CONGO' => 'CD',
            'MADAGASCAR' => 'MG',
            'COMOROS' => 'KM',
            'DJIBOUTI' => 'DJ',
            'BURUNDI' => 'BI',
            'RWANDA' => 'RW',
        ];
    }

    protected static function labels(): array
    {
        return [
            'SENEGAL' => 'Sénégal',
            'IVORY_COAST' => 'Côte d\'Ivoire',
            'BURKINA_FASO' => 'Burkina Faso',
            'MALI' => 'Mali',
            'NIGER' => 'Niger',
            'GUINEA' => 'Guinée',
            'BENIN' => 'Bénin',
            'TOGO' => 'Togo',
            'MAURITANIA' => 'Mauritanie',
            'CAMEROON' => 'Cameroun',
            'CHAD' => 'Tchad',
            'CENTRAL_AFRICAN_REPUBLIC' => 'République Centrafricaine',
            'GABON' => 'Gabon',
            'EQUATORIAL_GUINEA' => 'Guinée Équatoriale',
            'REPUBLIC_OF_CONGO' => 'République du Congo',
            'DEMOCRATIC_REPUBLIC_OF_CONGO' => 'République Démocratique du Congo',
            'MADAGASCAR' => 'Madagascar',
            'COMOROS' => 'Comores',
            'DJIBOUTI' => 'Djibouti',
            'BURUNDI' => 'Burundi',
            'RWANDA' => 'Rwanda',
        ];
    }
}