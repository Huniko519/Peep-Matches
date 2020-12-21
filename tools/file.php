<?php

class UTIL_File
{
    /**
     * Avaliable image extensions
     *
     * @var array
     */
    private static $imageExtensions = array('jpg', 'jpeg', 'png', 'gif');

    /**
     * Avaliable video extensions
     *
     * @var array
     */
    private static $videoExtensions = array('avi', 'mpeg', 'wmv', 'flv', 'mov', 'mp4');

    /**
     * Enter description here...
     *
     * @param unknown_type $sourcePath
     * @param unknown_type $destPath
     */
    public static function copyDir( $sourcePath, $destPath, array $fileTypes = null, $level = -1 )
    {
        $sourcePath = self::removeLastDS($sourcePath);

        $destPath = self::removeLastDS($destPath);

        if ( !self::checkDir($sourcePath) )
        {
            return;
        }

        if ( !file_exists($destPath) )
        {
            mkdir($destPath);
        }

        $handle = opendir($sourcePath);

        if ( $handle !== false )
        {
            while ( ($item = readdir($handle)) !== false )
            {
                if ( $item === '.' || $item === '..' )
                {
                    continue;
                }

                $path = $sourcePath . DS . $item;
                $dPath = $destPath . DS . $item;

                if ( is_file($path) && ( $fileTypes === null || in_array(self::getExtension($item), $fileTypes) ) )
                {
                    copy($path, $dPath);
                }
                else if ( $level && is_dir($path) )
                {
                    self::copyDir($path, $dPath, $fileTypes, ($level - 1));
                }
            }

            closedir($handle);
        }
    }

    /**
     *
     * @param string $dirPath
     * @param array $fileTypes
     * @param integer $level
     * @return array
     */
    public static function findFiles( $dirPath, array $fileTypes = null, $level = -1 )
    {
        $dirPath = self::removeLastDS($dirPath);

        $resultList = array();

        $handle = opendir($dirPath);

        if ( $handle !== false )
        {
            while ( ($item = readdir($handle)) !== false )
            {
                if ( $item === '.' || $item === '..' )
                {
                    continue;
                }

                $path = $dirPath . DS . $item;

                if ( is_file($path) && ( $fileTypes === null || in_array(self::getExtension($item), $fileTypes) ) )
                {
                    $resultList[] = $path;
                }
                else if ( $level && is_dir($path) )
                {
                    $resultList = array_merge($resultList, self::findFiles($path, $fileTypes, ($level - 1)));
                }
            }

            closedir($handle);
        }

        return $resultList;
    }

    /**
     * Removes directory with content
     *
     * @param string $dirPath
     * @param boolean $empty
     */
    public static function removeDir( $dirPath, $empty = false )
    {
        $dirPath = self::removeLastDS($dirPath);

        if ( !self::checkDir($dirPath) )
        {
            return;
        }

        $handle = opendir($dirPath);

        if ( $handle !== false )
        {
            while ( ($item = readdir($handle)) !== false )
            {
                if ( $item === '.' || $item === '..' )
                {
                    continue;
                }

                $path = $dirPath . DS . $item;

                if ( is_file($path) )
                {
                    unlink($path);
                }
                else if ( is_dir($path) )
                {
                    self::removeDir($path);
                }
            }

            closedir($handle);
        }

        if ( $empty === false )
        {
            if ( !rmdir($dirPath) )
            {
                trigger_error("Cant remove directory `" . $dirPath . "`!", E_USER_WARNING);
            }
        }
    }

    /**
     * Returns file extension
     *
     * @param string $filename
     * @return string
     */
    public static function getExtension( $filenName )
    {
        return strtolower(substr($filenName, (strrpos($filenName, '.') + 1)));
    }

    /**
     * Rteurns filename with stripped extension
     *
     * @param string $fileName
     * @return string
     */
    public static function stripExtension( $fileName )
    {
        if ( !strstr($fileName, '.') )
        {
            return trim($fileName);
        }

        return substr($fileName, 0, (strrpos($fileName, '.')));
    }

    /**
     * Returns path without last directory separator
     *
     * @param string $path
     * @return string
     */
    public static function removeLastDS( $path )
    {
        $path = trim($path);

        if ( substr($path, -1) === DS )
        {
            $path = substr($path, 0, -1);
        }

        return $path;
    }

    public static function checkDir( $path )
    {
        if ( !file_exists($path) || !is_dir($path) )
        {
            //trigger_warning("Cant find directory `".$path."`!");

            return false;
        }

        if ( !is_readable($path) )
        {
            //trigger_warning('Cant read directory `'.$path.'`!');

            return false;
        }

        return true;
    }
    /* NEED to be censored */

    /**
     * Validates file
     *
     * @param string $fileName
     * @param array $avalia
     * bleExtensions
     * @return bool
     */
    public static function validate( $fileName, array $avaliableExtensions = array() )
    {
        if ( !( $fileName = trim($fileName) ) )
        {
            return false;
        }

        if ( empty($avaliableExtensions) )
        {
            $avaliableExtensions = array_merge(self::$imageExtensions, self::$videoExtensions);
        }

        $extension = self::getExtension($fileName);

        return in_array($extension, $avaliableExtensions);
    }

    /**
     * Validates image file
     *
     * @param string $fileName
     * @return bool
     */
    public static function validateImage( $fileName )
    {
        return self::validate($fileName, self::$imageExtensions);
    }

    /**
     * Validates video file
     *
     * @param string $fileName
     * @return bool
     */
    public static function validateVideo( $fileName )
    {
        return self::validate($fileName, self::$videoExtensions);
    }

    /**
     * Sanitizes a filename, replacing illegal characters
     *
     * @param string $fileName
     * @return string
     */
    public static function sanitizeName( $fileName )
    {
        if ( !( $fileName = trim($fileName) ) )
        {
            return false;
        }

        $specialChars = array("?", "[", "]", "/", "\\", "=", "<", ">", ":", ";", ",", "'", "\"", "&", "$", "#", "*", "(", ")", "|", "~", "`", "!", "{", "}");
        $fileName = str_replace($specialChars, '', $fileName);
        $fileName = preg_replace('/[\s-]+/', '-', $fileName);
        $fileName = trim($fileName, '.-_');

        return $fileName;
    }
    
    /**
     *
     * @param int $uploadErrorCode
     * @return string
     */
    public static function getErrorMessage( $uploadErrorCode )
    {
        if ( !isset($uploadErrorCode) )
        {
            return false;
        }

        $message = '';
        
        if ( $uploadErrorCode != UPLOAD_ERR_OK )
        {
            switch ( $uploadErrorCode )
            {
                case UPLOAD_ERR_INI_SIZE:
                    $error = $language->text('base', 'upload_file_max_upload_filesize_error');
                    break;

                case UPLOAD_ERR_PARTIAL:
                    $error = $language->text('base', 'upload_file_file_partially_uploaded_error');
                    break;

                case UPLOAD_ERR_NO_FILE:
                    $error = $language->text('base', 'upload_file_no_file_error');
                    break;

                case UPLOAD_ERR_NO_TMP_DIR:
                    $error = $language->text('base', 'upload_file_no_tmp_dir_error');
                    break;

                case UPLOAD_ERR_CANT_WRITE:
                    $error = $language->text('base', 'upload_file_cant_write_file_error');
                    break;

                case UPLOAD_ERR_EXTENSION:
                    $error = $language->text('base', 'upload_file_invalid_extention_error');
                    break;

                default:
                    $error = $language->text('base', 'upload_file_fail');
            }

            PEEP::getFeedback()->error($error);
            $this->redirect();
        }

        return $fileName;
    }
}