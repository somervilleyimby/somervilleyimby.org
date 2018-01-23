# config valid for current version and patch releases of Capistrano
lock '~> 3.10.0'

set :application, 'somervilleyimby.org'
set :repo_url, 'git@github.com:jeffbyrnes/somervilleyimby.org.git'

server 'aws-jb', user: 'deploy', roles: %w(web app)

before 'deploy:finished', 'sass:default'
