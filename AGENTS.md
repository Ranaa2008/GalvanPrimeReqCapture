# AI Agent Instructions for Laravel Dynamic RBAC Boilerplate

> **📌 Essential Guide for AI Agents working with this Laravel boilerplate**
> 
> This document provides comprehensive instructions for AI agents (like yourself) to understand, navigate, and work effectively with this enterprise-grade Laravel boilerplate featuring dynamic RBAC, management hierarchy, and advanced user management.

---

## 🎯 Quick Overview

**Project Type**: Laravel 12.35.1 Enterprise Boilerplate  
**Primary Features**: 100% Dynamic RBAC, Management Tree Hierarchy, Multi-Method Authentication  
**Serving Structure**: Serves from root with automatic redirect to public/ directory  
**Database**: MySQL/PostgreSQL/SQLite  
**Frontend**: Blade Templates + Alpine.js + Tailwind CSS  

---

## 📂 Critical Project Structure

### Root Directory Layout
```
root/                          
├── index.php                  ← Redirects to public/
├── .htaccess                  ← Apache rewrite to public/
├── public/                    ← Actual serving directory
│   ├── index.php              ← Main entry point
│   ├── robots.txt             ← SEO configuration
│   └── build/                 ← Vite compiled assets
│       ├── manifest.json
│       └── assets/
├── app/                       ← Application logic
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Admin/        ← RBAC management controllers
│   │   │   │   ├── UserController.php      (CRUD + verification + blocking)
│   │   │   │   ├── RoleController.php      (Dynamic role management)
│   │   │   │   ├── PermissionController.php
│   │   │   │   └── DashboardController.php
│   │   │   └── Auth/         ← Multi-method authentication
│   │   ├── Middleware/
│   │   │   ├── AdminMiddleware.php         (Management access check)
│   │   │   └── CheckIfBlocked.php          (Blocked user handling)
│   │   └── Requests/         ← Form validation
│   ├── Models/
│   │   ├── User.php          ← CRITICAL: Hierarchy methods (getManagementTreeIds, canManage)
│   │   └── Role.php          ← Extended with 'superiority' field
│   ├── Repositories/         ← Data access layer
│   │   ├── UserRepository.php
│   │   └── RoleRepository.php
│   └── Services/             ← Business logic
│       ├── OtpService.php
│       ├── AuthorizationService.php
│       └── ManagementTreeService.php
├── database/
│   ├── migrations/
│   │   ├── *_add_hierarchy_system_to_tables.php  ← Adds managed_by, superiority
│   │   ├── *_add_avatar_to_users.php
│   │   └── *_create_permission_tables.php
│   └── seeders/
│       ├── RolePermissionSeeder.php              ← Initial setup
│       └── HierarchySystemSeeder.php
├── resources/
│   ├── views/
│   │   ├── admin/            ← Admin panel views (users, roles, permissions)
│   │   ├── auth/             ← Login (multi-method), Register
│   │   └── components/       ← Reusable Blade components
│   ├── css/app.css           ← Tailwind CSS
│   └── js/app.js             ← Alpine.js bootstrap
├── routes/
│   ├── web.php               ← Main routes
│   └── auth.php              ← Authentication routes
├── config/                   ← Laravel configuration
├── storage/                  ← Logs, cache, uploads
├── vendor/                   ← Composer dependencies
├── bootstrap/                ← Laravel bootstrap
└── public/                   ← Web server document root
    ├── index.php             ← Application entry point
    ├── robots.txt
    └── build/                ← Compiled assets from Vite
```

---

## 🔧 Critical Configuration Changes

### 1. Serving from Root Directory
**Important**: This boilerplate serves from root but automatically redirects to public/

**How it works**:
- Root [index.php](index.php) redirects all requests to public/
- [.htaccess](.htaccess) handles Apache rewrites to public/
- Allows serving with `php -S localhost:8000` from root
- Production servers can point to root or public/ directory

**To start development**:
```bash
# From root directory
php -S localhost:7500
# Automatically redirects to public/ via index.php

# Or use Laravel's artisan
php artisan serve
```

**Production deployment**:
Point your web server document root to the application root directory. The .htaccess will handle redirects to public/.

### 2. Vite Asset Building
```bash
npm run dev     # Development with hot reload
npm run build   # Production build to public/build/
```

Assets are referenced in Blade templates using:
```blade
@vite(['resources/css/app.css', 'resources/js/app.js'])
```

---

## 🏗️ Core Architecture Concepts

