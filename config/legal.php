<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Legal / Document Defaults
    |--------------------------------------------------------------------------
    |
    | These values are used when rendering legal PDFs (e.g. Debt Assurity).
    | Keep them in env so they can be updated per environment without code changes.
    |
    */

    'assurity_creditor_name' => env('ASSURITY_CREDITOR_NAME', ''),
    'assurity_creditor_reg' => env('ASSURITY_CREDITOR_REG', ''),
];

