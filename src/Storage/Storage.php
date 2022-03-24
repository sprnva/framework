<?php

namespace App\Core;

use App\Core\Filesystem\Filesystem;

class Storage
{
    protected $fileCollected;

    /**
     * Get the fileupload input.
     *
     * @param  file  $file
     * @return $this
     *
     */
    public static function file($file)
    {
        $self = new static;
        $self->fileCollected = $_FILES[$file];

        return $self;
    }


    /**
     * Check the request if it is a file.
     *
     * @param  file  $file
     * @return bool
     *
     */
    public static function hasFile($file, $multiIndex = '')
    {
        $sizeConf = ($multiIndex == '')
            ? $_FILES[$file]['size']
            : $_FILES[$file]['size'][$multiIndex];

        $erroConf = ($multiIndex == '')
            ? $_FILES[$file]['error']
            : $_FILES[$file]['error'][$multiIndex];

        if ($sizeConf == 0 && $erroConf == 0) {
            return false;
        }

        return true;
    }

    /**
     * Get the filesize.
     *
     * @return float
     */
    public function getSize($multiIndex = '', $inUnit = 'kb')
    {
        $file = $this->scaffoldIndex('size', $multiIndex);
        if ($inUnit == 'mb') {
            $size = ($file / 1000000) . " MB";
        } else {
            $size = ($file / 1000) . " KB";
        }

        return $size;
    }

    /**
     * get the extension.
     *
     * @return string
     */
    public function getExtension($multiIndex = '')
    {
        $file_type = explode('.', $this->scaffoldIndex('name', $multiIndex));
        $file_type_end = end($file_type);

        return $file_type_end;
    }

    /**
     * get the filename.
     *
     * @return string
     */
    public function getName($multiIndex = '')
    {
        $file_name = explode('_', str_replace(array('.', ' ', ',', '-'), '_', $this->scaffoldIndex('name', $multiIndex)));
        array_pop($file_name);

        return implode('_', $file_name);
    }

    /**
     * get the origanl filename.
     *
     * @return string
     */
    public function getOriginalName($multiIndex = '')
    {
        return $this->scaffoldIndex('name', $multiIndex);
    }

    /**
     * get the temp file name.
     *
     * @return string
     */
    public function getTmpFile($multiIndex = '')
    {
        return $this->scaffoldIndex('tmp_name', $multiIndex);
    }

    /**
     * Store the file as new.
     *
     * @return bool
     */
    public function storeAs($file_tmp, $dirPath, $type, $filename, $folder = '')
    {
        Filesystem::noMemoryLimit();
        $data = Filesystem::get($file_tmp);

        $imagedata = 'data:' . $type . ';base64,' . base64_encode($data);

        $tmp_folder = $dirPath;

        if (!Filesystem::exists($tmp_folder . $folder)) {
            Filesystem::makeDirectory($tmp_folder . $folder);
        }

        $path = $tmp_folder . $folder . '/' . $filename;

        list($type, $imagedata) = explode(';', $imagedata);
        list(, $imagedata) = explode(',', $imagedata);

        $imagedata = base64_decode($imagedata);

        return Filesystem::put($path, $imagedata);
    }

    /**
     * delete the file.
     *
     * @param $filePath
     * @return bool
     */
    public static function delete($filePath)
    {
        return (new Filesystem())->delete($filePath);
    }

    /**
     * Store the file as new.
     *
     * @return bool
     */
    public static function deleteDirectory($filePath)
    {
        return (new Filesystem())->deleteDirectory($filePath);
    }

    /**
     * get the file type with slash
     *
     * @return string
     */
    public function type($multiIndex = '')
    {
        return $this->scaffoldIndex('type', $multiIndex);
    }

    /**
     * get the avatar of the user
     *
     * @return string
     */
    public static function getAvatar($user_id)
    {
        $user_avatar = DB()->select('*', 'users', "id = '$user_id'")->get();

        if ($user_avatar['avatar'] != "") {
            // dd($user_avatar);
            $_avatar = public_url($user_avatar['avatar']);
        } else {
            $_avatar = public_url('/storage/images/default.png');
        }

        return $_avatar;
    }

    public function scaffoldIndex($indexName, $multiIndex = '')
    {
        $file = ($multiIndex == '' || is_numeric(intval($multiIndex)) == false)
            ? $this->fileCollected[$indexName]
            : $this->fileCollected[$indexName][intval($multiIndex)];

        return $file;
    }
}
