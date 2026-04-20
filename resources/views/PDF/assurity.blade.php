<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <style>
            @page { margin: 26px 28px; }
            body { font-family: Arial, sans-serif; font-size: 12px; color: #111; line-height: 1.35; }
            h1 { font-size: 16px; margin: 0 0 10px 0; text-align: center; }
            .meta { font-size: 11px; margin: 10px 0 14px; }
            .meta .row { margin: 2px 0; }
            .label { font-weight: bold; }
            .block { margin-top: 10px; }
            ol { margin: 6px 0 0 18px; padding: 0; }
            li { margin: 6px 0; }
            .underline { border-bottom: 1px solid #000; display: inline-block; min-width: 180px; }
            .sign { margin-top: 22px; }
            .sign-row { margin-top: 10px; }
            .signature img { width: 200px; height: auto; }
            .small { font-size: 10px; color: #444; }
            .consent { margin-top: 14px; padding-top: 10px; border-top: 1px solid #ddd; }
        </style>
    </head>
    <body>
        @php
            $suretyName = trim((string) (($student->sponFirstName ?? '') . ' ' . ($student->sponLastName ?? '')));
            $suretyId = trim((string) ($student->sponId ?? ''));

            $debtorName = trim((string) (($student->userFirstName ?? '') . ' ' . ($student->userLastname ?? '')));
            $debtorId = trim((string) ($student->userId ?? ($student->userPassport ?? '')));

            $creditorName = trim((string) (config('legal.assurity_creditor_name') ?: config('app.name')));
            $creditorReg = trim((string) (config('legal.assurity_creditor_reg') ?: ''));

            $sponsorAddressParts = array_filter([
                trim((string) ($student->pAddress1 ?? '')),
                trim((string) ($student->pAddress2 ?? '')),
                trim((string) ($student->pAddressCity ?? '')),
                trim((string) ($student->pAddressPostal ?? '')),
            ]);
            $studentAddressParts = array_filter([
                trim((string) ($student->userAddress1 ?? '')),
                trim((string) ($student->userAddress2 ?? '')),
                trim((string) ($student->userAddressCity ?? '')),
                trim((string) ($student->userAddressPostal ?? '')),
            ]);
            $domiciliumAddress = trim(implode(', ', !empty($sponsorAddressParts) ? $sponsorAddressParts : $studentAddressParts));

            $fallbackLine = '______________________________';
        @endphp

        <h1>DEED OF SURETYSHIP</h1>

        <div class="meta">
            <div class="row"><span class="label">Student:</span> {{ $debtorName ?: $fallbackLine }}</div>
            <div class="row"><span class="label">Application #:</span> {{ $booking->booking_code ?? '' }}</div>
            <div class="row"><span class="label">Residence:</span> {{ $residence->residence ?? '' }}</div>
        </div>

        <div class="block">
            I/We, the undersigned
            <span class="underline">{{ $suretyName ?: $fallbackLine }}</span>
            (Identity No:
            <span class="underline">{{ $suretyId ?: $fallbackLine }}</span>)
            hereby bind myself/ourselves jointly and severally to
            <span class="underline">{{ $creditorName ?: $fallbackLine }}</span>
            @if ($creditorReg !== '')
                (Reg No. {{ $creditorReg }})
            @endif
            and their successors in title and assigns ("the creditor") as surety for and co-principal debtor with
            <span class="underline">{{ $debtorName ?: $fallbackLine }}</span>
            (Identity/Passport No:
            <span class="underline">{{ $debtorId ?: $fallbackLine }}</span>)
            ("the debtor") for the due and punctual payment and performance by the debtor of all debts and obligations which may now be owing and which may hereafter be owing or which may arise, in terms of or in respect of an agreement of lease entered into or about to be entered into between the creditor and the debtor (which debts and obligations are hereinafter collectively referred to as "the obligations" and which lease is hereinafter referred to as "the lease"). As part of my/our liability in terms hereof I/we bind myself/ourselves as aforesaid to pay the amount of all charges and expenses of whatsoever nature, including, but without derogating from the generality of the aforegoing, attorney and client legal costs and collection commission incurred by the creditor in securing or endeavouring to secure fulfilment of the obligations.
        </div>

        <ol>
            <li>My/our liability and the rights of the creditor hereunder shall in no way be affected or diminished if the creditor, either now or in the future, obtains additional suretyship, guarantees, securities or indemnities, whether real or personal, in connection with or in respect of the obligations.</li>
            <li>This deed of suretyship shall be a continuing covering suretyship and shall remain in full force and effect for as long as there are any amounts owing or payable by the debtor to the creditor in terms of the lease or for as long as there are any other obligations owing or which might become owing by the debtor to the creditor in terms of the lease. This deed of suretyship shall accordingly remain in full force and effect notwithstanding that there might be fluctuations in the obligations and notwithstanding that for a certain period there may be no obligations owing by the debtor to the creditor in terms of the lease.</li>
            <li>The creditor shall be entitled, without reference or notification to me/us, and without affecting its rights hereunder, and without releasing me/us hereunder, to take whatever steps it deems fit against the debtor, to release other securities and/or sureties and/or grant the debtor extensions of time for payment and/or to compound or to make any other arrangements with the debtor for the discharge of the debtor's indebtedness.</li>
            <li>Any leniency or extensions of time which may be granted to the debtor or a variation or alteration of the lease shall not be construed as a waiver of any of the creditor's rights or claims against me/us hereunder or as a novation of any claim and shall in no way release us from my/our liability hereunder.</li>
            <li>If the debtor shall be placed in liquidation or under judicial management (whether provisionally, finally, compulsorily or voluntarily), or under sequestration, or shall submit an offer of compromise or of composition or scheme of arrangement in terms of any company or insolvency law, the creditor shall be entitled to accept any dividend on account and in reduction of the debtor's indebtedness without prejudice to its rights against me/us, which rights shall further not be prejudiced by its acceptance of any other securities, guarantees or suretyship arising out of any such event.</li>
            <li>I/We shall be bound by any admissions or acknowledgements of indebtedness made or given at any time by the debtor to the creditor in regard to any cause and/or obligation arising from the lease until such time as all obligations arising in terms of the lease are discharged in full by the debtor.</li>
            <li>
                A certificate under the hand of any director of the creditor as to:
                <ol type="a">
                    <li>the existence and amount of the indebtedness ("the debt") of the debtor to the creditor and/or of myself/ourselves at any time;</li>
                    <li>the fact that the due date for payment of the debt and/or for the discharge of the obligations and/or my/our obligations hereunder has arrived;</li>
                    <li>the amount of interest accrued on the debt;</li>
                    <li>the rate of interest applicable to the debt;</li>
                    <li>any other fact, matter or thing relating to the debt and/or the obligations and/or my/our obligations hereunder;</li>
                </ol>
                shall for the purpose of provisional sentence or summary judgement or any other proceedings against me/us in any competent court be prima facie proof of the correctness of the matters stated therein, deemed to be sufficient particularity for the purposes of pleading or trial, and valid as a liquid document for those purposes.
            </li>
            <li>As collateral security for the discharge of the obligations assumed by me/us in terms hereof, I/we hereby cede, assign, transfer and make over unto and in favour of the creditor all my right, title and interest in and to any amounts which now are or may hereafter become owing to me/us by the debtor from any cause of indebtedness whatsoever, including any reversionary right or interest which I might acquire after termination of any prior cession, assignment or transfer.</li>
            <li>I/we hereby renounce the benefits of the legal exceptions "non causa debiti", "errore calculi", "excussion", "division", "de duobus vel pluribus reis debendi", "no value received" and "revision of accounts" with the meaning, force and effect of all which exceptions I/we declare myself/ourselves to be fully acquainted.</li>
            <li>I/we hereby agree and consent that the creditor shall, at its option, be entitled to institute any legal proceedings which may arise out of or in connection with this deed of suretyship in any magistrate's court having jurisdiction notwithstanding the fact that the claim or value of the matter in dispute might exceed the jurisdiction of such magistrate's court.</li>
            <li>
                <span class="label">Domicilium</span>
                <ol type="a">
                    <li>I/we hereby choose domicilium citandi et executandi at the address/addresses set out below for all purposes relating to this deed of suretyship.</li>
                    <li>All notices addressed to me/us and despatched by prepaid registered post shall be deemed (unless the contrary is proved) to have reached me/us seven days after the date of posting of such notice.</li>
                    <li>The said address/addresses may be changed by written notice delivered to the creditor to another address/addresses in the Republic of South Africa which is not a post office box or poste restante.</li>
                    <li>The address/addresses referred to above is/are (please insert full address but not a PO Box number): <span class="underline" style="min-width: 520px;">{{ $domiciliumAddress !== '' ? $domiciliumAddress : $fallbackLine }}</span></li>
                </ol>
            </li>
            <li>My/our liability hereunder shall in no way be affected or diminished if one or more of my/our co-sureties fails and/or refuses to sign this deed of suretyship.</li>
            <li>I/we acknowledge by my/our signature to this deed of suretyship that it was fully complete in every respect at the time it was signed by me/us.</li>
        </ol>

        <div class="sign">
            <div class="sign-row">
                Signed at <span class="underline">{{ !empty($signed_at) ? $signed_at : $fallbackLine }}</span>
                on <span class="underline">{{ !empty($signed_on) ? $signed_on : $fallbackLine }}</span>
            </div>

            <div class="block small">
                NOTE: If the surety is married in community of property, his/her spouse must consent to the suretyship in terms of Section 15 of the Matrimonial Property Act No 88 of 1984. If not applicable, please state reason here (for example: married by A.N.C. or unmarried etc.).
            </div>

            <div class="consent">
                @if (!empty($spouse_required))
                    <div class="block">
                        <span class="label">SPOUSE CONSENT</span><br>
                        I, the undersigned spouse, do hereby consent to the surety granting the suretyship hereinbefore contained.
                    </div>
                @else
                    <div class="block">
                        <span class="label">CONSENT NOT APPLICABLE - REASON</span><br>
                        <span class="underline" style="min-width: 420px;">{{ !empty($spouse_reason) ? $spouse_reason : $fallbackLine }}</span>
                    </div>
                @endif
            </div>

            @php
                // Prefer a direct filesystem path if the controller provided one (more reliable for PDF rendering),
                // otherwise fall back to a public-relative path.
                $signatureSrc = null;
                if (!empty($signature_path)) {
                    $signatureSrc = $signature_path;
                } elseif (!empty($signature)) {
                    $signatureSrc = public_path($signature);
                }
            @endphp

            @if (!empty($signatureSrc))
                <div class="signature sign-row">
                    <div class="label">Surety Signature:</div>
                    <img src="{{ $signatureSrc }}" alt="Signature">
                </div>
            @endif

            @php
                $spouseSignatureSrc = null;
                if (!empty($spouse_signature_path)) {
                    $spouseSignatureSrc = $spouse_signature_path;
                } elseif (!empty($spouse_signature)) {
                    $spouseSignatureSrc = public_path($spouse_signature);
                }
            @endphp

            @if (!empty($spouseSignatureSrc))
                <div class="signature sign-row">
                    <div class="label">Spouse Signature:</div>
                    <img src="{{ $spouseSignatureSrc }}" alt="Spouse Signature">
                </div>
            @endif

            @if (!empty($signed_at))
                <div class="sign-row">
                    <span class="label">Signed at:</span> {{ $signed_at }}
                </div>
            @endif
        </div>
    </body>
</html>
