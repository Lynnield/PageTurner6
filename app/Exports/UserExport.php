<?php

namespace App\Exports;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldQueue;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class UserExport implements FromQuery, WithHeadings, WithMapping, ShouldQueue
{
    public function __construct(
        private readonly bool $redactPii = true
    ) {}

    public function query(): Builder
    {
        return User::query()->orderBy('id');
    }

    public function headings(): array
    {
        return [
            'User ID',
            'Name',
            'Email',
            'Role',
            'Joined At'
        ];
    }

    public function map($user): array
    {
        /** @var \App\Models\User $user */
        
        $email = $user->email;
        $name = $user->name;

        if ($this->redactPii) {
            // Mask email (e.g., j***@example.com)
            $emailParts = explode('@', $email);
            if (count($emailParts) === 2) {
                $email = substr($emailParts[0], 0, 1) . '***@' . $emailParts[1];
            }
            
            // Mask name partially
            $name = substr($name, 0, 1) . '***';
        }

        return [
            $user->id,
            $name,
            $email,
            ucfirst($user->role),
            $user->created_at->format('Y-m-d'),
        ];
    }
}
