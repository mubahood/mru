<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generate Summary Reports</title>
    <link rel="stylesheet" href="{{ admin_asset('vendor/laravel-admin/AdminLTE/bootstrap/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ admin_asset('vendor/laravel-admin/font-awesome/css/font-awesome.min.css') }}">
    <style>
        body {
            background: #f4f6f9;
            padding: 20px;
            font-family: 'Source Sans Pro', sans-serif;
        }
        .container {
            max-width: 900px;
            margin: 0 auto;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            padding: 20px;
            background: white;
            border-radius: 5px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .header h1 {
            color: #333;
            margin-bottom: 10px;
        }
        .filters-info {
            background: #e3f2fd;
            padding: 15px;
            border-radius: 5px;
            margin-top: 15px;
        }
        .filters-info strong {
            color: #1976d2;
        }
        .report-buttons {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-top: 30px;
        }
        .report-card {
            background: white;
            border-radius: 8px;
            padding: 30px 20px;
            text-align: center;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            cursor: pointer;
        }
        .report-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
        .report-card.vc {
            border-top: 4px solid #4caf50;
        }
        .report-card.dean {
            border-top: 4px solid #2196f3;
        }
        .report-card.pass {
            border-top: 4px solid #ff9800;
        }
        .report-card.retake {
            border-top: 4px solid #f44336;
        }
        .report-card i {
            font-size: 48px;
            margin-bottom: 15px;
        }
        .report-card.vc i { color: #4caf50; }
        .report-card.dean i { color: #2196f3; }
        .report-card.pass i { color: #ff9800; }
        .report-card.retake i { color: #f44336; }
        .report-card h3 {
            font-size: 20px;
            margin: 10px 0;
            color: #333;
        }
        .report-card p {
            color: #666;
            font-size: 14px;
            margin: 5px 0;
        }
        .report-card .criteria {
            background: #f5f5f5;
            padding: 8px;
            border-radius: 4px;
            margin-top: 10px;
            font-size: 13px;
            color: #555;
        }
        .btn-generate {
            margin-top: 15px;
            width: 100%;
        }
        .back-link {
            text-align: center;
            margin-top: 30px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><i class="fa fa-file-pdf-o"></i> Generate Summary Reports</h1>
            <p style="color: #666;">Select the type of report you want to generate</p>
            
            @if($acad || $semester || $progid || $studyyear)
            <div class="filters-info">
                <strong><i class="fa fa-filter"></i> Active Filters:</strong>
                @if($acad)
                    <span class="label label-info">Academic Year: {{ $acad }}</span>
                @endif
                @if($semester)
                    <span class="label label-info">Semester: {{ $semester }}</span>
                @endif
                @if($progid)
                    <span class="label label-info">Programme: {{ $progid }}</span>
                @endif
                @if($studyyear)
                    <span class="label label-info">Study Year: {{ $studyyear }}</span>
                @endif
            </div>
            @endif
        </div>

        <div class="report-buttons">
            <!-- VC's List -->
            <div class="report-card vc" onclick="generateReport('vc-list')">
                <i class="fa fa-trophy"></i>
                <h3>VC's List</h3>
                <p>Vice Chancellor's Honor List</p>
                <div class="criteria">CGPA: 4.40 - 5.00</div>
                <button class="btn btn-success btn-generate">
                    <i class="fa fa-download"></i> Generate PDF
                </button>
            </div>

            <!-- Dean's List -->
            <div class="report-card dean" onclick="generateReport('deans-list')">
                <i class="fa fa-star"></i>
                <h3>Dean's List</h3>
                <p>Dean's Honor List</p>
                <div class="criteria">CGPA: 4.00 - 4.39</div>
                <button class="btn btn-primary btn-generate">
                    <i class="fa fa-download"></i> Generate PDF
                </button>
            </div>

            <!-- Pass Cases -->
            <div class="report-card pass" onclick="generateReport('pass-cases')">
                <i class="fa fa-check-circle"></i>
                <h3>Pass Cases</h3>
                <p>Normal Progress - All Passed</p>
                <div class="criteria">Score ≥ 50 (UG) / ≥ 60 (PG)</div>
                <button class="btn btn-warning btn-generate">
                    <i class="fa fa-download"></i> Generate PDF
                </button>
            </div>

            <!-- Retake Cases -->
            <div class="report-card retake" onclick="generateReport('retake-cases')">
                <i class="fa fa-repeat"></i>
                <h3>Retake Cases</h3>
                <p>Failed One or More Courses</p>
                <div class="criteria">Score < 50 (UG) / < 60 (PG)</div>
                <button class="btn btn-danger btn-generate">
                    <i class="fa fa-download"></i> Generate PDF
                </button>
            </div>
        </div>

        <div class="back-link">
            <a href="javascript:window.close();" class="btn btn-default">
                <i class="fa fa-arrow-left"></i> Close Window
            </a>
        </div>
    </div>

    <script src="{{ admin_asset('vendor/laravel-admin/AdminLTE/plugins/jQuery/jquery-2.2.3.min.js') }}"></script>
    <script src="{{ admin_asset('vendor/laravel-admin/AdminLTE/bootstrap/js/bootstrap.min.js') }}"></script>
    <script>
        function generateReport(type) {
            var params = new URLSearchParams();
            
            @if($acad)
                params.append('acad', '{{ $acad }}');
            @endif
            @if($semester)
                params.append('semester', '{{ $semester }}');
            @endif
            @if($progid)
                params.append('progid', '{{ $progid }}');
            @endif
            @if($studyyear)
                params.append('studyyear', '{{ $studyyear }}');
            @endif
            
            var urls = {
                'vc-list': '{{ admin_url("mru-results/generate-vc-list") }}',
                'deans-list': '{{ admin_url("mru-results/generate-deans-list") }}',
                'pass-cases': '{{ admin_url("mru-results/generate-pass-cases") }}',
                'retake-cases': '{{ admin_url("mru-results/generate-retake-cases") }}'
            };
            
            var url = urls[type] + '?' + params.toString();
            window.open(url, '_blank');
        }
    </script>
</body>
</html>
