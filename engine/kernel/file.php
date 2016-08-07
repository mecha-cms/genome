<?php

class File extends Socket {

    protected static $open = "";
    protected static $cache = "";

    public static $config = [
        'file_size_min_allow' => 0, // Minimum allowed file size
        'file_size_max_allow' => 2097152, // Maximum allowed file size
        'file_extension_allow' => [] // List of allowed file extension(s)
    ];

    // Inspect file path
    public static function inspect($path, $key = null, $fail = false) {
        $path = To::path($path);
        $n = Path::N($path);
        $x = Path::X($path);
        $update = self::T($path);
        $update_date = $update !== null ? date('Y-m-d H:i:s', $update) : null;
        $output = [
            'path' => $path,
            'name' => $n,
            'url' => To::url($path),
            'extension' => is_file($path) ? $x : null,
            'update_raw' => $update,
            'update' => $update_date,
            'size_raw' => file_exists($path) ? filesize($path) : null,
            'size' => file_exists($path) ? self::size($path) : null,
            'is' => [
                // hidden file/folder only
                'hidden' => strpos($n, '__') === 0 || strpos($n, '.') === 0,
                'file' => is_file($path),
                'folder' => is_dir($path)
            ]
        ];
        return $key !== null ? Anemon::get($output, $key, $fail) : $output;
    }

    // List all file(s) from a folder
    public static function explore($folder = ROOT, $deep = false, $flat = false, $fail = false) {
        $folder = rtrim(To::path($folder), DS);
        $files = array_merge(
            glob($folder . DS . '*', GLOB_NOSORT),
            glob($folder . DS . '.*', GLOB_NOSORT)
        );
        $output = [];
        foreach ($files as $file) {
            $b = Path::B($file);
            if ($b && $b !== '.' && $b !== '..') {
                if (is_dir($file)) {
                    if (!$flat) {
                        $output[$file] = $deep ? self::explore($file, true, false, []) : 0;
                    } else {
                        $output[$file] = 0;
                        $output = $deep ? array_merge($output, self::explore($file, true, true, [])) : $output;
                    }
                } else {
                    $output[$file] = 1;
                }
            }
        }
        return !empty($output) ? $output : $fail;
    }

    // Check if file/folder does exist
    public static function exist($input, $fail = false) {
        $input = To::path($input);
        return file_exists($input) ? $input : $fail;
    }

    // Open a file
    public static function open($input) {
        self::$open = To::path($input);
        self::$cache = "";
        return new static;
    }

    // Append `$data` to the file content
    public static function append($data) {
        self::$cache = file_get_contents(self::$open) . $data;
        return new static;
    }

    // Prepend `$data` to the file content
    public static function prepend($data) {
        self::$cache = $data . file_get_contents(self::$open);
        return new static;
    }

    // Print the file content
    public static function read($fail = "") {
        return file_exists(self::$open) ? file_get_contents(self::$open) : $fail;
    }

    // Print the file content line by line
    public static function get($stop = null, $fail = false, $ch = 1024) {
        $i = 0;
        $output = "";
        if ($hand = fopen(self::$open, 'r')) {
            while (($chunk = fgets($hand, $ch)) !== false) {
                if (is_int($stop) && $stop === $i) break;
                $output .= $chunk;
                $i++;
                if (is_string($stop) && strpos($chunk, $stop) !== false || is_array($stop) && strpos($chunk, $stop[0]) === $stop[1]) break;
            }
            fclose($hand);
            return rtrim($output);
        }
        return $fail;
    }

    // Write `$data` before save
    public static function write($data) {
        self::$cache = $data;
        return new static;
    }

    // Serialize `$data` before save
    public static function serialize($data) {
        self::$cache = serialize($data);
        return new static;
    }

    // Unserialize the serialized `$data` to output
    public static function unserialize($fail = []) {
        if (file_exists(self::$open)) {
            $data = file_get_contents(self::$open);
            return Is::serialize($data) ? unserialize($data) : $fail;
        }
        return $fail;
    }

    // Save the `$data`
    public static function save($consent = null) {
        self::saveTo(self::$open, $consent);
        return new static;
    }

    // Save the `$data` to ...
    public static function saveTo($input, $consent = null) {
        $input = To::path($input);
        if (!file_exists(Path::D($input))) {
            mkdir(Path::D($input), 0777, true);
        }
        $hand = fopen($input, 'w') or die('Cannot open file: ' . $input);
        fwrite($hand, self::$cache);
        fclose($hand);
        if ($consent !== null) {
            chmod($input, $consent);
        }
        self::$open = $input;
        return new static;
    }

    // Rename the file/folder
    public static function renameTo($name) {
        if (file_exists(self::$open)) {
            $a = Path::B(self::$open);
            $b = Path::D(self::$open) . DS;
            if ($name !== $a) {
                rename($b . $a, $b . $name);
            }
            self::$open = $b . $name;
        }
        return new static;
    }

