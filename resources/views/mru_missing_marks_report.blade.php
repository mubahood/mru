<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Students with Missing Marks - {{ $export->export_name }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Helvetica Neue', Arial, sans-serif;
            background: #fff;
            padding: 15px;
            font-size: 11px;
            line-height: 1.4;
        }
        .container {
            max-width: 1400px;
            margin: 0 auto;
        }
        .header {
            border-bottom: 2px solid {{ $enterprise->primary_color ?? '#1a5490' }};
            padding-bottom: 10px;
            margin-bottom: 15px;
        }
        .header h1 {
            font-size: 18px;
            font-weight: 600;
            color: #333;
            margin-bottom: 3px;
        }
        .header .subtitle {
            font-size: 11px;
            color: #666;
        }
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 8px;
            margin-bottom: 12px;
            padding: 10px;
            background: #f8f9fa;
            border-left: 3px solid {{ $enterprise->primary_color ?? '#1a5490' }};
        }
        .info-item {
            font-size: 10px;
        }
        .info-item label {
            color: #666;
            font-weight: 500;
        }
        .info-item span {
            color: #333;
            font-weight: 600;
        }
        .summary-count {
            background: #f8f9fa;
            padding: 8px 12px;
            margin-bottom: 12px;
            font-size: 11px;
            border-left: 3px solid #666;
        }
        .summary-count strong {
            color: #333;
            font-size: 14px;
        }
        .actions {
            margin-bottom: 12px;
            display: flex;
            gap: 8px;
        }
        .btn {
            padding: 6px 12px;
            font-size: 10px;
            text-decoration: none;
            border-radius: 3px;
            display: inline-block;
            border: 1px solid #ddd;
            background: white;
            color: #333;
        }
        .btn-primary {
            background: {{ $enterprise->primary_color ?? '#1a5490' }};
            color: white;
            border-color: {{ $enterprise->primary_color ?? '#1a5490' }};
        }
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10px;
            margin-bottom: 15px;
        }
        thead th {
            background: {{ $enterprise->primary_color ?? '#1a5490' }};
            color: white;
            padding: 6px 4px;
            text-align: left;
            font-weight: 600;
            font-size: 9px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }
        tbody td {
            padding: 5px 4px;
            border-bottom: 1px solid #e0e0e0;
            vertical-align: top;
        }
        tbody tr:hover {
            background: #f8f9fa;
        }
        tbody tr:nth-child(even) {
            background: #fafafa;
        }
        .num-cell {
            color: #333;
            font-weight: 600;
        }
        .badge {
            display: inline-block;
            padding: 2px 6px;
            font-size: 9px;
            background: #e0e0e0;
            color: #333;
            border-radius: 2px;
            margin: 1px;
        }
        .footer {
            text-align: center;
            padding-top: 10px;
            border-top: 1px solid #e0e0e0;
            color: #999;
            font-size: 9px;
        }
        @media print {
            body { padding: 5px; font-size: 10px; }
            .actions { display: none; }
            thead th {
                background: {{ $enterprise->primary_color ?? '#1a5490' }} !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            @if($logoPath)
                <img src="{{ $logoPath }}" alt="Logo" style="height: 40px; margin-bottom: 8px;">
            @endif
            <h1>⚠ STUDENTS WITH INCOMPLETE MARKS</h1>
            <div class="subtitle">{{ $enterprise->name ?? 'Academic Institution' }}</div>
        </div>

        <div class="actions">
            <button onclick="window.print()" class="btn btn-primary">Print Report</button>
            <a href="?type=excel" class="btn">Export Excel</a>
            <a href="?type=pdf" class="btn">Download PDF</a>
            <a href="{{ admin_url('mru-academic-result-exports') }}" class="btn">Back</a>
        </div>

        <div class="info-grid">
            <div class="info-item"><label>Export:</label> <span>{{ $export->export_name }}</span></div>
            <div class="info-item"><label>Academic Year:</label> <span>{{ $export->academic_year }}</span></div>
            <div class="info-item"><label>Semester:</label> <span>{{ $export->semester }}</span></div>
            <div class="info-item"><label>Year of Study:</label> <span>Year {{ $export->study_year }}</span></div>
            <div class="info-item" style="grid-column: 1/-1;"><label>Programme:</label> <span>{{ $export->programme ? $export->programme->progname : 'All Programmes' }}</span></div>
        </div>

        <div class="summary-count">
            <strong>{{ count($incompleteStudents) }}</strong> students have incomplete marks and require follow-up
        </div>

        <table>
            <thead>
                <tr>
                    <th style="width: 3%;">No.</th>
                    <th style="width: 10%;">Reg No</th>
                    <th style="width: 18%;">Student Name</th>
                    <th style="width: 15%;">Specialization</th>
                    <th style="width: 6%; text-align: center;">Total</th>
                    <th style="width: 8%; text-align: center;">Obtained</th>
                    <th style="width: 8%; text-align: center;">Missing</th>
                    <th style="width: 32%;">Missing Courses</th>
                </tr>
            </thead>
            <tbody>
                @foreach($incompleteStudents as $index => $student)
                <tr>
                    <td style="text-align: center;">{{ $index + 1 }}</td>
                    <td><strong>{{ $student['regno'] }}</strong></td>
                    <td>{{ $student['name'] }}</td>
                    <td>{{ $student['specialization'] }}</td>
                    <td style="text-align: center;" class="num-cell">{{ $student['total_courses'] }}</td>
                    <td style="text-align: center;" class="num-cell">{{ $student['marks_obtained'] }}</td>
                    <td style="text-align: center;" class="num-cell">{{ $student['marks_missing_count'] }}</td>
                    <td>
                        @php
                            $courses = explode(', ', $student['missing_courses']);
                        @endphp
                        @foreach($courses as $course)
                            <span class="badge">{{ $course }}</span>
                        @endforeach
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="footer">
            <p>Generated: {{ now()->format('d M Y H:i:s') }} | MRU Academic Management System</p>
        </div>
    </div>
</body>
</html>
