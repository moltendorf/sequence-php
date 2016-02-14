/**
 * Document    : analytics.js
 * Created on  : ‎January ‎30, ‎2016, ‏‎03:37:45
 * Author      : Matthew Oltendorf (matthew@oltendorf.net)
 * Description : Typekit initialize script.
 */

(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
})(window,document,'script','//www.google-analytics.com/analytics.js','ga');

ga('create', '<?= $s['analytics_tracking_id'] ?>', <?= $s['analytics_tracking_settings'] ?? "'auto'" ?>);
ga('require', 'linkid');
ga('send', 'pageview');
