<?php

use App\Models\Role;
use App\Support\Workflow;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
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
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Don't delete system roles
    }
};
