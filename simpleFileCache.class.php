<?php

/**
 * File I/O based PHP cache system.
 * @author Frank Houweling <houweling.frank@gmail.com>
 * @verson 1.1
 */

class simpleFileCache
{
    
    private $cacheDir;
    private $salt;
    
    /**
     * Constructor for the Simple File Cache class.
     * 
     * @param string $cacheDir The directory where the cache folder is located.
     * @param string $salt The application file name salt. You do not have to
     * change this to be save, just keep the cache directory locked!
     * @return bool Returns if cache object is created.
     */
    
    function __construct( $cacheDir = "cache/", $salt = "justasalt" )
    {
        
        if( !$this->setDir( $cacheDir ) )
        {
            
            throw new Exception( "Cache directory <i>" . $cacheDir . "</i> could 
             not be found, or was readable." );
            return false;  
         
        }
        else if( !$this->updateCache() )
        {
            
            throw new Exception( "Cache could not be cleaned." );
            return false;
            
        }
        
        $this->setSalt( $salt );
        
    }
    
     /**
     * Set the current dir from where simpleFileCache runs.
     * 
     * @param string $cacheDir The directory where the cache folder is located.
     * @return bool Returns if the directory exists and is writable, and with 
      * that if the current dir from where simpleFileCache runs is changed.
     */
    
    function setDir( $cacheDir )
    {
        
        if( !is_dir( $cacheDir ) || !is_writable( $cacheDir ) )
        {
            
            return false;
            
        }
        else
        {
            
            $this->cacheDir =   $cacheDir;
            return true;
            
        }
        
    }
    
    /**
     * Set the current file name salt value.
     * @param string $salt The to be used salt. <strong>Make sure you use the 
     * same salt all over the application!</strong>
     */
    
    function setSalt( $salt )
    {
        
        $this->salt = $salt;
        
    }
    
    /**
     * Get an item from the cache.
     * @param string $name The item name.
     * @return mixed Returns false when the items does not exist in cache, returns
     * a string of the item when it exists in the cache.
     */
    function get( $name )
    {
        
        if( !$get    =   @file_get_contents( $this->cacheDir . 
                md5( $name . $this->salt ) . ".dat" ) )
        {
            
            return false;
            
        }
        else
        {
            
            return $get;
            
        }
        
        
    }
    
    
    /**
     * Save an item in the cache.
     * 
     * @param string $name The identifier where later the data can be requested with.
     * @param int $time The time in seconds how long the cache item should be saved.
     *                  The default value is 14 400 = 4 hours
     * @param string $value The value saved in the cache for the specified identifier.
     * @throws Exception When given values are not valid (= empty)
     */
    function set( $name, $value, $time = 14400 )
    {
        
        if( !empty($name) && !empty( $value ) )
        {
            
            // Write in the data file
            $handler    =   fopen( $this->cacheDir . md5( $name . $this->salt ) . ".dat", "x" );
            fwrite( $handler, $value );
            fclose( $handler );
            
            // Change in the XML file
            
            $dom = new DOMDocument;
            
            // Load the File as a DOM document
       
            if( !$dom->loadXML( file_get_contents( $this->cacheDir . "cache.xml" ) ) )
            {

                throw new Exception( "Could not load xml cache store file." );

            }
            
            $root       =   $dom->documentElement;
            $file       =   $dom->createElement( "file" );
            
            $filename   =   $dom->createElement( "filename",  
                    md5( $name . $this->salt ) . ".dat" );
            $file->appendChild( $filename );
            
            $timestamp  = $dom->createElement( "timestamp", time()+$time );
            $file->appendChild( $timestamp );
            
            $root->appendChild( $file );
            
            $dom->save( $this->cacheDir . "cache.xml" );
       
            return true;
            
        }
        else
        {
            
            throw new Exception( "The given values for name and value where 
                    not valid." );
            
        }
        
    }
    
    /**
     * Removes old cache files from the current working directory.
     */
    
    function updateCache()
    {
        
        // Load simpleXML for reading the cache data file.        
       $dom = new DOMDocument;
       
       // Load the File as a DOM document
       
       if( !$dom->loadXML( file_get_contents( $this->cacheDir . "cache.xml" ) ) )
       {
           
           throw new Exception( "Could not load xml cache store file." );
           
       }
       
       $xpath = new DOMXPath($dom);
       $els = $xpath->query("//file");
       
       foreach( $els as $file) {
           
           // If the Cache file is old...
           if( $file->getElementsByTagName( "timestamp" )->item(0)->nodeValue 
                   < time() )
           {
               
               // Remove item from the XML document
               $dom->documentElement->removeChild( $file );
               
               // Remove cache file
               unlink( $this->cacheDir . $file->getElementsByTagName( "filename" )->
                       item(0)->nodeValue );
               
           }
           
       }
       
       if( !@$dom->save( $this->cacheDir . "cache.xml" ) )
       {
           
           throw new Exception( "Could not write the <i>" .  $this->cacheDir . 
                   "cache.xml" . "</i> file." );
           
       }
       
       return true;
        
    }
    
}

?>
