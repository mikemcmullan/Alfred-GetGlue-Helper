## Alfred GetGlue Helper

**Version: 1.0**

An alfred extension to quickly launch a search on GetGlue in your default web browser. By default the extension uses the keyword 'getglue'. As well as search the user can also created aliases to launch directly launch into a shows, movie, etc GetGlue page. The syntax to create an alias is as follows.

	get glue alias name url

	Example:
	getglue alias fringe http://getglue.com/tv_shows/fringe

The alias name can only container alphanumeric characters. To access the alias simply type in the alias name as a search query.  If you have created an alias with the same name as a previously created alias the previous alias will be overwritten.

This extension requires php and has only been tested in OSX Lion which comes bundled with version 5.3.6.