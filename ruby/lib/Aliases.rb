class Aliases
    attr_accessor :alias

    def self.aliases_path= path
        @@aliases_path = path
    end

    def self.aliases_path
        @@aliases_path
    end

    def self.create_dir
        Dir.mkdir @@aliases_path unless File.directory? @@aliases_path
    end

    def self.alias_key key
        Digest::MD5.hexdigest key
    end

    def self.alias file_name, process = true
        file_name = Aliases.alias_key file_name if process 
        file = Dir.glob(File.join @@aliases_path, "#{file_name}*").shift
        return PHP.unserialize File.open(file, 'r').read unless file.nil?
    end

    def self.aliases
        files = Dir.glob File.join @@aliases_path, '*'
        return files.map do |file|
            self.alias(File.basename(file[0..-5]), false).merge({ 
                'alias_file_url' => File.expand_path(File.join(@@aliases_path, File.basename(file))), 
                'alias_file_name' => File.join(@@aliases_path, File.basename(file)) 
            })
        end
    end

    def self.update file_name
        file_name = File.join @@aliases_path, Aliases.alias_key(file_name)
        file      = Dir.glob("#{file_name}*").shift
        info      = PHP.unserialize File.open(file, 'r').read unless file.nil?

        if info 
            new_alias = Aliases.new({ 
                :data => info['data'], 
                :alias => info['alias'], 
                :number_of_visits => info['number_of_visits'] ? info['number_of_visits']+1 : 0 
            })
            new_alias.save
        end
    end

    def initialize args = {}
        @alias = args
    end

    def save
        file = File.join @@aliases_path, Aliases.alias_key(@alias[:alias])
        File.open "#{file}.txt", 'w' do |file|
            file.puts PHP.serialize(@alias)
        end
        @alias
    end
end