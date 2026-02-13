<h3>Infinite Scroll</h3>
<div id="content"></div>
<script>
    window.onscroll = function() {
        if ((window.innerHeight + window.scrollY) >= document.body.offsetHeight) {
            let p = document.createElement("p");
            p.innerText = "Added more content...";
            document.getElementById("content").appendChild(p);
        }
    };
    // Initial content
    for(i=0; i<20; i++) document.write("<p>Scroll down to see more...</p>");
</script>