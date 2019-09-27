<?php

Language::set([
    'archive' => ['Archive', 'Archive', 'Archives'],
    'author' => ['Author', 'Author', 'Authors'],
    'content' => ['Content', 'Content', 'Contents'],
    'data' => ['Data', 'Datum', 'Data'],
    'date' => ['Date', 'Date', 'Dates'],
    'description' => ['Description', 'Description', 'Descriptions'],
    'draft' => ['Draft', 'Draft', 'Drafts'],
    'first' => 'First',
    'home' => 'Home',
    'is-error' => 'Error',
    'is-success' => 'Success',
    'last' => 'Last',
    'link' => ['Link', 'Link', 'Links'],
    'name' => ['Name', 'Name', 'Names'],
    'next' => 'Next',
    'page' => ['Page', 'Page', 'Pages'],
    'page-count' => function(int $i) {
        return $i . ' Page' . ($i === 1 ? "" : 's');
    },
    'parent' => ['Parent', 'Parent', 'Parents'],
    'path' => 'Path',
    'photo' => ['Photo', 'Photo', 'Photos'],
    'picture' => ['Picture', 'Picture', 'Pictures'],
    'prev' => 'Previous',
    'status' => ['Status', 'Status', 'Statuses'],
    'time' => ['Time', 'Time', 'Time'],
    'title' => ['Title', 'Title', 'Titles'],
    'type' => ['Type', 'Type', 'Types'],
    'update' => ['Update', 'Update', 'Updates'],
    'version' => ['Version', 'Version', 'Versions'],
]);