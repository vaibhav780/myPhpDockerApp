<!DOCTYPE html>
<html>
<head>
    <title>Dynamic Controls</title>
    <script>
        function toggleCheckbox() {
            let cb = document.getElementById('checkbox');
            let msg = document.getElementById('message');
            document.getElementById('loading').style.display = 'block';
            setTimeout(() => {
                document.getElementById('loading').style.display = 'none';
                if (cb) {
                    cb.remove();
                    msg.innerHTML = "It's gone!";
                } else {
                    let newCb = document.createElement('input');
                    newCb.type = 'checkbox'; newCb.id = 'checkbox';
                    document.getElementById('cb-container').appendChild(newCb);
                    msg.innerHTML = "It's back!";
                }
            }, 3000);
        }
    </script>
</head>
<body>
    <h3>Dynamic Controls</h3>
    <div id="cb-container"><input type="checkbox" id="checkbox"> A checkbox</div>
    <button onclick="toggleCheckbox()">Remove/Add</button>
    <div id="loading" style="display:none;">Loading...</div>
    <p id="message"></p>
</body>
</html>