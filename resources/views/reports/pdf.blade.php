<!doctype html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; }
        h2 { margin: 0 0 10px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 16px; }
        th, td { border: 1px solid #ccc; padding: 6px 8px; font-size: 12px; }
        th { background: #f0f0f0; }
        .muted { color: #666; font-size: 12px; }
    </style>
    <title>تقرير الفترة</title>
    </head>
<body>
    <h2>تقرير الفترة</h2>
    <div class="muted">من {{ $start }} إلى {{ $end }}</div>

    <table>
        <thead>
        <tr>
            <th>إجمالي الدخل</th>
            <th>إجمالي المصروف</th>
            <th>الصافي</th>
            <th>معدل الادخار %</th>
        </tr>
        </thead>
        <tbody>
        <tr>
            <td>{{ number_format($totalIncome, 2) }}</td>
            <td>{{ number_format($totalExpense, 2) }}</td>
            <td>{{ number_format($net, 2) }}</td>
            <td>{{ number_format($savingsRate, 1) }}</td>
        </tr>
        </tbody>
    </table>

    <h3>تفصيل المصروف حسب الفئة</h3>
    <table>
        <thead>
        <tr>
            <th>الفئة</th>
            <th>المجموع</th>
        </tr>
        </thead>
        <tbody>
        @foreach($categoryBreakdown as $row)
            <tr>
                <td>{{ $row->category ?? 'غير مصنف' }}</td>
                <td>{{ number_format((float)$row->total, 2) }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>

    <h3>الميزانيات ضمن الفترة</h3>
    <table>
        <thead>
        <tr>
            <th>الفئة</th>
            <th>الفترة</th>
            <th>الحد</th>
            <th>المصروف</th>
            <th>المتبقي</th>
            <th>نسبة الاستهلاك %</th>
        </tr>
        </thead>
        <tbody>
        @foreach($budgets as $b)
            <tr>
                <td>{{ $b['categoryName'] ?? '—' }}</td>
                <td>{{ $b['period_start'] }} → {{ $b['period_end'] }}</td>
                <td>{{ number_format($b['limit'] ?? 0, 2) }}</td>
                <td>{{ number_format($b['spent'] ?? 0, 2) }}</td>
                <td>{{ number_format($b['remaining'] ?? 0, 2) }}</td>
                <td>{{ number_format($b['progress'] ?? 0, 0) }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
</body>
</html>
