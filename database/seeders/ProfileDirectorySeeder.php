<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Department;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProfileDirectorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $departments = [
            'Executive',
            'Admin',
            'Sales',
            'TSD',
            'DBE',
            'Accounting',
            'Audit',
            'HRAD',
            'Purchasing',
            'Warehouse',
            'MIS',
            'VISMIN',
        ];

        foreach ($departments as $name) {
            Department::firstOrCreate(
                ['name' => $name],
                ['is_active' => true],
            );
        }

        $companies = [
            ['name' => 'DB International Sales & Services, Inc.', 'code' => 'DBISS'],
            ['name' => 'General', 'code' => 'GENERAL'],
            ['name' => 'GGC Foundation', 'code' => 'GGC'],
            ['name' => 'Globaltronics, Inc.', 'code' => 'GBTX'],
            ['name' => 'Glory International Technology Inc.', 'code' => 'GLORY'],
            ['name' => 'Nutriceutical', 'code' => 'NUTRICEUTICAL'],
            ['name' => 'Organic Coconut Association of The Philippines', 'code' => 'OCAP'],
            ['name' => 'Philippine Shanghai Chamber Of Commerce and Industry Inc.', 'code' => 'PSCCI'],
            ['name' => 'TeamAsia Corp.', 'code' => 'TEAMASIA'],
            ['name' => 'The Parc Foundations', 'code' => 'PARC'],
        ];

        foreach ($companies as $company) {
            Company::firstOrCreate(
                ['name' => $company['name']],
                ['code' => $company['code'], 'is_active' => true],
            );
        }
    }
}
