set :domain,      "beta.dreamkas.ru"
set :host,        "beta" unless exists?(:host)
set :branch,      "primary" unless exists?(:branch)

set (:application_folder) {"#{host}.#{app_end}"}
set (:application_url) {"http://#{host}.dreamkas.ru"}

server domain, :app, :web, :primary => true