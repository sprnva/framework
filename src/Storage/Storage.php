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
    public static function hasFile($file)
    {
        if ($_FILES[$file]['size'] == 0 && $_FILES[$file]['error'] == 0) {
            return false;
        }

        return true;
    }

    /**
     * Get the filesize.
     *
     * @return float
     */
    public function getSize($inUnit = 'kb')
    {
        if ($inUnit == 'mb') {
            $size = ($this->fileCollected['size'] / 1000000) . " MB";
        } else {
            $size = ($this->fileCollected['size'] / 1000) . " KB";
        }

        return $size;
    }

    /**
     * get the extension.
     *
     * @return string
     */
    public function getExtension()
    {
        $file_type = explode('.', $this->fileCollected['name']);
        $file_type_end = end($file_type);

        return $file_type_end;
    }

    /**
     * get the filename.
     *
     * @return string
     */
    public function getName()
    {
        $file_name = explode('_', str_replace(array('.', ' ', ',', '-'), '_', $this->fileCollected['name']));
        array_pop($file_name);

        return implode('_', $file_name);
    }

    /**
     * get the origanl filename.
     *
     * @return string
     */
    public function getOriginalName()
    {
        return $this->fileCollected['name'];
    }

    /**
     * get the temp file name.
     *
     * @return string
     */
    public function getTmpFile()
    {
        return $this->fileCollected['tmp_name'];
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
    public function type()
    {
        return $this->fileCollected['type'];
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
}
