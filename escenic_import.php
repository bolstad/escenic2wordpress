<?php
    
  require "../../wp-load.php";
  
  # requires https://github.com/bolstad/wp-tagliatelle
  $taggy = new Tagliatelle; 

  $escenic_source = '000002_migration_article.xml';
  echo "Hello \n";

    function find_tagged_post($tag,$value,$title = '')
    {
            $superid = null;
            query_posts( array( 'posts_per_page' => '1',
                             'post_type' => 'artikel',
                             'meta_key'=>$tag,
                             'meta_value'=>$value,
                             'orderby'=>'meta_value_num',
                             'order'=>'DESC' ) );
            global $wp_query;
            print_r($wp_query);                 
            if (have_posts()) :
                    while (have_posts()) :
                                                    the_post();
                                                    $superid = get_the_ID();
                    endwhile;
            endif;
            wp_reset_query();   
            return $superid;                
    }
      
    function toArray($data) {
        if (is_object($data)) $data = get_object_vars($data);
        return is_array($data) ? array_map(__FUNCTION__, $data) : $data;
    }

    function replace_newline($string) {
      return (string)str_replace(array("\r", "\r\n", "\n"), ' ', $string);
    }
    
  if (file_exists($escenic_source)) 
    {
        $xml = simplexml_load_file($escenic_source); 
        foreach ($xml->content as $content_piece)
            {            
                $postobj = array();                
                $type = $content_piece->attributes()->type;
                $state = $content_piece->attributes()->state;
                $postobj['post_date'] = (string)$content_piece->attributes()->publishdate;
                $postobj['post_type'] = 'artikel';                
                $postobj['post_status'] = 'publish';
                print "state: '$state'\n";
                if (($type == 'article') and ($state == 'published'))
                        {
                            print $content_piece->uri . "\n";                   
                            print "state : " .  $content_piece->attributes()->state . "\n";
                            echo "Yay, found an article\n";
                            
                            foreach ($content_piece->field as $article_field)
                                {
                                    # the_content
                                    if ($article_field->attributes()->name == 'headline')
                                    {
                                        $postobj['post_title'] = replace_newline($article_field[0]);
                                    }

                                    if ($article_field->attributes()->name == 'body')
                                        {
                                            $thetext = '';
                                            foreach ($article_field->p as $line)
                                                {
                                                    if (gettype($line) == 'object')
                                                        {
                                                            # the original web article + xml got paragraphs, so we are adding them aswell                                                   
                                                            $thetext .= "<p>$line</p>\n";
                                                        }
                                                        else
                                                        {
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
                                                
                                                    # the excerpt got some bold words in it, make sure to fidn hose
                                                    if ($keys = array_keys(get_object_vars($line)))
                                                        {
                                                            foreach ($keys as $key)
                                                                {
                                                                    $post_excerpt .= "<$key>". $line->$key. "</$key>\n";                                                                    
                                                                }
                                                        }
                                                    # the original web article + xml got paragraphs, so we are adding them aswell                                                                                                           
                                                    $post_excerpt .= "<p>$line</p>";
                                                }
                                           $postobj['post_excerpt'] = $post_excerpt ;
                                        }
                                                                
                                }                                

                            print_r($postobj);      
                            $sourceid = (string)$content_piece->attributes()->sourceid;
                            
                            # skip it if the sourceid contains '-orig' (to avoid dupes, we only want the version without -orig suffix
                            $pos = strpos($sourceid, '-orig');
                            if ($pos === false) 
                            {        
                                    if ($oldie = find_tagged_post('sourceid',$sourceid))
                                        {
                                            echo "Found an old post with the sourceid $sourceid, updating instead of inserting\n";
                                            $postobj['ID'] = $oldie;
                                        }                                                                    
                                    print "sourceid : $sourceid\n";                           
                                    $newid =   wp_insert_post( $postobj );                                
                                    update_post_meta($newid, 'sourceid', $sourceid); 
                                    update_post_meta($newid, 'ingress',$postobj['post_excerpt'] );                 
                                    echo "inserted as $newid \n";
                            }
                            else
                            {
                                echo "Just a old copy, skipping $sourceid ... \n";
                            }
                        }
    
            }
    } 
        else 
    {
        exit("Could not find $escenic_source.");
    }


?>