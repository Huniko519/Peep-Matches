<?php

interface PEEP_Storage
{
    const EVENT_ON_FILE_UPLOAD = 'cloud.on_file_upload';
    const EVENT_ON_FILE_DELETE = 'cloud.on_file_delete';

     /**
     * Copy dir to storage
     *
     * @param string $sourcePath
     * @param string $destPath
     * @param array $fileTypes
     * @param int $level
     *
     * @return boolean
     */
    public function copyDir ( $sourcePath, $destPath, array $fileTypes = null, $level = -1 );

     /**
     * Copy file to storage
     *
     * @param string $sourcePath
     * @param string $destPath
     *
     * @return boolean
     */
    public function copyFile ( $sourcePath, $destPath );

     /**
     * Copy file to local file system
     *
     * @param string $destPath
     * @param string $toFilePath
     *
     * @return boolean
     */
    public function copyFileToLocalFS ( $destPath, $toFilePath );

     /**
     * Return storage file content
     *
     * @param string $destPath
     *
     * @return string
     */
    public function fileGetContent ( $destPath );

     /**
     * Set storage file content
     *
     * @param string $destPath
     * @param string $content
     *
     * @return boolean
     */
    public function fileSetContent ( $destPath, $conent );

     /**
     * Remove storage dir
     *
     * @param string $destPath
     *
     * @return boolean
     */
    public function removeDir ( $destPath );

     /**
     * Remove storage file
     *
     * @param string $destPath
     *
     * @return boolean
     */
    public function removeFile ( $destPath );

     /**
     * Return file storage file
     *
     * @param string $path
     * @param string $prefix
     * @param array $fileTypes
     *
     * @return array
     */
    public function getFileNameList ( $path, $prefix = null, array $fileTypes = null );

     /**
     * Return file url
     *
     * @param string $path
     *
     * @return string
     */
    public function getFileUrl ( $path );

     /**
     * Checks whether a file or directory exists
     *
     * @param string $path
     *
     * @return boolean
     */
    public function fileExists ( $path );

     /**
     * Tells whether the $path is a regular file
     *
     * @param string $path
     *
     * @return boolean
     */
    public function isFile ( $path );

     /**
     * Tells whether the $path is a directory
     *
     * @param string $path
     *
     * @return boolean
     */
    public function isDir ( $path );

     /**
     * Create directory
     *
     * @param string $path
     *
     * @return boolean
     */
    public function mkdir ( $path );
    
     /**
     * Tells whether the filename is writable
     *
     * @param string $path
     *
     * @return boolean
     */
    public function isWritable ( $filename );

     /**
     * Rename file
     *
     * @param string $oldPath
     * @param string $newPath
     *
     * @return boolean
     */
    public function renameFile ( $oldPath, $newPath );

    /**
     * Rename file
     *
     * @param string $destPath
     * @param string $premissions
     *
     * @return boolean
     */
    public function chmod ( $destPath, $premissions );
}

?>
