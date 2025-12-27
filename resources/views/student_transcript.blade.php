<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Academic Transcript - {{ $student->firstname }} {{ $student->othername }}</title>
    <style>
        @page {
            size: A4;
            margin: 15mm;
        }
        
        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 11px;
            line-height: 1.4;
            color: #000;
            margin: 0;
            padding: 20px;
            background: #fff;
        }
        
        .transcript-container {
            max-width: 210mm;
            margin: 0 auto;
            background: white;
            border: 2px solid #000;
            padding: 15px;
        }
        
        .header {
            text-align: center;
            border-bottom: 3px double #000;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }
        
        .header-logo {
            max-height: 70px;
            margin-bottom: 5px;
        }
        
        .header h1 {
            font-size: 20px;
            margin: 5px 0;
            font-weight: bold;
            text-transform: uppercase;
        }
        
        .header h2 {
            font-size: 16px;
            margin: 5px 0;
            font-weight: bold;
            text-decoration: underline;
        }
        
        .header p {
            margin: 2px 0;
            font-size: 10px;
        }
        
        .student-info {
            background: #f8f9fa;
            border: 1px solid #000;
            padding: 10px;
            margin-bottom: 15px;
        }
        
        .student-info table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .student-info td {
            padding: 4px 8px;
            font-size: 11px;
        }
        
        .student-info td:first-child {
            font-weight: bold;
            width: 150px;
        }
        
        .semester-section {
            margin-bottom: 15px;
        }
        
        .semester-header {
            background: #000;
            color: #fff;
            padding: 6px 10px;
            font-weight: bold;
            font-size: 12px;
            margin-bottom: 5px;
        }
        
        .results-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
            font-size: 10px;
        }
        
        .results-table th {
            background: #e9ecef;
            border: 1px solid #000;
            padding: 5px 4px;
            text-align: left;
            font-weight: bold;
        }
        
        .results-table th.center {
            text-align: center;
        }
        
        .results-table td {
            border: 1px solid #000;
            padding: 4px 4px;
        }
        
        .results-table td.center {
            text-align: center;
        }
        
        .results-table td.right {
            text-align: right;
        }
        
        .results-table tr.passed {
            background: #fff;
        }
        
        .results-table tr.failed {
            background: #ffe6e6;
        }
        
        .semester-summary {
            background: #f8f9fa;
            border: 1px solid #000;
            padding: 8px;
            margin-bottom: 10px;
            font-size: 11px;
            font-weight: bold;
        }
        
        .semester-summary span {
            margin-right: 20px;
        }
        
        .final-summary {
            border: 2px solid #000;
            padding: 12px;
            margin-top: 15px;
            background: #f8f9fa;
        }
        
        .final-summary h3 {
            margin: 0 0 10px 0;
            font-size: 14px;
            text-align: center;
            text-decoration: underline;
        }
        
        .summary-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-bottom: 15px;
        }
        
        .summary-item {
            border: 1px solid #000;
            padding: 8px;
        }
        
        .summary-item strong {
            display: block;
            font-size: 10px;
            color: #666;
            margin-bottom: 3px;
        }
        
        .summary-item .value {
            font-size: 16px;
            font-weight: bold;
            color: #000;
        }
        
        .distinctions, .warnings {
            margin-top: 10px;
        }
        
        .distinctions h4, .warnings h4 {
            font-size: 12px;
            margin: 0 0 5px 0;
            text-decoration: underline;
        }
        
        .distinctions ul, .warnings ul {
            margin: 0;
            padding-left: 20px;
            font-size: 10px;
        }
        
        .distinctions li {
            color: #155724;
            margin-bottom: 3px;
        }
        
        .warnings li {
            color: #721c24;
            margin-bottom: 3px;
        }
        
        .footer {
            margin-top: 20px;
            border-top: 2px solid #000;
            padding-top: 15px;
        }
        
        .footer-grid {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 10px;
            font-size: 10px;
        }
        
        .signature-line {
            border-top: 1px solid #000;
            margin-top: 40px;
            padding-top: 5px;
            text-align: center;
        }
        
        .print-btn {
            position: fixed;
            top: 20px;
            right: 20px;
            background: #0d6efd;
            color: white;
            border: none;
            padding: 10px 20px;
            font-size: 14px;
            cursor: pointer;
            z-index: 1000;
        }
        
        @media print {
            body {
                padding: 0;
            }
            
            .transcript-container {
                border: none;
                padding: 0;
            }
            
            .print-btn {
                display: none;
            }
            
            .semester-section {
                page-break-inside: avoid;
            }
        }
    </style>
