# Somerville YIMBY

Somerville YIMBY’s website is powered by these technologies:

* Digital Ocean droplet
* Terraform for provisioning
* NGINX for web server & reverse proxy
* Certbot for automatically issuing HTTPS certificates
* [Discourse](https://discourse.org) as a web forum & email listserv at [discourse.somervilleyimby.org](https://discourse.somervilleyimby.org)
* WordPress for the brochure website & blog at [somervilleyimby.org](https://somervilleyimby.org)

## Setup

### Prerequisites

One needs these Environment Variables set in order to run Terraform to create and configure a Digital Ocean droplet to serve the sites:

```bash
TF_VAR_cloudflare_account_id="REDACTED"
TF_VAR_somervilleyimby_cloudflare_account_id="REDACTED"
TF_VAR_mailgun_api_key="REDACTED"
TF_VAR_mailgun_somervilleyimby_smtp_password="REDACTED"
TF_VAR_dd_api_key="REDACTED"
TF_VAR_maxminddb_license_key="REDACTED"
```

These Env Vars are only used when running Terraform.

Once the server is live, one needs to manually set up NGINX and Certbot:

1. Add NGINX package repo & install NGINX:

    ```bash
    $ sudo apt install curl gnupg2 ca-certificates lsb-release ubuntu-keyring
    $ curl https://nginx.org/keys/nginx_signing.key | gpg --dearmor \
    | sudo tee /usr/share/keyrings/nginx-archive-keyring.gpg >/dev/null
    # Fingerprint shown in this output should match 573BFD6B3D8FBC641079A6ABABF5BD827BD9BF62
    $ gpg --dry-run --quiet --no-keyring --import --import-options import-show /usr/share/keyrings/nginx-archive-keyring.gpg
    $ echo "deb [signed-by=/usr/share/keyrings/nginx-archive-keyring.gpg] \
    <http://nginx.org/packages/ubuntu> `lsb_release -cs` nginx" \
        | sudo tee /etc/apt/sources.list.d/nginx.list
    $ echo -e "Package: *\nPin: origin nginx.org\nPin: release o=nginx\nPin-Priority: 900\n" \
    | sudo tee /etc/apt/preferences.d/99nginx
    $ sudo apt update && sudo apt install nginx
    ```

2. Install Certbot: `sudo snap install --classic certbot`
3. Add NGINX configs to allow Certbot to issue certificates (port 80 w/ a specific URI needs to be reachable externally):

    ```bash
    # Copy NGINX configs to host
    $ scp ./etc/nginx/sites-available/* somervilleyimby:/etc/nginx/sites-available/
    # SSH to the host
    $ ssh somervilleyimby
    # Remove the default NGINX site
    $ sudo rm /etc/nginx/sites-enabled/default
    # Symlink the available configs to be active sites
    $ sudo ln -s /etc/nginx/sites-available/00-discourse /etc/nginx/sites-enabled/00-discourse
    $ sudo ln -s /etc/nginx/sites-available/01-wordpress /etc/nginx/sites-enabled/01-wordpress
    # Reload NGINX’s config
    $ sudo systemctl reload nginx
    ```

4. Use Certbot to issue & set up HTTPS certificates:

    ```bash
    # Use the NGINX plugin to find, via `server_name`, each domain that needs an HTTPS certificate, issue one, place it on disk for use, and adjust the NGINX configs
    # Do not pick a specific domain, instead, as the instructions say, “leave input blank to select all options shown”
    $ sudo certbot --nginx
    # Once successful, Certbot will set up a systemd timer, `snap.certbot.renew.timer`, to automatically renew these certificates
    ```

5. Double-check that HTTPS certificate renewals work:

    ```bash
    $ sudo certbot renew --dry-run
    Saving debug log to /var/log/letsencrypt/letsencrypt.log

    - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
    Processing /etc/letsencrypt/renewal/discourse.somervilleyimby.org.conf
    - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
    Simulating renewal of an existing certificate for somervilleyimby.org and 2 more domains

    - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
    Congratulations, all simulated renewals succeeded:
    /etc/letsencrypt/live/discourse.somervilleyimby.org/fullchain.pem (success)
    * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * -
    ```

6. Reload NGINX, and check its logs to ensure it is healthy:

    ```bash
    $ sudo systemctl reload nginx
    $ sudo journalctl -u nginx
    Jul 26 21:15:59 somervilleyimby systemd[1]: Stopped nginx.service - nginx - high performance web server.
    Jul 26 21:15:59 somervilleyimby systemd[1]: Starting nginx.service - nginx - high performance web server...
    Jul 26 21:15:59 somervilleyimby systemd[1]: Started nginx.service - nginx - high performance web server.
    # control-C to exit from viewing the journal logs
    ```

7. Visit [somervilleyimby.org](https://somervilleyimby.org) and [discourse.somervilleyimby.org](https://somervilleyimby.org) to ensure they are working well for a visitor.
