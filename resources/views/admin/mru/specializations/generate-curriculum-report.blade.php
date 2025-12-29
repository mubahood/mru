<div class="container-fluid" style="padding: 20px;">
    <div class="card">
        <div class="card-header bg-success text-white">
            <h3 class="card-title">
                <i class="fa fa-check-circle"></i> Curriculum Generation Report
            </h3>
        </div>
        <div class="card-body">
            <!-- Specialization Info -->
            <div class="alert alert-info">
                <h4><i class="fa fa-graduation-cap"></i> Specialization Details</h4>
                <p><strong>ID:</strong> {{ $specialization->spec_id }}</p>
                <p><strong>Name:</strong> {{ $specialization->spec }}</p>
                <p><strong>Programme:</strong> {{ $specialization->prog_id }} 
                    @if($specialization->programme)
                        - {{ $specialization->programme->progname }}
                    @endif
                </p>
            </div>

            <!-- Summary Statistics -->
            <div class="row" style="margin-bottom: 30px;">
                <div class="col-md-3">
                    <div class="small-box bg-aqua">
                        <div class="inner">
                            <h3>{{ $total_processed }}</h3>
                            <p>Total Courses Processed</p>
                        </div>
                        <div class="icon">
                            <i class="fa fa-book"></i>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="small-box bg-green">
                        <div class="inner">
                            <h3>{{ $total_created }}</h3>
                            <p>New Records Created</p>
                        </div>
                        <div class="icon">
                            <i class="fa fa-plus-circle"></i>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="small-box bg-yellow">
                        <div class="inner">
                            <h3>{{ $total_existing }}</h3>
                            <p>Already Existing</p>
                        </div>
                        <div class="icon">
                            <i class="fa fa-check"></i>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="small-box bg-red">
                        <div class="inner">
                            <h3>{{ $total_skipped + $total_errors }}</h3>
                            <p>Skipped/Errors</p>
                        </div>
                        <div class="icon">
                            <i class="fa fa-exclamation-triangle"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Created Records -->
            @if(count($created) > 0)
                <div class="card card-success">
                    <div class="card-header">
                        <h4 class="card-title">
                            <i class="fa fa-plus-circle"></i> Newly Created Records ({{ $total_created }})
                        </h4>
                    </div>
                    <div class="card-body" style="padding: 0;">
                        <table class="table table-hover table-bordered" style="margin: 0;">
                            <thead class="bg-success">
                                <tr>
                                    <th>Course Code</th>
                                    <th>Course Name</th>
                                    <th style="text-align: center;">Year</th>
                                    <th style="text-align: center;">Semester</th>
                                    <th style="text-align: center;">Credits</th>
                                    <th style="text-align: center;">Type</th>
                                    <th style="text-align: center;">Status</th>
                                    <th style="text-align: center;">Approval</th>
                                    <th style="text-align: center;">Students</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($created as $record)
                                    <tr>
                                        <td><strong>{{ $record['course_code'] }}</strong></td>
                                        <td>{{ $record['course_name'] }}</td>
                                        <td style="text-align: center;">Year {{ $record['year'] }}</td>
                                        <td style="text-align: center;">Semester {{ $record['semester'] }}</td>
                                        <td style="text-align: center;">{{ $record['credits'] }}</td>
                                        <td style="text-align: center;">
                                            <span class="badge badge-info">{{ ucfirst($record['type']) }}</span>
                                        </td>
                                        <td style="text-align: center;">
                                            <span class="badge badge-success">{{ ucfirst($record['status']) }}</span>
                                        </td>
                                        <td style="text-align: center;">
                                            <span class="badge badge-warning">{{ ucfirst($record['approval_status']) }}</span>
                                        </td>
                                        <td style="text-align: center;">{{ $record['student_count'] ?? 0 }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

            <!-- Existing Records -->
            @if(count($existing) > 0)
                <div class="card card-warning">
                    <div class="card-header">
                        <h4 class="card-title">
                            <i class="fa fa-check"></i> Already Existing Records ({{ $total_existing }})
                        </h4>
                    </div>
                    <div class="card-body" style="padding: 0;">
                        <table class="table table-hover table-bordered" style="margin: 0;">
                            <thead class="bg-warning">
                                <tr>
                                    <th>Course Code</th>
                                    <th>Course Name</th>
                                    <th style="text-align: center;">Year</th>
                                    <th style="text-align: center;">Semester</th>
                                    <th style="text-align: center;">Status</th>
                                    <th style="text-align: center;">Approval</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($existing as $record)
                                    <tr>
                                        <td><strong>{{ $record['course_code'] }}</strong></td>
                                        <td>{{ $record['course_name'] }}</td>
                                        <td style="text-align: center;">Year {{ $record['year'] }}</td>
                                        <td style="text-align: center;">Semester {{ $record['semester'] }}</td>
                                        <td style="text-align: center;">
                                            <span class="badge badge-{{ $record['status'] === 'active' ? 'success' : 'secondary' }}">
                                                {{ ucfirst($record['status']) }}
                                            </span>
                                        </td>
                                        <td style="text-align: center;">
                                            <span class="badge badge-{{ $record['approval_status'] === 'approved' ? 'success' : ($record['approval_status'] === 'pending' ? 'warning' : 'danger') }}">
                                                {{ ucfirst($record['approval_status']) }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

            <!-- Skipped Records -->
            @if(count($skipped) > 0)
                <div class="card card-default">
                    <div class="card-header">
                        <h4 class="card-title">
                            <i class="fa fa-times-circle"></i> Skipped Records ({{ $total_skipped }})
                        </h4>
                    </div>
                    <div class="card-body" style="padding: 0;">
                        <table class="table table-hover table-bordered" style="margin: 0;">
                            <thead class="bg-gray">
                                <tr>
                                    <th>Course Code</th>
                                    <th style="text-align: center;">Year</th>
                                    <th style="text-align: center;">Semester</th>
                                    <th>Reason</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($skipped as $record)
                                    <tr>
                                        <td><strong>{{ $record['course_code'] }}</strong></td>
                                        <td style="text-align: center;">Year {{ $record['year'] }}</td>
                                        <td style="text-align: center;">Semester {{ $record['semester'] }}</td>
                                        <td>{{ $record['reason'] }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

            <!-- Errors -->
            @if(count($errors) > 0)
                <div class="card card-danger">
                    <div class="card-header">
                        <h4 class="card-title">
                            <i class="fa fa-exclamation-circle"></i> Errors ({{ $total_errors }})
                        </h4>
                    </div>
                    <div class="card-body" style="padding: 0;">
                        <table class="table table-hover table-bordered" style="margin: 0;">
                            <thead class="bg-danger">
                                <tr>
                                    <th>Course Code</th>
                                    <th style="text-align: center;">Year</th>
                                    <th style="text-align: center;">Semester</th>
                                    <th>Error Message</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($errors as $record)
                                    <tr>
                                        <td><strong>{{ $record['course_code'] }}</strong></td>
                                        <td style="text-align: center;">{{ $record['year'] }}</td>
                                        <td style="text-align: center;">{{ $record['semester'] }}</td>
                                        <td><code>{{ $record['error'] }}</code></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

            <!-- Action Buttons -->
            <div class="text-center" style="margin-top: 30px;">
                <a href="{{ admin_url('mru-specialisations') }}" class="btn btn-primary btn-lg">
                    <i class="fa fa-arrow-left"></i> Back to Specializations
                </a>
                <a href="{{ admin_url('mru-specialization-courses') }}" class="btn btn-success btn-lg" style="margin-left: 10px;">
                    <i class="fa fa-list"></i> View Specialization Courses
                </a>
            </div>
        </div>
    </div>
</div>

<style>
    .small-box {
        border-radius: 5px;
        position: relative;
        display: block;
        margin-bottom: 20px;
        box-shadow: 0 1px 1px rgba(0,0,0,0.1);
    }
    .small-box .inner {
        padding: 10px;
    }
    .small-box .inner h3 {
        font-size: 38px;
        font-weight: bold;
        margin: 0 0 10px 0;
        white-space: nowrap;
        padding: 0;
    }
    .small-box .inner p {
        font-size: 15px;
        margin: 0;
    }
    .small-box .icon {
        position: absolute;
        top: 10px;
        right: 10px;
        z-index: 0;
        font-size: 70px;
        color: rgba(0,0,0,0.15);
    }
    .bg-aqua {
        background-color: #00c0ef !important;
        color: #fff;
    }
    .bg-green {
        background-color: #00a65a !important;
        color: #fff;
    }
    .bg-yellow {
        background-color: #f39c12 !important;
        color: #fff;
    }
    .bg-red {
        background-color: #dd4b39 !important;
        color: #fff;
    }
    .card {
        box-shadow: 0 1px 3px rgba(0,0,0,0.12), 0 1px 2px rgba(0,0,0,0.24);
        margin-bottom: 30px;
    }
    .card-header {
        padding: 15px;
        border-bottom: 1px solid #f4f4f4;
    }
    .table {
        font-size: 14px;
    }
    .table thead th {
        color: #fff;
        font-weight: bold;
    }
</style>
