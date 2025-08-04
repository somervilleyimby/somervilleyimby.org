resource "aws_s3_bucket" "somerville_yimby" {
  bucket = "somerville-yimby"
}

resource "aws_s3_bucket_ownership_controls" "somerville_yimby" {
  bucket = aws_s3_bucket.somerville_yimby.id
  rule {
    object_ownership = "BucketOwnerPreferred"
  }
}

resource "aws_s3_bucket_public_access_block" "somerville_yimby" {
  bucket = aws_s3_bucket.somerville_yimby.id

  block_public_acls       = false
  block_public_policy     = false
  ignore_public_acls      = false
  restrict_public_buckets = false
}
resource "aws_s3_bucket_acl" "somerville_yimby" {
  depends_on = [
    aws_s3_bucket_ownership_controls.somerville_yimby,
    aws_s3_bucket_public_access_block.somerville_yimby,
  ]

  bucket = aws_s3_bucket.somerville_yimby.id
  acl    = "public-read"
}

resource "aws_s3_bucket_lifecycle_configuration" "somerville_yimby" {
  bucket = aws_s3_bucket.somerville_yimby.id

  rule {
    id     = "purge_tombstone"
    status = "Enabled"

    filter {
      prefix = "tombstone/"
    }

    expiration {
      days = 30
    }
  }

  rule {
    id     = "purge_aborted_multipart_uploads"
    status = "Enabled"

    abort_incomplete_multipart_upload {
      days_after_initiation = 7
    }

    filter {
      prefix = ""
    }
  }
}

resource "aws_s3_bucket_metric" "somerville_yimby" {
  bucket = aws_s3_bucket.somerville_yimby.bucket
  name   = "EntireBucket"
}

data "aws_iam_policy_document" "somerville_yimby_public_access" {
  statement {
    actions = [
      "s3:GetObject",
    ]

    resources = [
      "${aws_s3_bucket.somerville_yimby.arn}/*",
    ]

    principals {
      type = "*"

      identifiers = [
        "*",
      ]
    }
  }
}

resource "aws_s3_bucket_policy" "somerville_yimby_public_access" {
  bucket = aws_s3_bucket.somerville_yimby.id
  policy = data.aws_iam_policy_document.somerville_yimby_public_access.json
}
