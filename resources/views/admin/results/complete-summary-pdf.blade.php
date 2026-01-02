<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Academic Results Summary Report</title>
    <style>
        @page {
            margin: 10mm 8mm;
        }
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 8.5pt;
            color: #000;
            line-height: 1.2;
            margin: 0;
            padding: 0;
        }
        
        /* Header Section */
        .header-section {
            border-bottom: 2px solid #000;
            padding-bottom: 6px;
            margin-bottom: 8px;
            display: table;
            width: 100%;
        }
        .header-logo {
            display: table-cell;
            width: 70px;
            vertical-align: middle;
        }
        .header-logo img {
            max-width: 60px;
            max-height: 60px;
        }
        .header-info {
            display: table-cell;
            text-align: center;
            vertical-align: middle;
            padding: 0 8px;
        }
        .header-info h1 {
            margin: 0 0 2px 0;
            font-size: 13pt;
            font-weight: bold;
            color: #1a5490;
            text-transform: uppercase;
        }
        .header-info .address {
            font-size: 7pt;
            color: #333;
            margin: 1px 0;
        }
        .header-info .export-name {
            font-size: 9pt;
            color: #1a5490;
            margin: 4px 0 2px 0;
            font-weight: 600;
        }
        .header-info .generated {
            font-size: 6.5pt;
            color: #666;
            margin-top: 1px;
        }
        
        /* Report Info Box */
        .report-info {
            background: #f5f5f5;
            border: 1px solid #ddd;
            padding: 5px 8px;
            margin-bottom: 10px;
        }
        .report-info table {
            width: 100%;
            border-collapse: collapse;
        }
        .report-info td {
            padding: 1px 5px;
            border: none;
            font-size: 7.5pt;
        }
        .report-info td:first-child {
            font-weight: bold;
            width: 120px;
            color: #333;
        }
        .report-info td strong {
            color: #1a5490;
        }
        
        /* Section Headers */
        .section-header {
            background: #000;
            color: #fff;
            padding: 6px 8px;
            margin-top: 15px;
            margin-bottom: 6px;
            font-size: 10pt;
            font-weight: bold;
            text-transform: uppercase;
            page-break-after: avoid;
        }
        .section-header:first-of-type {
            margin-top: 8px;
        }
        .section-header .count {
            float: right;
            background: #1a5490;
            padding: 2px 8px;
            border-radius: 2px;
            font-size: 8.5pt;
        }
        
        /* Criteria Box */
        .criteria-box {
            background: #f0f4f8;
            border-left: 3px solid #1a5490;
            padding: 4px 8px;
            margin-bottom: 6px;
            font-size: 7.5pt;
            page-break-after: avoid;
        }
        .criteria-box strong {
            color: #1a5490;
        }
        
        /* Tables */
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
            page-break-inside: auto;
        }
        table.data-table thead {
            background: #000;
            color: #fff;
        }
        table.data-table th {
            padding: 5px 3px;
            text-align: center;
            font-weight: bold;
            border: 1px solid #000;
            font-size: 7.5pt;
            text-transform: uppercase;
        }
        table.data-table tbody tr {
            page-break-inside: avoid;
        }
        table.data-table tbody tr:nth-child(even) {
            background: #f8f9fa;
        }
        table.data-table td {
            padding: 4px 3px;
            border: 1px solid #ddd;
            font-size: 8pt;
        }
        table.data-table td.number {
            text-align: center;
            font-weight: bold;
            color: #666;
        }
        table.data-table td.regno {
            font-weight: bold;
            color: #1a5490;
        }
        table.data-table td.name {
            text-align: left;
        }
        table.data-table td.cgpa {
            font-weight: bold;
            color: #198754;
            text-align: center;
        }
        table.data-table td.failed {
            color: #dc3545;
            font-size: 7pt;
        }
        table.data-table td.center {
            text-align: center;
        }
        
        /* No Data Message */
        .no-data {
            text-align: center;
            padding: 20px;
            color: #999;
            font-style: italic;
            background: #f8f9fa;
            border: 1px dashed #ddd;
            margin-bottom: 12px;
            font-size: 8pt;
        }
        
        /* Summary Stats */
        .summary-stats {
            background: #f0f4f8;
            border: 2px solid #1a5490;
            padding: 6px 10px;
            margin: 15px 0 8px 0;
            text-align: center;
            font-size: 8.5pt;
        }
        .summary-stats strong {
            color: #1a5490;
        }
        
        /* Footer */
        .report-footer {
            margin-top: 15px;
            padding-top: 8px;
            border-top: 2px solid #000;
            text-align: center;
            font-size: 6.5pt;
            color: #666;
        }
        .report-footer p {
            margin: 2px 0;
        }
        .report-footer strong {
            color: #000;
        }
        
        /* Page Break Control */
        .page-break {
            page-break-after: always;
        }
    </style>
