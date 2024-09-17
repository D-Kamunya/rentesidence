$(document).ready(function () {
    document.getElementById("images").addEventListener("change", function () {
        const previewContainer = document.getElementById("image-preview");
        previewContainer.innerHTML = ""; // Clear previous previews

        const files = this.files;

        if (files) {
            Array.from(files).forEach((file) => {
                const reader = new FileReader();

                reader.onload = function (e) {
                    const img = document.createElement("img");
                    img.src = e.target.result;
                    previewContainer.appendChild(img);
                };

                reader.readAsDataURL(file);
            });
        }
    });
});
