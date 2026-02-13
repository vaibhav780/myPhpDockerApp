<!DOCTYPE html>
<html>
<body>
    <h3>Shadow DOM</h3>
    <div id="shadow-host"></div>
    <script>
        const host = document.querySelector('#shadow-host');
        const root = host.attachShadow({mode: 'open'});
        root.innerHTML = '<p id="shadow-text">I am inside the Shadow DOM</p>';
    </script>
</body>
</html>