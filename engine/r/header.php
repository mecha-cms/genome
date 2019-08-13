<?php

// Set default document status
Header::status(403); // “Forbidden”

// Set default `Content-Type` value to `text/html`
Header::type('text/html');

// Set default `X-Powered-By` value
Header::set('X-Powered-By', 'Mecha/' . VERSION);