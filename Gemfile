# frozen_string_literal: true

source 'https://rubygems.org'
# Hello! This is where you manage which Jekyll version is used to run.
# When you want to use a different version, change it below, save the
# file and run `bundle install`. Run Jekyll with `bundle exec`, like so:
#
#     bundle exec jekyll serve
#
# If you have any plugins, put them here!
group :jekyll_plugins do
  gem 'github-pages'
  gem 'jekyll-archives', '~> 2.2'
  gem 'jekyll-feed', '~> 0.12'
end

# Windows and JRuby does not include zoneinfo files, so bundle the tzinfo-data gem
# and associated library.
platforms :mingw, :x64_mingw, :mswin, :jruby do
  gem 'tzinfo', '~> 1.2'
  gem 'tzinfo-data'
end

# Performance-booster for watching directories on Windows
gem 'wdm', '~> 0.1.1', platforms: %i[mingw x64_mingw mswin]

gem 'rubocop'

gem 'faraday-retry'

# Need to add this explicitly on Ruby >= 3.0.0
gem 'webrick', '~> 1.7'

# Need to add this explicitly on Ruby >= 3.4.0
gem 'csv'

# Need to add this explicitly on Ruby >= 3.5.0
gem 'logger'
