<?php

use App\Models\Role;
use App\Models\User;
use App\Support\Workflow;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations: Seed user accounts for all workflow stages (Stage 1 to Stage 7).
     */
    public function up(): void
    {
        // 1. Ensure all workflow roles exist
        $workflowRoles = Workflow::roles();
        foreach ($workflowRoles as $slug => $info) {
            Role::firstOrCreate(
                ['slug' => $slug],
                [
                    'name' => $info['name'],
                    'description' => $info['description'],
                    'badge_color' => 'blue',
                    'is_system' => true,
                ]
            );
        }

        // 2. Define user accounts for all workflow stages
        $stageUsers = [
            // Stage 2: ASO 1st & SJO 2nd Approvals
            [
                'name' => 'Jovy',
                'email' => 'jovy@globaltronics.net',
                'password' => 'GlobaltronicsApprover@2026',
                'roles' => [Workflow::ROLE_JOVY],
                'is_admin' => false,
            ],
            [
                'name' => 'Fred',
                'email' => 'fred@globaltronics.net',
                'password' => 'GlobaltronicsApprover@2026',
                'roles' => [Workflow::ROLE_FRED],
                'is_admin' => false,
            ],
            // Stage 2: ASO 2nd Approval
            [
                'name' => 'Owen',
                'email' => 'owen@globaltronics.net',
                'password' => 'GlobaltronicsApprover@2026',
                'roles' => [Workflow::ROLE_OWEN],
                'is_admin' => false,
            ],
            // Stage 2: ASO 3rd Final Approval
            [
                'name' => 'Macy Guido Lee',
                'email' => 'macy@globaltronics.net',
                'password' => 'GlobaltronicsApprover@2026',
                'roles' => [Workflow::ROLE_MACY],
                'is_admin' => false,
            ],
            // Stage 4: SJO 1st Approval
            [
                'name' => 'Badet',
                'email' => 'badet@globaltronics.net',
                'password' => 'GlobaltronicsApprover@2026',
                'roles' => [Workflow::ROLE_BADET],
                'is_admin' => false,
            ],
            [
                'name' => 'King',
                'email' => 'king@globaltronics.net',
                'password' => 'GlobaltronicsApprover@2026',
                'roles' => [Workflow::ROLE_KING],
                'is_admin' => false,
            ],
            // Stage 5: Content Review & Proofing
            [
                'name' => 'Content Team',
                'email' => 'content@globaltronics.net',
                'password' => 'GlobaltronicsContent@2026',
                'roles' => [Workflow::ROLE_CONTENT],
                'is_admin' => false,
            ],
            // Stage 6: IT Operations & Upload
            [
                'name' => 'IT Operations',
                'email' => 'it@globaltronics.net',
                'password' => 'GlobaltronicsIt@2026',
                'roles' => [Workflow::ROLE_IT],
                'is_admin' => false,
            ],
            // Stage 7: Accounting Audit & Verification
            [
                'name' => 'Accounting Team',
                'email' => 'accounting@globaltronics.net',
                'password' => 'GlobaltronicsAcct@2026',
                'roles' => [Workflow::ROLE_ACCOUNTING],
                'is_admin' => false,
            ],
        ];

        foreach ($stageUsers as $userData) {
            $user = User::updateOrCreate(
                ['email' => $userData['email']],
                [
                    'name' => $userData['name'],
                    'password' => $userData['password'],
                    'is_admin' => $userData['is_admin'],
                ]
            );

            $roleIds = Role::whereIn('slug', $userData['roles'])->pluck('id')->toArray();
            if (!empty($roleIds)) {
                $user->roles()->syncWithoutDetaching($roleIds);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Keep user accounts
    }
};
