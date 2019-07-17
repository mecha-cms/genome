<?php

Language::set([
    'alert' => ['Alert', 'Alert', 'Alerts'],
    'alert-count' => function(int $i) {
        return $i . ' Alert' . ($i === 1 ? "" : 's');
    },
    'alert-error-file-exist' => 'File %s already exists.',
    'alert-error-file-size' => [
        0 => 'Must be greater than %s.',
        1 => 'Must be less than %s.'
    ],
    'alert-error-file-type' => 'File type %s is not allowed.',
    'alert-error-file-x' => 'Extension %s is not allowed.',
    'alert-error-folder-exist' => 'Folder %s already exists.',
    'alert-error-search' => 'No results for %s.',
    'alert-info-are' => 'Your %1$s are %2$s.',
    'alert-info-file' => [
        0 => 'There is no error, the file uploaded with success.',
        1 => 'The uploaded file exceeds the <code>upload_max_filesize</code> directive in <code>php.ini</code>.',
        2 => 'The uploaded file exceeds the <code>MAX_FILE_SIZE</code> directive that was specified in the <abbr title="Hyper Text Markup Language">HTML</abbr> form.',
        3 => 'The uploaded file was only partially uploaded.',
        4 => 'No file was uploaded.',
        5 => '?',
        6 => 'Missing a temporary folder.',
        7 => 'Failed to write file to disk.',
        8 => 'A PHP extension stopped the file upload.'
    ],
    'alert-info-is' => 'Your %1$s is %2$s.',
    'alert-info-search' => 'Search results for %s.',
    'alert-info-void' => 'No %s yet.',
    'alert-success-file-create' => 'File %s created.',
    'alert-success-file-delete' => 'File %s deleted.',
    'alert-success-file-push' => 'File %s uploaded.',
    'alert-success-file-update' => 'File %s updated.',
    'alert-success-folder-create' => 'Folder %s created.',
    'alert-success-folder-delete' => 'Folder %s deleted.',
    'alert-success-folder-update' => 'Folder %s updated.'
]);