</head>
<body>
    @php
        // Get enterprise/institution data
        $ent = \App\Models\Enterprise::first();
        $institutionName = $ent ? strtoupper($ent->name) : 'MBARARA UNIVERSITY OF SCIENCE AND TECHNOLOGY';
        $logoPath = $ent && $ent->logo ? public_path('storage/' . $ent->logo) : null;
        
        // Convert logo to base64 for PDF
        $logoDataUri = null;
        if ($logoPath && file_exists($logoPath)) {
            $imageType = mime_content_type($logoPath);
            $imageData = base64_encode(file_get_contents($logoPath));
            $logoDataUri = "data:{$imageType};base64,{$imageData}";
        }
        
        $address = $ent ? $ent->address : '';
        $phone = $ent ? $ent->phone : '';
        $email = $ent ? $ent->email : '';
    @endphp

    <!-- Header Section -->
    <div class="header-section">
        @if($logoDataUri)
        <div class="header-logo">
            <img src="{{ $logoDataUri }}" alt="Logo">
        </div>
        @endif
        
        <div class="header-info">
            <h1>{{ $institutionName }}</h1>
            @if($address || $phone || $email)
            <div class="address">
                @if($address) {{ $address }} @endif
                @if($phone) | Tel: {{ $phone }} @endif
                @if($email) | Email: {{ $email }} @endif
            </div>
            @endif
            <div class="export-name">{{ $export->export_name }}</div>
            <div class="generated">Generated: {{ date('l, F d, Y - h:i A') }}</div>
        </div>
        
        @if($logoDataUri)
        <div class="header-logo"></div> <!-- Spacer for symmetry -->
        @endif
    </div>

    <!-- Report Configuration -->
    <div class="report-info">
        <table>
            <tr>
                <td>Academic Year:</td>
                <td><strong>{{ $params['acad'] }}</strong></td>
                <td>Semester:</td>
                <td><strong>{{ $params['semester'] }}</strong></td>
            </tr>
            <tr>
                <td>Study Year:</td>
                <td><strong>Year {{ $params['studyyear'] }}</strong></td>
                <td>Programme:</td>
                <td><strong>{{ $export->programme->progname ?? 'All Programmes' }}</strong></td>
            </tr>
            @if($params['specialisation_id'])
            <tr>
                <td>Specialisation:</td>
                <td colspan="3">
                    <strong>
                        @php
                            $spec = \DB::table('acad_specialisation')->where('spec_id', $params['specialisation_id'])->first();
                            echo $spec ? $spec->abbrev . ' - ' . $spec->spec : $params['specialisation_id'];
                        @endphp
                    </strong>
                </td>
            </tr>
            @endif
            @if($params['start_range'] && $params['end_range'])
            <tr>
                <td>Student Range:</td>
                <td colspan="3"><strong>{{ $params['start_range'] }} - {{ $params['end_range'] }}</strong></td>
            </tr>
            @endif
        </table>
    </div>

    <!-- VC's List -->
    <div class="section-header">
        VICE CHANCELLOR'S LIST (FIRST CLASS)
        <span class="count">{{ count($vcList) }} Students</span>
    </div>
    
    <div style="margin: 8px 0; font-size: 8pt; line-height: 1.4; text-align: justify;">
        The following students obtained a CGPA between <strong>4.40</strong> and <strong>5.00</strong>.
    </div>
    
    <div class="criteria-box">
        <strong>Criteria:</strong> Students with Cumulative Grade Point Average (CGPA) between <strong>4.40 and 5.00</strong>
    </div>

    @if(count($vcList) > 0)
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 5%">#</th>
                <th style="width: 13%">REG. NO</th>
                <th style="width: 13%">ENTRY NO</th>
                <th style="width: 38%">STUDENT NAME</th>
                <th style="width: 8%">GENDER</th>
                <th style="width: 10%">CGPA</th>
                <th style="width: 13%">PROGRAMME</th>
            </tr>
        </thead>
        <tbody>
            @foreach($vcList as $index => $student)
            <tr>
                <td class="number">{{ $index + 1 }}</td>
                <td class="regno">{{ $student->regno }}</td>
                <td class="center">{{ $student->entryno ?? '-' }}</td>
                <td class="name">{{ $student->studname }}</td>
                <td class="center">{{ $student->gender ?? '-' }}</td>
                <td class="cgpa">{{ number_format($student->cgpa, 2) }}</td>
                <td class="center">{{ $student->progid }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @else
    <div class="no-data">No students meet the VC's List criteria (CGPA 4.40 - 5.00)</div>
    @endif

    <!-- Dean's List -->
    <div class="section-header">
        DEAN'S LIST (SECOND CLASS UPPER DIVISION)
        <span class="count">{{ count($deansList) }} Students</span>
    </div>
    
    <div style="margin: 8px 0; font-size: 8pt; line-height: 1.4; text-align: justify;">
        The following students obtained a CGPA between <strong>4.00</strong> and <strong>4.39</strong>.
    </div>
    
    <div class="criteria-box">
        <strong>Criteria:</strong> Students with Cumulative Grade Point Average (CGPA) between <strong>4.00 and 4.39</strong>
    </div>

    @if(count($deansList) > 0)
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 5%">#</th>
                <th style="width: 13%">REG. NO</th>
                <th style="width: 13%">ENTRY NO</th>
                <th style="width: 38%">STUDENT NAME</th>
                <th style="width: 8%">GENDER</th>
                <th style="width: 10%">CGPA</th>
                <th style="width: 13%">PROGRAMME</th>
            </tr>
        </thead>
        <tbody>
            @foreach($deansList as $index => $student)
            <tr>
                <td class="number">{{ $index + 1 }}</td>
                <td class="regno">{{ $student->regno }}</td>
                <td class="center">{{ $student->entryno ?? '-' }}</td>
                <td class="name">{{ $student->studname }}</td>
                <td class="center">{{ $student->gender ?? '-' }}</td>
                <td class="cgpa">{{ number_format($student->cgpa, 2) }}</td>
                <td class="center">{{ $student->progid }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @else
    <div class="no-data">No students meet the Dean's List criteria (CGPA 4.00 - 4.39)</div>
    @endif

    <!-- Pass Cases -->
    <div class="section-header">
        SECOND CLASS LOWER DIVISION
        <span class="count">{{ count($passCases) }} Students</span>
    </div>
    
    <div style="margin: 8px 0; font-size: 8pt; line-height: 1.4; text-align: justify;">
        The following candidates, whose registration numbers appear below, <strong>PASSED</strong> their semester examinations and were recommended to proceed subject to the approval of the <strong>SENATE Examination Board</strong>.
    </div>
    
    <div class="criteria-box">
        <strong>Criteria:</strong> Students who passed all courses - Score ≥ 50 (Undergraduate) or ≥ 60 (Postgraduate)
    </div>

    @if(count($passCases) > 0)
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 5%">#</th>
                <th style="width: 13%">REG. NO</th>
                <th style="width: 13%">ENTRY NO</th>
                <th style="width: 35%">STUDENT NAME</th>
                <th style="width: 8%">GENDER</th>
                <th style="width: 10%">CGPA</th>
                <th style="width: 16%">PROGRAMME</th>
            </tr>
        </thead>
        <tbody>
            @foreach($passCases as $index => $student)
            <tr>
                <td class="number">{{ $index + 1 }}</td>
                <td class="regno">{{ $student->regno }}</td>
                <td class="center">{{ $student->entryno ?? '-' }}</td>
                <td class="name">{{ $student->studname }}</td>
                <td class="center">{{ $student->gender ?? '-' }}</td>
                <td class="cgpa">{{ isset($student->cgpa) ? number_format($student->cgpa, 2) : '-' }}</td>
                <td class="center">{{ $student->progid }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @else
    <div class="no-data">No students in this category</div>
    @endif

    <!-- Incomplete Cases -->
    <div class="section-header">
        INCOMPLETE
        <span class="count">{{ count($incompleteCases) }} Students</span>
    </div>
    
    <div style="margin: 8px 0; font-size: 8pt; line-height: 1.4; text-align: justify;">
        The following students have <strong>INCOMPLETE</strong> results due to missing scores or grades.
    </div>
    
    <div class="criteria-box">
        <strong>Status:</strong> Students with incomplete examination results
    </div>

    @if(count($incompleteCases) > 0)
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 4%">#</th>
                <th style="width: 12%">REG. NO</th>
                <th style="width: 12%">ENTRY NO</th>
                <th style="width: 27%">STUDENT NAME</th>
                <th style="width: 7%">GENDER</th>
                <th style="width: 11%">PROGRAMME</th>
                <th style="width: 27%">INCOMPLETE COURSES</th>
            </tr>
        </thead>
        <tbody>
            @foreach($incompleteCases as $index => $student)
            <tr>
                <td class="number">{{ $index + 1 }}</td>
                <td class="regno">{{ $student->regno }}</td>
                <td class="center">{{ $student->entryno ?? '-' }}</td>
                <td class="name">{{ $student->studname }}</td>
                <td class="center">{{ $student->gender ?? '-' }}</td>
                <td class="center">{{ $student->progid }}</td>
                <td class="failed">{{ $student->incomplete_courses ?? 'N/A' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @else
    <div class="no-data">No students with incomplete results</div>
    @endif

    <!-- Halted Cases -->
    <div class="section-header">
        HALTED
        <span class="count">{{ count($haltedCases) }} Students</span>
    </div>
    
    <div style="margin: 8px 0; font-size: 8pt; line-height: 1.4; text-align: justify;">
        The following students have been <strong>HALTED</strong> as their number of retake courses exceeds the maximum semester load.
    </div>
    
    <div class="criteria-box">
        <strong>Status:</strong> Students with retake courses exceeding maximum semester load (6 courses)
    </div>

    @if(count($haltedCases) > 0)
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 4%">#</th>
                <th style="width: 12%">REG. NO</th>
                <th style="width: 12%">ENTRY NO</th>
                <th style="width: 27%">STUDENT NAME</th>
                <th style="width: 7%">GENDER</th>
                <th style="width: 11%">PROGRAMME</th>
                <th style="width: 27%">FAILED COURSES</th>
            </tr>
        </thead>
        <tbody>
            @foreach($haltedCases as $index => $student)
            <tr>
                <td class="number">{{ $index + 1 }}</td>
                <td class="regno">{{ $student->regno }}</td>
                <td class="center">{{ $student->entryno ?? '-' }}</td>
                <td class="name">{{ $student->studname }}</td>
                <td class="center">{{ $student->gender ?? '-' }}</td>
                <td class="center">{{ $student->progid }}</td>
                <td class="failed">{{ $student->failed_courses }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @else
    <div class="no-data">No halted students</div>
    @endif

    <!-- Retake Cases -->
    <div class="section-header">
        RETAKE CASES (PASS DEGREE)
        <span class="count">{{ count($retakeCases) }} Students</span>
    </div>
    
    <div style="margin: 8px 0; font-size: 8pt; line-height: 1.4; text-align: justify;">
        The following candidates were recommended to <strong>RETAKE</strong> the papers indicated against their registration numbers when next offered, subject to the approval of the <strong>SENATE Examination Board</strong>.
    </div>
    
    <div class="criteria-box">
        <strong>Criteria:</strong> Students who failed one or more courses - Score < 50 (Undergraduate) or < 60 (Postgraduate)
    </div>

    @if(count($retakeCases) > 0)
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 4%">#</th>
                <th style="width: 12%">REG. NO</th>
                <th style="width: 12%">ENTRY NO</th>
                <th style="width: 27%">STUDENT NAME</th>
                <th style="width: 7%">GENDER</th>
                <th style="width: 11%">PROGRAMME</th>
                <th style="width: 27%">FAILED COURSES</th>
            </tr>
        </thead>
        <tbody>
            @foreach($retakeCases as $index => $student)
            <tr>
                <td class="number">{{ $index + 1 }}</td>
                <td class="regno">{{ $student->regno }}</td>
                <td class="center">{{ $student->entryno ?? '-' }}</td>
                <td class="name">{{ $student->studname }}</td>
                <td class="center">{{ $student->gender ?? '-' }}</td>
                <td class="center">{{ $student->progid }}</td>
                <td class="failed">{{ $student->failed_courses }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @else
    <div class="no-data">No students with failed courses</div>
    @endif

    <!-- Summary Footer -->
    <div class="summary-stats">
        <strong>OVERALL SUMMARY:</strong> 
        VC's List: <strong>{{ count($vcList) }}</strong> | 
        Dean's List: <strong>{{ count($deansList) }}</strong> | 
        Second Class Lower: <strong>{{ count($passCases) }}</strong> | 
        Incomplete: <strong>{{ count($incompleteCases) }}</strong> | 
        Halted: <strong>{{ count($haltedCases) }}</strong> | 
        Retake Cases: <strong>{{ count($retakeCases) }}</strong> | 
        TOTAL: <strong>{{ count($vcList) + count($deansList) + count($passCases) + count($incompleteCases) + count($haltedCases) + count($retakeCases) }}</strong>
    </div>
    
    <!-- Report Footer -->
    <div class="report-footer">
        <p><strong>{{ $institutionName }}</strong></p>
        <p>This is a computer-generated report. No signature required.</p>
        <p>© {{ date('Y') }} {{ $ent ? $ent->name : 'MUST' }}. All Rights Reserved.</p>
    </div>
</body>
</html>
