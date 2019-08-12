<?php

// Set default document status
Header::status(403); // “Forbidden”

// Set default `X-Powered-By` value
Header::set('X-Powered-By', 'Mecha/' . VERSION);