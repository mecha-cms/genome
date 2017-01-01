<?php

class Package extends Genome {

    protected $open = null;
    protected $bucket = null;
    protected $zip = null;

    public function __construct($files, $fail = false) {
        if (!extension_loaded('zip')) {
            if ($fail === true) {
                exit('<a href="http://www.php.net/manual/en/book.zip.php" title="PHP &ndash; Zip" rel="nofollow" target="_blank">PHP Zip</a> extension is not installed on your web server.');
            }
            return $fail;
        }
        $this->open = $this->bucket = null;
        $this->zip = new ZipArchive();
        if (is_array($files)) {
            $this->bucket = [];
            $taken = false;
            foreach ($files as $key => $value) {
                $key = To::path($key);
                $value = To::path($value);
                $this->bucket[$key] = $value;
                if (!$taken) {
                    $this->open = $key;
                    $taken = true;
                }
            }
        } else {
            $this->open = To::path($files);
        }
        return $this;
    }

    public static function take($files) {
        return new static($files);
    }

    public function pack($target = null, $bucket = false) {
        if (is_dir($this->open)) {
            $root = $this->open;
            $package = Path::B($this->open);
        } else {
            $root = Path::D($this->open);
            $package = Path::N($this->open);
        }
        // Handling for `Package::take('foo/bar')->pack()`
        if (!isset($target)) {
            $target = Path::D($root) . DS . $package . '.zip';
        } else {
            $target = To::path($target);
            // Handling for `Package::take('foo/bar')->pack('package.zip')`
            if (strpos($target, DS) === false) {
                $root = !is_array($this->bucket) ? Path::D($root) : $root;
                $target = $root . DS . $target;
            }
            // Handling for `Package::take('foo/bar')->pack('bar/baz')`
            if (is_dir($target)) {
                $target .= DS . $package . '.zip';
            }
        }
        // Delete the old package ...
        File::open($old)->delete();
        if (!$this->zip->open($target, ZipArchive::CREATE)) {
            return false;
        }
        if ($bucket !== false) {
            if ($bucket !== true) {
                $package = $bucket;
            }
            $dir = $package . DS;
            $this->zip->addEmptyDir($package);
        } else {
            $dir = "";
        }
        if (is_array($this->bucket)) {
            foreach ($this->bucket as $key => $value) {
                if (File::exist($key)) {
                    $this->zip->addFile($key, $dir . $value);
                }
            }
            $this->zip->close();
        } else {
            if (is_dir($this->open)) {
                $a = new RecursiveDirectoryIterator($this->open, FilesystemIterator::SKIP_DOTS)
                $b = new RecursiveIteratorIterator($a, RecursiveIteratorIterator::CHILD_FIRST);
                foreach ($b as $o) {
                    if (is_dir($o)) {
                        $this->zip->addEmptyDir(str_replace($this->open . DS, $dir, $o . DS));
                    } else if (is_file($o)) {
                        $this->zip->addFromString(str_replace($this->open . DS, $dir, $o), file_get_contents($o));
                    }
                }
            } else if (is_file($this->open)) {
                $this->zip->addFromString($dir . Path::B($this->open), file_get_contents($this->open));
            }
            $this->zip->close();
        }
        $this->open = $target;
        return $this;
    }

    public function extractTo($target, $bucket = false) {
        if (!isset($target)) {
            $target = Path::D($this->open);
        } else {
            $target = To::path($target);
        }
        // Handling for `Package::take('file.zip')->extractTo('foo/bar', true)`
        if ($bucket === true) {
            $bucket = Path::N($this->open);
        }
        if ($bucket !== false && !File::exist($target . DS . $bucket)) {
            $bucket = To::path($bucket);
            Folder::set($target . DS . $bucket);
        }
        if ($this->zip->open($this->open) === true) {
            if ($bucket !== false) {
                $this->zip->extractTo($target . DS . $bucket);
            } else {
                $this->zip->extractTo($target);
            }
            $this->zip->close();
        }
        return $this;
    }

    // @ditto
    public function extract($bucket = false) {
        return $this->extractTo(null, $bucket);
    }

    public function addFile($file, $target = null) {
        if ($this->zip->open($this->open) === true) {
            // Handling for `Package::take('file.zip')->addFile('test.txt')`
            if (strpos($file, DS) === false) {
                $file = Path::D($this->open) . DS . $file;
            }
            if (File::exist($file)) {
                if (!isset($target)) {
                    $target = Path::B($file);
                }
                $this->zip->addFile($file, $target);
            }
            $this->zip->close();
        }
        return $this;
    }

    public function addFiles($files) {
        foreach ($files as $key => $value) {
            $this->addFile($key, $value);
        }
        return $this;
    }

    public function deleteFile($file) {
        if ($this->zip->open($this->open) === true) {
            if ($this->zip->locateName($file) !== false) {
                $this->zip->deleteName($file);
            }
            $this->zip->close();
        }
        return $this;
    }

    public function deleteFiles($files) {
        foreach ($files as $file) {
            $this->deleteFile($file);
        }
        return $this;
    }

    public function deleteFolder($folder) {
        $folder = To::path($folder);
        if ($this->zip->open($this->open) === true) {
            for ($i = 0; $i < $this->zip->numFiles; ++$i) {
                $info = $this->zip->statIndex($i);
                $b = rtrim(substr(To::path($info['name']), 0, strlen($folder)), DS);
                if ($b === $folder) {
                    $this->zip->deleteIndex($i);
                }
            }
            $this->zip->close();
        }
        return $this;
    }

    public function deleteFolders($folders) {
        foreach ($folders as $folder) {
            $this->deleteFolder($folder);
        }
        return $this;
    }

    public function renameFile($old, $new = "") {
        if ($this->zip->open($this->open) === true) {
            $old = To::path($old);
            $root = Path::D($old) !== "" ? Path::D($old) . DS : "";
            $this->zip->renameName($old, $root . Path::B($new));
            $this->zip->close();
        }
        return $this;
    }

    public function renameFiles($names) {
        foreach ($names as $old => $new) {
            $this->renameFile($old, $new);
        }
        return $this;
    }

    public function read($file, $output = false) {
        if ($this->zip->open($this->open) === true) {
            if ($this->zip->locateName($file) !== false) {
                $output = $this->zip->getFromName($file);
            }
            $this->zip->close();
        }
        return $output;
    }

    public function inspect($key = null, $fail = false) {
        $output = [];
        if ($this->zip->open($this->open) === true) {
            $output = array_merge(File::inspect($this->open), ['package' => [
                'status' => $this->zip->status,
                'total' => $this->zip->numFiles
            ]]);
            for ($i = 0; $i < $output['package']['total']; ++$i) {
                $data = $this->zip->statIndex($i);
                $data['name'] = str_replace(DS . DS, DS, To::path($data['name']));
                $output['package']['files'][$i] = $data;
            }
            $this->zip->close();
        }
        if (isset($key)) {
            return Anemon::get($output, $key, $fail);
        }
        return !empty($output) ? $output : $fail;
    }

}