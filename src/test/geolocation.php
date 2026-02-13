<!DOCTYPE html>
<html>
<head><title>Geolocation</title></head>
<body>
    <h3>Geolocation</h3>
    <button onclick="getLocation()">Where am I?</button>
    <div id="demo"></div>

    <script>
    function getLocation() {
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(showPosition);
        } else { 
            document.getElementById("demo").innerHTML = "Geolocation is not supported.";
        }
    }
    function showPosition(position) {
        document.getElementById("demo").innerHTML = "Latitude: " + position.coords.latitude + 
        "<br>Longitude: " + position.coords.longitude;
    }
    </script>
</body>
</html>
