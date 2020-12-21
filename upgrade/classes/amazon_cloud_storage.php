<?php

require( PEEP_DIR_LIB . 'amazonS3' . DS . 'S3.php' );

class UPDATE_AmazonCloudStorage implements PEEP_Storage
{
    const CLOUD_FOLDER_NAME = '.folder';

    const CLOUD_FILES_DS = '/';

    const MAX_OBJECT_LIST_SIZE = 10000;

    //const CONTENT_TYPE_DIRECTORY = 'application/directory';

    private $s3;
    private $cloudfilesTmpDir;
    private $bucketName;
    private $bucketUrl;

    /**
     * Constructor.
     *
     */
    public function __construct()
    {
        $this->cloudfilesTmpDir = PEEP_DIR_PLUGINFILES . 'base' . DS . 'cloudfiles' . DS;

        // Connect to Rackspace Cloud Files
        $this->s3 = new S3(PEEP_AMAZON_S3_ACCESS_KEY, PEEP_AMAZON_S3_SECRET_KEY);
        $this->bucketName = PEEP_AMAZON_S3_BUCKET_NAME;
        $this->bucketUrl = PEEP_AMAZON_S3_BUCKET_URL;
    }

    /**
     * Copy folder to cloud storage
     *
     * @param string $sourcePath
     * @param string $destPath
     * @param array $fileTypes
     *      * @param int $level
     *
     * @return boolean
     */
    public function copyDir( $sourcePath, $destPath, array $fileTypes = null, $level = -1 )
    {
        $sourcePath = UTIL_File::removeLastDS($sourcePath);
        $destPath = UTIL_File::removeLastDS($destPath);

        if ( !UTIL_File::checkDir($sourcePath) )
        {
            return false;
        }

        if ( !$this->fileExists($destPath) )
        {
            $this->mkdir($destPath);
        }

        $handle = opendir($sourcePath);

        while ( ($item = readdir($handle)) !== false )
        {
            if ( $item === '.' || $item === '..' || $item === '' )
            {
                continue;
            }

            $path = $sourcePath . DS . $item;
            $dPath = $destPath . DS . $item;
            if ( is_file($path) )
            {
                if ( $fileTypes === null || in_array(UTIL_File::getExtension($path), $fileTypes) )
                {
                    $this->copyFile($path, $dPath);
                }
            }
            else if ( $level && is_dir($path) )
            {
                $this->copyDir($path, $dPath, $fileTypes, ($level - 1));
            }
        }

        closedir($handle);

        return true;
    }

    /**
     * Copy file to cloud storage
     *
     * @param string $sourcePath
     * @param string $destPath
     *
     * @return boolean
     */
    public function copyFile( $sourcePath, $destPath )
    {
        $destPath = $this->getCloudFilePath($destPath);

        $obj = $this->s3->putObjectFile($sourcePath, $this->bucketName, $destPath, S3::ACL_PUBLIC_READ);

        if ( $obj === null )
        {
            return false;
        }

        $object = $this->s3->getObjectInfo($this->bucketName, $destPath);
        $this->triggerFileUploadEvent($destPath, $object['size']);

        return true;
    }

    public function copyFileToLocalFS( $destPath, $sourcePath )
    {
        $cloudPath = $this->getCloudFilePath($destPath);

        $result = $this->s3->getObject($this->bucketName, $cloudPath, $sourcePath);

        if( isset($result) && isset($result->code) && $result->code = 200 )
        {
            return true;
        }

        return false;
    }

    public function removeFile( $path )
    {
        $cloudPath = $this->getCloudFilePath($path);
        $result = $this->s3->deleteObject($this->bucketName, $cloudPath);

        $this->triggerFileDeleteEvent($cloudPath);

        return $result;
    }

    private function getFileList( $path, $prefix = null, $marker = null, $limit = self::MAX_OBJECT_LIST_SIZE, $returnCommonPrefixes = true )
    {
        $path = $this->removeSlash( $this->getCloudFilePath($path) ) . self::CLOUD_FILES_DS;
        $marker = ( $marker === null ) ? null : $this->removeSlash( $this->getCloudFilePath($marker) ) . self::CLOUD_FILES_DS;
        $cloudPrefix = $prefix === null ? $path : $path . $prefix;

        $result = $this->s3->getBucket($this->bucketName, $cloudPrefix, $marker, $limit, self::CLOUD_FILES_DS, $returnCommonPrefixes);

        return $result ? $result : array() ;
    }

    public function getFileNameList( $path, $prefix = null, array $fileTypes = null, $marker = null, $limit = self::MAX_OBJECT_LIST_SIZE )
    {
        $files = $this->getFileList( $path, $prefix, $marker, $limit );

        $result = array();

        foreach ( $files as $file )
        {
            switch(true)
            {
                case $fileTypes !== null :

                    if ( !isset($file['name']) )
                    {
                        continue;
                    }

                    if( is_array($fileTypes) && !in_array(UTIL_File::getExtension($file['name']), $fileTypes) )
                    {
                        continue;
                    }

                case isset($file['name']) :

                    preg_match('/.*?\/([^\/]*)$/', $file['name'], $matches);

                    if( isset($matches[1]) &&  trim($matches[1]) === self::CLOUD_FOLDER_NAME )
                    {
                        continue;
                    }

                    $result[] = $this->getLocalFSPath($file['name']);

                    break;

                case isset($file['prefix']) :

                    $result[] = $this->getLocalFSPath($file['prefix']);

                    break;
            }
        }

        return $result;
    }

