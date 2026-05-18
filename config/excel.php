<?php

use Maatwebsite\Excel\Excel;
use PhpOffice\PhpSpreadsheet\Reader\Csv;

return [
    'exports' => [
        'chunk_size'             => 1000,
        'pre_calculate_formulas' => false,
        'strict_null_comparison' => false,
        'csv'                    => [
            'delimiter'              => ',',
            'enclosure'              => '"',
            'line_ending'            => PHP_EOL,
            'use_bom'                => false,
            'include_separator_line' => false,
            'excel_compatibility'    => false,
            'output_encoding'        => '',
            'test_auto_detect'       => true,
        ],
        'properties'             => [
            'creator'        => 'PageTurner',
            'lastModifiedBy' => 'PageTurner',
            'title'          => 'Book Export',
            'description'    => 'Exported books from PageTurner catalog',
            'subject'        => '',
            'keywords'       => '',
            'category'       => '',
            'manager'        => '',
            'company'        => '',
        ],
    ],

    'imports'            => [
        'read_only'    => true,
        'ignore_empty' => false,
        'heading_row'  => [
            'formatter' => 'slug',
        ],
        'csv'          => [
            'delimiter'        => null,
            'enclosure'        => '"',
            'escape_character' => '\\',
            'contiguous'       => false,
            'input_encoding'   => Csv::GUESS_ENCODING,
        ],
        'properties'   => [
            'creator'        => '',
            'lastModifiedBy' => '',
            'title'          => '',
            'description'    => '',
            'subject'        => '',
            'keywords'       => '',
            'category'       => '',
            'manager'        => '',
            'company'        => '',
        ],
        'cells'        => [
            'middleware' => [],
        ],
    ],

    'extension_detector' => [
        'xlsx'     => Excel::XLSX,
        'xlsm'     => Excel::XLSX,
        'xltx'     => Excel::XLSX,
        'xltm'     => Excel::XLSX,
        'xls'      => Excel::XLS,
        'xlt'      => Excel::XLS,
        'ods'      => Excel::ODS,
        'ots'      => Excel::ODS,
        'slk'      => Excel::SLK,
        'xml'      => Excel::XML,
        'gnumeric' => Excel::GNUMERIC,
        'htm'      => Excel::HTML,
        'html'     => Excel::HTML,
        'csv'      => Excel::CSV,
        'tsv'      => Excel::TSV,
        'pdf'      => Excel::DOMPDF,
    ],

    'value_binder'       => [
        'default' => Maatwebsite\Excel\DefaultValueBinder::class,
    ],

    'cache'        => [
        /*
        |--------------------------------------------------------------------------
        | Default cell caching driver
        |--------------------------------------------------------------------------
        |
        | By default PhpSpreadsheet keeps all cell values in memory, however when
        | dealing with large files, this might result into memory issues. If you
        | want to mitigate that, you can configure a cell caching driver here.
        |
        | We use 'batch' here to mitigate memory issues for large imports/exports.
        */
        'driver'      => 'batch',

        'batch'       => [
            'memory_limit' => 60000,
        ],

        /*
        |--------------------------------------------------------------------------
        | Illuminate cache
        |--------------------------------------------------------------------------
        |
        | The 'database' and 'file' cache stores do not support tagging, which
        | Laravel-Excel uses for its cell cache. We force it to use 'array'
        | (which supports tags) for Excel-specific operations to avoid errors.
        */
        'illuminate'  => [
            'store' => 'array',
        ],

        'default_ttl' => 10800,
    ],

    'transactions' => [
        'handler' => 'db',
        'db'      => [
            'connection' => null,
        ],
    ],

    'temporary_files' => [
        'local_path'          => storage_path('framework/cache/laravel-excel'),
        'remote_disk'         => null,
        'remote_prefix'       => null,
        'force_resync_remote' => null,
    ],
];
