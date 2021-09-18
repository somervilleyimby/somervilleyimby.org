# frozen_string_literal: true

set :deploy_to, "/var/www/dev.#{fetch(:application)}"
