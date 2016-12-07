<?php

class File extends Genome {

    protected static $path_ = "";
    protected static $content_ = "";

    public static $config = [
        'sizes' => [0, 2097152], // Range of allowed file size(s)
        'extensions' => [] // List of allowed file extension(s)
    ];

    // Inspect file path
    protected static function inspect_($path, $key = null, $fail = false) {
        $path = To::path($input);
        $n = Path::N($path);
        $x = Path::X($path);
        $update = self::T_($path);
        $update_date = $update !== null ? date('Y-m-d H:i:s', $update) : null;
        $output = [
            'path' => $path,
            'name' => $n,
            'url' => To::url($path),
            'extension' => is_file($path) ? $x : null,
            'update' => $update_date,
            'size' => file_exists($path) ? self::size_($path) : null,
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
    protected static function explore_($folder = ROOT, $deep = false, $flat = false, $fail = false) {
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
                        $output[$file] = $deep ? self::explore_($file, true, false, []) : 0;
                    } else {
                        $output[$file] = 0;
                        $output = $deep ? array_merge($output, self::explore_($file, true, true, [])) : $output;
                    }
                } else {
                    $output[$file] = 1;
                }
            }
        }
        return !empty($output) ? $output : $fail;
    }

    // Check if file/folder does exist
    protected static function exist_($input, $fail = false) {
        $input = To::path($input);
        return file_exists($input) ? $input : $fail;
    }

    // Open a file
    protected static function open_($input) {
        self::$path_ = To::path($input);
        self::$content_ = "";
        return new static;
    }

    // Append `$data` to the file content
    protected static function append_($data) {
        if (is_array(self::$content_)) {
            self::$content_ = array_merge(self::$content_, $data);
            return new static;
        }
        self::$content_ = file_get_contents(self::$path_) . $data;
        return new static;
    }

    // Prepend `$data` to the file content
    protected static function prepend_($data) {
        if (is_array(self::$content_)) {
            self::$content_ = array_merge($data, self::$content_);
            return new static;
        }
        self::$content_ = $data . file_get_contents(self::$path_);
        return new static;
    }

    // Print the file content
    protected static function read_($fail = "") {
        return file_exists(self::$path_) ? file_get_contents(self::$path_) : $fail;
    }

    // Print the file content line by line
    protected static function get_($stop = null, $fail = false, $ch = 1024) {
        $i = 0;
        $output = "";
        if ($hand = fopen(self::$path_, 'r')) {
            while (($chunk = fgets($hand, $ch)) !== false) {
                if (is_int($stop) && $stop === $i) break;
                $output .= $chunk;
                ++$i;
                if (is_string($stop) && strpos($chunk, $stop) !== false || is_array($stop) && strpos($chunk, $stop[0]) === $stop[1]) break;
            }
            fclose($hand);
            return rtrim($output);
        }
        return $fail;
    }

    // Write `$data` before save
    protected static function write_($data) {
        self::$content_ = $data;
        return new static;
    }

    // Import the exported PHP file
    protected static function import_($fail = []) {
        if (!file_exists(self::$path_)) return $fail;
        return include self::$path_;
    }

    // Export value to a PHP file
    protected static function export_($data, $format = '<?php return %s;') {
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
        self::$content_ = sprintf($format, $output);
        return new static;
    }

    // Serialize `$data` before save
    protected static function serialize_($data) {
        self::$content_ = serialize($data);
        return new static;
    }

    // Unserialize the serialized `$data` to output
    protected static function unserialize_($fail = []) {
        if (file_exists(self::$path_)) {
            $data = file_get_contents(self::$path_);
            return __is_serialize__($data) ? unserialize($data) : $fail;
        }
        return $fail;
    }

    // Save the `$data`
    protected static function save_($consent = null) {
        self::saveTo_(self::$path_, $consent);
        return new static;
    }

    // Save the `$data` to ...
    protected static function saveTo_($input, $consent = null) {
        $input = To::path($input);
        if (!file_exists(Path::D($input))) {
            mkdir(Path::D($input), 0777, true);
        }
        $hand = fopen($input, 'w') or die('Cannot open file: ' . $input);
        fwrite($hand, self::$content_);
        fclose($hand);
        if ($consent !== null) {
            chmod($input, $consent);
        }
        self::$path_ = $input;
        return new static;
    }

    // Rename the file/folder
    protected static function renameTo_($name) {
        if (file_exists(self::$path_)) {
            $a = Path::B(self::$path_);
            $b = Path::D(self::$path_) . DS;
            if ($name !== $a) {
                rename($b . $a, $b . $name);
            }
            self::$path_ = $b . $name;
        }
        return new static;
    }

    // Move the file/folder to ...
    protected static function moveTo_($target = ROOT) {
        if (file_exists(self::$path_)) {
            $target = To::path($input);
            if (is_dir($target) && is_file(self::$path_)) {
                $target .= DS . Path::B(self::$path_);
            }
            if (!file_exists(Path::D($target))) {
                mkdir(Path::D($target), 0777, true);
            }
            rename(self::$path_, $target);
            self::$path_ = $target;
        }
        return new static;
    }

    // Copy the file/folder to ...
    protected static function copyTo_($target = ROOT, $s = '.%s') {
        $i = 1;
        if (file_exists(self::$path_)) {
            foreach ((array) $target as $v) {
                $v = To::path($input);
                if (is_dir($v)) {
                    if (!file_exists($v)) {
                        mkdir($v, 0777, true);
                    }
                    $v .= DS . Path::B(self::$path_);
                } else {
                    if (!file_exists(Path::D($v))) {
                        mkdir(Path::D($v), 0777, true);
                    }
                }
                if (!file_exists($v)) {
                    copy(self::$path_, $v);
                    $i = 1;
                } else {
                    $v = preg_replace('#\.([a-z\d]+)$#', sprintf($s, $i) . '.$1', $v);
                    copy(self::$path_, $v);
                    ++$i;
                }
                self::$path_ = $v;
            }
        }
        return new static;
    }

    // Delete the file
    protected static function delete_() {
        if (file_exists(self::$path_)) {
            if (is_dir(self::$path_)) {
                $a = new RecursiveDirectoryIterator(self::$path_, FilesystemIterator::SKIP_DOTS);
                $b = new RecursiveIteratorIterator($a, RecursiveIteratorIterator::CHILD_FIRST);
                foreach ($b as $o) {
                    if ($o->isFile()) {
                        unlink($o->getPathname());
                    } else {
                        rmdir($o->getPathname());
                    }
                }
                rmdir(self::$path_);
            } else {
                unlink(self::$path_);
            }
        }
    }

    // Get file modification time
    protected static function T_($input, $fail = null) {
        return file_exists($input) ? filemtime($input) : $fail;
    }

    // Set file permission
    protected static function consent_($consent) {
        chmod(self::$path_, $consent);
        return new static;
    }

    // Upload the file
    protected static function upload_($file, $target = ROOT, $fn = null, $fail = false) {
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
                Message::error('file_size.0', self::size_($file['size']));
            }
            // Too large
            if ($file['size'] > self:$config['sizes'][1]) {
                Message::error('file_size.1', self::size_($file['size']));
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
    protected static function size_($file, $unit = null, $prec = 2) {
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