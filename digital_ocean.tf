resource "digitalocean_reserved_ip" "discourse" {
  droplet_id = digitalocean_droplet.somervilleyimby_org.id
  region     = digitalocean_droplet.somervilleyimby_org.region
}

resource "digitalocean_ssh_key" "jeffbyrnes" {
  name       = "2021-09-30 Jeff’s iMac id_ed25519"
  public_key = "ssh-ed25519 AAAAC3NzaC1lZDI1NTE5AAAAIO5mP8t/YHN79Yx+D8OoeE5lYi1gicP6J7L37wVn9KZk thejeffbyrnes@gmail.com"
}

data "cloudinit_config" "somervilleyimby" {
  gzip          = false
  base64_encode = false

  part {
    content = templatefile("${path.module}/app.yml.tmpl", {
      domain              = mailgun_domain.somervilleyimby_discourse.name,
      maxmind_license_key = var.maxminddb_license_key,
      smtp_user_name      = mailgun_domain.somervilleyimby_discourse.smtp_login,
      smtp_password       = var.mailgun_somervilleyimby_smtp_password,
    })
    filename = "/root/app.yml"
  }

  part {
    content  = "${path.module}/datadog-agent/conf.d/openmetrics.d/conf.yaml"
    filename = "/etc/datadog-agent/conf.d/openmetrics.d/conf.yaml"
  }

  part {
    content_type = "text/cloud-config"
    content = templatefile("${path.module}/user-data.yaml.tmpl", {
      dd_api_key = var.dd_api_key
    })
  }
}
resource "digitalocean_droplet" "somervilleyimby_org" {
  name       = var.yimby_domain
  image      = "ubuntu-24-04-x64"
  region     = "nyc3"
  size       = "s-2vcpu-4gb"
  monitoring = true
  ssh_keys   = [digitalocean_ssh_key.jeffbyrnes.fingerprint]

  connection {
    host  = self.ipv4_address
    agent = true
  }

  user_data = data.cloudinit_config.somervilleyimby.rendered

  lifecycle {
    ignore_changes = [
      user_data
    ]
  }
}

resource "digitalocean_monitor_alert" "somervilleyimby_org_mem_alert" {
  alerts {
    email = ["thejeffbyrnes@gmail.com"]
  }
  window  = "1h"
  type    = "v1/insights/droplet/memory_utilization_percent"
  compare = "GreaterThan"
  value   = 90
  enabled = true
  entities = [
    digitalocean_droplet.somervilleyimby_org.id,
  ]
  description = "Memory Utilization Percent is running high"
}

resource "datadog_monitor" "root_vol" {
  name    = "root vol will hit 100% in a week"
  type    = "query alert"
  message = "{{#is_alert}}root vol on {{host.name}} is forecast to fill up in a week{{/is_alert}}\n{{#is_recovery}}root vol space utilization on {{host.name}} is < 90% {{/is_recovery}} @thejeffbyrnes@gmail.com"

  query = "max(next_1w):forecast(max:system.disk.in_use{device:/dev/vda1 OR device:/dev/mapper/ubuntu--vg-ubuntu--lv} by {host}, 'linear', 1, model='default', interval='60m', history='1w') >= 1"

  monitor_thresholds {
    critical          = 1
    critical_recovery = 0.9
  }

  include_tags    = false
  new_group_delay = 300
}
