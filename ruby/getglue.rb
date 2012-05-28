APP_ROOT = File.dirname(__FILE__)
$:.unshift(File.join(APP_ROOT, 'lib'))

require 'base64'
require 'erb'
require 'uri'
require 'digest'
require 'PHPSerialize'
require 'Aliases'
require 'CommandInterface'

Aliases.aliases_path = File.join APP_ROOT, '..', 'aliases'

cmd = CommandInterface.new(ARGV)