# frozen_string_literal: true

# Load DSL and Setup Up Stages
require 'capistrano/setup'

# Includes default deployment tasks
require 'capistrano/deploy'

# Load the SCM plugin appropriate to your project:
require 'capistrano/scm/git'
install_plugin Capistrano::SCM::Git

# Use deploy-tagger to tag releases
require 'cap-deploy-tagger'

# Loads custom tasks from `lib/capistrano/tasks` if you have any defined.
Dir.glob('lib/capistrano/tasks/*.rake').each { |r| import r }
