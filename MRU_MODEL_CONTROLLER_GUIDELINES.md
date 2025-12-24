# MRU Model & Controller Development Guidelines

**Version:** 1.0  
**Date:** December 21, 2025  
**Purpose:** Comprehensive guidelines for creating perfect Laravel models and Laravel Admin controllers in the MRU system

---

## Table of Contents
1. [Naming Conventions](#naming-conventions)
2. [Model Structure](#model-structure)
3. [Controller Structure](#controller-structure)
4. [Code Quality Standards](#code-quality-standards)
5. [Testing Requirements](#testing-requirements)
6. [Complete Examples](#complete-examples)

---

## 1. Naming Conventions

### Model Naming
- **Prefix:** All MRU-specific models MUST be prefixed with `Mru`
- **Format:** `Mru{EntityName}` in PascalCase
- **Examples:**
  - `MruResult` for results table
  - `MruCourse` for courses table
  - `MruStudent` for students table
  - `MruEnrollment` for enrollments table

### Controller Naming
- **Format:** `Mru{EntityName}Controller`
- **Examples:**
  - `MruResultController`
  - `MruCourseController`
  - `MruStudentController`

### Table Naming
- Keep existing table names as-is
- Models map to existing database tables
- **Examples:**
  - `MruResult` → `acad_results`
  - `MruCourse` → `acad_course`

### File Locations
```
app/
├── Models/
│   ├── MruResult.php
│   ├── MruCourse.php
│   └── MruStudent.php
└── Admin/
    └── Controllers/
        ├── MruResultController.php
        ├── MruCourseController.php
        └── MruStudentController.php
```

---

## 2. Model Structure

### Complete Model Template

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

/**
 * Mru{EntityName} Model
 * 
 * Brief description of what this model represents.
 * 
 * @property int $id Primary key
 * @property string $field_name Description
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * 
 * @method static Builder scopeName($param) Scope description
 */
class Mru{EntityName} extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'actual_table_name';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'id'; // or custom key

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = true; // false for non-numeric keys

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = true; // false if no created_at/updated_at

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'field1',
        'field2',
        'field3',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'id' => 'integer',
        'is_active' => 'boolean',
        'amount' => 'float',
        'data' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Constants for status/type values
     */
    const STATUS_ACTIVE = 'active';
    const STATUS_INACTIVE = 'inactive';
    
    const TYPE_ONE = 1;
    const TYPE_TWO = 2;

    // Relationships
    // Scopes
    // Accessors
    // Mutators
    // Public Methods
    // Static Methods
}
```

### 2.1 Database Configuration

#### ✅ ALWAYS Specify
```php
protected $table = 'actual_table_name';        // Required
protected $primaryKey = 'id';                  // Required if not 'id'
public $incrementing = false;                  // Required if non-numeric PK
public $timestamps = false;                    // Required if no timestamps
```

#### ✅ Check and Document
- Verify table exists: `DESCRIBE table_name;`
- Check primary key: `SHOW KEYS FROM table_name WHERE Key_name = 'PRIMARY';`
- Document column types and constraints
- Note foreign key relationships

### 2.2 Fillable Fields

#### ✅ Best Practices
```php
protected $fillable = [
    // List ALL fields that can be mass-assigned
    'field1',
    'field2',
    'field3',
];
```

#### ❌ Avoid
```php
protected $guarded = []; // Too permissive, security risk
```

#### 📝 Guidelines
- Include ALL user-editable fields
- Exclude: `id`, `created_at`, `updated_at`, `deleted_at`
- Exclude: sensitive fields like passwords (use $hidden instead)
- Document each field in class docblock

### 2.3 Type Casting

#### ✅ Required Casts
```php
protected $casts = [
    // Always cast these types:
    'id' => 'integer',
    'user_id' => 'integer',
    'is_active' => 'boolean',
    'is_published' => 'boolean',
    'price' => 'float',
    'score' => 'float',
    'quantity' => 'integer',
    'settings' => 'array',     // for JSON columns
    'metadata' => 'json',      // for JSON columns
    'published_at' => 'datetime',
    'expires_at' => 'datetime',
];
```

#### 📝 Cast Types Reference
- `integer` - for int/bigint columns
- `float` / `double` - for decimal/float columns
- `boolean` - for tinyint(1) columns
- `string` - for varchar/text (default)
- `array` - for JSON (converts to PHP array)
- `json` - for JSON (keeps as JSON)
- `datetime` - for timestamp/datetime columns
- `date` - for date-only columns

### 2.4 Relationships

#### BelongsTo Example
```php
/**
 * Get the user who owns this record.
 * 
 * @return BelongsTo
 */
public function user(): BelongsTo
{
    return $this->belongsTo(User::class, 'user_id', 'id');
    // Parameters: RelatedModel, foreignKey, ownerKey
}
```

#### HasMany Example
```php
/**
 * Get all comments for this post.
 * 
 * @return HasMany
 */
public function comments(): HasMany
{
    return $this->hasMany(Comment::class, 'post_id', 'id');
    // Parameters: RelatedModel, foreignKey, localKey
}
```

#### ⚠️ Important Notes
- **ALWAYS verify foreign key columns exist**
- For MRU system: User table uses `username` field, not `regno`
- Example: `belongsTo(User::class, 'regno', 'username')`
- Test relationships immediately after creation

### 2.5 Query Scopes

#### ✅ Scope Template
```php
/**
 * Scope: Filter by status
 *
 * @param Builder $query
 * @param string $status
 * @return Builder
 */
public function scopeByStatus(Builder $query, string $status): Builder
{
    return $query->where('status', $status);
}

// Usage: Model::byStatus('active')->get();
```

#### 📝 Common Useful Scopes
```php
// Active/Inactive
public function scopeActive(Builder $query): Builder
{
    return $query->where('is_active', true);
}

// Date ranges
public function scopeCreatedBetween(Builder $query, $start, $end): Builder
{
    return $query->whereBetween('created_at', [$start, $end]);
}

// Search
public function scopeSearch(Builder $query, string $term): Builder
{
    return $query->where('name', 'LIKE', "%{$term}%");
}

// Related filters
public function scopeForUser(Builder $query, int $userId): Builder
{
    return $query->where('user_id', $userId);
}
```

### 2.6 Accessors

#### ✅ Accessor Template
```php
/**
 * Accessor: Get formatted name
 *
 * @return string
 */
public function getFormattedNameAttribute(): string
{
    return ucwords($this->name);
}

// Usage: $model->formatted_name
```

#### 📝 Common Accessor Patterns
```php
// Boolean checks
public function getIsPublishedAttribute(): bool
{
    return $this->status === self::STATUS_PUBLISHED;
}

// Formatted values
public function getFormattedPriceAttribute(): string
{
    return '$' . number_format($this->price, 2);
}

// Combined fields
public function getFullNameAttribute(): string
{
    return "{$this->first_name} {$this->last_name}";
}

// Status labels
public function getStatusLabelAttribute(): string
{
    return match($this->status) {
        self::STATUS_ACTIVE => 'Active',
        self::STATUS_INACTIVE => 'Inactive',
        default => 'Unknown'
    };
}
```

### 2.7 Mutators

#### ✅ Mutator Template
```php
/**
 * Mutator: Ensure name is capitalized
 *
 * @param string $value
 * @return void
 */
public function setNameAttribute($value): void
{
    $this->attributes['name'] = ucwords(trim($value));
}
```

#### 📝 Common Mutator Patterns
```php
// Trimming
public function setEmailAttribute($value): void
{
    $this->attributes['email'] = strtolower(trim($value));
}

// Validation
public function setScoreAttribute($value): void
{
    $this->attributes['score'] = max(0, min(100, $value));
}

// Formatting
public function setPhoneAttribute($value): void
{
    $this->attributes['phone'] = preg_replace('/[^0-9]/', '', $value);
}

// Normalization
public function setStatusAttribute($value): void
{
    $this->attributes['status'] = strtolower($value);
}
```

### 2.8 Constants

#### ✅ Constants Template
```php
/**
 * Status constants
 */
const STATUS_PENDING = 'pending';
const STATUS_APPROVED = 'approved';
const STATUS_REJECTED = 'rejected';

/**
 * Array of all statuses
 */
const STATUSES = [
    self::STATUS_PENDING,
    self::STATUS_APPROVED,
    self::STATUS_REJECTED,
];

/**
 * Status labels for display
 */
const STATUS_LABELS = [
    self::STATUS_PENDING => 'Pending Review',
    self::STATUS_APPROVED => 'Approved',
    self::STATUS_REJECTED => 'Rejected',
];
```

### 2.9 Public Methods

#### ✅ Method Template
```php
/**
 * Calculate total amount with tax
 *
 * @param float $taxRate Tax rate as decimal (e.g., 0.18 for 18%)
 * @return float
 */
public function calculateTotal(float $taxRate = 0.18): float
{
    $subtotal = $this->quantity * $this->price;
    return round($subtotal * (1 + $taxRate), 2);
}
```

#### 📝 Common Method Patterns
```php
// Calculations
public function calculateDiscount(): float
{
    return $this->price * ($this->discount_percentage / 100);
}

// Checks
public function canBeEdited(): bool
{
    return $this->status === self::STATUS_DRAFT;
}

// Actions
public function activate(): bool
{
    $this->status = self::STATUS_ACTIVE;
    return $this->save();
}

// Data retrieval
public function getRelatedCount(): int
{
    return $this->relatedItems()->count();
}
```

### 2.10 Static Methods

#### ✅ Static Method Template
```php
/**
 * Get all active records
 *
 * @return \Illuminate\Database\Eloquent\Collection
 */
public static function getActive()
{
    return self::active()->get();
}

/**
 * Calculate statistics for a date range
 *
 * @param string $startDate
 * @param string $endDate
 * @return array
 */
public static function getStatistics(string $startDate, string $endDate): array
{
    $records = self::whereBetween('created_at', [$startDate, $endDate])->get();
    
    return [
        'total' => $records->count(),
        'average' => $records->avg('amount'),
        'sum' => $records->sum('amount'),
    ];
}
```

---

## 3. Controller Structure

### Complete Controller Template

```php
<?php

namespace App\Admin\Controllers;

use App\Models\Mru{EntityName};
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

/**
 * Mru{EntityName}Controller
 * 
 * Laravel Admin controller for managing {entity description}.
 * 
 * @package App\Admin\Controllers
 */
class Mru{EntityName}Controller extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '{Entity Display Name}';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Mru{EntityName}());

        // Configure grid
        $grid->model()->orderBy('id', 'desc');
        
        // Define columns
        // Add filters
        // Add actions
        
        return $grid;
    }

    /**
     * Make a show builder.
     *
     * @param mixed $id
     * @return Show
     */
    protected function detail($id)
    {
        $show = new Show(Mru{EntityName}::findOrFail($id));

        // Define fields
        
        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new Mru{EntityName}());

        // Define form fields
        // Add validation
        // Add callbacks
        
        return $form;
    }
}
```

### 3.1 Grid Configuration

#### ✅ Essential Grid Setup
```php
protected function grid()
{
    $grid = new Grid(new MruModel());

    // 1. Set default ordering
    $grid->model()->orderBy('created_at', 'desc');
    
    // 2. Configure batch actions
    $grid->disableBatchActions(); // or keep enabled
    
    // 3. Configure row actions
    $grid->actions(function ($actions) {
        $actions->disableDelete(); // if needed
        $actions->disableView();   // if needed
        $actions->disableEdit();   // if needed
    });
    
    // 4. Configure export
    $grid->export(function ($export) {
        $export->filename('Export_' . date('Y-m-d_His'));
        $export->column('field1', 'Display Name 1');
        $export->column('field2', 'Display Name 2');
    });
    
    return $grid;
}
```

#### ✅ Column Definitions
```php
// Basic column
$grid->column('id', __('ID'))->sortable();

// Text with filtering
$grid->column('name', __('Name'))
    ->sortable()
    ->filter('like');

// Relationship
$grid->column('user.name', __('User Name'));

// Select options
$grid->column('status', __('Status'))
    ->using([
        'active' => 'Active',
        'inactive' => 'Inactive',
    ])
    ->sortable();

// Label (colored badge)
$grid->column('status', __('Status'))
    ->label([
        'active' => 'success',
        'pending' => 'warning',
        'rejected' => 'danger',
    ]);

// Custom display
$grid->column('amount', __('Amount'))
    ->display(function ($amount) {
        return '$' . number_format($amount, 2);
    });

// Boolean display
$grid->column('is_active', __('Active'))
    ->display(function ($value) {
        return $value ? 
            "<span class='label label-success'>Yes</span>" : 
            "<span class='label label-default'>No</span>";
    });

// Date formatting
$grid->column('created_at', __('Created'))
    ->display(function ($date) {
        return $date ? $date->format('Y-m-d H:i') : 'N/A';
    });
```

#### ✅ Filters
```php
$grid->filter(function ($filter) {
    // Remove default ID filter
    $filter->disableIdFilter();
    
    // Like filter
    $filter->like('name', 'Name');
    
    // Equal filter with dropdown
    $filter->equal('status', 'Status')->select([
        'active' => 'Active',
        'inactive' => 'Inactive',
    ]);
    
    // Between filter (for numbers/dates)
    $filter->between('created_at', 'Created Date')->datetime();
    $filter->between('amount', 'Amount');
    
    // Greater than / Less than
    $filter->gt('amount', 'Min Amount');
    $filter->lt('amount', 'Max Amount');
    
    // Where filter (custom)
    $filter->where(function ($query) {
        $query->where('field', 'LIKE', "%{$this->input}%");
    }, 'Label');
    
    // Relationship filter
    $filter->equal('user_id', 'User')->select('/admin/api/users');
});
```

### 3.2 Detail (Show) Configuration

```php
protected function detail($id)
{
    $show = new Show(MruModel::findOrFail($id));

    // Disable actions if needed
    $show->panel()
        ->tools(function ($tools) {
            $tools->disableDelete();
        });

    // Group fields with panels
    $show->panel()
        ->title('Basic Information')
        ->style('primary');
    
    $show->field('id', __('ID'));
    $show->field('name', __('Name'));
    
    // Divider between sections
    $show->divider();
    
    $show->panel()
        ->title('Additional Details')
        ->style('info');
    
    $show->field('description', __('Description'));
    
    // Display relationships
    $show->field('user.name', __('User'));
    
    // Custom formatting
    $show->field('amount', __('Amount'))->as(function ($amount) {
        return '$' . number_format($amount, 2);
    });
    
    // Badge display
    $show->field('status', __('Status'))->badge();
    
    return $show;
}
```

### 3.3 Form Configuration

#### ✅ Basic Form Setup
```php
protected function form()
{
    $form = new Form(new MruModel());

    // Disable features if needed
    $form->disableCreatingCheck();
    $form->disableEditingCheck();
    $form->disableViewCheck();
    
    // Configure tools
    $form->tools(function (Form\Tools $tools) {
        $tools->disableDelete();
    });
    
    return $form;
}
```

#### ✅ Form Fields
```php
// Text input
$form->text('name', __('Name'))
    ->rules('required|max:255')
    ->help('Enter the name');

// Textarea
$form->textarea('description', __('Description'))
    ->rows(5)
    ->rules('nullable|max:1000');

// Number input
$form->number('quantity', __('Quantity'))
    ->min(0)
    ->max(999)
    ->rules('required|integer|min:0');

// Decimal input
$form->decimal('price', __('Price'))
    ->rules('required|numeric|min:0');

// Select dropdown
$form->select('status', __('Status'))
    ->options([
        'active' => 'Active',
        'inactive' => 'Inactive',
    ])
    ->rules('required|in:active,inactive')
    ->default('active');

// Select with AJAX (for large datasets)
$form->select('user_id', __('User'))
    ->options(function ($id) {
        if ($id) {
            return User::where('id', $id)->pluck('name', 'id');
        }
    })
    ->ajax('/admin/api/users')
    ->rules('required');

// Date picker
$form->date('start_date', __('Start Date'))
    ->rules('required|date');

// DateTime picker
$form->datetime('published_at', __('Published At'))
    ->rules('nullable|date');

// Switch (toggle)
$form->switch('is_active', __('Active'))
    ->default(1);

// Radio buttons
$form->radio('type', __('Type'))
    ->options([
        1 => 'Type 1',
        2 => 'Type 2',
    ])
    ->rules('required');

// Checkbox
$form->checkbox('features', __('Features'))
    ->options([
        'feature1' => 'Feature 1',
        'feature2' => 'Feature 2',
    ]);

// File upload
$form->image('avatar', __('Avatar'))
    ->rules('nullable|image|max:2048');

// Currency input
$form->currency('amount', __('Amount'))
    ->symbol('$')
    ->rules('required|numeric|min:0');
```

#### ✅ Tabbed Forms
```php
$form->tab('Basic Info', function ($form) {
    $form->text('name', __('Name'));
    $form->text('email', __('Email'));
});

$form->tab('Details', function ($form) {
    $form->textarea('description', __('Description'));
    $form->number('quantity', __('Quantity'));
});

$form->tab('Settings', function ($form) {
    $form->switch('is_active', __('Active'));
    $form->select('status', __('Status'));
});
```

#### ✅ Form Callbacks
```php
// Before saving
$form->saving(function (Form $form) {
    // Validate custom logic
    if ($form->quantity > 100) {
        admin_error('Error', 'Quantity cannot exceed 100');
        return back()->withInput();
    }
    
    // Auto-fill fields
    if (empty($form->slug)) {
        $form->slug = \Str::slug($form->name);
    }
});

// After saving
$form->saved(function (Form $form) {
    // Log activity
    \Log::info('Record saved', ['id' => $form->model()->id]);
    
    // Send notification
    // Update related records
    // etc.
});
```

### 3.4 Custom Methods

```php
/**
 * Display statistics
 *
 * @return string
 */
protected function renderStatistics()
{
    $total = MruModel::count();
    $active = MruModel::where('is_active', true)->count();
    
    return '
    <style>
        .custom-stats { margin: 10px 0 15px 0; display: flex; gap: 10px; flex-wrap: wrap; }
        .stat-box { background: #fff; border: 1px solid #d2d6de; border-radius: 3px; padding: 8px 12px; min-width: 140px; flex: 1; }
        .stat-box .stat-label { font-size: 11px; color: #666; text-transform: uppercase; margin-bottom: 3px; }
        .stat-box .stat-value { font-size: 18px; font-weight: bold; color: #333; }
        .stat-box.stat-primary { border-left: 3px solid #3c8dbc; }
        .stat-box.stat-success { border-left: 3px solid #00a65a; }
    </style>
    <div class="custom-stats">
        <div class="stat-box stat-primary">
            <div class="stat-label">Total Records</div>
            <div class="stat-value">' . number_format($total) . '</div>
        </div>
        <div class="stat-box stat-success">
            <div class="stat-label">Active</div>
            <div class="stat-value">' . number_format($active) . '</div>
        </div>
    </div>';
}

// Use in grid:
$grid->header(function () {
    return $this->renderStatistics();
});
```

---

## 4. Code Quality Standards

### 4.1 Documentation Requirements

#### ✅ Class Docblock
```php
/**
 * MruModel Class
 * 
 * Detailed description of the model's purpose and what it represents.
 * 
 * @property int $id Primary key
 * @property string $name Model name
 * @property bool $is_active Active status
 * @property \Carbon\Carbon $created_at Creation timestamp
 * 
 * @method static Builder active() Get active records
 * @method static Builder forUser(int $userId) Get records for user
 * 
 * @package App\Models
 */
```

#### ✅ Method Docblock
```php
/**
 * Calculate the total amount including tax
 * 
 * This method calculates the subtotal based on quantity and price,
 * then applies the specified tax rate to get the final total.
 *
 * @param float $taxRate Tax rate as decimal (e.g., 0.18 for 18%)
 * @param bool $rounded Whether to round to 2 decimal places
 * @return float The calculated total amount
 * @throws \InvalidArgumentException If tax rate is negative
 */
public function calculateTotal(float $taxRate = 0.18, bool $rounded = true): float
{
    // Implementation
}
```

### 4.2 Code Style

#### ✅ Required Standards
- Use **type hints** for all parameters and return types
- Use **strict types** where appropriate
- Keep methods **short and focused** (max 50 lines)
- Use **meaningful variable names**
- Add **inline comments** for complex logic
- Follow **PSR-12** coding standard

#### ✅ Example
```php
// ✅ Good
public function calculateDiscount(float $amount, float $percentage): float
{
    if ($percentage < 0 || $percentage > 100) {
        throw new \InvalidArgumentException('Percentage must be between 0 and 100');
    }
    
    return round($amount * ($percentage / 100), 2);
}

// ❌ Bad
public function calc($a, $p) {
    return $a * ($p / 100);
}
```

### 4.3 Security

#### ✅ Required Security Measures
```php
// 1. Use fillable instead of guarded
protected $fillable = ['name', 'email']; // ✅
protected $guarded = [];                  // ❌

// 2. Validate input in controllers
$form->text('email')->rules('required|email|unique:users');

// 3. Prevent SQL injection (use query builder)
User::where('email', $email)->first();           // ✅
User::whereRaw("email = '$email'")->first();     // ❌

// 4. Hide sensitive attributes
protected $hidden = ['password', 'remember_token'];

// 5. Use transactions for multiple operations
DB::transaction(function () {
    // Multiple database operations
});
```

---

## 5. Testing Requirements

### 5.1 Model Testing

#### ✅ Required Tests
```php
// 1. Test model instantiation
php artisan tinker --execute="
    echo 'Testing MruModel...';
    \$model = new App\Models\MruModel();
    echo 'Model loads: ✓';
"

// 2. Test database connection
php artisan tinker --execute="
    \$count = App\Models\MruModel::count();
    echo 'Total records: ' . \$count;
"

// 3. Test relationships
php artisan tinker --execute="
    \$model = App\Models\MruModel::with('user')->first();
    echo 'Relationship: ' . (\$model->user ? '✓' : '✗');
"

// 4. Test scopes
php artisan tinker --execute="
    \$active = App\Models\MruModel::active()->count();
    echo 'Active records: ' . \$active;
"

// 5. Test accessors
php artisan tinker --execute="
    \$model = App\Models\MruModel::first();
    echo 'Accessor: ' . \$model->formatted_name;
"
```

### 5.2 Controller Testing

#### ✅ Required Tests
1. **Access grid page:** Visit `/admin/mru-models` - should load without errors
2. **Test filters:** Apply each filter - should return results
3. **Test create form:** Click "New" - form should load
4. **Test edit form:** Click edit on record - form should load with data
5. **Test detail view:** Click view on record - details should display
6. **Test export:** Click export - file should download

### 5.3 Checklist

```markdown
## Pre-Deployment Checklist

### Model
- [ ] Class name follows `Mru{EntityName}` convention
- [ ] Table name specified correctly
- [ ] Primary key specified correctly
- [ ] Timestamps configuration correct
- [ ] All fillable fields listed
- [ ] All fields have proper casts
- [ ] All relationships defined and tested
- [ ] At least 3 useful scopes added
- [ ] Common accessors added (is_active, formatted_name, etc.)
- [ ] Constants defined for status/type values
- [ ] All methods have docblocks
- [ ] All methods have type hints

### Controller
- [ ] Controller name follows `Mru{EntityName}Controller` convention
- [ ] Title property set
- [ ] Grid configured with columns
- [ ] Grid has appropriate filters
- [ ] Grid export configured
- [ ] Show page configured
- [ ] Form has all necessary fields
- [ ] Form has validation rules
- [ ] Form has help text
- [ ] Custom actions added if needed

### Testing
- [ ] Model instantiates without errors
- [ ] Can query database successfully
- [ ] Relationships work correctly
- [ ] Scopes return expected results
- [ ] Accessors return correct values
- [ ] Grid page loads without errors
- [ ] Filters work correctly
- [ ] Form creates new records
- [ ] Form updates existing records
- [ ] Detail view displays correctly

### Documentation
- [ ] Class docblock complete
- [ ] All methods documented
- [ ] Usage examples provided
- [ ] README updated if needed
```

---

## 6. Complete Examples

### Example 1: MruStudent Model

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

/**
 * MruStudent Model
 * 
 * Represents student records in the MRU system.
 * Maps to students table.
 * 
 * @property int $id
 * @property string $regno Registration number
 * @property string $name Full name
 * @property string $email Email address
 * @property int $class_id Current class
 * @property bool $is_active Active status
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * 
 * @method static Builder active() Active students
 * @method static Builder forClass(int $classId) Students in class
 */
class MruStudent extends Model
{
    protected $table = 'students';
    protected $primaryKey = 'id';
    public $timestamps = true;

    protected $fillable = [
        'regno',
        'name',
        'email',
        'class_id',
        'is_active',
    ];

    protected $casts = [
        'id' => 'integer',
        'class_id' => 'integer',
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    const STATUS_ACTIVE = true;
    const STATUS_INACTIVE = false;

    /**
     * Get the class this student belongs to
     */
    public function class(): BelongsTo
    {
        return $this->belongsTo(AcademicClass::class, 'class_id');
    }

    /**
     * Get student's results
     */
    public function results(): HasMany
    {
        return $this->hasMany(MruResult::class, 'regno', 'regno');
    }

    /**
     * Scope: Active students
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: Students in specific class
     */
    public function scopeForClass(Builder $query, int $classId): Builder
    {
        return $query->where('class_id', $classId);
    }

    /**
     * Accessor: Get full name with regno
     */
    public function getFullDisplayNameAttribute(): string
    {
        return "{$this->regno} - {$this->name}";
    }

    /**
     * Get student's average score
     */
    public function getAverageScore(): float
    {
        return round($this->results()->avg('score') ?? 0, 2);
    }
}
```

### Example 2: MruStudentController

```php
<?php

namespace App\Admin\Controllers;

use App\Models\MruStudent;
use App\Models\AcademicClass;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class MruStudentController extends AdminController
{
    protected $title = 'Students';

    protected function grid()
    {
        $grid = new Grid(new MruStudent());
        
        $grid->model()->orderBy('created_at', 'desc');
        
        $grid->column('id', 'ID')->sortable();
        $grid->column('regno', 'Reg No.')->sortable()->filter('like');
        $grid->column('name', 'Name')->sortable()->filter('like');
        $grid->column('class.name', 'Class');
        $grid->column('email', 'Email');
        $grid->column('is_active', 'Status')
            ->display(function ($value) {
                return $value ? 
                    "<span class='label label-success'>Active</span>" : 
                    "<span class='label label-default'>Inactive</span>";
            });
        
        $grid->filter(function ($filter) {
            $filter->like('regno', 'Registration No.');
            $filter->like('name', 'Name');
            $filter->equal('class_id', 'Class')->select(
                AcademicClass::pluck('name', 'id')
            );
            $filter->equal('is_active', 'Status')->select([
                1 => 'Active',
                0 => 'Inactive',
            ]);
        });
        
        return $grid;
    }

    protected function detail($id)
    {
        $show = new Show(MruStudent::findOrFail($id));
        
        $show->field('id', 'ID');
        $show->field('regno', 'Registration No.');
        $show->field('name', 'Name');
        $show->field('email', 'Email');
        $show->field('class.name', 'Class');
        $show->field('is_active', 'Status')->as(function ($value) {
            return $value ? 'Active' : 'Inactive';
        });
        $show->field('created_at', 'Created');
        $show->field('updated_at', 'Updated');
        
        return $show;
    }

    protected function form()
    {
        $form = new Form(new MruStudent());
        
        $form->text('regno', 'Registration No.')
            ->rules('required|unique:students,regno,' . $form->model()->id);
        $form->text('name', 'Full Name')
            ->rules('required|max:255');
        $form->email('email', 'Email')
            ->rules('required|email|unique:students,email,' . $form->model()->id);
        $form->select('class_id', 'Class')
            ->options(AcademicClass::pluck('name', 'id'))
            ->rules('required');
        $form->switch('is_active', 'Active')->default(1);
        
        return $form;
    }
}
```

---

## Quick Reference Card

```
MODEL CHECKLIST:
✓ Mru prefix
✓ Table specified
✓ Primary key specified
✓ Timestamps config
✓ Fillable fields
✓ Casts defined
✓ Constants added
✓ Relationships defined
✓ 3+ scopes
✓ 3+ accessors
✓ Public methods
✓ Docblocks complete

CONTROLLER CHECKLIST:
✓ MruController suffix
✓ Title property
✓ Grid columns
✓ Grid filters
✓ Grid export
✓ Show fields
✓ Form fields
✓ Validation rules
✓ Help text
✓ Callbacks

TESTING:
✓ Model loads
✓ Database query works
✓ Relationships work
✓ Scopes work
✓ Grid loads
✓ Form works
✓ CRUD operations work
```

---

**End of Guidelines**

*Follow these guidelines for all new MRU models and controllers to ensure consistency, quality, and maintainability across the entire codebase.*