### 1. Dynamic RBAC System (100% Database-Driven)

**Key Principle**: NO hardcoded role checks (except super-admin)

#### Role Structure
```php
roles table:
- id
- name (any custom name: "Admin", "Teacher", "Manager", "CEO", etc.)
- superiority (1-99): Lower number = Higher authority
  - 1: Reserved for Super Admin
  - 2-99: Custom roles (user-defined)
- guard_name
```

**Why Superiority?**
- Determines who can manage whom
- User with superiority 2 can manage users with superiority 3+
- Database-driven hierarchy, infinitely flexible

#### Permission System
```php
permissions table:
- id
- name (kebab-case: "view-users", "edit-roles")
- guard_name

role_has_permissions pivot:
- role_id
- permission_id
- assignable (boolean): Can this role delegate this permission to subordinates?
```

**Available Permissions** (as of current version):
- User Management: `view-users`, `create-users`, `edit-users`, `delete-users`, `verify-users`, `deverify-users`, `block-users`
- Role Management: `view-roles`, `create-roles`, `edit-roles`, `delete-roles`
- Permission Management: `view-permissions`, `create-permissions`, `edit-permissions`, `delete-permissions`
- Assignment: `assign-roles`, `assign-permissions`

### 2. Management Tree Hierarchy

**Critical Understanding**: Two-layered authorization system

#### Layer 1: Superiority (Authority Level)
```
Super Admin (1) > Admin (2) > Manager (3) > Staff (5)
```

#### Layer 2: Management Tree (Reporting Structure)
```sql
users table:
- managed_by (FK to users.id): Who is this user's direct manager?
```

Example hierarchy:
```
Super Admin (superiority: 1, managed_by: NULL)
├── Admin North (superiority: 2, managed_by: Super Admin)
│   ├── Manager N1 (superiority: 3, managed_by: Admin North)
│   │   └── Staff N1A (superiority: 5, managed_by: Manager N1)
│   └── Manager N2 (superiority: 3, managed_by: Admin North)
└── Admin South (superiority: 2, managed_by: Super Admin)
    └── Manager S1 (superiority: 3, managed_by: Admin South)
```

**Access Rules**:
- Admin North can ONLY see/edit: Admin North, Manager N1, Manager N2, Staff N1A
- Admin North CANNOT see: Admin South, Manager S1 (different management tree)
- Manager N1 can ONLY see/edit: Manager N1, Staff N1A
- Super Admin sees EVERYONE (bypasses all restrictions)

#### Implementation in Code

**User Model Critical Methods**:
```php
class User extends Authenticatable
{
    // Get all subordinates recursively (direct + indirect)
    public function getManagementTreeIds(): array
    
    // Check if current user can manage target user
    public function canManage(User $targetUser): bool
    
    // Get user's highest authority level
    public function getSuperiority(): int
    
    // Check if user can assign a specific role
    public function canAssignRole(Role $role): bool
}
```

**UserController Key Methods**:
```php
// ALWAYS filter user lists by management tree
public function index()
{
    $userIds = $this->getManagementTreeIds(auth()->id());
    $users = User::whereIn('id', $userIds)->paginate(15);
}

// Recursive tree lookup
private function getManagementTreeIds($userId): array
{
    $ids = [$userId];
    $directReports = User::where('managed_by', $userId)->pluck('id');
    foreach ($directReports as $reportId) {
        $ids = array_merge($ids, $this->getManagementTreeIds($reportId));
    }
    return array_unique($ids);
}
```

### 3. Multi-Method Authentication

**Login accepts any of**:
1. Email address
2. Phone number (primary)
3. Username

**Implementation** (LoginRequest.php):
```php
public function authenticate()
{
    $identifier = $this->input('identifier');
    $field = filter_var($identifier, FILTER_VALIDATE_EMAIL) ? 'email' 
           : (preg_match('/^\+?[0-9]{10,15}$/', $identifier) ? 'phone_number' 
           : 'username');
           
    $credentials = [$field => $identifier, 'password' => $this->input('password')];
    // ... authentication logic
}
```

### 4. User Verification System

**Critical Understanding**: Two types of verification exist in this system

#### Self-Verification (Users verify themselves)
- Users can verify their own email and phone number
- **Email Verification**: User receives OTP code via email, enters code to verify
- **Phone Verification**: User receives OTP code via SMS, enters code to verify
- **Both verifications required**: Email AND phone must be verified for complete account activation
- Access: Available in user profile page