    public function removeDir( $dirPath )
    {
        $files = null;
        $marker = null;
        $result = true;

        do
        {
            $files = $this->getFileList($dirPath, null, $marker, 1000, true);

            foreach ( $files as $file )
            {
                if( isset( $file['name'] ) )
                {
                    $marker = $this->getLocalFSPath($file['name']);
                    if( !$this->removeFile( $marker ) )
                    {
                        $result = false;
                    }
                }
                else if( isset( $file['prefix'] ) )
                {
                    $marker = $this->getLocalFSPath($file['prefix']);
                    $this->removeDir($this->getLocalFSPath($file['prefix']));
                }
            }
        }
        while ( !empty($files) );

        return $result;
    }

    public function fileGetContent( $destPath )
    {
        $cloudPath = $this->getCloudFilePath($destPath);
        $object = $this->s3->getObject($this->bucketName, $cloudPath);

        if ( !$object )
        {
            return null;
        }

        if ( $object->code !== 200 || !isset($object->body) )
        {
            return null;
        }

        return $object->body;
    }

    public function fileSetContent( $destPath, $content )
    {
        $cloudPath = $this->getCloudFilePath($destPath);
        $result = $this->s3->putObject($content, $this->bucketName, $cloudPath, S3::ACL_PUBLIC_READ);

        if ( $result )
        {
            $object = $this->s3->getObjectInfo($this->bucketName, $cloudFilePath);
            $this->triggerFileUploadEvent($object);
        }

        return $result;
    }

    public function getFileUrl( $path )
    {
        return $this->getBucketUrl() . ($this->getCloudFilePath($path));
    }

    public function getBucketUrl()
    {
        return $this->bucketUrl;
    }

    public function fileExists( $path )
    {
        $result = false;

        if ( $this->isFile($path) || $this->isDir($path) )
        {
            $result = true;
        }

        return $result;
    }

    public function isFile( $path )
    {
        $cloudFilePath = $this->getCloudFilePath($path);

        $info = $this->s3->getObjectInfo($this->bucketName, $cloudFilePath);

        $result = false;

        if ( isset($info) && !empty($info['hash']) )
        {
            $result = true;
        }

        return $result;
    }

    public function isDir( $path )
    {
        $cloudFilePath = $this->removeSlash($this->getCloudFilePath($path)) . self::CLOUD_FILES_DS . self::CLOUD_FOLDER_NAME;

        $info = $this->s3->getObjectInfo($this->bucketName, $cloudFilePath);

        $result = false;

        if ( isset($info) && !empty($info['hash']) )
        {
            $result = true;
        }

        return $result;
    }

    public function mkdir( $path )
    {
        if ( empty($path) )
        {
            return false;
        }

        $array = preg_split('/\//', $this->removeSlash($this->getCloudFilePath($path)));

        $cloudFilePath = '';

        foreach( $array as $folder )
        {
            if ( !empty($folder) )
            {
                $cloudFilePath .= $folder . self::CLOUD_FILES_DS;
                $this->s3->putObject(self::CLOUD_FOLDER_NAME, $this->bucketName, $cloudFilePath . self::CLOUD_FOLDER_NAME);
            }
        }

        return true;
    }

    private function removeSlash( $path )
    {
        $path = trim($path);

        if ( substr($path, 0, 1) === self::CLOUD_FILES_DS )
        {
            $path = substr($path, 1);
        }

        if ( substr($path, -1) === self::CLOUD_FILES_DS )
        {
            $path = substr($path, 0, -1);
        }

        return $path;
    }

    private function getCloudFilePath( $path )
    {
        $cloudPath = null;

        $prefixLength = strlen(PEEP_DIR_ROOT);
        $filePathLength = strlen($path);

        if ( $prefixLength <= $filePathLength && substr($path, 0, $prefixLength) === PEEP_DIR_ROOT )
        {
            $cloudPath = str_replace(PEEP_DIR_ROOT, '', $path);
            $cloudPath = str_replace(DS, '/', $cloudPath);
            $cloudPath = $this->removeSlash($cloudPath);
        }
        else
        {
            trigger_error("Cant find directory `" . $path . "`!");
        }

        return $cloudPath;
    }

    private function getLocalFSPath( $cloudPath )
    {
        $cloudPath = $this->removeSlash($cloudPath);

        $result = PEEP_DIR_ROOT . str_replace('/', DS, $cloudPath);

        return $result;
    }

    private static function getExtension( $filenName )
    {
        if ( strrpos($filenName, '.') == 0 )
        {
            return null;
        }

        return UTIL_File::getExtension($filenName);
    }

    public function isWritable( $path )
    {
        return true;
    }

    public function renameFile( $oldDestPath, $newDestPath )
    {
        $result = false;

        $oldCloudPath = $this->getCloudFilePath($oldDestPath);
        $newCloudPath = $this->getCloudFilePath($newDestPath);

        $result = $this->s3->copyObject($this->bucketName, $oldCloudPath, $this->bucketName, $newCloudPath, S3::ACL_PUBLIC_READ);

        if ( $oldCloudPath != $newCloudPath )
        {
            $this->removeFile($oldDestPath);
        }

        $info = $this->s3->getObjectInfo($this->bucketName, $newCloudPath);
        $this->triggerFileUploadEvent( $newCloudPath, $info['size']);
        
        return $result;
    }

    private function triggerFileUploadEvent( $path, $size )
    {
        if ( empty($path) )
        {
            return;
        }

        $params = array(
            'path' => $path,
            'size' => (int)$size
        );

        $event = new PEEP_Event(self::EVENT_ON_FILE_UPLOAD, $params);
        PEEP::getEventManager()->trigger($event);
    }

    private function triggerFileDeleteEvent( $path )
    {
        if ( empty($path)  )
        {
            return;
        }

        $params = array(
            'path' => $path
        );

        $event = new PEEP_Event(self::EVENT_ON_FILE_DELETE, $params);
        PEEP::getEventManager()->trigger($event);
    }
}
