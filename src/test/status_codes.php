<ul>
    <li><a href="status_codes.php?code=200">200</a></li>
    <li><a href="status_codes.php?code=404">404</a></li>
    <li><a href="status_codes.php?code=500">500</a></li>
</ul>
<?php if(isset($_GET['code'])) http_response_code($_GET['code']); ?>