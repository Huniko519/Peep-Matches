<?php

require( PEEP_DIR_LIB . 'rackspace_cloudfiles' . DS . 'cloudfiles.php');

class BASE_CLASS_CloudStorage implements PEEP_Storage
{
    const CLOUD_FILES_DS = '/';

    const MAX_OBJECT_LIST_SIZE = 10000;

    const CONTENT_TYPE_DIRECTORY = 'application/directory';


    private $auth;
    private $connection;
    private $container;
    private $cloudfilesTmpDir;

    /**
     * Constructor.
     *
     */
    public function __construct()
    {
        $this->cloudfilesTmpDir = PEEP_DIR_PLUGINFILES . 'base' . DS . 'cloudfiles' . DS;

        // Connect to Rackspace Cloud Files
        $this->auth = new CF_Authentication(PEEP_CLOUDFILES_USER, PEEP_CLOUDFILES_API_KEY);
        $this->auth->authenticate();
        $this->connection = new CF_Connection($this->auth);
        $this->container = $this->connection->get_container(PEEP_CLOUDFILES_CONTAINER);
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
                if ( $fileTypes === null || in_array(UTIL_File::getExtension($item), $fileTypes) )
                {
                    $this->copyFile($path, $dPath);
                }
            }
            else if ( $level && is_dir($path) )
            {
                $this->mkdir($dPath);
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

        // local hash
        $md5Local = md5_file($sourcePath);
        $md5Remote = '';

        $obj = null;

        // remote hash
        try
        {
            $obj = $this->container->create_object($destPath);

            $md5Remote = $obj->getETag();
        }
        catch ( Exception $e )
        {
            $md5Remote = false;
            $obj = $this->getObject($destPath);
        }

        if ( $obj === null )
        {
            return false;
        }

        if ( $md5Remote != $md5Local )
        {
//            try
//            {
            $obj->load_from_filename($sourcePath);
            return true;
//            }
//            catch( Exception $ex )
//            {
//                return false;
//            }
        }
    }

    public function copyFileToLocalFS( $destPath, $sourcePath )
    {
        $object = $this->getObject($this->getCloudFilePath($destPath));

        if ( $object )
        {
//            try
//            {
            $object->save_to_filename($sourcePath);
            return true;
//            }
//            catch( IOException $ex )
//            {
//                //ignore
//            }
        }

        return false;
    }

    public function removeDir( $dirPath )
    {
        if ( !$this->isDir($dirPath) )
        {
            return false;
        }

        $files = null;
        $marker = null;
        $result = true;

        do
        {
            if ( isset($files) && is_array($files) && count($files) > 0 )
            {
                $marker = $files[count($files) - 1];
            }

            $files = $this->getFileNameList($dirPath, null, null, $marker, 1000);

            foreach ( $files as $file )
            {
                if ( $this->fileExists($file) )
                {
                    if ( $this->isFile($file) )
                    {
                        if ( !$this->removeFile($file) )
                        {
                            $result = false;
                        }
                    }
                    else
                    {
                        if ( !$this->removeDir($file) )
                        {
                            $result = false;
                        }
                    }
                }
            }
        }
        while ( !empty($files) );

        if ( !$this->container->delete_object($this->getCloudFilePath($dirPath)) )
        {
            $result = false;
        }

        return $result;
    }

    public function fileGetContent( $destPath )
    {
        $destPath = $this->getCloudFilePath($destPath);
        $object = $this->getObject($destPath);

        if ( !$object )
        {
            return null;
        }

        return $object->read();
    }

    public function fileSetContent( $destPath, $content )
    {
        if ( empty($content) )
        {
            return false;
        }

        do
        {
            $tmpFilename = $this->cloudfilesTmpDir . uniqid();
        }
        while ( file_exists($tmpFilename) );

        file_put_contents($tmpFilename, $content);

        if ( !file_exists($tmpFilename) )
        {
            return false;
        }

        $result = $this->copyFile($tmpFilename, $destPath);

        unlink($tmpFilename);

        return $result;
    }

    public function removeFile( $path )
    {
        if ( $this->isFile($path) )
        {
//            try
//            {
            return $this->container->delete_object($this->getCloudFilePath($path));
//            }
//            catch( SyntaxException $ex )
//            {
//                //ignore;
//                return false;
//            }
//            catch( NoSuchObjectException $ex )
//            {
//                //ignore;
//                return false;
//            }
        }

        return false;
    }

