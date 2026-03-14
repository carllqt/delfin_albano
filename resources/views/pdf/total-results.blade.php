<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $categoryName }} Results</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 8mm;
        }
        
        body {
            font-family: Arial, sans-serif;
            font-size: 8pt;
            margin: 0;
            padding: 0;
        }
        
        h2 {
            text-align: center;
            font-size: 16pt;
            margin-bottom: 8px;
            color: #000;
            font-weight: bold;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }
        
        th, td {
            border: 1px solid #333;
            padding: 4px 5px;
            text-align: center;
            vertical-align: middle;
            font-size: 8pt;
        }
        
        th {
            background-color: #e5e7eb;
            font-weight: bold;
            color: #000;
            font-size: 8pt;
        }
        
        td {
            background-color: #fff;
            color: #000;
        }
        
        .rank-1 {
            background-color: #fbbf24 !important;
            font-weight: bold;
            color: #000 !important;
        }
        
        .rank-1 td {
            background-color: #fbbf24 !important;
            font-weight: bold;
            color: #000 !important;
        }
        
        .candidate-cell {
            text-align: left;
            padding-left: 6px;
        }
        
        .signatures {
            margin-top: 10px;
            page-break-inside: avoid;
        }
        
        .signatures h3 {
            font-size: 11pt;
            margin-bottom: 8px;
            font-weight: bold;
        }
        
        .signature-grid {
            display: table;
            width: 100%;
        }
        
        .signature-row {
            display: table-row;
        }
        
        .signature-item {
            display: table-cell;
            width: 33.33%;
            padding: 6px 15px 6px 0;
        }
        
        .signature-label {
            font-size: 9pt;
            margin-bottom: 25px;
        }
        
        .signature-line {
            border-bottom: 1px solid #666;
            margin-top: 3px;
        }
    </style>
</head>
<body>
    <h2>{{ $categoryName }} Results</h2>
    
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Candidate</th>
                @foreach($categories as $cat)
                    <th>{{ $categoryLabels[$cat] ?? strtoupper(str_replace('_', ' ', $cat)) }}</th>
                @endforeach
                <th>Total</th>
                <th>Rank</th>
            </tr>
        </thead>
        <tbody>
            @foreach($candidates as $c)
                <tr class="{{ $c['rank'] == 1 ? 'rank-1' : '' }}">
                    <td>{{ $c['candidate']->candidate_number }}</td>
                    <td class="candidate-cell">
                        {{ $c['candidate']->first_name }} {{ $c['candidate']->last_name }}
                    </td>
                    @foreach($categories as $cat)
                        <td>{{ number_format($c['scores'][$cat] ?? 0, 2) }}</td>
                    @endforeach
                    <td>{{ number_format($c['total'], 2) }}</td>
                    <td>{{ $c['rank'] }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
    
    <div class="signatures">
        <h3>Judge Signatures:</h3>
        <div class="signature-grid">
            @foreach(['judge_1', 'judge_2', 'judge_3', 'judge_4', 'judge_5'] as $index => $judge)
                @if($index % 3 == 0)
                    <div class="signature-row">
                @endif
                        <div class="signature-item">
                            <div class="signature-label">{{ strtoupper(str_replace('_', ' ', $judge)) }}</div>
                            <div class="signature-line"></div>
                        </div>
                @if($index % 3 == 2 || $loop->last)
                    </div>
                @endif
            @endforeach
        </div>
    </div>
</body>
</html>
