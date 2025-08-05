locals {
  somervilleyimby_org_zone_id      = "fa18b85393d895bc0d57553a619aa635"
  somervilleyimby_discourse_domain = "discourse.${var.yimby_domain}"
}

resource "cloudflare_zone" "somervilleyimby_org" {
  account = {
    id = var.somervilleyimby_cloudflare_account_id
  }
  name = var.yimby_domain
}


# Actual .org records
resource "cloudflare_dns_record" "somervilleyimby" {
  for_each = toset([
    "185.199.108.153",
    "185.199.109.153",
    "185.199.110.153",
    "185.199.111.153",
  ])
  zone_id = local.somervilleyimby_org_zone_id
  name    = "somervilleyimby.org"
  type    = "A"
  content = each.key
  proxied = false
  ttl     = 1
}

resource "cloudflare_dns_record" "somervilleyimby_aaaa" {
  for_each = toset([
    "2606:50c0:8000::153",
    "2606:50c0:8001::153",
    "2606:50c0:8002::153",
    "2606:50c0:8003::153",
  ])
  zone_id = local.somervilleyimby_org_zone_id
  name    = "somervilleyimby.org"
  type    = "AAAA"
  content = each.key
  proxied = false
  ttl     = 1
}

resource "cloudflare_dns_record" "somervilleyimby_www" {
  zone_id = local.somervilleyimby_org_zone_id
  name    = "www.${var.yimby_domain}"
  type    = "CNAME"
  content = var.yimby_domain
  proxied = false
  ttl     = 1
}

resource "cloudflare_dns_record" "somervilleyimby_discourse" {
  zone_id = local.somervilleyimby_org_zone_id
  name    = local.somervilleyimby_discourse_domain
  type    = "A"
  # TODO: Switch this in after new droplet is up
  # content   = digitalocean_floating_ip.somervilleyimby_org.ip_address
  content = "167.99.21.174"
  proxied = true
  ttl     = 1
}

resource "cloudflare_dns_record" "somervilleyimby_txt" {
  for_each = toset([
    "\"v=spf1 include:_spf.google.com include:servers.mcsv.net ~all\"",
    "\"google-site-verification=whz05HG8yZds-eTDyeTY9w91ZJHO6IIHzh_-dTKCPug\"",
  ])

  zone_id = local.somervilleyimby_org_zone_id
  name    = var.yimby_domain
  type    = "TXT"
  content = each.key
  ttl     = 1
}

resource "cloudflare_dns_record" "somervilleyimby_txt_dkim" {
  zone_id = local.somervilleyimby_org_zone_id
  name    = "google._domainkey.${var.yimby_domain}"
  type    = "TXT"
  content = "\"v=DKIM1; k=rsa; p=MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAxh+aGY73ZWqnR32wrEB5U4Frc+ehQwarxEoYunxY24/s+d5T8CRtnfT23RFqAJxyiHU/k8HYQQOLTcdbTKvbDYnx28LztxlxfNVXWDYu2VQu6JlyZ2bygUbQ4rM6+zhSk9aIrrYvHHRXqUKV5HLw29Q/NHD+kK4eOkVEV6f4GRtCwmt+IlGizkYBReasQPS1P\" \"JeyTAviVWgDwzLocujYQ53Gpxjktu6f2ynNEFUBeQNQVVf6DYd/Dq4JFHXo1t728ykkSXdtkHp+vH2LJB7BZohETIkfhjnlPGR1RAFt6Qpt1pOcs6whMRl1u6C/nIcHzeTqqNT0wei/W60uZxiiHwIDAQAB\""
  proxied = false
  ttl     = 1
}

# Mailchimp DKIM record
resource "cloudflare_dns_record" "somervilleyimby_cname_dkim" {
  zone_id = local.somervilleyimby_org_zone_id
  name    = "k1._domainkey.${var.yimby_domain}"
  type    = "CNAME"
  content = "dkim.mcsv.net"
  proxied = false
  ttl     = 1
}

