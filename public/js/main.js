$(document).ready(function () {
    $('form').submit(function (event) {

        if (document.getElementById("urlField").value === "") {
            errorForm.style.display = "block";
            document.getElementById("errorText").innerHTML = "Error: Please enter a link!";

            event.preventDefault();
        } else if (document.getElementById("urlField").value.startsWith("http://nocry.me")) {
            errorForm.style.display = "block";
            document.getElementById("errorText").innerHTML = "Error: You can not generate a shorten link with nocry.me domain.";

            event.preventDefault();
        } else if (document.getElementById("urlField").value.startsWith("https://nocry.me")) {
            errorForm.style.display = "block";
            document.getElementById("errorText").innerHTML = "Error: You can not generate a shorten link with nocry.me domain.";

            event.preventDefault();
        } else {
            var formData = {
                'urlField': $('input[name=urlField]').val(),
            };

            $.ajax({
                type: 'POST',
                url: '/api/create',
                data: formData
            })
                .done(function (data) {
                    var errorForm = document.getElementById("errorForm");
                    if (data === "no_vaild_url") {
                        errorForm.style.display = "block";
                        document.getElementById("errorText").innerHTML = "Error: Not a valid link!";
                    } else if (data === "no_nocry_domain") {
                        errorForm.style.display = "block";
                        document.getElementById("errorText").innerHTML = "Error: You can not generate a shorten link with nocry.me domain.";
                    } else {
                        errorForm.style.display = "none";

                        document.getElementById("urlField").value = data;
                    }
                });

            event.preventDefault();
        }
    });
});
