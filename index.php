<?php

    /*
        Command formats:
        
        getglue sg1
        getglue alias sg1 http://getglue.com/tv_shows/stargate_sg_1
        getglue generate
    */
    
    // Turn off error reporting.
    error_reporting(0);
    
    // If debug is true url will be echo'd instead of opened.
    $debug = false;

    // The absolute location of the getglue terminal app. Not yet available.
    $getglue_terminal_location = '~/getglue';
    
    // Get the query from the command line.
    $query_string = trim(stripcslashes($argv[1]));
    
    // Convert the command into an array and remove any empty values.
    $query = array_filter(explode(' ', $query_string));
    
    // All available commands
    $commands = array('search', 's', 'alias', 'a', 'generate', 'g', 'checkin', 'c');
    
    // Check if the command is in the commands array. If not set the command to be search.
    // Also remove the command from the query.
    if(in_array($query[0], $commands)):
       $command = $query[0];
       array_shift($query);
    else:
        $command = $commands[0];
    endif;
    
    // Recreate the query string without the command.
    $query_string = implode(' ', $query);
        
    // If the command is search or s for short then preform a getglue search.
    if($command === 'search' || $command === 's'):

        $alias = md5($query_string);
        
        // Search for the alias in the aliases folder.
        $files = glob("aliases/{$alias}*");
        
        if(!empty($files)):
        
            // Get the data and unserialize it.
            $get_alias = file_get_contents($files[0]);
            $alias_data = unserialize($get_alias);
            
            // Add a visit to the number of visits.
            if(isset($alias_data['number_of_visits']))
                $alias_data['number_of_visits'] += 1;
            else
                $alias_data['number_of_visits'] = 1;
            
            // Write the new data to the original file.
            file_put_contents($files[0], serialize($alias_data));

            // If is real url then set as url else set as search query
            if(filter_var($alias_data['data'], FILTER_VALIDATE_URL))
                $url = $alias_data['data'];
            else
                $url = "http://getglue.com/search?q=" . urlencode($alias_data['data']);
                
        else:
        
            $url = "http://getglue.com/search?q=" . urlencode($query_string);
            
        endif;
        
        open($url);
            
    // If the command is alias or a for short create an alias.
    elseif($command === 'alias' || $command === 'a'):
    
        $value = end($query);
        array_pop($query);
        $key = implode(' ', $query);
        
        // If both variables are valid write the alias file.
        if($key && $value):
        
            $filename = md5($key);
            $contents = serialize(array('data' => $value, 'alias' => $key, 'number_of_visits' => 0));
            
            file_put_contents("aliases/{$filename}.txt", $contents);
            
            echo "Alias Create: {$key}\n";
            echo "{$value}";
        
        else:
        
            echo "Error: Missing Name Or Value";
        
        endif;
    
    // If command is generate or g for short create an html page with all created aliases.
    elseif($command === 'generate' || $command === 'g'):
        
        // Find all the alias files.
        $files = glob("aliases/*");
        
        $template = file_get_contents('template.html');
        
        // Find the table row content from inside the template.
        preg_match('/\{{content}}(.*){{\/content}}/s', $template, $row_template);
        $row_template = $row_template[1];
        
        // Get the contents of the file and unserialize it and format the html output.
        $map = function($file) use($row_template)
        {
            $content = ($content = file_get_contents($file)) ? unserialize($content) : false;
            
            if($content)
            {
                $search = array('/{{alias}}/', '/{{data}}/', '/{{number_of_visits}}/', '/{{alias_file_url}}/', '/{{alias_file_name}}/');
                $replace = array($content['alias'], $content['data'], (int) $content['number_of_visits'], dirname(__FILE__) . '/' . $file, $file);
                
                return preg_replace($search, $replace, $row_template);
            }
        };
        
        $aliases = array_map($map, $files);
                        
        // Replace all template tags with corresponding content
        $search = array('/{{title}}/', '/\{{content}}(.*){{\/content}}/s', '/{{num_aliases}}/');
        $replace = array('GetGlue Stored Aliases', implode('', $aliases), count($aliases));
        
        $template = preg_replace($search, $replace, $template);
        
        $file = sys_get_temp_dir() . '/getglue_aliases.html';
        
        // Put the newly created template into an html file.
        file_put_contents($file, $template);
        
        $url = $file;
        
        open($url);
    
    // Will integrate with Glue Terminal to allow checkins from alfed.
    elseif($command === 'checkin' || $command === 'c'):
    
        // The alias name and comment are separate by a dash. Find the dash and save the
        // array position.
        if($sep_position = array_search('-', $query)):
            
            // Separate the alias name and the comment. We will use the stored position of
            // the dash to know where the comment begins. Anything before the dash will
            // be assumed as the alias name. Then reassenble back into a string.
            $alias = implode(' ', array_slice($query, 0, $sep_position));
            $comment = implode(' ', array_slice($query, $sep_position+1));
            
        else:
        
            // Since there is no comment just reassemble the query back into a string as
            // the alias name.
            $alias = implode(' ', $query);
        
        endif;
        
        $alias_filename = md5($alias);
        
        // Find the alias file.
        $files = glob("aliases/{$alias_filename}*");
        
        // If the file is found then proceed with checkin, else error out.
        if(!empty($files)):
        
            $get_alias = file_get_contents($files[0]);
            $alias_data = unserialize($get_alias);

            // Add a visit to the number of visits.
            if(isset($alias_data['number_of_visits']))
                $alias_data['number_of_visits'] += 1;
            else
                $alias_data['number_of_visits'] = 1;
            
            // Write the new data to the original file.
            file_put_contents($files[0], serialize($alias_data));
            
            $getglue_data = array(
                'objectId' => $alias_data['data'],
                'comment' => (isset($comment) && trim($comment) != '') ? $comment : ''
            );
                                    
            $getglue_data = base64_encode(serialize($getglue_data));
            
            $getglue_path = escapeshellarg($getglue_terminal_location);
            
            echo shell_exec("php {$getglue_path} checkin-direct {$getglue_data}");
        
        else:
        
            echo 'Could not find alias by that name.';
        
        endif;
        
    endif;
    
    function open($url)
    {
        global $debug;
        
        if($debug === true)
            echo $url;
        else
            `open {$url}`;
    }