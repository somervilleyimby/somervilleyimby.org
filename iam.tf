resource "aws_iam_policy" "Somerville_YIMBY_S3" {
  name   = "Somerville_YIMBY_S3"
  policy = data.aws_iam_policy_document.Somerville_YIMBY_S3.json
}

data "aws_iam_policy_document" "Somerville_YIMBY_S3" {
  statement {
    actions = [
      "s3:AbortMultipartUpload",
      "s3:CreateBucket",
      "s3:DeleteObject",
      "s3:Get*",
      "s3:List*",
      "s3:PutBucketCORS",
      "s3:PutLifecycleConfiguration",
      "s3:PutObject",
      "s3:PutObjectAcl",
      "s3:PutObjectVersionAcl",
    ]

    resources = [
      aws_s3_bucket.somerville_yimby.arn,
      "${aws_s3_bucket.somerville_yimby.arn}/*",
    ]
  }

  statement {
    actions = [
      "s3:HeadBucket",
      "s3:ListAllMyBuckets",
    ]

    resources = [
      "*"
    ]
  }
}

resource "aws_iam_user" "backup" {
  name = "backup"
}

resource "aws_iam_user_policy_attachment" "Somerville_YIMBY_S3" {
  user       = aws_iam_user.backup.name
  policy_arn = aws_iam_policy.Somerville_YIMBY_S3.arn
}