resource "mailgun_domain" "somervilleyimby_discourse" {
  name        = local.somervilleyimby_discourse_domain
  region      = "us"
  spam_action = "tag"
}

resource "cloudflare_dns_record" "somervilleyimby_discourse_mail_receiving" {
  for_each = {
    for record in mailgun_domain.somervilleyimby_discourse.receiving_records_set : record.id => {
      type     = record.record_type
      value  = record.value
      priority = record.priority
    }
  }

  zone_id  = local.somervilleyimby_org_zone_id
  name     = local.somervilleyimby_discourse_domain
  type     = each.value.type
  content  = each.value.value
  priority = each.value.priority
  proxied  = false
  ttl      = 1
}

resource "cloudflare_dns_record" "somervilleyimby_discourse_mail_sending" {
  for_each = {
    for record in mailgun_domain.somervilleyimby_discourse.sending_records_set : record.id => {
      name    = record.name
      type    = record.record_type
      value = record.value
    }
  }

  zone_id = local.somervilleyimby_org_zone_id
  name    = each.value.name
  type    = each.value.type
  content = each.value.value
  proxied = false
  ttl     = 1
}

resource "cloudflare_dns_record" "somervilleyimby_google_verification" {
  zone_id  = local.somervilleyimby_org_zone_id
  name     = var.yimby_domain
  type     = "MX"
  ttl      = "1"
  content  = "5trsvb4og72xhf5hnnloly6eanedadtmokrc3az3ne6e5closlqa.mx-verification.google.com"
  priority = "15"
  proxied  = false
}

resource "cloudflare_dns_record" "somervilleyimby_mail_google_0" {
  zone_id  = local.somervilleyimby_org_zone_id
  name     = var.yimby_domain
  type     = "MX"
  ttl      = "1"
  content  = "aspmx.l.google.com"
  priority = "1"
  proxied  = false
}

resource "cloudflare_dns_record" "somervilleyimby_mail_google_1" {
  zone_id  = local.somervilleyimby_org_zone_id
  name     = var.yimby_domain
  type     = "MX"
  ttl      = "1"
  content  = "alt1.aspmx.l.google.com"
  priority = "5"
  proxied  = false
}

resource "cloudflare_dns_record" "somervilleyimby_mail_google_2" {
  zone_id  = local.somervilleyimby_org_zone_id
  name     = var.yimby_domain
  type     = "MX"
  ttl      = "1"
  content  = "alt2.aspmx.l.google.com"
  priority = "5"
  proxied  = false
}

resource "cloudflare_dns_record" "somervilleyimby_mail_google_3" {
  zone_id  = local.somervilleyimby_org_zone_id
  name     = var.yimby_domain
  type     = "MX"
  ttl      = "1"
  content  = "alt3.aspmx.l.google.com"
  priority = "10"
  proxied  = false
}

resource "cloudflare_dns_record" "somervilleyimby_mail_google_4" {
  zone_id  = local.somervilleyimby_org_zone_id
  name     = var.yimby_domain
  type     = "MX"
  ttl      = "1"
  content  = "alt4.aspmx.l.google.com"
  priority = "10"
  proxied  = false
}

resource "cloudflare_dns_record" "somervilleyimby_mail_google_cname" {
  zone_id = local.somervilleyimby_org_zone_id
  name    = "mail.${var.yimby_domain}"
  type    = "CNAME"
  content = "ghs.googlehosted.com"
  proxied = false
  ttl     = 1
}

resource "cloudflare_dns_record" "somervilleyimby_calendar_google_cname" {
  zone_id = local.somervilleyimby_org_zone_id
  name    = "calendar.${var.yimby_domain}"
  type    = "CNAME"
  content = "ghs.googlehosted.com"
  proxied = false
  ttl     = 1
}

resource "cloudflare_dns_record" "somervilleyimby_drive_google_cname" {
  zone_id = local.somervilleyimby_org_zone_id
  name    = "drive.${var.yimby_domain}"
  type    = "CNAME"
  content = "ghs.googlehosted.com"
  proxied = false
  ttl     = 1
}
