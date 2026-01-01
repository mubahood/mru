<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    <style>
        @page {
            margin: 10mm;
        }
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 8.5pt;
            color: #333;
            line-height: 1.3;
        }
        .header {
            text-align: center;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid #1a5490;
        }
        .logo {
            width: 60px;
            height: auto;
            margin-bottom: 5px;
        }
        .institution {
            font-size: 11pt;
            font-weight: bold;
            color: #1a5490;
            text-transform: uppercase;
            margin: 5px 0;
            letter-spacing: 0.5px;
        }
        .address {
            font-size: 7.5pt;
            color: #666;
            margin: 3px 0;
        }
        .report-title {
            font-size: 9pt;
            font-weight: 600;
            color: #333;
            margin: 8px 0 3px 0;
        }
        .date {
            font-size: 7pt;
            color: #999;
        }
        .config-box {
            background: #f8f9fa;
            padding: 6px 8px;
            margin-bottom: 12px;
            border: 1px solid #dee2e6;
            font-size: 7.5pt;
        }
        .config-box strong {
            color: #1a5490;
        }
        .summary {
            background: #f0f4f8;
            padding: 6px 8px;
            margin-bottom: 12px;
            text-align: center;
            border: 1px solid #1a5490;
            font-size: 8pt;
            font-weight: 600;
            color: #1a5490;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 5px;
        }
        th {
            background: #000;
            color: #fff;
            padding: 5px 4px;
            text-align: left;
            font-weight: 600;
            font-size: 7.5pt;
            border: 1px solid #000;
        }
        td {
            padding: 4px;
            border: 1px solid #ddd;
            font-size: 7.5pt;
        }
        tr:nth-child(even) {
            background: #f9f9f9;
        }
        .no-data {
            text-align: center;
            padding: 30px;
            color: #999;
            font-style: italic;
            font-size: 8.5pt;
        }
        .footer {
            margin-top: 15px;
            padding-top: 8px;
            border-top: 1px solid #ddd;
            text-align: center;
            font-size: 6.5pt;
            color: #666;
        }
        .cgpa {
            font-weight: 600;
            color: #1a5490;
        }
        .regno {
            font-weight: 600;
        }
    </style>
</head>
<body>
    @php
        $ent = \App\Models\Enterprise::first();
    @endphp
    
    <!-- Header -->
    <div class="header">
        @if($ent && $ent->logo)
        <img src="{{ public_path('storage/enterprises/' . $ent->logo) }}" class="logo" alt="Logo">
        @endif
        <div class="institution">{{ $ent ? $ent->name : 'MBARARA UNIVERSITY OF SCIENCE AND TECHNOLOGY' }}</div>
        @if($ent)
        <div class="address">
            {{ $ent->address }} | {{ $ent->phone }} | {{ $ent->email }}
        </div>
        @endif
        <div class="report-title">{{ $export->export_name ?? 'Academic Results' }}</div>
        <div class="date">Generated: {{ date('F d, Y h:i A') }}</div>
    </div>

    <!-- Configuration -->
    @if(!empty($params['acad']) || !empty($params['semester']) || !empty($params['progid']) || !empty($params['studyyear']))
    <div class="config-box">
        <strong>Configuration:</strong>
        @if(!empty($params['acad']))
            Academic Year: {{ $params['acad'] }}
        @endif
        @if(!empty($params['semester']))
            | Semester: {{ $params['semester'] }}
        @endif
        @if(!empty($params['progid']))
            | Programme: {{ $params['progid'] }}
        @endif
        @if(!empty($params['studyyear']))
            | Study Year: {{ $params['studyyear'] }}
        @endif
    </div>
    @endif

    <!-- Report Title Section -->
    <div style="background: #1a5490; padding: 8px; margin-bottom: 12px;">
        <div style="font-size: 9pt; font-weight: 600; color: #fff; margin-bottom: 3px;">{{ $title }}</div>
        <div style="font-size: 7pt; color: #f0f4f8;">
            @if(str_contains($title, "VC's List"))
                Vice Chancellor's Honor List - Exceptional Academic Performance
            @elseif(str_contains($title, "Dean's List"))
                Dean's Honor List - Outstanding Academic Achievement
            @elseif(str_contains($title, 'Pass'))
                Students with Successful Completion of All Courses
            @elseif(str_contains($title, 'Retake'))
                Students Requiring Course Retakes
            @endif
        </div>
    </div>

    <!-- Summary -->
    <div class="summary">
        Total Students: {{ count($students) }}
    </div>

    <!-- Student Table -->
    @if(count($students) > 0)
    <table>
        <thead>
            <tr>
                <th style="width: 6%">#</th>
                <th style="width: 13%">Reg. No</th>
                <th style="width: 13%">Entry No</th>
                <th>Name</th>
                <th style="width: 8%">Gender</th>
                @if(isset($students[0]->cgpa))
                <th style="width: 10%">CGPA</th>
                @endif
                @if(isset($students[0]->failed_courses))
                <th style="width: 28%">Failed Courses</th>
                @endif
            </tr>
        </thead>
        <tbody>
            @foreach($students as $index => $student)
            <tr>
                <td style="text-align: center;">{{ $index + 1 }}</td>
                <td class="regno">{{ $student->regno }}</td>
                <td>{{ $student->entryno ?? 'N/A' }}</td>
                <td>{{ $student->studname }}</td>
                <td style="text-align: center;">{{ $student->gender ?? 'N/A' }}</td>
                @if(isset($student->cgpa))
                <td class="cgpa" style="text-align: center;">{{ number_format($student->cgpa, 2) }}</td>
                @endif
                @if(isset($student->failed_courses))
                <td style="font-size: 7pt;">{{ $student->failed_courses }}</td>
                @endif
            </tr>
            @endforeach
        </tbody>
    </table>
    @else
    <div class="no-data">
        <strong>No students found matching the criteria.</strong>
    </div>
    @endif

    <!-- Footer -->
    <div class="footer">
        Computer-generated report | {{ $ent ? $ent->name : 'Mbarara University of Science and Technology' }} | © {{ date('Y') }}
    </div>
</body>
</html>
