<?php
$title = "Calendar";
$body_class = "calendar";
require_once "../includes/header.php";
?>
    <section class="main">
      <h2>Calendar of Public Events & Meetings</h2>

      <p>This calendar is a collection of city & neighborhood meetings, and other events, relevant to advocating for smart growth in Somerville & the Boston metro region. It is not exhaustive, so if you have an event you’d like added, please reach out to <a href="mailto:steering+addtocalendar@somervilleyimby.org">steering@somervilleyimby.org</a></p>

      <div class="google-calendar">
        <iframe src="https://calendar.google.com/calendar/embed?src=somervilleyimby.org_8duipmcoo11uvtvchrj1gqp0n4%40group.calendar.google.com&ctz=America%2FNew_York" style="border: 0" width="800" height="600" frameborder="0" scrolling="no"></iframe>
      </div>

      <hr>
    </section>
<?php
require_once "../includes/mailing-list.php";
require_once "../includes/footer.php";
