## Alfred GetGlue Helper

**Version: 1.0.3**

*Note: you can find the single extension file on the downloads page. <https://github.com/MikeMcMullan/Alfred-GetGlue-Helper/downloads>* 

An [Alfred App](http://alfredapp.com/) extension to quickly launch a GetGlue search in your default web browser. You will also need the [Powerpack](http://alfredapp.com/powerpack/) to use this. By default this extension uses the keyword 'getglue'. The search syntax is as follows.

	getglue search terms

As well as search the user can also define aliases to launch directly into a shows, movies, etc GetGlue page. The syntax to create an alias is as follows.

	getglue alias name url

	Example:
	getglue alias fringe http://getglue.com/tv_shows/fringe

The alias name may only contain alphanumeric characters, dashes underscores and spaces. The url must be a valid up including the http://. To access the alias simply type in the alias name as a search query. 

	getglue alias_name

	Example:
	getglue fringe

If you create an alias with the same name as a previously created alias the previous alias will be overwritten.

This extension requires php and has only been tested in OSX Lion which comes bundled with version 5.3.6.