    // Move the file/folder to ...
    public static function moveTo($target = ROOT) {
        if (file_exists(self::$open)) {
            $target = To::path($target);
            if (is_dir($target) && is_file(self::$open)) {
                $target .= DS . Path::B(self::$open);
            }
            if (!file_exists(Path::D($target))) {
                mkdir(Path::D($target), 0777, true);
            }
            rename(self::$open, $target);
            self::$open = $target;
        }
        return new static;
    }

    // Copy the file/folder to ...
    public static function copyTo($target = ROOT, $s = '.%s') {
        $i = 1;
        if (file_exists(self::$open)) {
            foreach ((array) $target as $v) {
                $v = To::path($v);
                if (is_dir($v)) {
                    if (!file_exists($v)) {
                        mkdir($v, 0777, true);
                    }
                    $v .= DS . Path::B(self::$open);
                } else {
                    if (!file_exists(Path::D($v))) {
                        mkdir(Path::D($v), 0777, true);
                    }
                }
                if (!file_exists($v)) {
                    copy(self::$open, $v);
                    $i = 1;
                } else {
                    $v = preg_replace('#\.([a-z\d]+)$#', sprintf($s, $i) . '.$1', $v);
                    copy(self::$open, $v);
                    $i++;
                }
                self::$open = $v;
            }
        }
        return new static;
    }

    // Delete the file
    public static function delete() {
        if (file_exists(self::$open)) {
            if (is_dir(self::$open)) {
                $a = new RecursiveDirectoryIterator(self::$open, FilesystemIterator::SKIP_DOTS);
                $b = new RecursiveIteratorIterator($a, RecursiveIteratorIterator::CHILD_FIRST);
                foreach ($b as $o) {
                    if ($o->isFile()) {
                        unlink($o->getPathname());
                    } else {
                        rmdir($o->getPathname());
                    }
                }
                rmdir(self::$open);
            } else {
                unlink(self::$open);
            }
        }
    }

    // Get file modify time
    public static function T($input, $fail = null) {
        return file_exists($input) ? filemtime($input) : $fail;
    }

    // Set file permission
    public static function consent($consent) {
        chmod(self::$open, $consent);
        return new static;
    }

    // Upload the file
    public static function upload($file, $target = ROOT, $fn = null, $fail = false) {
        $target = To::path($target);
        $errors = [
            'There is no error, the file uploaded with success.',
            'The uploaded file exceeds the <code>upload_max_filesize</code> directive in <code>php.ini</code>.',
            'The uploaded file exceeds the <code>MAX_FILE_SIZE</code> directive that was specified in the <abbr title="Hyper Text Markup Language">HTML</abbr> form.',
            'The uploaded file was only partially uploaded.',
            'No file was uploaded.',
            '?',
            'Missing a temporary folder.',
            'Failed to write file to disk.',
            'A <abbr>PHP</abbr> extension stopped the file upload.'
        ];
        // Create a safe file name
        $file['name'] = To::safe('name.file', $file['name']);
        $x = Path::X($file['name']);
        // Something goes wrong
        if ($file['error'] > 0 && isset($errors[$file['error']])) {
            Notify::error($errors[$file['error']]);
        } else {
            // Unknown file type
            if (empty($file['type'])) {
                Notify::error('Unknown file type.');
            }
            // Bad file extension
            $x_ok = X . implode(X, self::$config['file_extension_allow']) . X;
            if (strpos($x_ok, X . $x . X) === false) {
                Notify::error('Extension ' . $x . ' is not allowed.');
            }
            // Too small
            if ($file['size'] < self::$config['file_size_min_allow']) {
                Notify::error('File too small.');
            }
            // Too large
            if ($file['size'] > self::$config['file_size_max_allow']) {
                Notify::error('File too large.');
            }
        }
        if (!Notify::errors()) {
            // Destination not found
            if (!file_exists($target)) Folder::create($target);
            // Move the uploaded file to the destination folder
            if (!file_exists($target . DS . $file['name'])) {
                move_uploaded_file($file['tmp_name'], $target . DS . $file['name']);
                // Create public asset URL to be hooked on file uploaded
                $url = To::url($target) . '/' . $file['name'];
                Notify::success('File uploaded.');
                if (is_callable($fn)) {
                    return call_user_func($fn, $file['name'], $file['type'], $file['size'], $url);
                }
                return $target . DS . $file['name'];
            }
            Notify::error('File already exist.');
            return $fail;
        }
        return $fail;
    }

    // Convert file size to ...
    public static function size($file, $unit = null, $prec = 2) {
        $size = is_numeric($file) ? $file : filesize($file);
        $size_base = log($size, 1024);
        $x = ['B', 'KB', 'MB', 'GB', 'TB'];
        if (!$u = array_search((string) $unit, $x)) {
            $u = $size > 0 ? floor($size_base) : 0;
        }
        $output = round($size / pow(1024, $u), $prec);
        return $output < 0 ? 'Unknown' : trim($output . ' ' . $x[$u]);
    }

}