#### Admin-Verification (Admins verify other users)
- Users with `verify-users` permission can manually verify other users' email/phone
- Users with `deverify-users` permission can remove verification status
- Quick verification without OTP codes
- Access: Available in admin panel user management

**Verification Workflow**:
```
1. User registers → Account created (unverified)
2. User logs in → Dashboard with verification alerts shown
3. User verifies email → Sends OTP, enters code
4. User verifies phone → Sends OTP, enters code
5. Both verified → Account verification complete
6. Super-admin assigns role → User gains system access
```

**Important**: Until a super-admin (or user with `assign-roles` permission) assigns a role to the user, they have NO permissions to access any part of the system except their profile.

### 5. Role Assignment Requirement

**Critical System Behavior**: New users have ZERO access until role assigned

**After Registration**:
- User can login ✅
- User can view profile ✅
- User can verify email/phone ✅
- User can logout ✅
- User CANNOT access any other feature ❌

**After Role Assignment**:
- User gains permissions based on assigned role
- Can access features according to role permissions
- Subject to management tree filtering

**Who can assign roles**:
- Super-admin (always)
- Any user with `assign-roles` permission
- Can only assign roles with lower superiority than their own

### 6. User Blocking System

**blocked_at column** (nullable timestamp):
- NULL: User is active
- Timestamp: User is blocked

**Blocked User Behavior**:
- Can login (not prevented)
- Can view own profile (read-only)
- Can logout
- CANNOT edit anything, access other pages, or perform actions

**Middleware**: `CheckIfBlocked.php` enforces restrictions

---

## � User Lifecycle & Verification Flow

### Complete User Journey

#### 1. Registration Phase
```
User fills registration form:
- Full name
- Username (unique)
- Email (unique)
- Phone number (unique, required)
- Address
- Password

System creates user:
- email_verified = false
- phone_verified = false
- No roles assigned = NO SYSTEM ACCESS
```

#### 2. Post-Registration Access
```
User logs in → Sees dashboard with alerts:
- "Email not verified" (yellow alert)
- "Phone not verified" (yellow alert)
- Can only access:
  - Profile page (read/edit own info)
  - Verification pages
  - Logout
```

#### 3. Self-Verification Process
```
Email Verification:
- User clicks "Verify Email" in profile
- System generates 6-digit OTP
- OTP sent to user's email
- User enters OTP code
- System marks email_verified = true

Phone Verification:
- User clicks "Verify Phone" in profile
- System generates 6-digit OTP
- OTP sent via SMS to phone number
- User enters OTP code
- System marks phone_verified = true
```

#### 4. Role Assignment Phase
```
Super-admin/Admin logs in:
- Views user list (filtered by management tree)
- Sees new unverified users
- Can manually verify email/phone (instant, no OTP)
- Assigns appropriate role to user
- Selects manager for user (if applicable)
- User now has permissions based on role
```

#### 5. Active User Phase
```
User logs in again:
- Has role assigned = System access granted
- Can access features per role permissions
- Sees only users in management tree
- Subject to superiority hierarchy rules
```

### Verification Permission Matrix

| Action | User (Self) | Admin with Permission | Super Admin |
|--------|-------------|----------------------|-------------|
| Verify own email | ✅ (via OTP) | - | - |
| Verify own phone | ✅ (via OTP) | - | - |
| Verify others' email | ❌ | ✅ (if has `verify-users`) | ✅ |
| Verify others' phone | ❌ | ✅ (if has `verify-users`) | ✅ |
| Remove verification | ❌ | ✅ (if has `deverify-users`) | ✅ |
| Assign roles | ❌ | ✅ (if has `assign-roles`) | ✅ |

### Key Points for AI Agents

1. **No Role = No Access**: Users without roles can ONLY access their profile, nothing else
2. **Verification is Optional for Access**: User can be assigned role without verification
3. **Both Verifications Recommended**: For complete account activation, verify both email and phone
4. **Admins Bypass OTP**: Admin verification is instant (no OTP codes needed)
5. **Role Assignment is Critical**: This is the gate that unlocks system access

---

## �🛠️ Common Tasks & How to Implement

### Task 1: Add a New Permission

**Steps**:
1. Create migration:
```bash
php artisan make:migration add_new_permission_to_permissions_table
```

2. In migration:
```php
use Spatie\Permission\Models\Permission;

Permission::create(['name' => 'export-reports', 'guard_name' => 'web']);
```

