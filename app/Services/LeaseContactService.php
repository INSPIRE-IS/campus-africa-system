<?php

namespace App\Services;

class LeaseContactService
{
    public static function resolve($data): array
    {
        $placeholder = '________________________';

        $kinFirst = $data->kinFirstName ?? '';
        $kinLast = $data->kinLastName ?? '';
        $kinName = trim($kinFirst . ' ' . $kinLast);

        $sponFirst = $data->sponFirstName ?? '';
        $sponLast = $data->sponLastName ?? '';
        $sponName = trim($sponFirst . ' ' . $sponLast);

        $displayKinName = $kinName !== '' ? $kinName : $sponName;
        $displayKinEmail = $data->kinEmail ?: ($data->sponEmail ?? ($data->userEmail ?? ''));
        $displayKinCellCode = $data->kinCellCode ?: ($data->sponCellCode ?? '');
        $displayKinCellNo = $data->kinCellNo ?: ($data->sponCell ?? '');
        $displayKinCell = trim(($displayKinCellCode !== '' ? $displayKinCellCode . ' ' : '') . ($displayKinCellNo ?? ''));

        $ackName = $sponName !== '' ? $sponName : $kinName;
        $ackId = $data->sponId ?: (($data->kinId ?? '') ?: ($data->kinPassportNo ?? ''));

        return [
            'kin_name' => $displayKinName !== '' ? $displayKinName : $placeholder,
            'kin_email' => $displayKinEmail !== '' ? $displayKinEmail : $placeholder,
            'kin_cell' => $displayKinCell !== '' ? $displayKinCell : $placeholder,
            'ack_name' => $ackName !== '' ? $ackName : $placeholder,
            'ack_id' => $ackId !== '' ? $ackId : $placeholder,
        ];
    }

    public static function missingFields($data): array
    {
        $missing = [];

        $appParentKin = ((string) ($data->app_parent_kin ?? '')) === '1';
        $appAddress = ((string) ($data->app_address ?? '')) === '1';

        if (trim($data->kinTitle ?? '') === '') {
            $missing[] = 'Next of Kin Title';
        }
        if (trim($data->kinRelationship ?? '') === '') {
            $missing[] = 'Next of Kin Relationship';
        }

        if (!$appParentKin) {
            if (trim($data->kinFirstName ?? '') === '') {
                $missing[] = 'Next of Kin First Name';
            }
            if (trim($data->kinLastName ?? '') === '') {
                $missing[] = 'Next of Kin Last Name';
            }
            if (trim($data->kinCellNo ?? '') === '') {
                $missing[] = 'Next of Kin Cell';
            }
            if (!$appAddress) {
                if (trim($data->kinAddressStr ?? '') === '') {
                    $missing[] = 'Next of Kin Address';
                }
                if (trim($data->kinAddressSuburb ?? '') === '') {
                    $missing[] = 'Next of Kin Suburb';
                }
                if (trim($data->kinAddressCity ?? '') === '') {
                    $missing[] = 'Next of Kin City';
                }
                if (trim($data->kinAddressPost ?? '') === '') {
                    $missing[] = 'Next of Kin Postal Code';
                }
            }
        }

        return $missing;
    }
}
