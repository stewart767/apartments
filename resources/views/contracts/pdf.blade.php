<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Lease Agreement #{{ $contract->contract_number }}</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 13px;
            line-height: 1.5;
            color: #333;
            margin: 30px;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #4f46e5;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .logo {
            font-size: 24px;
            font-weight: bold;
            color: #4f46e5;
            margin-bottom: 5px;
        }
        .title {
            font-size: 18px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #111;
        }
        .subtitle {
            font-size: 11px;
            color: #666;
            margin-top: 5px;
        }
        .section {
            margin-bottom: 25px;
        }
        .section-title {
            font-size: 13px;
            font-weight: bold;
            color: #4f46e5;
            border-bottom: 1px solid #e2e8f0;
            padding-bottom: 5px;
            margin-bottom: 12px;
            text-transform: uppercase;
        }
        .grid {
            width: 100%;
            border-collapse: collapse;
        }
        .grid td {
            padding: 6px 0;
            vertical-align: top;
        }
        .label {
            font-weight: bold;
            color: #666;
            width: 35%;
        }
        .value {
            color: #111;
        }
        .rules-list {
            padding-left: 20px;
            margin: 5px 0;
        }
        .footer {
            margin-top: 60px;
            border-top: 1px solid #e2e8f0;
            padding-top: 15px;
            font-size: 10px;
            color: #999;
            text-align: center;
        }
        .signatures {
            width: 100%;
            margin-top: 50px;
        }
        .signatures td {
            width: 50%;
            text-align: center;
            padding: 10px;
        }
        .signature-line {
            width: 80%;
            border-bottom: 1px solid #333;
            margin: 0 auto 10px auto;
            height: 40px;
        }
        .signature-name {
            font-weight: bold;
            font-size: 12px;
        }
        .signature-title {
            font-size: 10px;
            color: #666;
        }
    </style>
</head>
<body>

    <div class="header">
        <div class="logo">RelianceHomes</div>
        <div class="title">Residential Lease Agreement</div>
        <div class="subtitle">Contract Reference: {{ $contract->contract_number }} &bull; Generated: {{ date('d M, Y') }}</div>
    </div>

    <!-- 1. Parties -->
    <div class="section">
        <div class="section-title">1. Contracting Parties</div>
        <table class="grid">
            <tr>
                <td class="label">Property Owner (Lessor):</td>
                <td class="value">{{ $contract->property->owner->name }}</td>
            </tr>
            <tr>
                <td class="label">Tenant (Lessee):</td>
                <td class="value">{{ $contract->tenant->name }}</td>
            </tr>
            @if($contract->tenant->tenantDetail && $contract->tenant->tenantDetail->is_student)
                <tr>
                    <td class="label">Institution:</td>
                    <td class="value">{{ $contract->tenant->tenantDetail->institution->name }} (Reg: {{ $contract->tenant->tenantDetail->student_reg_number }})</td>
                </tr>
            @endif
        </table>
    </div>

    <!-- 2. Property & Allocation -->
    <div class="section">
        <div class="section-title">2. Accommodation Details</div>
        <table class="grid">
            <tr>
                <td class="label">Property:</td>
                <td class="value">{{ $contract->property->property_name }}</td>
            </tr>
            <tr>
                <td class="label">Physical Address:</td>
                <td class="value">{{ $contract->property->address }}</td>
            </tr>
            <tr>
                <td class="label">Allocated Unit/Bed:</td>
                <td class="value">
                    @if($contract->bed)
                        Room {{ $contract->room->room_number }} / Bed Space {{ $contract->bed->bed_number }}
                    @elseif($contract->room)
                        Room Unit {{ $contract->room->room_number }}
                    @else
                        Entire Property Unit
                    @endif
                </td>
            </tr>
        </table>
    </div>

    <!-- 3. Duration & Lease Term -->
    <div class="section">
        <div class="section-title">3. Duration & Lease Term</div>
        <table class="grid">
            <tr>
                <td class="label">Lease Commencement:</td>
                <td class="value">{{ \Carbon\Carbon::parse($contract->start_date)->format('F d, Y') }}</td>
            </tr>
            <tr>
                <td class="label">Lease Expiry:</td>
                <td class="value">{{ \Carbon\Carbon::parse($contract->end_date)->format('F d, Y') }}</td>
            </tr>
            <tr>
                <td class="label">Billing Frequency:</td>
                <td class="value capitalize">{{ $contract->billing_frequency }}</td>
            </tr>
        </table>
    </div>

    <!-- 4. Rent, Deposit & Fees -->
    <div class="section">
        <div class="section-title">4. Financial Terms</div>
        <table class="grid">
            <tr>
                <td class="label">Rent Amount:</td>
                <td class="value font-bold">TZS {{ number_format($contract->rent_amount) }} per {{ $contract->billing_frequency }}</td>
            </tr>
            <tr>
                <td class="label">Security Deposit:</td>
                <td class="value font-bold">TZS {{ number_format($contract->deposit_amount) }} (Refundable upon inspection)</td>
            </tr>
        </table>
    </div>

    <!-- 5. Rules & Conditions -->
    <div class="section" style="page-break-inside: avoid;">
        <div class="section-title">5. General Rules & Conditions</div>
        <div class="value" style="font-size: 11px;">
            {!! nl2br(e($contract->rules)) !!}
        </div>
    </div>

    <!-- 6. Legal Terms -->
    <div class="section" style="page-break-inside: avoid;">
        <div class="section-title">6. Terms & Conditions</div>
        <div class="value" style="font-size: 11px;">
            {!! nl2br(e($contract->terms_and_conditions)) !!}
        </div>
    </div>

    <!-- Signatures -->
    <table class="signatures" style="page-break-inside: avoid;">
        <tr>
            <td>
                <div class="signature-line">
                    @if($contract->owner_accepted_at)
                        <div style="font-style: italic; color: #4f46e5; padding-top: 15px;">Digitally Signed &bull; {{ $contract->owner_accepted_at->format('d/m/Y H:i') }}</div>
                    @endif
                </div>
                <div class="signature-name">{{ $contract->property->owner->name }}</div>
                <div class="signature-title">Property Owner / Lessor</div>
            </td>
            <td>
                <div class="signature-line">
                    @if($contract->tenant_accepted_at)
                        <div style="font-style: italic; color: #4f46e5; padding-top: 15px;">Digitally Signed &bull; {{ $contract->tenant_accepted_at->format('d/m/Y H:i') }}</div>
                    @endif
                </div>
                <div class="signature-name">{{ $contract->tenant->name }}</div>
                <div class="signature-title">Tenant / Lessee</div>
            </td>
        </tr>
    </table>

    <div class="footer">
        This document is generated by RelianceHomes. Digital signature and identity audit trace verified on blockchain database.
    </div>

</body>
</html>
