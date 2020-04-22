List = {
    url: "",
    report_id: "",
    project_id: "",
    table: null,
    init: function () {
        $.each($('tr[reportid]'), function (index, value) {
            if (!isNaN($(this).attr('reportid')) && $(this).attr('reportid') !== '' && $(this).attr('reportid') != null) {
                var button = List.generateButton($(this).attr('reportid'));
                console.log(button)
                $(this).find('.rprt_btns').append(button)
            }
        });
    },
    generateButton: function (reportId) {
        var url = $("#csv-export-url").val() + "&report_id=" + reportId
        var html = '<hr><button class="data_export_btn jqbuttonmed ui-button ui-corner-all ui-widget" onclick=\"return List.redirect(\'' + url + '\');\" style=\"color:#000066;margin:0 0 0 5px;font-size:11px;\"><i class=\"fas fa-file-download\"></i> Repeating Renderer</button>';
        return html;
    },
    redirect: function (url) {
        window.location.replace(url);
    },
}

window.onload = function () {
    List.init();
}