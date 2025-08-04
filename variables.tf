variable "somervilleyimby_cloudflare_account_id" {
  description = "Account ID for Cloudflare for Somerville YIMBY"
  type        = string
  sensitive   = true
}

variable "dd_api_key" {
  description = "API key for Datadog"
  type        = string
  sensitive   = true
}

variable "yimby_domain" {
  default = "somervilleyimby.org"
}

# Supplied by env vars
variable "mailgun_api_key" {
  description = "API key for reading/writing to Mailgun"
  type        = string
  sensitive   = true
}

variable "mailgun_somervilleyimby_smtp_password" {
  description = "SMTP password for Somerville YIMBY"
  type        = string
  sensitive   = true
}

variable "maxminddb_license_key" {
  description = "API key for the MaxMind GeoIP DB"
  type        = string
  sensitive   = true
}
