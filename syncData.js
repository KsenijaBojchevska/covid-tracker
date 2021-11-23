$(function () {

    $("#syncdata").click(() => {
        $.ajax({
            method: "POST",
            url: "syncData.php"
        })
            .done(() => { });
    });
});