# config valid for current version and patch releases of Capistrano
lock '~> 3.10'

set :application, 'somervilleyimby.org'
set :repo_url, 'git@github.com:jeffbyrnes/somervilleyimby.org.git'
set :branch, 'main'

server 'do-jb', user: 'deploy', roles: %w[web app]

before 'deploy:finished', 'sass:default'
