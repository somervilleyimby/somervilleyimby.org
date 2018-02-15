<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title><?php if ($title) { echo "${title} — "; } ?>Somerville YIMBY</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link rel="stylesheet" href="/css/styles.css">
  <script>
    (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
    (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
    m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
    })(window,document,'script','https://www.google-analytics.com/analytics.js','ga');

    ga('create', 'UA-96931256-1', 'auto');
    ga('send', 'pageview');

  </script>

  <script src="https://code.jquery.com/jquery-3.2.1.min.js" integrity="sha256-hwg4gsxgFZhOsEEamdOYGBf13FyQuiTwlAQgxVSNgt4=" crossorigin="anonymous"></script>

  <script>
    // Refills centered navigation
    // http://refills.bourbon.io/#er-toc-id-3
    $(window).on("load resize",function(e) {
      var more = document.getElementById("js-centered-more");

      if ($(more).length > 0) {
        var windowWidth = $(window).width();
        var moreLeftSideToPageLeftSide = $(more).offset().left;
        var moreLeftSideToPageRightSide = windowWidth - moreLeftSideToPageLeftSide;

        if (moreLeftSideToPageRightSide < 330) {
          $("#js-centered-more .submenu .submenu").removeClass("fly-out-right");
          $("#js-centered-more .submenu .submenu").addClass("fly-out-left");
        }

        if (moreLeftSideToPageRightSide > 330) {
          $("#js-centered-more .submenu .submenu").removeClass("fly-out-left");
          $("#js-centered-more .submenu .submenu").addClass("fly-out-right");
        }
      }

      var menuToggle = $("#js-centered-navigation-mobile-menu").unbind();
      $("#js-centered-navigation-menu").removeClass("show");

      menuToggle.on("click", function(e) {
        e.preventDefault();
        $("#js-centered-navigation-menu").slideToggle(function(){
          if($("#js-centered-navigation-menu").is(":hidden")) {
            $("#js-centered-navigation-menu").removeAttr("style");
          }
        });
      });
    });
  </script>
</head>
<body class="<?php echo $body_class; ?>">
  <header class="centered-navigation" role="banner">
    <div class="centered-navigation-wrapper">
      <a href="javascript:void(0)" class="mobile-logo">
        <img src="https://raw.githubusercontent.com/thoughtbot/refills/master/source/images/placeholder_square.png" alt="Logo image">
      </a>

      <a href="javascript:void(0)" id="js-centered-navigation-mobile-menu" class="centered-navigation-mobile-menu">MENU</a>

      <nav role="navigation">
        <ul id="js-centered-navigation-menu" class="centered-navigation-menu show">
          <li class="nav-link logo">
            <a href="/" class="logo">
              <img src="/img/entering-somerville-ma.png" alt="Logo image">
            </a>
          </li>
          <li class="nav-link"><a href="/platform/">Platform</a></li>
          <li class="nav-link"><a href="/calendar/">Calendar</a></li>
          <li class="nav-link"><a href="/who-are-we/">Who Are We</a></li>
          <!-- <li class="nav-link"><a href="javascript:void(0)">Contact</a></li> -->
          <!-- <li class="nav-link"><a href="javascript:void(0)">Testimonials</a></li> -->
          <!-- <li id="js-centered-more" class="nav-link more"><a href="javascript:void(0)">More</a>
            <ul class="submenu">
              <li><a href="javascript:void(0)">Submenu Item</a></li>
              <li><a href="javascript:void(0)">Another Item</a></li>
              <li class="more"><a href="javascript:void(0)">Item with submenu</a>
                <ul class="submenu fly-out-right">
                  <li><a href="javascript:void(0)">Sub-submenu Item</a></li>
                  <li><a href="javascript:void(0)">Another Item</a></li>
                </ul>
              </li>
              <li class="more"><a href="javascript:void(0)">Another submenu</a>
                <ul class="submenu fly-out-right">
                  <li><a href="javascript:void(0)">Sub-submenu</a></li>
                  <li><a href="javascript:void(0)">An Item</a></li>
                </ul>
              </li>
            </ul>
          </li> -->
          <!-- <li class="nav-link"><a href="/#mc_embed_signup wrapper">Sign up</a></li> -->
        </ul>
      </nav>
    </div>
  </header>

  <header class="hero">
    <div class="hero-content">
      <h1>Somerville YIMBY</h1>
      <p>A group of Somerville residents advocating for smart growth that benefits everyone.</p>
    </div>
  </header>

  <div class="wrapper">
