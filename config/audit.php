<?php

$config = require base_path('vendor/owen-it/laravel-auditing/config/audit.php');

// Use our extended Audit model (adds checksum + metadata).
$config['implementation'] = \App\Models\Audit::class;

// Ensure the table matches our migration.
$config['drivers']['database']['table'] = 'audits';

// Allow auditing web + api guards.
$config['user']['guards'] = ['web', 'api'];

// Keep timestamps out of diffs to reduce noise.
$config['timestamps'] = false;

return $config;

