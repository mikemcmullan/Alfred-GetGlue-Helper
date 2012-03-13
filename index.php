<?php

	/*
		Command formats:
		
		getglue sg1
		getglue alias sg1 http://getglue.com/tv_shows/stargate_sg_1
	*/

	$query = trim(stripcslashes($argv[1]));
	
	$commands = array('alias', 'a', 'generate', 'g');
	
	// Get the command from arguments
	$command = explode(' ', $query);
	$command = $command[0];
	
	//file_put_contents('aliases/84c7b89607867094a77daaeeac94fca9', serialize(array('data' => 'http://getglue.com/tv_shows/stargate_sg_1', 'alias' => 'sg1')));
	
	// Check to see if the query contains a command. If not check to see if it is an alias.
	// If not open browser and do a search on getglue.
	if(!in_array($command, $commands)):
		
		$alias = md5($query);
		
		$search = glob("aliases/{$alias}*");
		
		if(!empty($search)):
		
			// Get the data and unserialize it.
			$get_alias = file_get_contents($search[0]);
			$alias_data = unserialize($get_alias);
						
			// If is real url then set as url else set as search query
			if(filter_var($alias_data['data'], FILTER_VALIDATE_URL))
				$url = $alias_data['data'];
			else
				$url = "http://getglue.com/search?q=" . urlencode($alias_data['data']);
				
		else:
		
			$url = "http://getglue.com/search?q=" . urlencode($query);
			
		endif;
		
		`open {$url}`;
		//echo "Opening $url" . PHP_EOL;
		
		die();

	endif;
	
	// Format the parameters correctly.
	$params = trim(preg_replace("/{$command}/", '', $query));
	$params = explode(' ', $params);
	
	// Remove empty value from array.
	foreach($params as $key => $param):
	
		if($param == '')
			unset($params[$key]);
			
	endforeach;
		
	// If the command is alias	
	if($command === "alias" || $command === 'a'):
	
        $value = end($params);
        array_pop($params);
        $key = implode(' ', $params);
		
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
			
		die();
		
	elseif($command === 'generate' || $command === 'g'):
			
		$files = glob("aliases/*");
		$template = file_get_contents('template.html');
		
		$map = function($file)
		{
			$content = ($content = file_get_contents($file)) ? unserialize($content) : false;
			
			if($content)
			{
				return "<tr><td>{$content['alias']}</td><td>{$content['data']}</td><td>{$file}</td></tr>";
			}
		};
		
		$aliases = array_map($map, $files);
		
		$search = array("/{{title}}/", "/{{content}}/");
		$replace = array('GetGlue Stored Aliases', implode('', $aliases));
		
		$template = preg_replace($search, $replace, $template);
		
		$file_name = 'generated_getglue_aliases_' . time() . '.html';
		
		file_put_contents('/tmp/' . $file_name, $template);
		
		$url = '/tmp/' . $file_name;
		
		`open {$url}`;
		
	endif;