3. Assign to role (if needed):
```php
$role = Role::findByName('Admin');
$role->givePermissionTo('export-reports');
```

4. Use in code:
```blade
@can('export-reports')
    <button>Export</button>
@endcan
```

### Task 2: Add a New Role

**Via Web Interface**:
1. Login as super-admin
2. Navigate to /admin/roles/create
3. Enter role name (e.g., "Department Head")
4. Set superiority level (e.g., 4)
5. Select permissions
6. Save

**Programmatically**:
```php
$role = Role::create([
    'name' => 'Department Head',
    'superiority' => 4,
    'guard_name' => 'web'
]);

$role->givePermissionTo(['view-users', 'edit-users']);
```

### Task 3: Create a User and Assign Manager

```php
$user = User::create([
    'name' => 'John Doe',
    'username' => 'johndoe',
    'email' => 'john@example.com',
    'phone_number' => '+1234567890',
    'password' => Hash::make('password'),
    'managed_by' => $managerId, // FK to manager's user ID
]);

$role = Role::find($roleId);
$user->assignRole($role);
```

**Important**: Manager must have higher authority (lower superiority) than assigned role

### Task 4: Filter Users by Management Tree

```php
// In any controller method
$currentUserId = auth()->id();
$managementTreeIds = $this->getManagementTreeIds($currentUserId);

$users = User::whereIn('id', $managementTreeIds)
    ->with('roles')
    ->paginate(15);
```

### Task 5: Check Authorization

**In Controllers**:
```php
// Check permission
if (!auth()->user()->can('edit-users')) {
    abort(403, 'Unauthorized');
}

// Check if can manage specific user
if (!auth()->user()->canManage($targetUser)) {
    abort(403, 'You cannot manage this user');
}
```

**In Blade**:
```blade
@can('edit-users')
    <!-- Show edit button -->
@endcan

@if(auth()->user()->canManage($user))
    <!-- Show management actions -->
@endif
```

### Task 6: Get Available Managers for a Role

**API Endpoint**: GET `/admin/users/managers-by-role?role_id={id}`

Returns users who:
1. Have superiority < target role's superiority
2. Are in current user's management tree

**Usage in Alpine.js**:
```javascript
async updateManagers() {
    const roleId = this.selectedRole;
    const response = await fetch(`/admin/users/managers-by-role?role_id=${roleId}`);
    this.availableManagers = await response.json();
}
```

---

## 🔍 Debugging & Troubleshooting

### Issue: User Cannot See Expected Subordinates

**Check**:
1. Is `managed_by` set correctly in database?
   ```sql
   SELECT id, name, managed_by FROM users WHERE id IN (1,2,3);
   ```

2. Is superiority set on roles?
   ```sql
   SELECT id, name, superiority FROM roles;
   ```

3. Test recursive query:
   ```php
   // In tinker
   $ids = app(App\Http\Controllers\Admin\UserController::class)->getManagementTreeIds(1);
   print_r($ids);
   ```

### Issue: Permission Check Fails

**Verify**:
1. User has role assigned:
   ```php
   User::find(1)->roles; // Should return collection of roles
   ```

2. Role has permission:
   ```php
   Role::findByName('Admin')->permissions; // Should include permission
   ```

3. Clear permission cache:
   ```bash
   php artisan permission:cache-reset
   ```

### Issue: Manager Dropdown Empty

**Causes**:
1. No users have higher authority than selected role
2. Current user's management tree is empty
3. JavaScript error (check browser console)
4. Route not working (check `php artisan route:list`)

### Issue: Assets Not Loading

**Solutions**:
1. Rebuild assets:
   ```bash
   npm run build
   ```

2. Check `build/` directory exists with `manifest.json`

3. Verify `@vite()` directive in layouts

4. Clear view cache:
   ```bash
   php artisan view:clear
   ```

---

## 🚀 Development Workflow

### Starting Fresh

```bash
# 1. Install dependencies
composer install
npm install

# 2. Environment setup
cp .env.example .env
php artisan key:generate

# 3. Database setup
# Configure DB_* in .env, then:
php artisan migrate:fresh --seed

# 4. Build assets
npm run build

# 5. Start server
php artisan serve
```

**Default Login**:
- Email: admin@example.com
- Password: password

### Making Changes

**Adding Features**:
1. Create migration if DB changes needed
2. Update models/controllers
3. Add routes
4. Create/update views
5. Add permissions if needed
6. Test with different user roles

