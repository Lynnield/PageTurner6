<?php

// Published from vendor/maatwebsite/excel with small enterprise tweaks:
// - keep default chunk_size 1000 (matches import/export requirements)
// - set temporary path under storage/framework/cache

return require base_path('vendor/maatwebsite/excel/config/excel.php');

