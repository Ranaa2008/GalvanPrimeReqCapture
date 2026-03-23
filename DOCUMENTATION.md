# Laravel Dynamic RBAC Boilerplate - Complete Documentation

> **A professional, enterprise-grade Laravel boilerplate with dynamic Role-Based Access Control, management hierarchy, and advanced user management.**

---

## 📋 Table of Contents

1. [Overview](#overview)
2. [System Requirements](#system-requirements)
3. [Installation](#installation)
4. [Core Features](#core-features)
5. [Architecture](#architecture)
6. [User Management](#user-management)
7. [Role & Permission System](#role--permission-system)
8. [Management Hierarchy](#management-hierarchy)
9. [Authentication](#authentication)
10. [UI/UX Features](#uiux-features)
11. [API Reference](#api-reference)
12. [Database Schema](#database-schema)
13. [Security Features](#security-features)
14. [Configuration](#configuration)
15. [Testing](#testing)
16. [Deployment](#deployment)
17. [Troubleshooting](#troubleshooting)

---

## 🎯 Overview

This Laravel boilerplate provides a complete, production-ready foundation for building ERP systems and enterprise applications with:

- **100% Dynamic RBAC**: No hardcoded roles (except super-admin) - create any role with any permissions
- **Management Tree Hierarchy**: Recursive organization structure with superior-subordinate relationships
- **Advanced User Management**: Profile pictures, email/phone verification, user blocking, manager assignment
- **Modern UI**: Dark mode support, responsive design, clean black/white aesthetics
- **Security First**: OTP verification, secure authentication, blocked user handling

### Tech Stack

- **Framework**: Laravel 12.35.1
- **PHP**: 8.2+
- **Database**: MySQL/PostgreSQL
- **Frontend**: Blade + Alpine.js + Tailwind CSS
- **Packages**: 
  - Spatie Laravel Permission (RBAC)
  - Cloudinary (Image Management)
  - intl-tel-input (Phone Validation)

---

## 💻 System Requirements

- PHP >= 8.2
- Composer
- Node.js >= 18.x & NPM
- MySQL 8.0+ or PostgreSQL 13+
- Apache/Nginx web server

---

## 🚀 Installation

### 1. Clone & Setup

```bash
git clone <repository-url>
cd laravel-boilerplate
composer install
npm install
```

### 2. Environment Configuration

```bash
cp .env.example .env
php artisan key:generate
```

Configure your `.env`:

```env
APP_NAME="Your App Name"
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_DATABASE=your_database
DB_USERNAME=your_username
DB_PASSWORD=your_password

# Cloudinary (for profile pictures)
CLOUDINARY_URL=cloudinary://api_key:api_secret@cloud_name
CLOUDINARY_UPLOAD_PRESET=your_preset
```

### 3. Database Setup

```bash
php artisan migrate
php artisan db:seed
```

**Default Credentials**:
- Email: `admin@example.com`
- Password: `password`

### 4. Build Assets

```bash
npm run build    # Production
npm run dev      # Development
```

### 5. Start Development Server

```bash
php artisan serve
```

Visit: `http://localhost:8000`

---

## ✨ Core Features

### 1. Authentication System

#### Multi-Method Login
Users can login with:
- Email address
- Phone number (primary)
- Username

#### Registration
Complete profile capture:
- Full name
- Username (unique)
- Email (verified)
- Phone number (verified, required)
- Secondary phone (optional)
- Address
- Password

#### OTP Verification
- Email verification via 6-digit OTP
- Phone verification via SMS OTP
- Automatic verification alerts for unverified users

### 2. User Management

#### User Profiles
- Profile picture upload (Cloudinary integration)
- Client-side image preview
- Letter avatar fallback
- Dark mode support

#### User Operations
- ✅ **Create**: Add new users with role and manager assignment
- ✅ **Edit**: Update user information, roles, manager
- ✅ **Delete**: Remove users (cascade handling)
- ✅ **Verify**: Manually verify email/phone
- ✅ **Deverify**: Remove verification status
- ✅ **Block/Unblock**: Restrict user access (view-only mode)

#### User List Features
- Combined User column (name + username)
- Inline verification icons (checkmark/x)
- Icon-only action buttons with tooltips
- Server-side search (name, username, email)
- Management tree filtering (see only subordinates)
- Clean black/white UI design

### 3. Role & Permission System

#### Dynamic Roles
- Create unlimited roles with custom names
- No hardcoded role names (except super-admin)
- Superiority levels (1-99): Lower number = Higher authority
- Rename any role (including system roles)

#### Permission Types
**User Management**:
- `view-users` - View user list
- `create-users` - Create new users
- `edit-users` - Edit user information
- `delete-users` - Delete users

**Verification**:
- `verify-users` - Manually verify email/phone
- `deverify-users` - Remove verification status

**User Control**:
- `block-users` - Block/unblock users

**Role Management**:
- `view-roles` - View roles list
- `create-roles` - Create new roles
- `edit-roles` - Edit existing roles
- `delete-roles` - Delete roles

**Permission Management**:
- `view-permissions` - View permissions list
- `create-permissions` - Create new permissions
- `edit-permissions` - Edit permissions
- `delete-permissions` - Delete permissions

**Assignment**:
- `assign-roles` - Assign roles to users
- `assign-permissions` - Assign permissions to roles

#### Assignable Permissions
Each role-permission relationship has an `assignable` flag:
- If `true`, users with this role can assign this permission to subordinates
- Controls permission delegation hierarchy

### 4. Management Hierarchy

#### Superiority System
- Database-driven hierarchy using `superiority` column
- Level 1: Super Admin (highest authority)
- Level 2+: Custom roles (Admin, Teacher, Manager, etc.)
- Higher number = Lower authority

#### Management Tree
- Recursive superior-subordinate relationships
- `managed_by` column links users to their manager
- Users see only their management tree:
  - Direct reports (managed_by = current user)
  - Indirect reports (subordinates' subordinates)
  - Recursive to unlimited depth

#### Dynamic Manager Assignment
- Manager dropdown populates based on selected role
- Shows only users with higher authority than target role
- Filtered by current user's management tree
- Alpine.js reactive updates on role selection

#### Access Control
Users can only:
- View users in their management tree
- Edit users they manage (lower superiority)
- Assign roles with lower authority than themselves
- Assign managers from their own tree

**Exception**: Super admin bypasses all restrictions

---

## 🏗️ Architecture

### Directory Structure

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── Admin/
│   │   │   ├── UserController.php       # User CRUD + verification
│   │   │   ├── RoleController.php       # Role management
│   │   │   ├── PermissionController.php # Permission management
│   │   │   └── DashboardController.php  # Admin dashboard
│   │   ├── Auth/                        # Authentication controllers
│   │   └── ProfileController.php        # User profile management
│   ├── Middleware/
│   │   ├── AdminMiddleware.php          # Management access check
│   │   └── CheckIfBlocked.php           # Block user access
│   └── Requests/                        # Form validation requests
├── Models/
│   ├── User.php                         # User model with hierarchy methods
│   └── Role.php                         # Custom role model with superiority
└── View/
    └── Components/                      # Blade components

resources/
├── views/
│   ├── admin/                           # Admin panel views
│   │   ├── users/                       # User management
│   │   ├── roles/                       # Role management
│   │   └── permissions/                 # Permission management
│   ├── auth/                            # Authentication views
│   ├── components/                      # Reusable components
│   │   ├── layout/                      # Layout components
│   │   └── tooltip.blade.php            # Custom tooltip component
│   └── pages/                           # Public pages
├── css/
│   └── app.css                          # Tailwind CSS
└── js/
    └── app.js                           # Alpine.js + Bootstrap

database/
├── migrations/
│   ├── *_add_hierarchy_system_to_tables.php
│   ├── *_add_avatar_to_users.php
│   └── ...
└── seeders/
    ├── RolePermissionSeeder.php         # Initial roles & permissions
    ├── HierarchySystemSeeder.php        # Superiority levels
    └── StandardizePermissionNamesSeeder.php
```

### Key Design Patterns

1. **Repository Pattern**: Controllers handle business logic, models encapsulate data
2. **Service Layer**: Complex operations (OTP, hierarchy) separated into reusable methods
3. **Component-Based UI**: Reusable Blade components with Alpine.js reactivity
4. **Middleware Pipeline**: Layered security (auth → admin → permission checks)
5. **Dynamic Queries**: Database-driven behavior (no hardcoded values)

---

## 👥 User Management

### User Model

```php
class User extends Authenticatable
{
    // Relationships
    public function roles()          // Many-to-many with roles
    public function managedBy()      // Belongs to User (manager)
    public function subordinates()   // Has many Users (managed_by)
    
    // Hierarchy Methods
    public function getSuperiority() // Get user's highest authority level
    public function canManage(User)  // Check if can manage target user
    public function canAssignRole()  // Check if can assign specific role
    public function getManagementTreeIds() // Recursive subordinates
    
    // Status Methods
    public function isBlocked()      // Check if user is blocked
}
```

### UserController Key Methods

```php
// Listing
index()                    // List users (filtered by management tree)
getManagementTreeIds()     // Recursive tree lookup

// CRUD
create()                   // Show create form
store()                    // Save new user
edit(User)                 // Show edit form
update(User)               // Update user
destroy(User)              // Delete user

// Verification
verifyEmail(User)          // Manually verify email
verifyPhone(User)          // Manually verify phone
deverifyEmail(User)        // Remove email verification
deverifyPhone(User)        // Remove phone verification

// Access Control
toggleBlock(User)          // Block/unblock user

// Utilities
canManageUser(User)        // Authorization check
getUserHighestSuperiority() // Get user's authority level
getAssignableRoles()       // Get roles user can assign
getManagersByRole()        // API: Get available managers for role
```

### User List Features

- **Search**: Server-side search by name, username, email
- **Filtering**: Automatic filtering by management tree
- **Inline Actions**: 
  - Verify/deverify icons under email/phone
  - Edit, Block, Delete buttons (icon-only with tooltips)
- **Pagination**: 15 users per page
- **Responsive**: Mobile-friendly table layout

---

## 🔐 Role & Permission System

### Role Model

```php
class Role extends SpatieRole
{
    protected $fillable = ['name', 'guard_name', 'superiority'];
    
    // Superiority: 1-99 (lower = higher authority)
    // 1: Super Admin (reserved)
    // 2+: Custom roles
}
```

### Permission Naming Convention

Use kebab-case with hyphens:
- ✅ `view-users`, `edit-posts`
- ❌ `view users`, `edit_posts`

### Role Management

#### Creating Roles

```php
// Web Interface: /admin/roles/create
// - Name: Any custom name
// - Superiority: 1-99 (super admin can set any, others cannot set)
// - Permissions: Select which permissions to grant
// - Assignable: Toggle which permissions can be delegated
```

#### Role Hierarchy

```
Level 1: Super Admin (full access, no restrictions)
Level 2: Admin (manage level 3+)
Level 3: Manager/Teacher (manage level 4+)
Level 4+: Staff/Student/Custom (limited access)
```

### Assignable Permissions System

The `assignable` flag in role_has_permissions pivot table controls delegation:

```php
// Super Admin role
'view-users' => assignable: true   // Can delegate this permission
'delete-users' => assignable: true // Can delegate this permission

// Manager role  
'view-users' => assignable: false  // Cannot delegate (can only use)
```

---

## 🌳 Management Hierarchy

### How It Works

#### 1. Superiority Levels (Authority)
- Database column: `roles.superiority`
- Lower number = Higher authority
- Determines who can manage whom

#### 2. Management Tree (Reporting Structure)
- Database column: `users.managed_by`
- Links user to their direct manager
- Creates organizational tree

#### 3. Combined Authorization

User A can manage User B if:
```
✅ User B is in User A's management tree (direct or indirect subordinate)
AND
✅ User A has higher authority (lower superiority number) than User B
```

### Example Scenario

```
Company Structure:

Super Admin (Level 1)
├── Admin North (Level 2, managed_by: Super Admin)
│   ├── Manager N1 (Level 3, managed_by: Admin North)
│   │   ├── Staff N1A (Level 5, managed_by: Manager N1)
│   │   └── Staff N1B (Level 5, managed_by: Manager N1)
│   └── Manager N2 (Level 3, managed_by: Admin North)
│       └── Staff N2A (Level 5, managed_by: Manager N2)
└── Admin South (Level 2, managed_by: Super Admin)
    └── Manager S1 (Level 3, managed_by: Admin South)
        └── Staff S1A (Level 5, managed_by: Manager S1)
```

**What Admin North can see:**
- ✅ Admin North (self)
- ✅ Manager N1, Manager N2 (direct reports)
- ✅ Staff N1A, N1B, N2A (indirect reports)
- ❌ Super Admin (higher level)
- ❌ Admin South (same level, different tree)
- ❌ Manager S1, Staff S1A (not in their tree)

**What Manager N1 can do:**
- View: N1, Staff N1A, N1B only
- Edit: Staff N1A, N1B (lower authority)
- Assign managers: Choose from Admin North or Manager N1
- Cannot see or manage Manager N2's staff

### Implementation

#### Database

```sql
-- roles table
CREATE TABLE roles (
    id BIGINT PRIMARY KEY,
    name VARCHAR(255),
    superiority INT DEFAULT 99,  -- 1=highest
    ...
);

-- users table
CREATE TABLE users (
    id BIGINT PRIMARY KEY,
    managed_by BIGINT NULL,      -- FK to users.id
    blocked_at TIMESTAMP NULL,
    ...
    FOREIGN KEY (managed_by) REFERENCES users(id) ON DELETE SET NULL
);
```

#### Recursive Tree Query

```php
private function getManagementTreeIds($userId)
{
    $ids = [$userId];
    
    // Get direct reports
    $directReports = User::where('managed_by', $userId)->pluck('id');
    
    foreach ($directReports as $reportId) {
        // Recursively get their subordinates
        $ids = array_merge($ids, $this->getManagementTreeIds($reportId));
    }
    
    return array_unique($ids);
}
```

---

## 🔒 Authentication

### Login Methods

Users can login with any of:
1. **Email**: `user@example.com`
2. **Phone**: `+1234567890`
3. **Username**: `johndoe`

### Login Flow

```
1. User enters identifier (email/phone/username) + password
2. System determines identifier type
3. Lookup user by identifier
4. Verify password
5. Check if blocked → Allow view-only access
6. Check if unverified → Show verification alert
7. Create session & redirect to dashboard
```

### Registration Flow

```
1. Collect user information (name, username, email, phone, address, password)
2. Validate uniqueness (username, email, phone)
3. Hash password
4. Create user record (unverified)
5. Send email OTP
6. Send phone OTP (integration ready)
7. Redirect to dashboard with verification alert
```

### OTP Verification

#### Email OTP
```php
// Send OTP
ProfileController::sendEmailOtp()
// - Generate 6-digit code
// - Store in session (5 min expiry)
// - Send via email

// Verify OTP
ProfileController::verifyEmailOtp()
// - Compare submitted code with session
// - Update user.email_verified = true
```

#### Phone OTP
```php
// Send OTP
ProfileController::sendPhoneOtp()
// - Generate 6-digit code
// - Store in session (5 min expiry)
// - Send via SMS (Twilio/etc integration point)

// Verify OTP
ProfileController::verifyPhoneOtp()
// - Compare submitted code with session
// - Update user.phone_verified = true
```

### Password Security

- **Hashing**: Bcrypt with Laravel defaults
- **Minimum Length**: 8 characters
- **Confirmation**: Required on registration/change
- **Reset**: Email-based password reset (Laravel Breeze)

---

## 🎨 UI/UX Features

### Design System

#### Colors
- **Primary**: Gray-900 (dark) / White (light)
- **Success**: Green-600
- **Danger**: Red-600
- **Warning**: Yellow-500
- **Info**: Blue-600

#### Typography
- **Font**: Poppins (Google Fonts)
- **Base Size**: 14px (87.5% of 16px)
- **Scale**: 0.875rem base

#### Icons
- Clean SVG stroke icons (Heroicons style)
- No colorful emojis or fancy icons
- Consistent 16px/24px sizes

### Dark Mode

- System-wide dark mode toggle
- Persistent (localStorage)
- Alpine.js reactive
- Tailwind dark: classes throughout

### Components

#### Custom Tooltip
```blade
<x-tooltip text="Tooltip text" position="top|bottom|left|right">
    <button>Hover me</button>
</x-tooltip>
```

Features:
- Black background, white text
- Smooth fade/scale transitions
- Arrow pointer
- Multiple positions
- Auto-positioning

#### Layout Components
- `<x-layout.topbar />` - Top navigation bar
- `<x-layout.sidebar />` - Collapsible sidebar
- Profile dropdown, dark mode toggle

### Responsive Design

- Mobile-first approach
- Breakpoints: sm (640px), md (768px), lg (1024px), xl (1280px)
- Collapsible sidebar on mobile
- Touch-friendly buttons (min 44px tap targets)

### User Experience

#### Loading States
- Spinner on form submissions
- Disabled buttons during processing
- Visual feedback on actions

#### Notifications
- Toast messages (success/error/info)
- Auto-dismiss after 5 seconds
- Positioned top-right

#### Validation
- Inline error messages
- Real-time validation feedback
- Clear error descriptions

---

## 🔌 API Reference

### Manager Dropdown API

**Endpoint**: `GET /admin/users/managers-by-role`

**Purpose**: Get available managers for a specific role

**Parameters**:
- `role_id` (required): ID of the role being assigned

**Response**:
```json
[
    {
        "id": 1,
        "name": "John Doe",
        "email": "john@example.com",
        "roles": "Super Admin",
        "superiority": 1
    },
    {
        "id": 2,
        "name": "Jane Smith",
        "email": "jane@example.com",
        "roles": "Admin",
        "superiority": 2
    }
]
```

**Logic**:
1. Find role by ID
2. Get users with superiority < role's superiority
3. Filter by current user's management tree
4. Return sorted by superiority (ascending)

**Usage** (Alpine.js):
```javascript
async updateManagers() {
    const response = await fetch(
        `/admin/users/managers-by-role?role_id=${roleId}`
    );
    this.availableManagers = await response.json();
}
```

---

## 🗄️ Database Schema

### Key Tables

#### users
```sql
id                  BIGINT PRIMARY KEY AUTO_INCREMENT
name                VARCHAR(255) NOT NULL
username            VARCHAR(255) UNIQUE NOT NULL
email               VARCHAR(255) UNIQUE NOT NULL
phone_number        VARCHAR(20) UNIQUE NOT NULL
phone_number_secondary VARCHAR(20) NULL
address             TEXT NULL
password            VARCHAR(255) NOT NULL
email_verified      BOOLEAN DEFAULT FALSE
phone_verified      BOOLEAN DEFAULT FALSE
avatar_url          VARCHAR(500) NULL
avatar_public_id    VARCHAR(255) NULL
managed_by          BIGINT NULL           -- FK to users.id
blocked_at          TIMESTAMP NULL
created_at          TIMESTAMP
updated_at          TIMESTAMP
```

#### roles
```sql
id                  BIGINT PRIMARY KEY AUTO_INCREMENT
name                VARCHAR(255) UNIQUE NOT NULL
guard_name          VARCHAR(255) NOT NULL
superiority         INT DEFAULT 99        -- 1=highest authority
created_at          TIMESTAMP
updated_at          TIMESTAMP
```

#### permissions
```sql
id                  BIGINT PRIMARY KEY AUTO_INCREMENT
name                VARCHAR(255) UNIQUE NOT NULL
guard_name          VARCHAR(255) NOT NULL
created_at          TIMESTAMP
updated_at          TIMESTAMP
```

#### role_has_permissions (Pivot)
```sql
permission_id       BIGINT NOT NULL       -- FK to permissions.id
role_id             BIGINT NOT NULL       -- FK to roles.id
assignable          BOOLEAN DEFAULT FALSE -- Can delegate?

PRIMARY KEY (permission_id, role_id)
```

#### model_has_roles (Pivot)
```sql
role_id             BIGINT NOT NULL       -- FK to roles.id
model_type          VARCHAR(255) NOT NULL -- 'App\Models\User'
model_id            BIGINT NOT NULL       -- User ID

PRIMARY KEY (role_id, model_id, model_type)
```

### Relationships

```
User
├── belongsTo: User (managedBy)
├── hasMany: User (subordinates)
├── belongsToMany: Role (roles)
└── belongsToMany: Permission (permissions)

Role
├── belongsToMany: Permission (permissions)
└── belongsToMany: User (users)

Permission
├── belongsToMany: Role (roles)
└── belongsToMany: User (users)
```

---

## 🛡️ Security Features

### 1. Authentication Security
- Bcrypt password hashing
- Session-based authentication (Laravel Sanctum ready)
- CSRF protection on all forms
- Rate limiting on login/registration

### 2. Authorization Layers
- Middleware: `auth`, `admin`, `verified`
- Permission checks: `@can()`, `hasPermissionTo()`
- Management tree filtering
- Superiority level enforcement

### 3. User Blocking
- Blocked users can only:
  - View their profile
  - Logout
- Cannot:
  - Edit profile
  - Access other pages
  - Perform any actions

### 4. Input Validation
- Server-side validation on all inputs
- Unique constraints (email, username, phone)
- Type validation (email format, phone format)
- Max length enforcement

### 5. SQL Injection Prevention
- Eloquent ORM (parameterized queries)
- No raw SQL queries without bindings
- Input sanitization

### 6. XSS Prevention
- Blade `{{ }}` auto-escapes output
- `{!! !!}` used only for trusted HTML
- CSP headers (configurable)

### 7. File Upload Security
- Avatar uploads via Cloudinary (off-server)
- File type validation
- Size limits enforced
- Public ID tracking for deletion

---

## ⚙️ Configuration

### Environment Variables

```env
# Application
APP_NAME="Laravel RBAC"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com

# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=laravel_rbac
DB_USERNAME=root
DB_PASSWORD=

# Cloudinary
CLOUDINARY_URL=cloudinary://key:secret@cloud
CLOUDINARY_UPLOAD_PRESET=preset_name

# Mail (for OTP)
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your_username
MAIL_PASSWORD=your_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@yourapp.com
MAIL_FROM_NAME="${APP_NAME}"

# SMS (for Phone OTP - configure your provider)
SMS_PROVIDER=twilio
TWILIO_SID=your_sid
TWILIO_TOKEN=your_token
TWILIO_FROM=+1234567890
```

### Permissions Configuration

To add new permissions:

1. Create migration:
```bash
php artisan make:migration add_new_permissions
```

2. Add in migration:
```php
Permission::create(['name' => 'new-permission']);
```

3. Assign to roles:
```php
$role->givePermissionTo('new-permission');
```

4. Use in code:
```php
@can('new-permission')
    <!-- Protected content -->
@endcan
```

---

## 🧪 Testing

### Manual Testing Checklist

#### Authentication
- [ ] Login with email
- [ ] Login with phone
- [ ] Login with username
- [ ] Registration with all fields
- [ ] Password reset flow
- [ ] Email OTP verification
- [ ] Phone OTP verification

#### User Management
- [ ] Create user (super-admin)
- [ ] Create user (manager - see filtered managers)
- [ ] Edit user (own tree)
- [ ] Cannot edit user (different tree)
- [ ] Delete user
- [ ] Verify email manually
- [ ] Verify phone manually
- [ ] Block user
- [ ] Unblock user
- [ ] Search users

#### Role Management
- [ ] Create role with superiority
- [ ] Edit role name
- [ ] Edit role superiority (super-admin only)
- [ ] Delete role
- [ ] Assign permissions
- [ ] Toggle assignable flag

#### Hierarchy
- [ ] User sees only their tree
- [ ] Manager dropdown filters correctly
- [ ] Cannot edit higher-level users
- [ ] Super admin sees everyone
- [ ] Recursive subordinates work

#### UI/UX
- [ ] Dark mode toggle
- [ ] Tooltips display correctly
- [ ] Responsive on mobile
- [ ] Icons display properly
- [ ] Search works
- [ ] Pagination works

---

## 🚀 Deployment

### Production Checklist

#### 1. Environment Setup
```bash
# Set production environment
APP_ENV=production
APP_DEBUG=false

# Generate new app key
php artisan key:generate

# Set production database
DB_HOST=production-host
DB_DATABASE=production-db
```

#### 2. Optimize Application
```bash
# Cache configuration
php artisan config:cache

# Cache routes
php artisan route:cache

# Cache views
php artisan view:cache

# Optimize autoloader
composer install --optimize-autoloader --no-dev
```

#### 3. Build Assets
```bash
npm run build
```

#### 4. Database Migration
```bash
# Backup database first!
php artisan migrate --force
php artisan db:seed --force
```

#### 5. File Permissions
```bash
chmod -R 755 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

#### 6. Web Server Configuration

**Apache (.htaccess)**:
```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteRule ^(.*)$ public/$1 [L]
</IfModule>
```

**Nginx**:
```nginx
server {
    listen 80;
    server_name yourdomain.com;
    root /var/www/laravel-rbac/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

#### 7. SSL Certificate
```bash
# Using Let's Encrypt
certbot --nginx -d yourdomain.com
```

#### 8. Supervisor (Queue Workers)
```ini
[program:laravel-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/laravel-rbac/artisan queue:work
autostart=true
autorestart=true
user=www-data
numprocs=1
redirect_stderr=true
stdout_logfile=/var/www/laravel-rbac/storage/logs/worker.log
```

---

## 🔧 Troubleshooting

### Common Issues

#### 1. Permission Denied Errors
```bash
# Fix storage permissions
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

#### 2. Class Not Found
```bash
# Clear cache and regenerate autoload
composer dump-autoload
php artisan clear-compiled
php artisan cache:clear
```

#### 3. CSRF Token Mismatch
```bash
# Clear sessions
php artisan session:clear
# Or delete storage/framework/sessions/*
```

#### 4. Database Connection Failed
- Check .env DB credentials
- Verify database exists
- Check DB user permissions
- Test connection: `php artisan tinker` → `DB::connection()->getPdo()`

#### 5. Cloudinary Upload Failed
- Verify CLOUDINARY_URL format
- Check upload preset exists
- Ensure preset is unsigned (for client uploads)
- Check network/firewall

#### 6. Dark Mode Not Working
```bash
# Clear view cache
php artisan view:clear
# Check Alpine.js loaded: View page source → search for "Alpine"
```

#### 7. Manager Dropdown Empty
- Check role has superiority set
- Verify users have roles assigned
- Check browser console for JS errors
- Clear route cache: `php artisan route:clear`

#### 8. User Cannot See Subordinates
- Check managed_by is set correctly
- Verify superiority levels (lower = higher)
- Check user has view-users permission
- Test recursive query manually:
```php
User::where('managed_by', 1)->get(); // Direct reports
```

---

## 📚 Additional Resources

### Laravel Documentation
- [Laravel 11 Docs](https://laravel.com/docs/11.x)
- [Blade Templates](https://laravel.com/docs/11.x/blade)
- [Eloquent ORM](https://laravel.com/docs/11.x/eloquent)

### Package Documentation
- [Spatie Laravel Permission](https://spatie.be/docs/laravel-permission/v6)
- [Cloudinary PHP SDK](https://cloudinary.com/documentation/php_integration)
- [Alpine.js](https://alpinejs.dev/)
- [Tailwind CSS](https://tailwindcss.com/docs)

### Community
- [Laravel Discord](https://discord.gg/laravel)
- [Spatie Discord](https://discord.gg/spatie)
- [Stack Overflow](https://stackoverflow.com/questions/tagged/laravel)

---

## 📝 License

This project is open-sourced software licensed under the MIT license.

---

## 🤝 Contributing

Contributions are welcome! Please follow these guidelines:

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

---

## 📞 Support

For questions, issues, or suggestions:
- Open an issue on GitHub
- Contact: your-email@example.com

---

**Built with ❤️ using Laravel, Alpine.js, and Tailwind CSS**