</head>
<body>
    <button onclick="window.print()" class="print-btn">🖨️ Print Transcript</button>
    
    <div class="transcript-container">
        <!-- Header -->
        <div class="header">
            @if($logo_path)
                <img src="{{ $logo_path }}" alt="Logo" class="header-logo">
            @endif
            <h1>{{ $enterprise->name ?? 'Institution Name' }}</h1>
            <p>{{ $enterprise->address ?? '' }}</p>
            <p>Tel: {{ $enterprise->phone ?? '' }} | Email: {{ $enterprise->email ?? '' }}</p>
            <h2>OFFICIAL ACADEMIC TRANSCRIPT</h2>
        </div>

        <!-- Student Information -->
        <div class="student-info">
            <table>
                <tr>
                    <td>Student Name:</td>
                    <td><strong>{{ strtoupper($student->firstname . ' ' . $student->othername) }}</strong></td>
                    <td>Registration Number:</td>
                    <td><strong>{{ $student->regno }}</strong></td>
                </tr>
                <tr>
                    <td>Programme:</td>
                    <td>{{ $programme->progname ?? 'N/A' }}</td>
                    <td>Specialization:</td>
                    <td>{{ $specialization->spec ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <td>Date of Birth:</td>
                    <td>{{ $student->dob ?? 'N/A' }}</td>
                    <td>Gender:</td>
                    <td>{{ $student->sex ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <td>Transcript Generated:</td>
                    <td colspan="3">{{ $generated_date->format('d F Y, H:i') }}</td>
                </tr>
            </table>
        </div>

        <!-- Academic Results by Semester -->
        @foreach($transcript_data as $semester)
            <div class="semester-section">
                <div class="semester-header">
                    {{ $semester['academic_year'] }} - SEMESTER {{ $semester['semester'] }} (Year {{ $semester['study_year'] }})
                </div>

                <table class="results-table">
                    <thead>
                        <tr>
                            <th style="width: 15%;">COURSE CODE</th>
                            <th style="width: 45%;">COURSE TITLE</th>
                            <th class="center" style="width: 10%;">CREDITS</th>
                            <th class="center" style="width: 10%;">GRADE</th>
                            <th class="center" style="width: 10%;">GRADE POINT</th>
                            <th class="right" style="width: 10%;">POINTS</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($semester['courses'] as $course)
                            <tr class="{{ $course['isPassed'] ? 'passed' : 'failed' }}">
                                <td>{{ $course['code'] }}</td>
                                <td>{{ $course['name'] }}</td>
                                <td class="center">{{ $course['credits'] }}</td>
                                <td class="center"><strong>{{ $course['grade'] }}</strong></td>
                                <td class="center">{{ number_format($course['gradePoint'], 2) }}</td>
                                <td class="right">{{ number_format($course['points'], 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                <div class="semester-summary">
                    <span>Credits Earned: {{ $semester['credits_earned'] }}</span>
                    <span>Semester GPA: {{ number_format($semester['semester_gpa'], 2) }}</span>
                    <span>Cumulative Credits: {{ $semester['cumulative_credits'] }}</span>
                    <span>CGPA: {{ number_format($semester['cgpa'], 2) }}</span>
                </div>

                @if(count($semester['failed_courses']) > 0)
                    <div style="background: #fff3cd; border: 1px solid #856404; padding: 5px; margin-bottom: 10px; font-size: 10px;">
                        <strong>Failed Courses:</strong> {{ implode(', ', $semester['failed_courses']) }}
                    </div>
                @endif
            </div>
        @endforeach

        <!-- Final Summary -->
        <div class="final-summary">
            <h3>ACADEMIC SUMMARY</h3>

            <div class="summary-grid">
                <div class="summary-item">
                    <strong>TOTAL CREDITS EARNED</strong>
                    <div class="value">{{ $summary['total_credits'] }}</div>
                </div>

                <div class="summary-item">
                    <strong>CUMULATIVE GPA (CGPA)</strong>
                    <div class="value">{{ number_format($summary['final_cgpa'], 2) }}</div>
                </div>

                <div class="summary-item" style="grid-column: 1 / -1;">
                    <strong>CLASSIFICATION</strong>
                    <div class="value" style="font-size: 14px;">{{ $summary['honors'] }}</div>
                </div>
            </div>

            @if(count($summary['distinctions']) > 0)
                <div class="distinctions">
                    <h4>DISTINCTIONS</h4>
                    <ul>
                        @foreach($summary['distinctions'] as $distinction)
                            <li>{{ $distinction['course'] }} - Grade {{ $distinction['grade'] }} ({{ $distinction['semester'] }})</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if(count($summary['warnings']) > 0)
                <div class="warnings">
                    <h4>ACADEMIC WARNINGS</h4>
                    <ul>
                        @foreach($summary['warnings'] as $warning)
                            <li><strong>{{ $warning['semester'] }}:</strong> {{ $warning['type'] }} - {{ $warning['reason'] }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>

        <!-- Footer with Authentication -->
        <div class="footer">
            <div class="footer-grid">
                <div>
                    <div class="signature-line">
                        <strong>Registrar</strong>
                    </div>
                </div>

                <div style="text-align: center; padding-top: 40px;">
                    <strong>OFFICIAL SEAL</strong>
                    <div style="border: 2px solid #000; width: 80px; height: 80px; margin: 10px auto; display: flex; align-items: center; justify-content: center; font-size: 8px;">
                        [SEAL]
                    </div>
                </div>

                <div>
                    <div class="signature-line">
                        <strong>Dean of Students</strong>
                    </div>
                </div>
            </div>

            <div style="text-align: center; margin-top: 15px; font-size: 9px; border-top: 1px solid #000; padding-top: 10px;">
                <strong>Document Verification Code:</strong> {{ strtoupper(substr(md5($student->regno . $generated_date->timestamp), 0, 12)) }}<br>
                <strong>Generated:</strong> {{ $generated_date->format('d F Y \a\t H:i:s') }}<br>
                <em>This is an official document. Any alteration renders it invalid.</em>
            </div>
        </div>
    </div>
</body>
</html>
