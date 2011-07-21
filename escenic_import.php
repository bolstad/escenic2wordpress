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
                $postobj = array();                
                $type = $content_piece->attributes()->type;
                if ($type == 'article')
                        {
                            print $content_piece->uri . "\n";                   
                            print "state : " .  $content_piece->attributes()->state . "\n";
                            echo "Yay, found an article\n";
                            foreach ($content_piece->field as $article_field)
                                {
                                    if ($article_field->attributes()->name == 'body')
                                        {
                                            foreach ($article_field->p as $line)
                                                {
                                                    # the original web article + xml got paragraphs, so we are adding them aswell                                                   
                                                    $thetext .= "<p>$line</p>\n";
                                                }
                                           $postobj['post_content'] = $thetext ;

                                        }
                                                                
                                }
                                
#                           print_r($content_piece);        
                            print_r($postobj);      
                            die;
                        }
    
            }
    } 
        else 
    {
        exit("Could not find $escenic_source.");
    }


?>