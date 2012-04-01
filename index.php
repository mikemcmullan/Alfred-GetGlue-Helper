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
    
    // Get the query from the command line.
    $query_string = trim(stripcslashes($argv[1]));
    
    // Convert the command into an array and remove any empty values.
    $query = array_filter(explode(' ', $query_string));
    
    // All available commands
    $commands = array('search', 's', 'alias', 'a', 'generate', 'g');
    
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
            $contents = serialize(array('data' => $value, 'alias' => $key));
            
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
        
        // Get the contents of the passed file and unserialize is and format the html output.
        $map = function($file)
        {
            $content = ($content = file_get_contents($file)) ? unserialize($content) : false;
            
            if($content)
            {
                return sprintf(
                '<tr>
                    <td class="alias"><strong>%1$s</strong</td>
                    <td><a href="%2$s" target="_blank">%2$s</a></td>
                    <td>%3$d</td>
                    <td><a href="%4$s" target="_blank">%5$s</a></td>
                </tr>
                ', $content['alias'], $content['data'], $content['number_of_visits'], dirname(__FILE__) . '/' . $file, $file);
            }
        };
        
        $aliases = array_map($map, $files);
                
        // Replace all template tags with corresponding content
        $search = array("/{{title}}/", "/{{content}}/");
        $replace = array('GetGlue Stored Aliases', implode('', $aliases));
        
        $template = preg_replace($search, $replace, $template);
        
        $file = sys_get_temp_dir() . '/getglue_aliases.html';
        
        // Put the newly created template into an html file.
        file_put_contents($file, $template);
        
        $url = $file;
        
        open($url);
        
    endif;
    
    function open($url)
    {
        global $debug;
        
        if($debug === true)
            echo $url;
        else
            `open {$url}`;
    }