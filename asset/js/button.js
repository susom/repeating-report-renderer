Button = {
    url: "",
    report_id: "",
    project_id: "",
    table: null,
    init: function () {
        this.inject();

        /**
         * Delete filter
         */
        $(document).on('click', '#render-repeating-report', function () {
            Button.submitReport();
        });

    },
    inject: function () {
        if ($("#render-repeating-report").length == 0) {
            var html = '<button id="render-repeating-report" class="report_btn jqbuttonmed ui-button ui-corner-all ui-widget" onclick="return false;" style="color:#000066;font-size:12px;"><i class="fas fa-file-download"></i> Render Repeating Instrument</button>';

            html = "<div id='children-buttons'>" + html + "</div>";

            // find the second div that includes the report buttons and inject html there.
            var $buttons = jQuery(jQuery(document).find("#report_div > div:nth-child(1)").find(".d-print-none")[1]);
            $buttons.append(html);
        }
    },
    redirect: function (url) {
        window.location.replace(url);
    },
    submitReport: function () {
        $.ajax({
            url: this.url,
            data: {pid: this.project_id, report_id: this.report_id},
            timeout: 60000000,
            type: 'POST',
            dataType: 'json',
            success: function (response) {

                var data = response.data;
                var columns = response.columns;
                //to export large data we saved input into session then pass its name to be used in export
                $("#inputs-name").val(response.session);

                columns.defaultContent = '';
                $("#filters-row").slideUp();
                $("#buttons-area").hide().removeClass('d-none').slideDown();
                jQuery("#report_table_wrapper").append("<table id=\"report-result\" class=\"display table table-striped table-bordered\"\n" +
                    "               cellspacing=\"0\" width=\"100%\"></table>");
                jQuery("#report_table").hide();
                Button.table = $('#report-result').DataTable({
                    dom: 'Bfrtip',
                    data: data,
                    pageLength: 50,
                    bDestroy: true,
                    columns: Button.prepareTableHeaders(columns),
                    buttons: [
                        'copy', 'csv', 'excel', 'pdf', 'print'
                    ]
                });
            },
            error: function (request, error) {
                alert("Request: " + JSON.stringify(request));
            }
        });
    },
    prepareTableHeaders: function (columns) {
        var arr = [];
        for (var i = 0; i < columns.length; i++) {
            arr.push(
                {
                    data: columns[i],
                    defaultContent: "<i>N/A</i>",
                    title: columns[i],
                }
            );
        }
        return arr;
        ;
    }
}
$(document).ajaxStop(function () {
    setTimeout(function () {
        Button.init();
    }, 200)
});
