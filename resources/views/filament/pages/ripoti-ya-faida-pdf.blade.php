<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Ripoti ya Faida</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; }
        .header { text-align: center; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        th, td { border: 1px solid #ccc; padding: 5px; text-align: left; }
        .section-title { font-size: 14px; font-weight: bold; margin-top: 20px; border-bottom: 1px solid #000; padding-bottom: 2px;}
        .shop-header { margin-top: 15px; page-break-inside: avoid; }
        .shop-title { font-size: 12px; font-weight: bold; background-color: #f2f2f2; padding: 5px; }
        .shop-content table { font-size: 9px; }
        .shop-image { float: left; width: 100px; margin-right: 10px; }
    </style>
</head>
<body>

    <div class="header">
        <h2>Ripoti ya Faida na Utendaji</h2>
        <p>Kuanzia: {{ \Carbon\Carbon::parse($startDate)->format('d M Y') }} hadi {{ \Carbon\Carbon::parse($endDate)->format('d M Y') }}</p>
    </div>

    <div class="section-title">Muhtasari Mkuu</div>
    <table>
        <tr>
            <td>Jumla ya Kamisheni (Kipindi Hiki)</td>
            <td style="text-align:right;">{{ number_format($totalCommissionInPeriod, 2) }} TZS</td>
        </tr>
    </table>

    <div class="section-title">Uchambuzi kwa Kila Duka</div>

    @foreach ($shopReportData as $shopData)
        <div class="shop-header">
            <div class="shop-title">Duka: {{ $shopData['name'] }}</div>
        </div>
        <table>
            <tr>
                <td style="width: 120px; border: none;">
                     @if($shopData['image_path'] && file_exists(storage_path('app/public/' . $shopData['image_path'])))
                        <img src="{{ storage_path('app/public/' . $shopData['image_path']) }}" style="width:100px; height:auto;">
                     @else
                        <div style="width:100px; height: 75px; border: 1px solid #ccc; text-align:center; padding-top: 30px;">Picha</div>
                     @endif
                </td>
                <td style="border: none; vertical-align: top;">
                    <table>
                        <tr>
                            <td style="font-weight: bold;">Faida ya Duka (Kamisheni)</td>
                            <td style="text-align:right; font-weight: bold;">{{ number_format($shopData['net_profit_period'], 2) }} TZS</td>
                        </tr>
                        @foreach ($shopData['mno_data'] as $mnoKey => $mnoDetails)
                            <tr>
                                <td>- Kamisheni ({{ ucfirst($mnoKey) }})</td>
                                <td style="text-align:right;">+{{ number_format($mnoDetails['commission'], 2) }} TZS</td>
                            </tr>
                        @endforeach
                    </table>
                </td>
            </tr>
        </table>
    @endforeach
</body>
</html>
