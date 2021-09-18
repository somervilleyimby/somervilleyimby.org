# frozen_string_literal: true

namespace :sass do
  task :default do
    invoke 'sass:build'
    invoke 'sass:upload'
    invoke 'sass:asset_rev'
    invoke 'sass:restart_php'
  end

  desc 'Build the app for release'
  task :build do
    run_locally do
      execute :sass, '--force', '--update', 'sass:css'
      set :sass_timestamp, Time.new.strftime('%Y%m%d%H%M')
    end
  end

  desc 'Upload the built assets'
  task :upload do
    on roles :web do
      execute :mkdir, "#{release_path}/css"

      %w[styles].each do |file|
        info "Uploading #{release_path}/css/#{file}.css"
        upload!(
          "./css/#{file}.css",
          "#{release_path}/css/#{file}.#{fetch(:sass_timestamp)}.css"
        )

        upload!(
          "./css/#{file}.css.map",
          "#{release_path}/css/#{file}.#{fetch(:sass_timestamp)}.css.map"
        )
      end
    end
  end

  desc 'Rev the timestamp'
  task :asset_rev do
    on roles :web do
      within release_path do
        execute(
          :sed,
          '-i',
          '-e',
          "s/styles.css/styles.#{fetch(:sass_timestamp)}.css/",
          'includes/header.php'
        )
      end
    end
  end

  desc 'Restart PHP-FPM to pick up new changes'
  task :restart_php do
    on roles :web do
      execute(
        :sudo,
        :systemctl,
        :restart,
        'php7.2-fpm.service'
      )
    end
  end
end
