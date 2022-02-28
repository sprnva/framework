<?php

namespace App\Core;

interface StorageInterface
{
    /**
     * Get the fileupload input.
     *
     * @param  file  $file
     * @return $this
     *
     */
    public static function file($file);

    /**
     * Check the request if it is a file.
     *
     * @param  file  $file
     * @return bool
     *
     */
    public static function hasFile($file);

    /**
     * Get the filesize.
     *
     * @return float
     */
    public function getSize($inUnit = 'kb');


    /**
     * get the extension.
     *
     * @return string
     */
    public function getExtension();

    /**
     * get the filename.
     *
     * @return string
     */
    public function getName();

    /**
     * get the origanl filename.
     *
     * @return string
     */
    public function getOriginalName();

    /**
     * get the temp file name.
     *
     * @return string
     */
    public function getTmpFile();

    /**
     * Store the file as new.
     *
     * @return bool
     */
    public function storeAs($file_tmp, $dirPath, $type, $filename, $folder = '');

    /**
     * delete the file.
     *
     * @param $filePath
     * @return bool
     */
    public static function delete($filePath);

    /**
     * Store the file as new.
     *
     * @return bool
     */
    public static function deleteDirectory($filePath);

    /**
     * get the file type with slash
     *
     * @return string
     */
    public function type();

    /**
     * get the avatar of the user
     *
     * @return string
     */
    public static function getAvatar($user_id);
}
