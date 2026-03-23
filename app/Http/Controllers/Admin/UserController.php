<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use App\Repositories\UserRepository;
use App\Repositories\RoleRepository;
use App\Services\ManagementTreeService;
use App\Services\AuthorizationService;
use App\Services\PhoneNumberService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class UserController extends Controller
{
    use AuthorizesRequests;

    protected $userRepository;
    protected $roleRepository;
    protected $treeService;
    protected $authService;
    protected $phoneService;

    public function __construct(
        UserRepository $userRepository,
        RoleRepository $roleRepository,
        ManagementTreeService $treeService,
        AuthorizationService $authService,
        PhoneNumberService $phoneService
    ) {
        $this->userRepository = $userRepository;
        $this->roleRepository = $roleRepository;
        $this->treeService = $treeService;
        $this->authService = $authService;
        $this->phoneService = $phoneService;
    }


    /**
     * Get available managers based on role superiority
     */
    public function getManagersByRole(Request $request)
    {
        $roleId = $request->input('role_id');
        
        if (!$roleId) {
            return response()->json([]);
        }
        
        $role = $this->roleRepository->find($roleId);
        
        if (!$role || !$role->superiority) {
            return response()->json([]);
        }
        
        $currentUser = auth()->user();
        
        // Get allowed user IDs based on management tree
        $allowedUserIds = [];
        if (!$currentUser->hasRole('super-admin')) {
            $allowedUserIds = $this->treeService->getCachedManagementTreeIds($currentUser->id);
        }
        
        $managers = $this->userRepository->getAvailableManagers($role->superiority, $allowedUserIds);
        
        return response()->json($managers);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Check permission or super-admin
        $user = auth()->user();
        if (!$user->hasRole('super-admin') && !$user->hasPermissionTo('view-users')) {
            abort(403, 'You do not have permission to view users.');
        }
        
        $filters = [];
        
        // Filter by management tree (except super-admin sees all)
        if (!$user->hasRole('super-admin')) {
            $filters['user_ids'] = $this->treeService->getCachedManagementTreeIds($user->id);
        }
        
        // Server-side search
        if ($request->filled('search')) {
            $filters['search'] = $request->search;
        }
        
        $users = $this->userRepository->getPaginatedUsers($filters, 15);
        
        return view('admin.users.index', compact('users'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Check permission or super-admin
        $user = auth()->user();
        if (!$user->hasRole('super-admin') && !$user->hasPermissionTo('create-users')) {
            abort(403, 'You do not have permission to create users.');
        }
        
        $roles = $this->authService->getAssignableRoles($user);
        
        // Get available managers (users with higher authority - lower superiority number)
        $currentSuperiority = $this->authService->getUserHighestSuperiority($user);
        $managers = $this->userRepository->getUsersByRoleSuperiority($currentSuperiority, '<');
        
        return view('admin.users.create', compact('roles', 'managers'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreUserRequest $request)
    {
        $validated = $request->validated();
        $currentUser = auth()->user();

        // Validate that user can only assign roles they have permission for
        if (isset($validated['roles'])) {
            $validation = $this->authService->validateRoleAssignment($currentUser, $validated['roles']);
            
            if (!$validation['valid']) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', $validation['error']);
            }
        }

        $validated['password'] = Hash::make($validated['password']);

        // Format phone numbers to international standard
        $validated = $this->phoneService->formatPhoneNumbersInData($validated);

        // Set managed_by - if not provided, default to current user (creator)
        if (empty($validated['managed_by'])) {
            $validated['managed_by'] = $currentUser->id;
        }

        $user = $this->userRepository->create($validated);
        
        if (isset($validated['roles'])) {
            $this->userRepository->syncRoles($user, $validated['roles']);
        }

        // Invalidate management tree cache
        $this->treeService->invalidateCache($user->id);

        return redirect()->route('admin.users.index')
            ->with('success', 'User created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        $user = $this->userRepository->findWithRelations($user->id);
        return view('admin.users.show', compact('user'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user)
    {
        // Check permission or super-admin
        $currentUser = auth()->user();
        if (!$currentUser->hasRole('super-admin') && !$currentUser->hasPermissionTo('edit-users')) {
            abort(403, 'You do not have permission to edit users.');
        }
        
        // Check if current user can manage this user
        if (!$this->authService->canManageUser($currentUser, $user, $this->treeService)) {
            return redirect()->route('admin.users.index')
                ->with('error', 'You do not have permission to edit this user.');
        }

        $roles = $this->authService->getAssignableRoles($currentUser);
        $userRoles = $user->roles->pluck('id')->toArray();
        
        // Get available managers (users with higher authority - lower superiority number)
        $currentSuperiority = $this->authService->getUserHighestSuperiority($currentUser);
        $managers = $this->userRepository->getUsersByRoleSuperiority($currentSuperiority, '<', [$user->id]);
        
        return view('admin.users.edit', compact('user', 'roles', 'userRoles', 'managers'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateUserRequest $request, User $user)
    {
        $currentUser = auth()->user();
        
        // Check if current user can manage this user
        if (!$this->authService->canManageUser($currentUser, $user, $this->treeService)) {
            return redirect()->route('admin.users.index')
                ->with('error', 'You do not have permission to update this user.');
        }

        $validated = $request->validated();

        // Validate that user can only assign roles they have permission for
        if (isset($validated['roles'])) {
            $validation = $this->authService->validateRoleAssignment($currentUser, $validated['roles']);
            
            if (!$validation['valid']) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', $validation['error']);
            }
        }

        if (isset($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        // Format phone numbers to international standard
        $validated = $this->phoneService->formatPhoneNumbersInData($validated);

        $oldManagedBy = $user->managed_by;
        
        $this->userRepository->update($user, $validated);
        $this->userRepository->syncRoles($user, $validated['roles'] ?? []);

        // Invalidate cache if managed_by changed
        if ($oldManagedBy !== $user->managed_by) {
            $this->treeService->invalidateCache($user->id);
            if ($oldManagedBy) {
                $this->treeService->invalidateCache($oldManagedBy);
            }
        }

        return redirect()->route('admin.users.index')
            ->with('success', 'User updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        // Check permission or super-admin
        $currentUser = auth()->user();
        if (!$currentUser->hasRole('super-admin') && !$currentUser->hasPermissionTo('delete-users')) {
            abort(403, 'You do not have permission to delete users.');
        }
        
        // Cannot delete yourself
        if ($user->id === $currentUser->id) {
            return redirect()->route('admin.users.index')
                ->with('error', 'Cannot delete your own account.');
        }

        // Check if current user can manage this user
        if (!$this->authService->canManageUser($currentUser, $user, $this->treeService)) {
            return redirect()->route('admin.users.index')
                ->with('error', 'You do not have permission to delete this user.');
        }

        $managedBy = $user->managed_by;
        
        $this->userRepository->delete($user);

        // Invalidate cache
        if ($managedBy) {
            $this->treeService->invalidateCache($managedBy);
        }

        return redirect()->route('admin.users.index')
            ->with('success', 'User deleted successfully.');
    }

    /**
     * Manually verify user's email (Admin/Super-Admin only)
     */
    public function verifyEmail(User $user)
    {
        $currentUser = auth()->user();
        
        // Check if current user can manage this user
        if (!$this->authService->canManageUser($currentUser, $user, $this->treeService)) {
            return redirect()->route('admin.users.index')
                ->with('error', 'You do not have permission to verify this user.');
        }

        $this->userRepository->verifyEmail($user);

        return redirect()->route('admin.users.index')
            ->with('success', 'Email verified successfully for user: ' . $user->name);
    }

    /**
     * Manually verify user's phone (Admin/Super-Admin only)
     */
    public function verifyPhone(User $user)
    {
        $currentUser = auth()->user();
        
        // Check if current user can manage this user
        if (!$this->authService->canManageUser($currentUser, $user, $this->treeService)) {
            return redirect()->route('admin.users.index')
                ->with('error', 'You do not have permission to verify this user.');
        }

        $this->userRepository->verifyPhone($user);

        return redirect()->route('admin.users.index')
            ->with('success', 'Phone verified successfully for user: ' . $user->name);
    }

    /**
     * Deverify user's email
     */
    public function deverifyEmail(User $user)
    {
        $currentUser = auth()->user();
        
        // Check permission
        if (!$currentUser->can('deverify-users')) {
            return redirect()->route('admin.users.index')
                ->with('error', 'You do not have permission to deverify users.');
        }

        // Check if current user can manage this user
        if (!$currentUser->canManage($user)) {
            return redirect()->route('admin.users.index')
                ->with('error', 'You do not have permission to deverify this user.');
        }

        $this->userRepository->deverifyEmail($user);

        return redirect()->route('admin.users.index')
            ->with('success', 'Email verification removed for user: ' . $user->name);
    }

    /**
     * Deverify user's phone
     */
    public function deverifyPhone(User $user)
    {
        $currentUser = auth()->user();
        
        // Check permission
        if (!$currentUser->can('deverify-users')) {
            return redirect()->route('admin.users.index')
                ->with('error', 'You do not have permission to deverify users.');
        }

        // Check if current user can manage this user
        if (!$currentUser->canManage($user)) {
            return redirect()->route('admin.users.index')
                ->with('error', 'You do not have permission to deverify this user.');
        }

        $this->userRepository->deverifyPhone($user);

        return redirect()->route('admin.users.index')
            ->with('success', 'Phone verification removed for user: ' . $user->name);
    }

    /**
     * Block or unblock a user
     */
    public function toggleBlock(User $user)
    {
        $currentUser = auth()->user();
        
        // Check permission
        if (!$currentUser->can('block-users')) {
            return redirect()->route('admin.users.index')
                ->with('error', 'You do not have permission to block/unblock users.');
        }

        // Check if current user can manage this user
        if (!$currentUser->canManage($user)) {
            return redirect()->route('admin.users.index')
                ->with('error', 'You do not have permission to manage this user.');
        }

        // Cannot block yourself
        if ($user->id === $currentUser->id) {
            return redirect()->route('admin.users.index')
                ->with('error', 'Cannot block your own account.');
        }

        if ($user->isBlocked()) {
            // Unblock
            $this->userRepository->unblock($user);
            $message = 'User unblocked successfully: ' . $user->name;
        } else {
            // Block
            $this->userRepository->block($user, $currentUser->id);
            $message = 'User blocked successfully: ' . $user->name;
        }

        return redirect()->route('admin.users.index')
            ->with('success', $message);
    }
}
