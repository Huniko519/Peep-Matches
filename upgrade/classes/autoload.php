<?php
class UPDATE_Autoload
{
    /**
     * Registered package pointers.
     *
     * @var array
     */
    private $packagePointers = array();
    /**
     * Registered classes.
     * 
     * @var array
     */
    private $classPathArray = array();

    /**
     * Constructor.
     *
     */
    private function __construct()
    {
        
    }
    /**
     * Singleton instance.
     *
     * @var PEEP_Autoload
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return PEEP_Autoload
     */
    public static function getInstance()
    {
        if ( self::$classInstance === null )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    /**
     * Main static method registered as autoloader.
     * Don't call it manually.
     */
    public static function autoload( $className )
    {
        $thisObj = self::getInstance();

        try
        {
            $path = $thisObj->getClassPath($className);
        }
        catch ( Exception $e )
        {
            return;
        }
        
        include $path;
    }

    /**
     * Returns class definition file path for provided classname.
     *
     * @throws InvalidArgumentException
     * @param string $class
     * @return string
     */
    public function getClassPath( $className )
    {
        // if class isn't found in class path array
        if ( !isset($this->classPathArray[$className]) )
        {
            $packagePointer = $this->getPackagePointer($className);

            // throw exception if package pointer is not registered
            if ( !isset($this->packagePointers[$packagePointer]) )
            {
                throw new InvalidArgumentException("Package pointer `" . $packagePointer . "` is not registered!");
            }

            $this->classPathArray[$className] = $this->packagePointers[$packagePointer] . $this->classToFilename($className);
        }

        return $this->classPathArray[$className];
    }

    /**
     * Registers class in autoloader.
     *
     * @throws LogicException
     * @param string $className
     * @param string $filePath
     */
    public function addClass( $className, $filePath )
    {
        $className = trim($className);

        if ( isset($this->classPathArray[$className]) )
        {
            throw new LogicException("Can't register `" . $className . "` in autoloader. Duplicated class name!");
        }

        $this->classPathArray[$className] = $filePath;
    }

    /**
     * Registers class list in autoloader.
     *
     * @throws LogicException
     * @param array $classArray
     */
    public function addClassArray( array $classArray )
    {
        foreach ( $classArray as $className => $filePath )
        {
            $this->addClass($className, $filePath);
        }
    }

    /**
     * Returns file name for provided class name.
     * 
     * Examples:
     * 		`MyNewClass` => `my_new_class.php`
     * 		`PEEP_MyClass` => `my_class.php`
     * 		`PEEP_BOL_MyClass` => `my_class.php`
     *
     * @param string $className
     * @param boolean $extension
     * @return string
     */
    public function classToFilename( $className, $extension = true )
    {
        // need to remove package pointer
        if ( strstr($className, '_') )
        {
            $className = substr($className, (strrpos($className, '_') + 1));
        }

        return substr(UTIL_String::capsToDelimiter($className), 1) . ($extension ? '.php' : '');
    }

    /**
     * Returns class name for provided file name and package pointer.
     *
     * @param string $fileName
     * @param string $packagePointer
     * @return string
     */
    public function filenameToClass( $fileName, $packagePointer = null )
    {
        $packagePointer = ( ( $packagePointer === null ) ? '' : strtoupper($packagePointer) . '_' );

        return $packagePointer . UTIL_String::delimiterToCaps('_' . substr($fileName, 0, -4));
    }

    /**
     * Returns package pointer for provided class name.
     *
     * @throws InvalidArgumentException
     * @param string $className
     * @return string
     */
    public function getPackagePointer( $className )
    {
        // throw exception if class doesn't have package pointer
        if ( !strstr($className, '_') )
        {
            throw new InvalidArgumentException("Can't find package pointer in class `" . $className . "` !");
        }

        return substr($className, 0, strrpos($className, '_'));
    }

    /**
     * Registers package pointer in autoloader.
     *
     * @throws InvalidArgumentException
     * @param string $packagePointer
     * @param string $dir
     */
    public function addPackagePointer( $packagePointer, $dir )
    {
        $packagePointer = trim($packagePointer);
        $dir = trim($dir);

        // throw exception if package pointer already registered
        if ( isset($this->packagePointers[$packagePointer]) )
        {
            throw new InvalidArgumentException("Can't add package pointer `" . $packagePointer . "`! Duplicated package pointer!");
        }

        // add directory separator if needed
        if ( substr($dir, -1) !== DS )
        {
            $dir = trim($dir) . DS;
        }

        $this->packagePointers[trim(strtoupper($packagePointer))] = $dir;
    }
}

