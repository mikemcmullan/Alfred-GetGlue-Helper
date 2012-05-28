require 'base64'
require 'erb'
require 'uri'
require 'digest'
require 'PHPSerialize'

APP_ROOT = File.dirname(__FILE__)
require_relative '../lib/Aliases'
Aliases.aliases_path = File.dirname(__FILE__) + '/../aliases/'

describe Aliases do

	before :all do
		if File.directory? Aliases.aliases_path
			Dir.foreach(Aliases.aliases_path) do |f| 
				File.delete Aliases.aliases_path + f if f != '.' && f != '..'
			end
			Dir.delete Aliases.aliases_path
		end
	end

	it "creates aliases dir" do
		Aliases.create_dir
	end

	it "creates a new alias" do
		hash = { 
            :data => 'http://getglue.com/tv_shows/futurama', 
            :alias => 'futurama', 
            :number_of_visits => 5 
        }

		new_alias = Aliases.new(hash)
        new_alias.save.should eql hash
	end

	it "updates the number of visits for an alias." do
		Aliases.update('futurama').should be_instance_of(Hash)
	end

	it "retrieves an alias" do
		Aliases.alias('futurama').should be_instance_of(Hash)
	end

	it "returns all aliases" do
		Aliases.aliases.should be_instance_of(Array)
	end

end