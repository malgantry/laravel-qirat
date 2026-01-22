@php
    use App\Helpers\ArabicShaper;
@endphp
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>{{ ArabicShaper::shape(__('Financial Report')) }}</title>
    <style>
        @page {
            margin: 40px;
        }
        body {
            font-family: 'DejaVu Sans', sans-serif;
            color: #334155;
            line-height: 1.5;
            font-size: 11px;
            margin: 0;
            padding: 0;
            background-color: #fff;
        }
        .header-container {
            border-bottom: 2px solid #e2e8f0;
            padding-bottom: 20px;
            margin-bottom: 30px;
            position: relative;
        }
        .brand {
            font-size: 24px;
            font-weight: bold;
            color: #0f172a;
            text-transform: uppercase;
            letter-spacing: 2px;
        }
        .brand span {
            color: #d4af37; /* Gold Accent */
        }
        .report-title {
            font-size: 14px;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-top: 5px;
        }
        .meta-info {
            position: absolute;
            top: 0;
            left: 0; /* RTL renders this on the left visually */
            text-align: left;
            font-size: 10px;
            color: #94a3b8;
        }
        
        /* Summary Cards */
        .summary-grid {
            display: table;
            width: 100%;
            margin-bottom: 40px;
            border-collapse: separate;
            border-spacing: 10px;
        }
        .summary-card {
            display: table-cell;
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 15px;
            width: 23%;
            vertical-align: middle;
        }
        .summary-label {
            font-size: 10px;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 8px;
        }
        .summary-value {
            font-size: 16px;
            font-weight: bold;
            color: #0f172a;
            direction: ltr; /* Numbers LTR */
            font-family: sans-serif;
        }
        
        /* Section styling */
        .section-header {
            font-size: 14px;
            font-weight: bold;
            color: #0f172a;
            margin-bottom: 15px;
            border-bottom: 1px solid #cbd5e1;
            padding-bottom: 8px;
            text-transform: uppercase;
        }
        
        /* Tables */
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10px;
            margin-bottom: 30px;
        }
        th {
            text-align: right;
            padding: 10px;
            background-color: #f1f5f9;
            color: #475569;
            font-weight: bold;
            border-bottom: 1px solid #e2e8f0;
            text-transform: uppercase;
        }
        td {
            padding: 10px;
            border-bottom: 1px solid #f1f5f9;
            color: #334155;
        }
        tr:last-child td {
            border-bottom: none;
        }
        .amount-col {
            font-family: sans-serif;
            font-weight: 600;
            text-align: left;
            direction: ltr;
        }
        
        /* Charts */
        .charts-wrapper {
            width: 100%;
            margin-bottom: 30px;
            text-align: center;
        }
        .chart-box {
            display: inline-block;
            width: 48%;
            margin: 0 1%;
            vertical-align: top;
            text-align: center;
        }
        .chart-img {
            max-width: 100%;
            height: auto;
            border: 1px solid #f1f5f9;
            border-radius: 4px;
            padding: 5px;
        }
        .chart-label {
            margin-bottom: 10px; 
            font-size: 10px; 
            font-weight: bold; 
            color: #64748b;
        }

        /* Insights */
        .insight {
            background-color: #fff;
            border-left: 3px solid #d4af37;
            padding: 10px 15px;
            margin-bottom: 10px;
            background-color: #fffbf0; /* Very light gold tint */
        }
        .insight-title {
            font-weight: bold;
            color: #854d0e;
            font-size: 11px;
            margin-bottom: 4px;
        }
        .insight-body {
            color: #78350f;
            font-size: 10px;
        }

        .footer {
            position: fixed;
            bottom: -30px;
            left: 0; right: 0;
            height: 20px;
            border-top: 1px solid #e2e8f0;
            padding-top: 10px;
            text-align: center;
            color: #cbd5e1;
            font-size: 8px;
        }
        
        /* Utils */
        .page-break { page-break-after: always; }
        .text-green { color: #059669; }
        .text-red { color: #dc2626; }
    </style>
</head>
<body>
    <div class="footer">
        QIRAT FINANCIAL INTELLIGENCE | {{ date('Y') }}
    </div>

    <!-- Header -->
    <div class="header-container">
        <div class="brand">QIRAT<span>AI</span></div>
        <div class="report-title">{{ ArabicShaper::shape(__('Financial Performance Report')) }}</div>
        <div class="meta-info">
            {{ __('Generated') }}: {{ date('Y-m-d') }}<br>
            {{ __('Period') }}: {{ $start }} - {{ $end }}
        </div>
    </div>

    <!-- Summary Stats -->
    <div class="summary-grid">
        <div class="summary-card">
            <div class="summary-label">{{ ArabicShaper::shape(__('Total Income')) }}</div>
            <div class="summary-value text-green">{{ number_format($totalIncome, 2) }}</div>
        </div>
        <div class="summary-card">
            <div class="summary-label">{{ ArabicShaper::shape(__('Total Expense')) }}</div>
            <div class="summary-value text-red">{{ number_format($totalExpense, 2) }}</div>
        </div>
        <div class="summary-card">
            <div class="summary-label">{{ ArabicShaper::shape(__('Net Balance')) }}</div>
            <div class="summary-value" style="color: {{ $net >= 0 ? '#059669' : '#dc2626' }}">
                {{ number_format($net, 2) }}
            </div>
        </div>
        <div class="summary-card">
            <div class="summary-label">{{ ArabicShaper::shape(__('Savings Rate')) }}</div>
            <div class="summary-value" style="color: {{ $savingsRate > 20 ? '#d4af37' : '#334155' }}">
                {{ number_format($savingsRate, 1) }}%
            </div>
        </div>
    </div>

    <!-- Charts -->
    @if(isset($chartCategory) || isset($chartComparison))
    <div class="section-header">{{ ArabicShaper::shape(__('Visual Analysis')) }}</div>
    <div class="charts-wrapper">
        @if(isset($chartComparison))
        <div class="chart-box">
            <div class="chart-label">{{ ArabicShaper::shape(__('Income vs Expense')) }}</div>
            <img src="{{ $chartComparison }}" class="chart-img">
        </div>
        @endif
        @if(isset($chartCategory))
        <div class="chart-box">
            <div class="chart-label">{{ ArabicShaper::shape(__('Expense Distribution')) }}</div>
            <img src="{{ $chartCategory }}" class="chart-img">
        </div>
        @endif
    </div>
    @endif

    <!-- Insights -->
    @if(!empty($aiInsights))
    <div class="section-header">{{ ArabicShaper::shape(__('Strategic Insights')) }}</div>
    <div style="margin-bottom: 30px;">
        @foreach($aiInsights as $insight)
            <div class="insight">
                <div class="insight-title">{{ ArabicShaper::shape($insight['title']) }}</div>
                <div class="insight-body">{{ ArabicShaper::shape($insight['message']) }}</div>
            </div>
        @endforeach
    </div>
    @endif

    <!-- Expense Table -->
    <div class="section-header">{{ ArabicShaper::shape(__('Detailed Expenses')) }}</div>
    <table>
        <thead>
            <tr>
                <th>{{ ArabicShaper::shape(__('Category')) }}</th>
                <th style="text-align:left">{{ ArabicShaper::shape(__('Total Amount')) }}</th>
                <th style="text-align:left">{{ ArabicShaper::shape(__('Share')) }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse($categoryBreakdown as $cat)
                <tr>
                    <td>{{ ArabicShaper::shape($cat->category_name ?? __('Uncategorized')) }}</td>
                    <td class="amount-col">{{ number_format($cat->total, 2) }}</td>
                    <td class="amount-col">{{ $totalExpense > 0 ? number_format(($cat->total / $totalExpense) * 100, 1) : 0 }}%</td>
                </tr>
            @empty
                <tr>
                    <td colspan="3" style="text-align:center; color:#94a3b8; padding:20px;">
                        {{ ArabicShaper::shape(__('No records found.')) }}
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="page-break"></div>

    <!-- Budgets -->
    <div class="section-header">{{ ArabicShaper::shape(__('Budget Controls')) }}</div>
    <table>
        <thead>
            <tr>
                <th>{{ ArabicShaper::shape(__('Budget Category')) }}</th>
                <th style="text-align:left">{{ ArabicShaper::shape(__('Limit')) }}</th>
                <th style="text-align:left">{{ ArabicShaper::shape(__('Spent')) }}</th>
                <th style="text-align:center">{{ ArabicShaper::shape(__('Status')) }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse($budgets as $b)
                <tr>
                    <td>{{ ArabicShaper::shape($b['categoryName'] ?? __('General')) }}</td>
                    <td class="amount-col">{{ number_format($b['limit'], 2) }}</td>
                    <td class="amount-col {{ $b['over'] ? 'text-red' : '' }}">{{ number_format($b['spent'], 2) }}</td>
                    <td style="text-align:center">
                        @if($b['over'])
                            <span style="color:#dc2626; font-weight:bold;">{{ ArabicShaper::shape(__('Over Budget')) }}</span>
                        @else
                            <span style="color:#059669; font-weight:bold;">{{ ArabicShaper::shape(__('Within Limit')) }}</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" style="text-align:center; color:#94a3b8; padding:20px;">
                        {{ ArabicShaper::shape(__('No active budgets.')) }}
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

</body>
</html>