    private function getObject( $path )
    {
        $object = null;

        try
        {
            $object = $this->container->get_object($this->removeSlash($path));
        }
        catch ( NoSuchObjectException $ex )
        {
            //ignore
        }

        return $object;
    }

    public function getFileNameList( $path, $prefix = null, array $fileTypes = null, $marker = null, $limit = self::MAX_OBJECT_LIST_SIZE )
    {
        if ( !$this->fileExists($path) || !$this->isDir($path) )
        {
            return array();
        }

        $path = $this->getCloudFilePath($path);
        $marker = ( $marker === null ) ? null : $this->getCloudFilePath($marker);
        $cloudPrefix = $prefix === null ? null : $path . self::CLOUD_FILES_DS . $prefix;
        $files = array();

//        try
//        {
        $files = $this->container->list_objects($limit, $marker, $cloudPrefix, $path);
//        }
//        catch ( InvalidResponseException $ex )
//        {
//            //ignore
//        }
//        printVar('----');
//        printVar($files);
//        printVar('----');

        $result = array();

        foreach ( $files as $file )
        {
            $filenName = substr($file, (strrpos($file, self::CLOUD_FILES_DS) + 1));
            $extention = $this->getExtension($filenName);
            //printVar($extention);

            if ( $fileTypes === null || $this->isFile($path) && in_array($this->getExtension($filenName), $fileTypes) )
            {
                $result[] = $this->getLocalFSPath($file);
            }
        }
        //printVar('!----!');
        return $result;
    }

    public function getFileUrl( $path )
    {
        return $this->getContainerUrl() . '/' . $this->getCloudFilePath($path);
    }

    public function getContainerUrl()
    {
        return $this->container->cdn_uri;
    }

    public function fileExists( $path )
    {
        $result = false;
        $object = $this->getObject($this->getCloudFilePath($path));

        if ( isset($object) )
        {
            $result = true;
        }

        return $result;
    }

    public function isFile( $path )
    {
        $object = $this->getObject($this->getCloudFilePath($path));

        $result = false;

        if ( isset($object) && $object->content_type !== self::CONTENT_TYPE_DIRECTORY )
        {
            $result = true;
        }

        return $result;
    }

    public function isDir( $path )
    {
        $result = false;

        $object = $this->getObject($this->getCloudFilePath($path));

        if ( isset($object) && $object->content_type === self::CONTENT_TYPE_DIRECTORY )
        {
            $result = true;
        }

        return $result;
    }

    public function mkdir( $path )
    {
        $path = $this->getCloudFilePath($path);

        if ( count(explode(self::CLOUD_FILES_DS, $path)) > 0 )
        {
            // add fake object
            $this->container->create_paths($path . self::CLOUD_FILES_DS . '1');
        }
        else
        {
            $this->container->create_paths($path);
        }
    }

    private function createFile( $path )
    {
        return $this->container->create_object($this->removeSlash($path));
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
        return $this->fileExists($path);
    }

    public function renameFile( $oldDestPath, $newDestPath )
    {
        $result = false;

        if ( $this->fileExists($oldDestPath) && $this->isFile($oldDestPath) && !$this->fileExists($newDestPath) )
        {
            do
            {
                $tmpFilename = $this->cloudfilesTmpDir . uniqid();
            }
            while ( file_exists($tmpFilename) );

            $this->copyFileToLocalFS($oldDestPath, $tmpFilename);

            if ( !file_exists($tmpFilename) )
            {
                return false;
            }

            $cloudPath = $this->getCloudFilePath($newDestPath);

            if ( count(explode(self::CLOUD_FILES_DS, $cloudPath)) > 0 )
            {
                $newDir = substr($newDestPath, 0, (strrpos($newDestPath, self::CLOUD_FILES_DS)));
                $this->mkdir($newDir);
            }

            $result = $this->copyFile($tmpFilename, $newDestPath);

            unlink($tmpFilename);
            $this->removeFile($oldDestPath);
        }
        return $result;
    }

    public function chmod($destPath, $premissions) {
        
    }
}