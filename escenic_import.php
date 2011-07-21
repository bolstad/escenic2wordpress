<?php

  $escenic_source = '000002_migration_article.xml';
  echo "Hello \n";
  
    function toArray($data) {
        if (is_object($data)) $data = get_object_vars($data);
        return is_array($data) ? array_map(__FUNCTION__, $data) : $data;
    }

    function replace_newline($string) {
      return (string)str_replace(array("\r", "\r\n", "\n"), '', $string);
    }
    
  if (file_exists($escenic_source)) 
    {
        $xml = simplexml_load_file($escenic_source); 
        foreach ($xml->content as $content_piece)
            {            
                $postobj = array();                
                $type = $content_piece->attributes()->type;
                $postobj['post_date'] = $content_piece->attributes()->publishdate;
                if ($type == 'article')
                        {
                            print $content_piece->uri . "\n";                   
                            print "state : " .  $content_piece->attributes()->state . "\n";
                            echo "Yay, found an article\n";
                            
                            foreach ($content_piece->field as $article_field)
                                {
                                    # the_content
                                    if ($article_field->attributes()->name == 'headline')
                                    {
                                        $postobj['the_title'] = replace_newline($article_field[0]);
                                    }

                                    if ($article_field->attributes()->name == 'body')
                                        {
                                            $thetext = '';
                                            foreach ($article_field->p as $line)
                                                {
                                                    if (gettype($line) == 'object')
                                                        {
#                                                            var_dump(get_object_vars($line));
                                                            # the original web article + xml got paragraphs, so we are adding them aswell                                                   
                                                            $thetext .= "<p>$line</p>\n";
                                                        }
                                                        else
                                                        {
#                                                            print_r($line);
                                                            echo gettype($line);
                                                        }
                                                }
                                           $postobj['post_content'] = $thetext ;
                                        }
                                    # post_excerpt
                                    if ($article_field->attributes()->name == 'leadtext')
                                        {

                                            print_r($article_field);
                                            $post_excerpt = '';
                                            foreach ($article_field->p as $line)
                                                {
                                                
                                                    if ($keys = array_keys(get_object_vars($line)))
                                                        {
                                                            print_r($keys);
                                                            foreach ($keys as $key)
                                                                {
                                                                    $post_excerpt .= "<$key>". $line->$key. "</$key>\n";
                                                                    
                                                                }
                                                        }

                                                    # the original web article + xml got paragraphs, so we are adding them aswell                                                   
                                                    $post_excerpt .= "<p>$line</p>\n";
                                                }
                                           $postobj['post_excerpt'] = $post_excerpt ;
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