**Testing Different Roles**:
1. Login as super-admin (sees everything)
2. Create test users with different roles
3. Assign different managers to test tree filtering
4. Login as each test user to verify access

---

## 📋 Checklist for Common Modifications

### Adding a New Module (e.g., "Projects")

- [ ] Create migration for `projects` table
- [ ] Create `Project` model with relationships
- [ ] Create permissions: `view-projects`, `create-projects`, etc.
- [ ] Assign permissions to appropriate roles
- [ ] Create `ProjectController` with authorization checks
- [ ] Add routes to `web.php`
- [ ] Create views in `resources/views/admin/projects/`
- [ ] Add navigation link to sidebar
- [ ] Filter by management tree if user-specific
- [ ] Test with different user roles

### Customizing User Fields

- [ ] Create migration to add columns to `users` table
- [ ] Update `User` model `$fillable` array
- [ ] Update registration form
- [ ] Update user edit form
- [ ] Update validation rules in Request classes
- [ ] Update user list view if displaying new fields

---

## 🎯 Best Practices for AI Agents

### When Modifying Code

1. **Always Consider Management Tree**: If code involves users, filter by management tree unless super-admin
   
2. **Never Hardcode Roles**: Check permissions, not role names (except super-admin check)
   ```php
   // ❌ Bad
   if (auth()->user()->hasRole('Admin'))
   
   // ✅ Good
   if (auth()->user()->can('edit-users'))
   ```

3. **Respect Superiority Levels**: When assigning roles or managers, ensure hierarchy is maintained

4. **Use Repositories**: Don't query directly in controllers; use repository pattern where implemented

5. **Follow Naming Conventions**:
   - Permissions: kebab-case (`view-users`, not `view users` or `view_users`)
   - Routes: kebab-case (`/admin/users/create`)
   - Controllers: StudlyCase (`UserController`)
   - Methods: camelCase (`getManagementTreeIds`)

### When Answering Questions

1. **Check Current Structure First**: Read relevant files before suggesting changes

2. **Consider Impact**: Changes to User/Role models affect entire system

3. **Test Scenarios**: Think through different user roles accessing the same feature

4. **Reference Existing Code**: Point to similar implementations already in the codebase

5. **Explain Context**: This is an ERP-style system, not a blog. Security and hierarchy matter.

---

## 📚 Key Files Reference

### Must-Read Files for Understanding System

1. **app/Models/User.php** - Hierarchy methods, relationships
2. **app/Models/Role.php** - Superiority field
3. **app/Http/Controllers/Admin/UserController.php** - Management tree implementation
4. **app/Http/Middleware/CheckIfBlocked.php** - Blocking logic
5. **database/seeders/RolePermissionSeeder.php** - Initial setup
6. **resources/views/admin/users/index.blade.php** - UI patterns
7. **routes/web.php** - Route structure and middleware

### Configuration Files

- **config/permission.php** - Spatie permission config
- **config/filesystems.php** - File storage (Cloudinary integration)
- **vite.config.js** - Asset compilation (outputs to public/build/)
- **.env** - Environment variables

---

## 🔐 Security Considerations

1. **Management Tree Isolation**: Users can ONLY see their tree. Never return all users without filtering.

2. **Authorization Layers**: Always check both permission AND management tree AND superiority

3. **Super Admin Bypass**: Only super-admin (superiority 1) bypasses tree restrictions

4. **Blocked Users**: Cannot perform actions but can view profile

5. **Password Security**: Uses bcrypt, minimum 8 characters

6. **CSRF Protection**: All forms must include `@csrf` directive

7. **Input Validation**: Use Form Requests for validation

---

## 🎨 UI/UX Patterns

### Design System

- **Colors**: Black/White primary, green (success), red (danger), blue (info)
- **Icons**: Clean SVG stroke icons (Heroicons style), no emojis
- **Typography**: Poppins font, 14px base (0.875rem)
- **Dark Mode**: System-wide toggle, persistent via localStorage
- **Responsive**: Mobile-first, collapsible sidebar

### Common Components

```blade
<!-- Tooltip -->
<x-tooltip text="Click to edit" position="top">
    <button>Edit</button>
</x-tooltip>

<!-- Layout -->
<x-layout.topbar />
<x-layout.sidebar />

<!-- Verification Icons -->
@if($user->email_verified)
    <svg class="text-green-600">...</svg>
@else
    <svg class="text-red-600">...</svg>
@endif
```

---

## 🌟 Advanced Features

### 1. OTP Verification

