var last_keyword = '';
$(document).ready(function () {

    $('#campus-list').change(function () {
        $('#college-list').val('').find('option:not(:first-child)').addClass('hidden');
        $('#major-list').val('').find('option:not(:first-child)').addClass('hidden');
        $('#class-list').val('').find('option:not(:first-child)').addClass('hidden');
        $('#college-list option[data-parent="' + $('#campus-list').val() + '"]').removeClass('hidden');
    });
    $('#college-list').change(function () {
        $('#major-list').val('').find('option:not(:first-child)').addClass('hidden');
        $('#class-list').val('').find('option:not(:first-child)').addClass('hidden');
        $('#major-list option[data-parent="' + $('#college-list').val() + '"]').removeClass('hidden');
    });
    $('#major-list').change(function () {
        $('#class-list').val('').find('option:not(:first-child)').addClass('hidden');
        $('#class-list option[data-parent="' + $('#major-list').val() + '"]').removeClass('hidden');
    });

    $('#btn-change-password').click(function () {
        $('#modal-sm').modal({
            backdrop: true,
            keyboard: true,
            remote: change_password_url
        });
    })
    $('#btn-change-avatar').click(function () {
        $('#modal-md').modal({
            backdrop: true,
            keyboard: true,
            remote: change_avatar_url
        });
    })


    // $('.input-group.date').datepicker({
    //     todayBtn: "linked",
    //     keyboardNavigation: false,
    //     forceParse: false,
    //
    //     autoclose: true,
    //     format: "yyyy-mm-dd",
    //     language: "zh-CN",
    // });

    $("#search-day").click(function () {
        $("#search-start").val(getToday());
        $("#search-end").val(getToday());
    });
    $("#search-week").click(function () {
        $("#search-start").val(getWeekStart());
        $("#search-end").val(getWeekEnd());
    });
    $("#search-month").click(function () {
        $("#search-start").val(getMonthStart());
        $("#search-end").val(getMonthEnd());
    });
    $("#search-month-last").click(function () {
        $("#search-start").val(getLastMonthStart());
        $("#search-end").val(getLastMonthEnd());
    });
    $("#search-year").click(function () {
        $("#search-start").val(getYearStart());
        $("#search-end").val(getYearEnd());
    });
    $("#search-btn").click(function () {
        var key = '';
        if ($("#search-content").val() != '') {
            key = $("#search-content").val() + ' ';
        }
        if ($("#search-start").val() != '' || $("#search-end").val() != '') {
            key += $("#search-start").val() + '-' + $("#search-end").val();

        }
        if (key != '') {
            $("#search-keyword").val(key);
            search();
        }

        $("#modal-search").modal('hide');
    });
    $("#search-clear").click(function () {
        $("#search-clear").hide();
        $("#search-keyword").val('');
        $("#search-keyword").focus();
    });
    $("#search-keyword").keydown(function (e) {
        if ($("#search-keyword").val() != '') {
            $("#search-clear").show();
        }
        else {
            $("#search-clear").hide();
        }
        var key = e.which;
        if (key == 13) {
            search();
        }
    });
    $("#search-button").click(function () {
        search();
    });
    if (typeof(data_table) == "undefined") {
        //$("#search-keyword").hide();
    }

    function search() {
        data_table.draw();
        if (last_keyword != $("#search-keyword").val()) {
            last_keyword = $("#search-keyword").val();
        }
    }

    $('#modal-md').on('hidden.bs.modal', function () {
        KindEditor.remove('.kindeditor');
        $(this).find(".modal-content").html('');
        $(this).removeData();
    });
    $('#modal-sm').on('hidden.bs.modal', function () {
        KindEditor.remove('.kindeditor');
        $(this).find(".modal-content").html('');
        $(this).removeData();
    });
    $('#modal-lg').on('hidden.bs.modal', function () {
        KindEditor.remove('.kindeditor');
        $(this).find(".modal-content").html('');
        $(this).removeData();
    });

    var location_url = self.location.href;//window.location.origin + window.location.pathname;
    $("#side-menu li a").each(function () {
        if ($(this).attr('href') != '' && $(this).attr('href') != '#') {
            if (location_url.toLowerCase().indexOf($(this).attr('href')) >= 0) {
                $(this).parents("li").addClass('active');
                $(this).parents(".nav-second-level").addClass('in');
                return false;
            }
        }
    });
    toastr.options = {
        closeButton: true,
        progressBar: true,
        showMethod: 'slideDown',
        timeOut: 2000
    };

    $('img').on('error', function () {
        $(this).attr('src', "/public/static/admin/img/empty.png");
    });
    $('img[src=""]').each(function () {
        $(this).attr('src', "/public/static/admin/img/empty.png");
    });

    $('#data-table tbody').on('dblclick', 'tr td:not(:last-child)', function () {
        var data = data_table.row(this).data();
        var btn_edit = $(this).parent().find(".btn-edit");
        if (btn_edit) {
            var target = $(btn_edit).data('target');
            var url = $(btn_edit).attr('href');
            $(target).modal({remote: url})
        }
    });
});