<?php


class BASE_CLASS_FileStorage implements PEEP_Storage
{

    public function copyDir( $sourcePath, $destPath, array $fileTypes = null, $level = -1 )
    {
        if ( !$this->fileExists($destPath) )
        {
            $this->mkdir($destPath);
        }

        UTIL_File::copyDir($sourcePath, $destPath, $fileTypes, $level);
    }

    // $destPath - must be a file path ( not directory path )

    public function copyFile( $sourcePath, $destPath )
    {
        if ( file_exists($sourcePath) && is_file($sourcePath) )
        {
            copy($sourcePath, $destPath);
            chmod($destPath, 0666);
            return true;
        }

        return false;
    }

    public function copyFileToLocalFS( $destPath, $toFilePath )
    {
        return $this->copyFile($destPath, $toFilePath);
    }

    public function fileGetContent( $destPath )
    {
        return file_get_contents($destPath);
    }

    public function fileSetContent( $destPath, $conent )
    {
        file_put_contents($destPath, $conent);
    }

    public function removeDir( $dirPath )
    {
        UTIL_File::removeDir($dirPath);
    }

    public function removeFile( $destPath )
    {
        return unlink($destPath);
    }

    public function getFileNameList( $dirPath, $prefix = null, array $fileTypes = null )
    {
        $dirPath = UTIL_File::removeLastDS($dirPath);

        $resultList = array();

        $handle = opendir($dirPath);

        while ( ($item = readdir($handle)) !== false )
        {
            if ( $item === '.' || $item === '..' )
            {
                continue;
            }

            if ( $prefix != null )
            {
                $prefixLength = strlen($prefix);

                if ( !( $prefixLength <= strlen($item) && substr($item, 0, $prefixLength) === $prefix ) )
                {
                    continue;
                }
            }

            $path = $dirPath . DS . $item;

            if ( $fileTypes === null || is_file($path) && in_array(UTIL_File::getExtension($item), $fileTypes) )
            {
                $resultList[] = $path;
            }
        }

        closedir($handle);

        return $resultList;
    }

    public function getFileUrl( $path )
    {
        if ( $path === null )
        {
            return '';
        }

        $url = '';

        $prefixLength = strlen(PEEP_DIR_ROOT);
        $filePathLength = strlen($path);

        if ( $prefixLength <= $filePathLength && substr($path, 0, $prefixLength) === PEEP_DIR_ROOT )
        {
            $url = str_replace(PEEP_DIR_ROOT, PEEP_URL_HOME, $path);
            $url = str_replace(DS, '/', $url);
        }

        return $url;
    }

    public function fileExists( $path )
    {
        return file_exists($path);
    }

    public function isFile( $path )
    {
        return is_file($path);
    }

    public function isDir( $path )
    {
        return is_dir($path);
    }

    public function mkdir( $path )
    {
        return mkdir($path, 0777, true);
    }

    public function isWritable( $path )
    {
        return is_writable($path);
    }

    public function renameFile( $oldDestPath, $newDestPath )
    {
        if ( is_file($oldDestPath) )
        {
            return rename($oldDestPath, $newDestPath);
        }

        return false;
    }


    public function chmod( $path, $permissions )
    {
        chmod($path, $permissions);
    }
}
?>
