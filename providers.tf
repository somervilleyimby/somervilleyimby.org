provider "aws" {
  region = "us-east-1"
}

provider "digitalocean" {

}

provider "cloudflare" {

}

provider "cloudinit" {

}

provider "datadog" {

}

provider "mailgun" {
  api_key = var.mailgun_api_key
}
