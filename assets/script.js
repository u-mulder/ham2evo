$( document ).ready( function() {

    var toast = document.getElementById('toast');
    var isInvalidClass = "is-invalid";

    function showToast(msg) {
        toast.MaterialSnackbar.showSnackbar({ message: msg });
    }

    function checkDate() {
        // TODO with regexp?
    }

    $( "#settings_form" ).on("submit", function() {
        var this$ = $ ( this );
        var actionUrl = this$.attr( "action" );
        var spinner$ = $( "#settings_spinner" );

        if ( actionUrl ) {
            spinner$.show();
            $.ajax( {
                url: actionUrl,
                data: this$.serialize(),
                dataType: "json",
                method: "POST",
                success: function( data ) {
                    if ( data.success ) {
                        showToast('Настройки успешно сохранены');

                        $( "#settings" ).remove();
                        $( "#filter" ).show();
                    } else {
                        spinner$.hide();
                        showToast('Ошибки сохранения настроек!');
                        $.each(data.errors, function (i, v) {
                            $( "#" + v ).parent().addClass( isInvalidClass );
                        });
                    }
                },
                error: function() {
                    spinner$.hide();
                    showToast('Неизвестная ошибка сервера!');
                }
            } );
        }

        return false;
    });

    $( "#filter_form" ).on("submit", function() {
        var this$ = $ ( this );
        var actionUrl = this$.attr( "action" );
        var spinner$ = $( "#filter_spinner" );
        var dateFrom, dateTo;

        if ( actionUrl ) {
            spinner$.show();
            dateFrom = $( "#dateFrom" ).val();
            dateTo = $( "#dateTo" ).val();
            if ( !dateFrom && !dateTo ) {
                spinner$.hide();
                showToast('Укажите период!');
                if ( !dateFrom ) {
                    $( "#dateFrom" ).parent().addClass( isInvalidClass );
                }
                if ( !dateTo ) {
                    $( "#dateTo" ).parent().addClass( isInvalidClass );
                }

            } else {
                $.ajax( {
                    url: actionUrl,
                    data: $(this).serialize(),
                    dataType: "json",
                    method: "POST",
                    success: function( data ) {
                        spinner$.hide();
                        if ( data.success ) {
                            var tableContents = "";

                            if ( 0 < data.records.length ) {
                                $( "#records_none" ).hide();
                                var spanSize = 6;
                                var rowIndex = 0;
                                var rec;

                                $.each(data.records, function (i, v) {
                                    tableContents += "<tr>"
                                        + "<td class='mdl-data-table__cell--non-numeric' colspan='" + spanSize + "'>"
                                        + "<i class='material-icons mdl-list__item-icon'>date_range</i>&nbsp;" + v.date + "</td></tr>";

                                    for (var k in v.items ) {
                                        for (var subkey in v.items[ k ] ) {
                                            rec = v.items[ k ][ subkey ];
                                            rowIndex++;
                                            tableContents += "<tr>"
                                                + "<td><label class='mdl-checkbox mdl-js-checkbox mdl-js-ripple-effect mdl-data-table__select' for='row" + rowIndex + "'>"
                                                + "<input type='checkbox' name='to_evo[]' id='row" + rowIndex +"' class='mdl-checkbox__input' checked /></label></td>"
                                                + "<td class='mdl-data-table__cell--non-numeric'>" + rec.name + "</td>"
                                                + "<td class='mdl-data-table__cell--non-numeric'>" + rec.date + "</td>"
                                                + "<td class='mdl-data-table__cell--non-numeric'>" + rec.hours + " / " + rec.seconds + "</td>"
                                                + "<td class='mdl-data-table__cell--non-numeric'>" + rec.comment + "</td>"
                                                + "<td class='mdl-data-table__cell--non-numeric'>" + rec.project_name + "</td>"
                                                + "</tr>";
                                        }
                                    }
                                });
                                $( "#hours_total" ).text( data.hours_total );
                            } else {
                                $( "#records_none" ).show();
                            }

                            $( "#records_table" ).find( "tbody" ).html( tableContents );

                            $( "#records" ).show();
                            $( "#filter" ).hide();
                        } else {
                            showToast('Ошибки получения записей!');

                            var errListHtml = "";
                            $.each( data.errors, function (i, v)  {
                                errListHtml += '<li class="mdl-list__item">'
                                    + '<span class="mdl-list__item-primary-content">'
                                    + '<i class="material-icons">error</i>&nbsp;&nbsp;'
                                    + '<span>' + v + '</span></span></li>';
                            });
                            $( "#filter_errors_list" ).html( errListHtml );
                            $( "#filter_errors" ).show();
                            $( "#filter" ).hide();
                        }
                    },
                    error: function() {
                        spinner$.hide();
                        showToast('Неизвестная ошибка сервера!');
                    }
                } );
            }
        }

        return false;
    });

    $( "#filter_retry" ).on( "click", function () {
        $( "#filter_errors" ).hide();
        $( "#filter_errors_list" ).html( "" );
        $( "#filter" ).show();
    } );

    $( "#new_filter" ).on( "click", function () {
        $( "#records" ).hide();
        $( "#filter" ).show();
    } );

    $( "#send_records" ).on( "click", function () {
        alert( "Функционал в разработке" );

        return false;
    } );

} );
