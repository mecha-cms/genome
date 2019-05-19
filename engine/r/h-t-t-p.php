<?php

// Set default document status
HTTP::status(403); // “Forbidden”

// Set default `X-Powered-By` value
HTTP::header('X-Powered-By', 'Mecha/' . Mecha::version);