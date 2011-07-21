<?php

  $escenic_source = '000002_migration_article.xml';
  echo "Hello \n";
  
    function toArray($data) {
        if (is_object($data)) $data = get_object_vars($data);
        return is_array($data) ? array_map(__FUNCTION__, $data) : $data;
    }
    
  if (file_exists($escenic_source)) 
    {
        $xml = simplexml_load_file($escenic_source); 
        foreach ($xml->content as $content_piece)
            {
            
#                print_r($content_piece);
                print $content_piece->uri . "\n";
                    
                print "Type : " .  $content_piece->attributes()->type . "\n";
                print "state : " .  $content_piece->attributes()->state . "\n";
                                            

            }
    } 
        else 
    {
        exit("Could not find $escenic_source.");
    }


?>