**Email OTP**:
```php
ProfileController::sendEmailOtp()  // Generates 6-digit code, stores in session
ProfileController::verifyEmailOtp() // Validates and marks verified
```

**Phone OTP**:
- Structure ready for SMS integration (Twilio, etc.)
- Currently stores in session, needs SMS provider configuration

### 2. Profile Pictures (Cloudinary)

- Upload to Cloudinary (off-server storage)
- Stores `avatar_url` and `avatar_public_id`
- Fallback to letter avatars (initials)
- Dark mode compatible

### 3. Dynamic Manager Selection

- Alpine.js reactive dropdown
- Fetches available managers via AJAX
- Filters by role superiority and management tree

### 4. Assignable Permissions

- `assignable` flag in `role_has_permissions` pivot
- Controls permission delegation hierarchy
- Super admins can delegate all, others restricted

---

## 🚢 Deployment Notes

### Production Checklist

- [ ] Set `APP_ENV=production` and `APP_DEBUG=false`
- [ ] Generate new `APP_KEY`
- [ ] Configure production database
- [ ] Set up Cloudinary credentials
- [ ] Run `php artisan config:cache`
- [ ] Run `php artisan route:cache`
- [ ] Run `php artisan view:cache`
- [ ] Run `composer install --optimize-autoloader --no-dev`
- [ ] Run `npm run build`
- [ ] Set proper file permissions (755 for storage/ and bootstrap/cache/)
- [ ] Point web server to public/ directory
- [ ] Set up SSL certificate
- [ ] Configure queue workers if using jobs
- [ ] Set up scheduled tasks (cron) if needed

### Web Server Configuration

**Nginx Example**:
```nginx
server {
    listen 80;
    server_name yourdomain.com;
    root /var/www/laravel-boilerplate/public;  # Points to public/

    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

**Apache**: No special config needed if using .htaccess in public/

---

## 💡 Pro Tips for AI Agents

1. **When unsure about hierarchy**: Test with `php artisan tinker` to query management tree

2. **When debugging permissions**: Use `php artisan permission:cache-reset` frequently

3. **When testing**: Create users with different roles and managed_by values to test tree isolation

4. **When modifying User model**: Remember it's used throughout the system; consider impact

5. **When adding features**: Follow existing patterns (UserController is excellent reference)

6. **When user reports "can't see users"**: First thing to check is management tree and superiority

7. **When permission check fails**: Verify user → role → permission chain in database

8. **When assets don't load**: Check public/build/ directory and manifest.json exist

---

## 📞 Quick Command Reference

```bash
# Development
php artisan serve                    # Start dev server
npm run dev                          # Watch mode for assets
php artisan tinker                   # Interactive console

# Database
php artisan migrate                  # Run migrations
php artisan migrate:fresh --seed    # Reset and seed
php artisan db:seed                  # Seed only

# Cache
php artisan config:cache            # Cache config
php artisan route:cache             # Cache routes
php artisan view:cache              # Cache views
php artisan cache:clear             # Clear app cache
php artisan permission:cache-reset  # Clear permission cache

# Assets
npm run build                       # Build for production
npm run dev                         # Development mode

# Debugging
php artisan route:list              # List all routes
php artisan config:clear            # Clear cached config
php artisan view:clear              # Clear compiled views
```

---

## 🎓 Learning Path for New AI Agents

1. **Start Here**: Read this file completely
2. **Understand RBAC**: Read User.php and Role.php models
3. **Study Hierarchy**: Read UserController.php, focus on management tree methods
4. **Explore Views**: Check admin/users/index.blade.php for UI patterns
5. **Review Routes**: Read web.php and auth.php
6. **Check Middleware**: Read AdminMiddleware.php and CheckIfBlocked.php
7. **Try It Out**: Use `php artisan serve` and explore the UI with different users

---

## ✅ Final Notes

This boilerplate is designed for **enterprise ERP systems** where:
- Users belong to organizational hierarchies
- Access control is strict and layered
- Roles and permissions are 100% dynamic
- Management tree isolation is critical for data security

When in doubt, prioritize:
1. Security (never expose data outside management tree)
2. Flexibility (support any role name, any hierarchy depth)
3. User experience (clear feedback, fast responses)

---

**Document Version**: 1.0  
**Last Updated**: December 20, 2025  
**Laravel Version**: 12.35.1  
**Maintained For**: AI Agent Collaboration

---

*For detailed feature documentation, see DOCUMENTATION.md (archived)*  
*For project overview, see README.md (archived)*
