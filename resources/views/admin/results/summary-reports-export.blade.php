<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generate Summary Reports - {{ $export->export_name }}</title>
    <link rel="stylesheet" href="{{ admin_asset('vendor/laravel-admin/AdminLTE/bootstrap/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ admin_asset('vendor/laravel-admin/font-awesome/css/font-awesome.min.css') }}">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            background: #ecf0f5;
            font-family: 'Segoe UI', 'Source Sans Pro', Arial, sans-serif;
            color: #333;
            line-height: 1.5;
        }
        
        .page-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px 15px;
        }
        
        /* Header Section */
        .page-header {
            background: #fff;
            border-left: 4px solid #1a5490;
            padding: 15px 20px;
            margin-bottom: 20px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.08);
        }
        
        .page-header h1 {
            font-size: 22px;
            font-weight: 600;
            color: #1a5490;
            margin-bottom: 5px;
        }
        
        .page-header .export-title {
            font-size: 15px;
            color: #666;
            font-weight: 500;
        }
        
        /* Configuration Box */
        .config-box {
            background: #fff;
            border: 1px solid #ddd;
            padding: 15px 20px;
            margin-bottom: 20px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.06);
        }
        
        .config-box h3 {
            font-size: 14px;
            font-weight: 600;
            color: #333;
            margin-bottom: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .config-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 10px 20px;
        }
        
        .config-item {
            display: flex;
            align-items: center;
            font-size: 13px;
        }
        
        .config-item label {
            font-weight: 600;
            color: #555;
            min-width: 100px;
            margin-right: 8px;
        }
        
        .config-item .value {
            color: #1a5490;
            font-weight: 500;
        }
        
        /* Complete Report Button */
        .complete-report-section {
            background: linear-gradient(135deg, #1a5490 0%, #2672c4 100%);
            padding: 25px;
            margin-bottom: 25px;
            text-align: center;
            box-shadow: 0 2px 8px rgba(26,84,144,0.3);
            border-radius: 3px;
        }
        
        .complete-report-section .btn-complete {
            background: #fff;
            color: #1a5490;
            border: none;
            padding: 14px 35px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            box-shadow: 0 2px 5px rgba(0,0,0,0.15);
            border-radius: 3px;
        }
        
        .complete-report-section .btn-complete:hover {
            background: #f8f9fa;
            box-shadow: 0 3px 8px rgba(0,0,0,0.25);
            transform: translateY(-1px);
        }
        
        .complete-report-section .btn-complete i {
            margin-right: 8px;
        }
        
        .complete-report-section p {
            color: #fff;
            font-size: 13px;
            margin-top: 10px;
            opacity: 0.95;
        }
        
        /* Divider */
        .divider {
            text-align: center;
            margin: 25px 0;
            position: relative;
        }
        
        .divider::before,
        .divider::after {
            content: '';
            position: absolute;
            top: 50%;
            width: 45%;
            height: 1px;
            background: #ddd;
        }
        
        .divider::before {
            left: 0;
        }
        
        .divider::after {
            right: 0;
        }
        
        .divider span {
            background: #ecf0f5;
            padding: 0 15px;
            color: #888;
            font-size: 13px;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        /* Report Cards Grid */
        .reports-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .report-card {
            background: #fff;
            border: 1px solid #ddd;
            padding: 20px 15px;
            text-align: center;
            transition: all 0.2s ease;
            cursor: pointer;
            position: relative;
            overflow: hidden;
        }
        
        .report-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: currentColor;
        }
        
        .report-card:hover {
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
            transform: translateY(-3px);
            border-color: currentColor;
        }
        
        .report-card:active {
            transform: translateY(-1px);
            box-shadow: 0 2px 8px rgba(0,0,0,0.15);
        }
        
        .report-card.vc {
            color: #28a745;
        }
        
        .report-card.dean {
            color: #1a5490;
        }
        
        .report-card.pass {
            color: #fd7e14;
        }
        
        .report-card.retake {
            color: #dc3545;
        }
        
        .report-card .icon {
            font-size: 36px;
            margin-bottom: 12px;
            color: currentColor;
        }
        
        .report-card h3 {
            font-size: 16px;
            font-weight: 600;
            color: #333;
            margin-bottom: 6px;
        }
        
        .report-card .subtitle {
            font-size: 12px;
            color: #777;
            margin-bottom: 10px;
            min-height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .report-card .criteria {
            background: #f8f9fa;
            border: 1px solid #e9ecef;
            padding: 8px 10px;
            font-size: 11px;
            color: #666;
            margin-bottom: 12px;
            min-height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .report-card .btn-generate {
            background: currentColor;
            color: #fff;
            border: none;
            padding: 8px 20px;
            font-size: 13px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s ease;
            width: 100%;
        }
        
        .report-card .btn-generate:hover {
            opacity: 0.85;
            box-shadow: 0 3px 8px rgba(0,0,0,0.3);
            transform: scale(1.02);
        }
        
        .report-card .btn-generate:active {
            transform: scale(0.98);
        }
        
        .report-card .btn-generate i {
            margin-right: 5px;
        }
        
        /* Footer */
        .page-footer {
            text-align: center;
            padding: 15px;
            margin-top: 20px;
        }
        
        .page-footer .btn {
            background: #6c757d;
            color: #fff;
            border: none;
            padding: 8px 20px;
            font-size: 13px;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        
        .page-footer .btn:hover {
            background: #5a6268;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .config-grid {
                grid-template-columns: 1fr;
            }
            
            .reports-grid {
                grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            }
        }
    </style>
</head>
<body>
    <div class="page-container">
        <!-- Page Header -->
        <div class="page-header">
            <h1><i class="fa fa-file-pdf-o"></i> Generate Summary Reports</h1>
            <div class="export-title">{{ $export->export_name }}</div>
        </div>

        <!-- Configuration Box -->
        <div class="config-box">
            <h3><i class="fa fa-cog"></i> Export Configuration</h3>
            <div class="config-grid">
                <div class="config-item">
                    <label>Academic Year:</label>
                    <span class="value">{{ $export->academic_year }}</span>
                </div>
                <div class="config-item">
                    <label>Semester:</label>
                    <span class="value">{{ $export->semester }}</span>
                </div>
                <div class="config-item">
                    <label>Study Year:</label>
                    <span class="value">Year {{ $export->study_year }}</span>
                </div>
                <div class="config-item">
                    <label>Programme:</label>
                    <span class="value">{{ $export->programme->progname ?? 'All' }}</span>
                </div>
                @if($export->specialisation_id)
                <div class="config-item">
                    <label>Specialisation:</label>
                    <span class="value">
                        @php
                            $spec = \DB::table('acad_specialisation')->where('spec_id', $export->specialisation_id)->first();
                            echo $spec ? $spec->abbrev : $export->specialisation_id;
                        @endphp
                    </span>
                </div>
                @endif
                @if($export->start_range && $export->end_range)
                <div class="config-item">
                    <label>Student Range:</label>
                    <span class="value">{{ $export->start_range }} - {{ $export->end_range }}</span>
                </div>
                @endif
            </div>
        </div>

        <!-- Complete Summary Report Button -->
        <div class="complete-report-section">
            <button class="btn-complete" onclick="generateCompleteSummary()">
                <i class="fa fa-file-pdf-o"></i> Generate Complete Summary Report (All Lists)
            </button>
            <p>This will generate a single PDF containing all summary lists</p>
        </div>

        <!-- Divider -->
        <div class="divider">
            <span>Or Generate Individual Reports</span>
        </div>

        <!-- Individual Report Cards -->
        <div class="reports-grid">
            <!-- VC's List -->
            <div class="report-card vc" onclick="generateReport('vc-list')">
                <div class="icon"><i class="fa fa-trophy"></i></div>
                <h3>VC's List</h3>
                <div class="subtitle">Vice Chancellor's Honor List</div>
                <div class="criteria">CGPA: 4.40 - 5.00</div>
                <button class="btn-generate">
                    <i class="fa fa-download"></i> Generate PDF
                </button>
            </div>

            <!-- Dean's List -->
            <div class="report-card dean" onclick="generateReport('deans-list')">
                <div class="icon"><i class="fa fa-star"></i></div>
                <h3>Dean's List</h3>
                <div class="subtitle">Dean's Honor List</div>
                <div class="criteria">CGPA: 4.00 - 4.39</div>
                <button class="btn-generate">
                    <i class="fa fa-download"></i> Generate PDF
                </button>
            </div>

            <!-- Pass Cases -->
            <div class="report-card pass" onclick="generateReport('pass-cases')">
                <div class="icon"><i class="fa fa-check-circle"></i></div>
                <h3>Pass Cases</h3>
                <div class="subtitle">Normal Progress - All Passed</div>
                <div class="criteria">Score ≥ 50 (UG) / ≥ 60 (PG)</div>
                <button class="btn-generate">
                    <i class="fa fa-download"></i> Generate PDF
                </button>
            </div>

            <!-- Retake Cases -->
            <div class="report-card retake" onclick="generateReport('retake-cases')">
                <div class="icon"><i class="fa fa-repeat"></i></div>
                <h3>Retake Cases</h3>
                <div class="subtitle">Failed One or More Courses</div>
                <div class="criteria">Score < 50 (UG) / < 60 (PG)</div>
                <button class="btn-generate">
                    <i class="fa fa-download"></i> Generate PDF
                </button>
            </div>
        </div>

        <!-- Footer -->
        <div class="page-footer">
            <button class="btn" onclick="window.close();">
                <i class="fa fa-arrow-left"></i> Close Window
            </button>
        </div>
    </div>

    <script src="{{ admin_asset('vendor/laravel-admin/AdminLTE/plugins/jQuery/jquery-2.2.3.min.js') }}"></script>
    <script src="{{ admin_asset('vendor/laravel-admin/AdminLTE/bootstrap/js/bootstrap.min.js') }}"></script>
    <script>
        function generateCompleteSummary() {
            var url = '{{ admin_url("mru-academic-result-exports/{$export->id}/generate-complete-summary") }}';
            window.open(url, '_blank');
        }

        function generateReport(type) {
            var urls = {
                'vc-list': '{{ admin_url("mru-academic-result-exports/{$export->id}/generate-vc-list") }}',
                'deans-list': '{{ admin_url("mru-academic-result-exports/{$export->id}/generate-deans-list") }}',
                'pass-cases': '{{ admin_url("mru-academic-result-exports/{$export->id}/generate-pass-cases") }}',
                'retake-cases': '{{ admin_url("mru-academic-result-exports/{$export->id}/generate-retake-cases") }}'
            };
            
            window.open(urls[type], '_blank');
        }
    </script>
</body>
</html>
