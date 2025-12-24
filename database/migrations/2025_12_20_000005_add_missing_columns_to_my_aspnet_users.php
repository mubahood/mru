<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMissingColumnsToMyAspnetUsers extends Migration
{
    /**
     * Run the migrations.
     * 
     * Add all necessary columns from admin_users to my_aspnet_users
     * to ensure full compatibility with Administrator model.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('my_aspnet_users', function (Blueprint $table) {
            // Core user fields
            if (!Schema::hasColumn('my_aspnet_users', 'username')) {
                $table->string('username')->nullable()->after('name');
            }
            if (!Schema::hasColumn('my_aspnet_users', 'password')) {
                $table->string('password')->nullable()->after('username');
            }
            if (!Schema::hasColumn('my_aspnet_users', 'avatar')) {
                $table->string('avatar')->nullable()->after('email');
            }
            if (!Schema::hasColumn('my_aspnet_users', 'status')) {
                $table->tinyInteger('status')->default(1)->after('user_type');
            }
            
            // Personal information
            if (!Schema::hasColumn('my_aspnet_users', 'first_name')) {
                $table->string('first_name')->nullable()->after('name');
            }
            if (!Schema::hasColumn('my_aspnet_users', 'last_name')) {
                $table->string('last_name')->nullable()->after('first_name');
            }
            if (!Schema::hasColumn('my_aspnet_users', 'given_name')) {
                $table->string('given_name')->nullable()->after('last_name');
            }
            if (!Schema::hasColumn('my_aspnet_users', 'date_of_birth')) {
                $table->date('date_of_birth')->nullable();
            }
            if (!Schema::hasColumn('my_aspnet_users', 'place_of_birth')) {
                $table->string('place_of_birth')->nullable();
            }
            if (!Schema::hasColumn('my_aspnet_users', 'sex')) {
                $table->string('sex', 10)->nullable();
            }
            if (!Schema::hasColumn('my_aspnet_users', 'nationality')) {
                $table->string('nationality')->nullable();
            }
            if (!Schema::hasColumn('my_aspnet_users', 'religion')) {
                $table->string('religion')->nullable();
            }
            if (!Schema::hasColumn('my_aspnet_users', 'marital_status')) {
                $table->string('marital_status')->nullable();
            }
            
            // Contact information
            if (!Schema::hasColumn('my_aspnet_users', 'home_address')) {
                $table->text('home_address')->nullable();
            }
            if (!Schema::hasColumn('my_aspnet_users', 'current_address')) {
                $table->text('current_address')->nullable();
            }
            if (!Schema::hasColumn('my_aspnet_users', 'residence')) {
                $table->string('residence')->nullable();
            }
            if (!Schema::hasColumn('my_aspnet_users', 'phone_number_1')) {
                $table->string('phone_number_1')->nullable();
            }
            if (!Schema::hasColumn('my_aspnet_users', 'phone_number_2')) {
                $table->string('phone_number_2')->nullable();
            }
            
            // Family information
            if (!Schema::hasColumn('my_aspnet_users', 'spouse_name')) {
                $table->string('spouse_name')->nullable();
            }
            if (!Schema::hasColumn('my_aspnet_users', 'spouse_phone')) {
                $table->string('spouse_phone')->nullable();
            }
            if (!Schema::hasColumn('my_aspnet_users', 'father_name')) {
                $table->string('father_name')->nullable();
            }
            if (!Schema::hasColumn('my_aspnet_users', 'father_phone')) {
                $table->string('father_phone')->nullable();
            }
            if (!Schema::hasColumn('my_aspnet_users', 'mother_name')) {
                $table->string('mother_name')->nullable();
            }
            if (!Schema::hasColumn('my_aspnet_users', 'mother_phone')) {
                $table->string('mother_phone')->nullable();
            }
            if (!Schema::hasColumn('my_aspnet_users', 'emergency_person_name')) {
                $table->string('emergency_person_name')->nullable();
            }
            if (!Schema::hasColumn('my_aspnet_users', 'emergency_person_phone')) {
                $table->string('emergency_person_phone')->nullable();
            }
            if (!Schema::hasColumn('my_aspnet_users', 'parent_id')) {
                $table->unsignedBigInteger('parent_id')->nullable();
            }
            
            // Identification documents
            if (!Schema::hasColumn('my_aspnet_users', 'national_id_number')) {
                $table->string('national_id_number')->nullable();
            }
            if (!Schema::hasColumn('my_aspnet_users', 'passport_number')) {
                $table->string('passport_number')->nullable();
            }
            if (!Schema::hasColumn('my_aspnet_users', 'tin')) {
                $table->string('tin')->nullable();
            }
            if (!Schema::hasColumn('my_aspnet_users', 'nssf_number')) {
                $table->string('nssf_number')->nullable();
            }
            
            // Banking information
            if (!Schema::hasColumn('my_aspnet_users', 'bank_name')) {
                $table->string('bank_name')->nullable();
            }
            if (!Schema::hasColumn('my_aspnet_users', 'bank_account_number')) {
                $table->string('bank_account_number')->nullable();
            }
            
            // Educational background
            if (!Schema::hasColumn('my_aspnet_users', 'primary_school_name')) {
                $table->string('primary_school_name')->nullable();
            }
            if (!Schema::hasColumn('my_aspnet_users', 'primary_school_year_graduated')) {
                $table->string('primary_school_year_graduated')->nullable();
            }
            if (!Schema::hasColumn('my_aspnet_users', 'seconday_school_name')) {
                $table->string('seconday_school_name')->nullable();
            }
            if (!Schema::hasColumn('my_aspnet_users', 'seconday_school_year_graduated')) {
                $table->string('seconday_school_year_graduated')->nullable();
            }
            if (!Schema::hasColumn('my_aspnet_users', 'high_school_name')) {
                $table->string('high_school_name')->nullable();
            }
            if (!Schema::hasColumn('my_aspnet_users', 'high_school_year_graduated')) {
                $table->string('high_school_year_graduated')->nullable();
            }
            if (!Schema::hasColumn('my_aspnet_users', 'certificate_school_name')) {
                $table->string('certificate_school_name')->nullable();
            }
            if (!Schema::hasColumn('my_aspnet_users', 'certificate_year_graduated')) {
                $table->string('certificate_year_graduated')->nullable();
            }
            if (!Schema::hasColumn('my_aspnet_users', 'diploma_school_name')) {
                $table->string('diploma_school_name')->nullable();
            }
            if (!Schema::hasColumn('my_aspnet_users', 'diploma_year_graduated')) {
                $table->string('diploma_year_graduated')->nullable();
            }
            if (!Schema::hasColumn('my_aspnet_users', 'degree_university_name')) {
                $table->string('degree_university_name')->nullable();
            }
            if (!Schema::hasColumn('my_aspnet_users', 'degree_university_year_graduated')) {
                $table->string('degree_university_year_graduated')->nullable();
            }
            if (!Schema::hasColumn('my_aspnet_users', 'masters_university_name')) {
                $table->string('masters_university_name')->nullable();
            }
            if (!Schema::hasColumn('my_aspnet_users', 'masters_university_year_graduated')) {
                $table->string('masters_university_year_graduated')->nullable();
            }
            if (!Schema::hasColumn('my_aspnet_users', 'phd_university_name')) {
                $table->string('phd_university_name')->nullable();
            }
            if (!Schema::hasColumn('my_aspnet_users', 'phd_university_year_graduated')) {
                $table->string('phd_university_year_graduated')->nullable();
            }
            
            // Academic fields
            if (!Schema::hasColumn('my_aspnet_users', 'current_class_id')) {
                $table->unsignedBigInteger('current_class_id')->nullable();
            }
            if (!Schema::hasColumn('my_aspnet_users', 'current_theology_class_id')) {
                $table->unsignedBigInteger('current_theology_class_id')->nullable();
            }
            if (!Schema::hasColumn('my_aspnet_users', 'stream_id')) {
                $table->unsignedBigInteger('stream_id')->nullable();
            }
            if (!Schema::hasColumn('my_aspnet_users', 'theology_stream_id')) {
                $table->unsignedBigInteger('theology_stream_id')->nullable();
            }
            
            // School payment integration
            if (!Schema::hasColumn('my_aspnet_users', 'school_pay_account_id')) {
                $table->string('school_pay_account_id')->nullable();
            }
            if (!Schema::hasColumn('my_aspnet_users', 'school_pay_payment_code')) {
                $table->string('school_pay_payment_code')->nullable();
            }
            if (!Schema::hasColumn('my_aspnet_users', 'pegpay_code')) {
                $table->string('pegpay_code')->nullable();
            }
            
            // System fields
            if (!Schema::hasColumn('my_aspnet_users', 'languages')) {
                $table->text('languages')->nullable();
            }
            if (!Schema::hasColumn('my_aspnet_users', 'demo_id')) {
                $table->string('demo_id')->nullable();
            }
            if (!Schema::hasColumn('my_aspnet_users', 'user_id')) {
                $table->unsignedBigInteger('user_id')->nullable();
            }
            if (!Schema::hasColumn('my_aspnet_users', 'user_batch_importer_id')) {
                $table->unsignedBigInteger('user_batch_importer_id')->nullable();
            }
            if (!Schema::hasColumn('my_aspnet_users', 'deleted_at')) {
                $table->timestamp('deleted_at')->nullable();
            }
            if (!Schema::hasColumn('my_aspnet_users', 'verification')) {
                $table->string('verification')->nullable();
            }
            if (!Schema::hasColumn('my_aspnet_users', 'main_role_id')) {
                $table->unsignedBigInteger('main_role_id')->nullable();
            }
            if (!Schema::hasColumn('my_aspnet_users', 'account_id')) {
                $table->unsignedBigInteger('account_id')->nullable();
            }
            
            // Profile completion flags
            if (!Schema::hasColumn('my_aspnet_users', 'has_personal_info')) {
                $table->string('has_personal_info', 10)->default('No');
            }
            if (!Schema::hasColumn('my_aspnet_users', 'has_educational_info')) {
                $table->string('has_educational_info', 10)->default('No');
            }
            if (!Schema::hasColumn('my_aspnet_users', 'has_account_info')) {
                $table->string('has_account_info', 10)->default('No');
            }
            
            // Additional fields
            if (!Schema::hasColumn('my_aspnet_users', 'lin')) {
                $table->string('lin')->nullable();
            }
            if (!Schema::hasColumn('my_aspnet_users', 'occupation')) {
                $table->string('occupation')->nullable();
            }
            if (!Schema::hasColumn('my_aspnet_users', 'last_seen')) {
                $table->timestamp('last_seen')->nullable();
            }
            if (!Schema::hasColumn('my_aspnet_users', 'supervisor_id')) {
                $table->unsignedBigInteger('supervisor_id')->nullable();
            }
            if (!Schema::hasColumn('my_aspnet_users', 'user_number')) {
                $table->string('user_number')->nullable();
            }
            if (!Schema::hasColumn('my_aspnet_users', 'token')) {
                $table->text('token')->nullable();
            }
            if (!Schema::hasColumn('my_aspnet_users', 'roles_text')) {
                $table->text('roles_text')->nullable();
            }
            if (!Schema::hasColumn('my_aspnet_users', 'plain_password')) {
                $table->string('plain_password')->nullable();
            }
            if (!Schema::hasColumn('my_aspnet_users', 'mail_verification_token')) {
                $table->string('mail_verification_token')->nullable();
            }
            if (!Schema::hasColumn('my_aspnet_users', 'sign')) {
                $table->text('sign')->nullable();
            }
            if (!Schema::hasColumn('my_aspnet_users', 'is_enrolled')) {
                $table->string('is_enrolled', 10)->default('No');
            }
        });
        
        // Add indexes for frequently queried columns
        Schema::table('my_aspnet_users', function (Blueprint $table) {
            if (!Schema::hasColumn('my_aspnet_users', 'status')) {
                return;
            }
            
            try {
                $sm = Schema::getConnection()->getDoctrineSchemaManager();
                $indexesFound = $sm->listTableIndexes('my_aspnet_users');
                
                if (!isset($indexesFound['idx_my_aspnet_users_status'])) {
                    $table->index('status', 'idx_my_aspnet_users_status');
                }
                if (!isset($indexesFound['idx_my_aspnet_users_username'])) {
                    $table->index('username', 'idx_my_aspnet_users_username');
                }
                if (!isset($indexesFound['idx_my_aspnet_users_current_class'])) {
                    $table->index('current_class_id', 'idx_my_aspnet_users_current_class');
                }
                if (!isset($indexesFound['idx_my_aspnet_users_parent'])) {
                    $table->index('parent_id', 'idx_my_aspnet_users_parent');
                }
                if (!isset($indexesFound['idx_my_aspnet_users_phone1'])) {
                    $table->index('phone_number_1', 'idx_my_aspnet_users_phone1');
                }
            } catch (\Exception $e) {
                // Indexes might already exist, continue
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('my_aspnet_users', function (Blueprint $table) {
            $columns = [
                'username', 'password', 'avatar', 'status',
                'first_name', 'last_name', 'given_name', 'date_of_birth', 'place_of_birth',
                'sex', 'nationality', 'religion', 'marital_status',
                'home_address', 'current_address', 'residence',
                'phone_number_1', 'phone_number_2',
                'spouse_name', 'spouse_phone', 'father_name', 'father_phone',
                'mother_name', 'mother_phone', 'emergency_person_name', 'emergency_person_phone',
                'parent_id', 'national_id_number', 'passport_number', 'tin', 'nssf_number',
                'bank_name', 'bank_account_number',
                'primary_school_name', 'primary_school_year_graduated',
                'seconday_school_name', 'seconday_school_year_graduated',
                'high_school_name', 'high_school_year_graduated',
                'certificate_school_name', 'certificate_year_graduated',
                'diploma_school_name', 'diploma_year_graduated',
                'degree_university_name', 'degree_university_year_graduated',
                'masters_university_name', 'masters_university_year_graduated',
                'phd_university_name', 'phd_university_year_graduated',
                'current_class_id', 'current_theology_class_id', 'stream_id', 'theology_stream_id',
                'school_pay_account_id', 'school_pay_payment_code', 'pegpay_code',
                'languages', 'demo_id', 'user_id', 'user_batch_importer_id',
                'deleted_at', 'verification', 'main_role_id', 'account_id',
                'has_personal_info', 'has_educational_info', 'has_account_info',
                'lin', 'occupation', 'last_seen', 'supervisor_id', 'user_number',
                'token', 'roles_text', 'plain_password', 'mail_verification_token',
                'sign', 'is_enrolled'
            ];
            
            foreach ($columns as $column) {
                if (Schema::hasColumn('my_aspnet_users', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
}
