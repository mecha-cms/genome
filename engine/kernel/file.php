<?php

class File extends Genome {

    protected static $path_static = "";
    protected static $content_static = "";

    public static $config = [
        'sizes' => [0, 2097152], // Range of allowed file size(s)
        'extensions' => [] // List of allowed file extension(s)
    ];

    // Inspect file path
    protected static function inspect_static($path, $key = null, $fail = false) {
        $path = To::path($input);
        $n = Path::N($path);
        $x = Path::X($path);
        $update = self::T_static($path);
        $update_date = $update !== null ? date('Y-m-d H:i:s', $update) : null;
        $output = [
            'path' => $path,
            'name' => $n,
            'url' => To::url($path),
            'extension' => is_file($path) ? $x : null,
            'update' => $update_date,
            'size' => file_exists($path) ? self::size_static($path) : null,
            'is' => [
                // hidden file/folder only
                'hidden' => strpos($n, '__') === 0 || strpos($n, '.') === 0,
                'file' => is_file($path),
                'folder' => is_dir($path)
            ],
            '__update' => $update,
            '__size' => file_exists($path) ? filesize($path) : null
        ];
        return $key !== null ? Anemon::get($output, $key, $fail) : $output;
    }

    // List all file(s) from a folder
    protected static function explore_static($folder = ROOT, $deep = false, $flat = false, $fail = false) {
        $folder = To::path($folder);
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
                        $output[$file] = $deep ? self::explore_static($file, true, false, []) : 0;
                    } else {
                        $output[$file] = 0;
                        $output = $deep ? array_merge($output, self::explore_static($file, true, true, [])) : $output;
                    }
                } else {
                    $output[$file] = 1;
                }
            }
        }
        return !empty($output) ? $output : $fail;
    }

    // Check if file/folder does exist
    protected static function exist_static($input, $fail = false) {
        $input = To::path($input);
        return file_exists($input) ? $input : $fail;
    }

    // Open a file
    protected static function open_static($input) {
        self::$path_static = To::path($input);
        self::$content_static = "";
        return new static;
    }

    // Append `$data` to the file content
    protected static function append_static($data) {
        if (is_array(self::$content_static)) {
            self::$content_static = array_merge(self::$content_static, $data);
            return new static;
        }
        self::$content_static = file_get_contents(self::$path_static) . $data;
        return new static;
    }

    // Prepend `$data` to the file content
    protected static function prepend_static($data) {
        if (is_array(self::$content_static)) {
            self::$content_static = array_merge($data, self::$content_static);
            return new static;
        }
        self::$content_static = $data . file_get_contents(self::$path_static);
        return new static;
    }

    // Print the file content
    protected static function read_static($fail = "") {
        return file_exists(self::$path_static) ? file_get_contents(self::$path_static) : $fail;
    }

    // Print the file content line by line
    protected static function get_static($stop = null, $fail = false, $ch = 1024) {
        $i = 0;
        $output = "";
        if ($hand = fopen(self::$path_static, 'r')) {
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
    protected static function write_static($data) {
        self::$content_static = $data;
        return new static;
    }

    // Import the exported PHP file
    protected static function import_static($fail = []) {
        if (!file_exists(self::$path_static)) return $fail;
        return include self::$path_static;
    }

    // Export value to a PHP file
    protected static function export_static($data, $format = '<?php return %s;') {
        $r = '#("(?:[^"\\\]++|\\\.)*+"|\'(?:[^\'\\\\]++|\\\.)*+\')#';
        $data = preg_split($r, json_encode($data), -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);
        $output = "";
        foreach ($data as $v) {
            if ($v[0] === '"' || $v[0] === "'") {
                $output .= $v;
            } else {
                $output .= str_replace(['{', '}', ':'], ['[', ']', '=>'], $v);
            }
        }
        self::$content_static = sprintf($format, $output);
        return new static;
    }

    // Serialize `$data` before save
    protected static function serialize_static($data) {
        self::$content_static = serialize($data);
        return new static;
    }

    // Unserialize the serialized `$data` to output
    protected static function unserialize_static($fail = []) {
        if (file_exists(self::$path_static)) {
            $data = file_get_contents(self::$path_static);
            return __is_serialize__($data) ? unserialize($data) : $fail;
        }
        return $fail;
    }

    // Save the `$data`
    protected static function save_static($consent = null) {
        self::saveTo_static(self::$path_static, $consent);
        return new static;
    }

    // Save the `$data` to ...
    protected static function saveTo_static($input, $consent = null) {
        $input = To::path($input);
        if (!file_exists(Path::D($input))) {
            mkdir(Path::D($input), 0777, true);
        }
        $hand = fopen($input, 'w') or die('Cannot open file: ' . $input);
        fwrite($hand, self::$content_static);
        fclose($hand);
        if ($consent !== null) {
            chmod($input, $consent);
        }
        self::$path_static = $input;
        return new static;
    }

    // Rename the file/folder
    protected static function renameTo_static($name) {
        if (file_exists(self::$path_static)) {
            $a = Path::B(self::$path_static);
            $b = Path::D(self::$path_static) . DS;
            if ($name !== $a) {
                rename($b . $a, $b . $name);
            }
            self::$path_static = $b . $name;
        }
        return new static;
    }

    // Move the file/folder to ...
    protected static function moveTo_static($target = ROOT) {
        if (file_exists(self::$path_static)) {
            $target = To::path($input);
            if (is_dir($target) && is_file(self::$path_static)) {
                $target .= DS . Path::B(self::$path_static);
            }
            if (!file_exists(Path::D($target))) {
                mkdir(Path::D($target), 0777, true);
            }
            rename(self::$path_static, $target);
            self::$path_static = $target;
        }
        return new static;
    }

    // Copy the file/folder to ...
    protected static function copyTo_static($target = ROOT, $s = '.%s') {
        $i = 1;
        if (file_exists(self::$path_static)) {
            foreach ((array) $target as $v) {
                $v = To::path($input);
                if (is_dir($v)) {
                    if (!file_exists($v)) {
                        mkdir($v, 0777, true);
                    }
                    $v .= DS . Path::B(self::$path_static);
                } else {
                    if (!file_exists(Path::D($v))) {
                        mkdir(Path::D($v), 0777, true);
                    }
                }
                if (!file_exists($v)) {
                    copy(self::$path_static, $v);
                    $i = 1;
                } else {
                    $v = preg_replace('#\.([a-z\d]+)$#', sprintf($s, $i) . '.$1', $v);
                    copy(self::$path_static, $v);
                    $i++;
                }
                self::$path_static = $v;
            }
        }
        return new static;
    }

    // Delete the file
    protected static function delete_static() {
        if (file_exists(self::$path_static)) {
            if (is_dir(self::$path_static)) {
                $a = new RecursiveDirectoryIterator(self::$path_static, FilesystemIterator::SKIP_DOTS);
                $b = new RecursiveIteratorIterator($a, RecursiveIteratorIterator::CHILD_FIRST);
                foreach ($b as $o) {
                    if ($o->isFile()) {
                        unlink($o->getPathname());
                    } else {
                        rmdir($o->getPathname());
                    }
                }
                rmdir(self::$path_static);
            } else {
                unlink(self::$path_static);
            }
        }
    }

    // Get file modification time
    protected static function T_static($input, $fail = null) {
        return file_exists($input) ? filemtime($input) : $fail;
    }

    // Set file permission
    protected static function consent_static($consent) {
        chmod(self::$path_static, $consent);
        return new static;
    }

    // Upload the file
    protected static function upload_static($file, $target = ROOT, $fn = null, $fail = false) {
        $target = To::path($input);
        // Create a safe file name
        $file['name'] = To::safe('file.name', $file['name']);
        $x = Path::X($file['name']);
        $e = Language::message_file_upload();
        // Something goes wrong
        if ($file['error'] > 0 && isset($e[$file['error']])) {
            Message::error($e[$file['error']]);
        } else {
            // Unknown file type
            if (empty($file['type'])) {
                Message::error('file_type');
            }
            // Bad file extension
            $x_ok = X . implode(X, self:$config['extensions']) . X;
            if (strpos($x_ok, X . $x . X) === false) {
                Message::error('file_extension', $x);
            }
            // Too small
            if ($file['size'] < self:$config['sizes'][0]) {
                Message::error('file_size.0', self::size_static($file['size']));
            }
            // Too large
            if ($file['size'] > self:$config['sizes'][1]) {
                Message::error('file_size.1', self::size_static($file['size']));
            }
        }
        if (!Message::errors()) {
            // Destination not found
            if (!file_exists($target)) Folder::create($target);
            // Move the upload(ed) file to the destination folder
            if (!file_exists($target . DS . $file['name'])) {
                move_uploaded_file($file['tmp_name'], $target . DS . $file['name']);
                // Create public asset URL to be hooked on file uploaded
                $file['url'] = To::url($target) . '/' . $file['name'];
                Message::success('file_upload', $file['name']);
                if (is_callable($fn)) {
                    return call_user_func($fn, $file);
                }
                return $target . DS . $file['name'];
            }
            Message::error('file_exist', $file['name']);
            return $fail;
        }
        return $fail;
    }

    // Convert file size to ...
    protected static function size_static($file, $unit = null, $prec = 2) {
        $size = is_numeric($file) ? $file : filesize($file);
        $size_base = log($size, 1024);
        $x = ['B', 'KB', 'MB', 'GB', 'TB'];
        if (!$u = array_search((string) $unit, $x)) {
            $u = $size > 0 ? floor($size_base) : 0;
        }
        $output = round($size / pow(1024, $u), $prec);
        return $output < 0 ? Language::unknown() : trim($output . ' ' . $x[$u]);
    }

}