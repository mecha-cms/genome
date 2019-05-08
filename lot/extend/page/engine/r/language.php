<?php

Language::set([
    'archive' => ['Archive', 'Archive', 'Archives'],
    'author' => ['Author', 'Author', 'Authors'],
    'content' => ['Content', 'Content', 'Contents'],
    'data' => ['Data', 'Datum', 'Data'],
    'description' => ['Description', 'Description', 'Descriptions'],
    'draft' => ['Draft', 'Draft', 'Drafts'],
    'first' => 'First',
    'home' => 'Home',
    'last' => 'Last',
    'link' => ['Link', 'Link', 'Links'],
    'message-info-kick' => 'You have just been redirected from %s',
    'next' => 'Next',
    'page' => ['Page', 'Page', 'Pages'],
    'page-count' => function(int $i) {
        return $i . ' Page' . ($i === 1 ? "" : 's');
    },
    'photo' => ['Photo', 'Photo', 'Photos'],
    'picture' => ['Picture', 'Picture', 'Pictures'],
    'prev' => 'Previous',
    'state' => ['State', 'State', 'States'],
    'status' => ['Status', 'Status', 'Statuses'],
    'title' => ['Title', 'Title', 'Titles'],
    'type' => ['Type', 'Type', 'Types'],
    'version' => ['Version', 'Version', 'Versions'],
]);