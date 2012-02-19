## Alfred GetGlue Helper

**Version: 1.0.1**

An alfred extension to quickly launch a search on GetGlue in your default web browser. By default the extension uses the keyword 'getglue'. The search syntax is as follows.

	getglue search terms

As well as search the user can also define aliases to launch directly launch into a shows, movies, etc GetGlue page. The syntax to create an alias is as follows.

	getglue alias name url/term

	Example:
	getglue alias fringe http://getglue.com/tv_shows/fringe
	getglue alias bluebloods Blue Bloods

The alias name can only contain alphanumeric characters, the url/term may contain a valid url or search terms. To access the alias simply type in the alias name as a search query.  If you have created an alias with the same name as a previously created alias the previous alias will be overwritten.

This extension requires php and has only been tested in OSX Lion which comes bundled with version 5.3.6.