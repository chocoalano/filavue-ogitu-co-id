<?php

return [

    'label' => 'Ekspor :label',

    'modal' => [

        'heading' => 'Ekspor :label',

        'form' => [

            'columns' => [

                'label' => 'Kolom',

                'actions' => [

                    'select_all' => [
                        'label' => 'Pilih semua',
                    ],

                    'deselect_all' => [
                        'label' => 'Batal pilih semua',
                    ],

                ],

                'form' => [

                    'is_enabled' => [
                        'label' => ':column diaktifkan',
                    ],

                    'label' => [
                        'label' => 'Label :column',
                    ],

                ],

            ],

        ],

        'actions' => [

            'export' => [
                'label' => 'Ekspor',
            ],

        ],

    ],

    'notifications' => [

        'completed' => [

            'title' => 'Ekspor selesai',

            'actions' => [

                'download_csv' => [
                    'label' => 'Unduh .csv',
                ],

                'download_xlsx' => [
                    'label' => 'Unduh .xlsx',
                ],

            ],

        ],

        'max_rows' => [
            'title' => 'Ekspor terlalu besar',
            'body' => 'Anda tidak dapat mengekspor lebih dari 1 baris sekaligus.|Anda tidak dapat mengekspor lebih dari :count baris sekaligus.',
        ],

        'no_columns' => [
            'title' => 'Tidak ada kolom dipilih',
            'body' => 'Pilih minimal satu kolom untuk diekspor.',
        ],

        'started' => [
            'title' => 'Ekspor dimulai',
            'body' => 'Ekspor dimulai dan 1 baris akan diproses di latar belakang. Anda akan menerima notifikasi berisi tautan unduhan setelah selesai.|Ekspor dimulai dan :count baris akan diproses di latar belakang. Anda akan menerima notifikasi berisi tautan unduhan setelah selesai.',
        ],

    ],

    'file_name' => 'ekspor-:export_id-:model',

];
