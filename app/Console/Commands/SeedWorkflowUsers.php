<?php

namespace App\Console\Commands;

use App\Models\Role;
use App\Models\User;
use App\Support\Workflow;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('workflow:seed-users')]
#[Description('Seed or update accounts for ADS Sales Order workflow stages 1 through 7 (Jovy, Fred, Owen, Macy, Badet, King, Content, IT, Accounting)')]
class SeedWorkflowUsers extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Ensuring all workflow roles are registered...');

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

        $stageUsers = [
            [
                'stage' => 'Stage 2 & 4',
                'name' => 'Jovy',
                'email' => 'jovy@globaltronics.net',
                'password' => 'GlobaltronicsApprover@2026',
                'role_slugs' => [Workflow::ROLE_JOVY],
                'responsibility' => 'ASO 1st Approval & SJO 2nd Approval',
            ],
            [
                'stage' => 'Stage 2, 4 & 5',
                'name' => 'Fred',
                'email' => 'fred@globaltronics.net',
                'password' => 'GlobaltronicsApprover@2026',
                'role_slugs' => [Workflow::ROLE_FRED],
                'responsibility' => 'ASO 1st Approval, SJO 2nd Approval & Creative Review',
            ],
            [
                'stage' => 'Stage 2',
                'name' => 'Owen',
                'email' => 'owen@globaltronics.net',
                'password' => 'GlobaltronicsApprover@2026',
                'role_slugs' => [Workflow::ROLE_OWEN],
                'responsibility' => 'ASO 2nd Approval',
            ],
            [
                'stage' => 'Stage 2',
                'name' => 'Macy Guido Lee',
                'email' => 'macy@globaltronics.net',
                'password' => 'GlobaltronicsApprover@2026',
                'role_slugs' => [Workflow::ROLE_MACY],
                'responsibility' => 'ASO 3rd Final Approval',
            ],
            [
                'stage' => 'Stage 4',
                'name' => 'Badet',
                'email' => 'badet@globaltronics.net',
                'password' => 'GlobaltronicsApprover@2026',
                'role_slugs' => [Workflow::ROLE_BADET],
                'responsibility' => 'SJO 1st Approval',
            ],
            [
                'stage' => 'Stage 4',
                'name' => 'King',
                'email' => 'king@globaltronics.net',
                'password' => 'GlobaltronicsApprover@2026',
                'role_slugs' => [Workflow::ROLE_KING],
                'responsibility' => 'SJO 1st Approval',
            ],
            [
                'stage' => 'Stage 5',
                'name' => 'Content Team',
                'email' => 'content@globaltronics.net',
                'password' => 'GlobaltronicsContent@2026',
                'role_slugs' => [Workflow::ROLE_CONTENT],
                'responsibility' => 'Material Check, Artist Assignment & Client Proofing',
            ],
            [
                'stage' => 'Stage 6',
                'name' => 'IT Operations',
                'email' => 'it@globaltronics.net',
                'password' => 'GlobaltronicsIt@2026',
                'role_slugs' => [Workflow::ROLE_IT],
                'responsibility' => 'LED Player Upload & Monitoring Telemetry',
            ],
            [
                'stage' => 'Stage 7',
                'name' => 'Accounting Team',
                'email' => 'accounting@globaltronics.net',
                'password' => 'GlobaltronicsAcct@2026',
                'role_slugs' => [Workflow::ROLE_ACCOUNTING],
                'responsibility' => 'Verification Checklist, Amortization & Financial Audit',
            ],
        ];

        $tableRows = [];

        foreach ($stageUsers as $data) {
            $user = User::updateOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'password' => $data['password'],
                    'is_admin' => false,
                ]
            );

            $roleIds = Role::whereIn('slug', $data['role_slugs'])->pluck('id')->toArray();
            if (!empty($roleIds)) {
                $user->roles()->syncWithoutDetaching($roleIds);
            }

            $tableRows[] = [
                $data['stage'],
                $data['name'],
                $data['email'],
                $data['password'],
                implode(', ', $data['role_slugs']),
                $data['responsibility'],
            ];
        }

        $this->table(
            ['Stage', 'Name', 'Email (Login)', 'Password', 'Assigned Roles', 'Workflow Responsibility'],
            $tableRows
        );

        $this->info('Workflow stage user accounts configured successfully!');

        return Command::SUCCESS;
    }
}
