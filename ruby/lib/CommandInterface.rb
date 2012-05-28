class CommandInterface

    @@debug     = false
    @@comands   = ['search', 's', 'a', 'alias', 'c', 'checkin', 'g', 'generate']
    @@args      = []
    @@command   = ''
    @@baseUrl   = 'http://getglue.com/'
    @@searchUrl = 'http://getglue.com/search?q='
    @@getglue   = '/usr/local/bin/getglue'

    def initialize query
        Aliases.create_dir
        @query = query.shift
        analyze
    end

    def analyze
        @query = @query.split ' '
        @@command = @@comands.include?(@query[0]) ? @query.shift : 's'
        @@args = @query

        dispatch
    end

    def dispatch
        case @@command
        when 's', 'search'
            cmd_search
        when 'a', 'alias'
            cmd_alias
        when 'c', 'checkin'
            cmd_checkin
        when 'g', 'generate'
            cmd_generate
        end
    end

    def cmd_search
        alias_name  = @@args.join ' '
        info        = Aliases.alias alias_name

        Aliases.update alias_name if info

        if info
            open info['data']
        else
            open URI::encode @@searchUrl + @@args.join(' ')
        end
    end

    def cmd_alias
        return puts "Missing alias name or url." if @@args.length < 2
        url         = @@args.pop
        alias_value = @@args.join ' '
        return puts 'Missing valid url for alias.' unless url =~ URI::regexp

        new_alias = Aliases.new({ 
            :data => url, 
            :alias => alias_value, 
            :number_of_visits => 0 
        })
        
        if new_alias.save
            puts "Alias created: #{alias_value}"
            puts url
        end
    end

    def cmd_generate
        aliases     = Aliases.aliases
        title       = 'GetGlue Stored Aliases'

        template    = File.read File.join APP_ROOT, 'template.erb';
        erb_result  = ERB.new(template).result binding
        file_path   = File.join `echo $TMPDIR`.chomp, 'getglue_aliases.html'

        File.open file_path , 'w' do |file|
            file.puts erb_result
        end

        open file_path
    end

    def cmd_checkin
        if sep_index = @@args.index('-')
            alias_name  = @@args[0, sep_index].join ' '
            comment     = @@args[sep_index+1, @@args.length].join ' '
        else
            alias_name  = @@args.join ' '
            comment     = ''
        end

        info = Aliases.alias alias_name

        if info
            Aliases.update alias_name unless @@debug

            getglue_data = { :objectId => info['data'], :comment =>  comment }
            encoded_data = Base64.encode64(PHP.serialize getglue_data).gsub "\n", ''

            puts "Would have checked into #{info['alias']}" if @@debug
            puts `#{@@getglue} checkin-direct #{encoded_data}` unless @@debug
        else
            puts "Could not find the alias '#{alias_name}'."
        end
    end

    def open url           
        return puts url if @@debug
        `open #{url}`
    end

end