<?php
    
    require( "simpleFileCache.class.php" );

    try
    {
     
        $cache    =   new simpleFileCache();
        
        var_dump( $cache->get( "test" ) );
        
        if( !$cache->get( "test" ) )
        {
         
            $cache->set( "test", "bladiebla" );
            
        }
        else {
            
            echo $cache->get( "test" );
            
        }
     
    }
    catch( Exception $e )
    {
        
        echo $e->getMessage();
        
    }
    
?>