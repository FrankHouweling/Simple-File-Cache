<?php
    
    require( "simpleFileCache.class.php" );

    try
    {
     
        $cache    =   new simpleFileCache();
        
        if( !$cache->get( "test" ) )
        {
         
            $cache->set( "test", "demo" );
            
        }
        else
        {
            
            echo $cache->get( "test" );
            
        }
        
     
    }
    catch( Exception $e )
    {
        
        echo $e->getMessage();
        
    }
    
?>