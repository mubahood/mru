<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Programme Curriculum - {{ $programme->progcode }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 9pt;
            line-height: 1.2;
            color: #333;
            margin: 10mm 12mm;
        }

        .header-table {
            width: 100%;
            margin-bottom: 8px;
        }

        .header-table td {
            vertical-align: top;
            padding: 0;
        }

        .header-logo {
            width: 12%;
        }

        .header-logo img {
            max-width: 100%;
            max-height: 50px;
            height: auto;
        }

        .header-center {
            text-align: center;
            padding: 0 10px;
        }

        .header-center h1 {
            font-size: 11pt;
            margin: 0 0 2px 0;
            line-height: 1.1;
        }

        .header-center p {
            font-size: 8pt;
            margin: 1px 0;
            line-height: 1.1;
        }

        .header-spacer {
            width: 12%;
        }

        .divider {
            border: none;
            border-top: 2px solid black;
            margin: 5px 0 8px 0;
        }

        .doc-title {
            text-align: center;
            font-size: 10pt;
            font-weight: bold;
            margin-bottom: 3px;
        }

        .doc-subtitle {
            text-align: center;
            font-size: 9pt;
            color: #666;
            margin-bottom: 8px;
        }

        .info-section {
            background: #f5f5f5;
            padding: 6px 8px;
            margin-bottom: 10px;
            border-left: 3px solid #3498db;
        }

        .info-row {
            display: table;
            width: 100%;
            margin-bottom: 2px;
            font-size: 8pt;
        }

        .info-row:last-child {
            margin-bottom: 0;
        }

        .info-label {
            display: table-cell;
            font-weight: bold;
            width: 28%;
            color: #2c3e50;
        }

        .info-value {
            display: table-cell;
            width: 72%;
            color: #34495e;
        }

        .year-section {
            margin-bottom: 12px;
            page-break-inside: avoid;
        }

        .year-header {
            background: #2c3e50;
            color: white;
            padding: 4px 8px;
            font-size: 9pt;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .semester-section {
            margin-bottom: 8px;
        }

        .semester-header {
            background: #34495e;
            color: white;
            padding: 3px 8px;
            font-size: 8pt;
            font-weight: bold;
            margin-bottom: 4px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 6px;
        }

        table thead {
            background: #95a5a6;
            color: white;
        }

        table th {
            padding: 4px 6px;
            text-align: left;
            font-weight: bold;
            font-size: 8pt;
            border: 1px solid #7f8c8d;
            line-height: 1.1;
        }

        table td {
            padding: 3px 6px;
            border: 1px solid #bdc3c7;
            font-size: 8pt;
            line-height: 1.2;
        }

        table tbody tr:nth-child(even) {
            background: #fafafa;
        }

        .course-code {
            font-weight: bold;
            color: #2980b9;
        }

        .course-name {
            color: #2c3e50;
        }

        .credit-unit {
            text-align: center;
            font-weight: bold;
            color: #27ae60;
        }

        .summary-section {
            background: #d5f4e6;
            padding: 6px 8px;
            margin-top: 10px;
            border-left: 3px solid #27ae60;
            page-break-inside: avoid;
        }

        .summary-row {
            display: table;
            width: 100%;
            margin-bottom: 2px;
            font-size: 8pt;
        }

        .summary-label {
            display: table-cell;
            font-weight: bold;
            width: 65%;
            color: #2c3e50;
        }

        .summary-value {
            display: table-cell;
            width: 35%;
            text-align: right;
            color: #27ae60;
            font-weight: bold;
            font-size: 9pt;
        }

        .footer {
            margin-top: 15px;
            padding-top: 8px;
            border-top: 1px solid #bdc3c7;
            text-align: center;
            font-size: 7pt;
            color: #7f8c8d;
        }

        .no-courses {
            padding: 15px;
            text-align: center;
            color: #e74c3c;
            font-style: italic;
            font-size: 9pt;
        }

        @page {
            margin: 12mm 10mm;
        }
    </style>
</head>
<body>
    {{-- Header with Logo --}}
    <table class="header-table">
        <tbody>
            <tr>
                <td class="header-logo">
                    @if($logoPath && file_exists($logoPath))
                        <img src="{{ $logoPath }}" alt="{{ $ent->name }}">
                    @endif
                </td>
                <td class="header-center">
                    <h1>{{ strtoupper($ent->name) }}</h1>
                    @if($ent->address)
                        <p>{{ $ent->address }}</p>
                    @endif
                    @if($ent->phone_number)
                        <p>Tel: {{ $ent->phone_number }}@if($ent->phone_number_2), {{ $ent->phone_number_2 }}@endif</p>
                    @endif
                    @if($ent->email)
                        <p>Email: {{ $ent->email }}</p>
                    @endif
                </td>
                <td class="header-spacer"></td>
            </tr>
        </tbody>
    </table>

    <hr class="divider">

    <div class="doc-title">PROGRAMME CURRICULUM</div>
    <div class="doc-subtitle">{{ $programme->progname }} ({{ $programme->progcode }})</div>

    <div class="info-section">
        <div class="info-row">
            <div class="info-label">Programme Code:</div>
            <div class="info-value">{{ $programme->progcode }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">Programme Name:</div>
            <div class="info-value">{{ $programme->progname }}</div>
        </div>
        @if($programme->faculty)
        <div class="info-row">
            <div class="info-label">Faculty:</div>
            <div class="info-value">{{ $programme->faculty->faculty }} ({{ $programme->faculty->abbrev }})</div>
        </div>
        @endif
        <div class="info-row">
            <div class="info-label">Duration:</div>
            <div class="info-value">
                @if($programme->maxduration && $programme->maxduration != $programme->couselength)
                    {{ $programme->couselength }}-{{ $programme->maxduration }} Years
                @else
                    {{ $programme->couselength }} {{ $programme->couselength == 1 ? 'Year' : 'Years' }}
                @endif
            </div>
        </div>
        <div class="info-row">
            <div class="info-label">Study System:</div>
            <div class="info-value">{{ $programme->study_system }}</div>
        </div>
        @if($curriculum)
        <div class="info-row">
            <div class="info-label">Curriculum Version:</div>
            <div class="info-value">{{ $curriculum->Tittle }} ({{ $curriculum->StartYear }} {{ $curriculum->intake }})</div>
        </div>
        @endif
        <div class="info-row">
            <div class="info-label">Document Generated:</div>
            <div class="info-value">{{ $generatedDate }}</div>
        </div>
    </div>

    @if(count($coursesByYearSem) > 0)
        @foreach($coursesByYearSem as $year => $semesters)
            <div class="year-section">
                <div class="year-header">YEAR {{ $year }}</div>

                @foreach($semesters as $semester => $courses)
                    <div class="semester-section">
                        <div class="semester-header">Semester {{ $semester }}</div>

                        <table>
                            <thead>
                                <tr>
                                    <th style="width: 5%;">#</th>
                                    <th style="width: 18%;">Course Code</th>
                                    <th style="width: 62%;">Course Name</th>
                                    <th style="width: 15%; text-align: center;">Credit Units</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($courses as $index => $programmeCourse)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td class="course-code">{{ $programmeCourse->course_code }}</td>
                                        <td class="course-name">
                                            {{ $programmeCourse->course ? $programmeCourse->course->courseName : 'N/A' }}
                                        </td>
                                        <td class="credit-unit">
                                            {{ $programmeCourse->course ? number_format($programmeCourse->course->CreditUnit, 1) : 'N/A' }}
                                        </td>
                                    </tr>
                                @endforeach
                                <tr style="background: #ecf0f1; font-weight: bold;">
                                    <td colspan="3" style="text-align: right; padding-right: 10px;">
                                        Semester Total:
                                    </td>
                                    <td class="credit-unit">
                                        {{ number_format(collect($courses)->sum(function($c) { return $c->course ? $c->course->CreditUnit : 0; }), 1) }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                @endforeach
            </div>
        @endforeach

        <div class="summary-section">
            <div class="summary-row">
                <div class="summary-label">Total Number of Courses:</div>
                <div class="summary-value">{{ $totalCourses }}</div>
            </div>
            <div class="summary-row">
                <div class="summary-label">Total Credit Units:</div>
                <div class="summary-value">{{ number_format($totalCredits, 1) }}</div>
            </div>
            @if($programme->mincredit)
            <div class="summary-row">
                <div class="summary-label">Minimum Credits Required:</div>
                <div class="summary-value">{{ $programme->mincredit }}</div>
            </div>
            @endif
        </div>
    @else
        <div class="no-courses">
            No courses found for this programme.
        </div>
    @endif

    <div class="footer">
        <p style="margin-top: 8px; font-size: 7pt;">This is a computer-generated document. No signature required.</p>
    </div>
</body>
</html>
