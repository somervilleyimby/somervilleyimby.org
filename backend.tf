terraform {
  cloud {

    organization = "jeffbyrnes"

    workspaces {
      name = "production"
    }
  }

  required_providers {
    aws = {
      source  = "hashicorp/aws"
      version = "~> 6.0"
    }

    digitalocean = {
      source  = "digitalocean/digitalocean"
      version = "~> 2.3"
    }

    cloudflare = {
      source  = "cloudflare/cloudflare"
      version = "~> 5.0"
    }

    cloudinit = {
      source  = "hashicorp/cloudinit"
      version = "~> 2.3"
    }

    datadog = {
      source  = "datadog/datadog"
      version = "~> 3.6"
    }

    mailgun = {
      source  = "wgebis/mailgun"
      version = "~> 0.7"
    }
  }
}
