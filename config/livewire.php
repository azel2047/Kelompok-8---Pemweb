<?php

return [
    'temporary_file_upload' => [
        'disk' => 'public_custom', 
        'rules' => null,
        'directory' => 'images/tmp', 
        'middleware' => null,
        'preview_mimes' => ['png', 'gif', 'jpg', 'jpeg', 'webp'],
        'max_upload_time' => 5,
    ],
];