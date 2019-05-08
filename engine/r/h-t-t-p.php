<?php

// Set default document status
HTTP::status(404); // “Not Found”

// Set default `X-Powered-By` value
HTTP::header('X-Powered-By', 'Mecha/' . Mecha::version);