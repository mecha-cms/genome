<?php

Language::set([
    'message' => ['Message', 'Message', 'Messages'],
    'message-count' => function(int $i) {
        return $i . ' Message' . ($i === 1 ? "" : 's');
    },
    'message-error-file-exist' => 'File %s already exists.',
    'message-error-file-size' => [
        0 => 'Must be greater than %s.',
        1 => 'Must be less than %s.'
    ],
    'message-error-file-type' => 'File type %s is not allowed.',
    'message-error-file-x' => 'Extension %s is not allowed.',
    'message-error-folder-exist' => 'Folder %s already exists.',
    'message-error-search' => 'No results for %s.',
    'message-info-are' => 'Your %1$s are %2$s.',
    'message-info-file-push' => [
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
    'message-info-is' => 'Your %1$s is %2$s.',
    'message-info-search' => 'Search results for %s.',
    'message-info-void' => 'No %s yet.',
    'message-success-file-create' => 'File %s created.',
    'message-success-file-delete' => 'File %s deleted.',
    'message-success-file-push' => 'File %s uploaded.',
    'message-success-file-update' => 'File %s updated.',
    'message-success-folder-create' => 'Folder %s created.',
    'message-success-folder-delete' => 'Folder %s deleted.',
    'message-success-folder-update' => 'Folder %s updated.'
]);