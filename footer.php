<script>

//per il fade-in
window.addEventListener("load", () => {
    setTimeout(() => {
        document.body.classList.add("loaded");
    }, 100); // piccolo delay
});


//per il fade-out
document.querySelectorAll("a").forEach(link => {
    link.addEventListener("click", function(e) {
        if (this.target === "_blank") return;

        e.preventDefault();
        const href = this.href;

        document.body.classList.remove("loaded");

        setTimeout(() => {
            window.location.href = href;
        }, 250);
    });
});

</script>