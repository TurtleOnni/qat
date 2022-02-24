<?php
include plugin_dir_path( __FILE__ ) . 'share/qat-menu-top.php';

?>
<div class="table-list">
    <table class="table">
        <thead>
        <tr>
            <td scope="col" width="20px">
                <input type="checkbox" id="selectAll"/>
            </td>
            <td scope="col" width="50px">
                #
            </td>
            <td scope="col">
                Name
            </td>
            <td scope="col">
                Alias
            </td>
            <td scope="col">
                Connected users
            </td>
            <td scope="col">
                Connected company
            </td>
            <td scope="col">
                Owner
            </td>
            <td scope="col">
                Project Manager
            </td>
            <td scope="col" width="60px">
                Actions
            </td>
        </tr>
        </thead>
        <tbody id="response">
        </tbody>
    </table>
</div>
<script>
    (function ($) {

        $('#selectAll').change(function () {
            if ($(this).prop("checked")) {
                $('#response input[type="checkbox"]').prop('checked', true);
            } else {
                $('#response input[type="checkbox"]').prop('checked', false);
            }
        });

        function init() {
            $.ajax({
                type: 'POST',
                headers: {'Autorization': window.qat_data.qat_token},
                url: window.qat_data.qat_ajax_url + '/projects',
                data: {
                    user_login: window.qat_data.qat_username,
                },
                success: function (response) {
                    buildResposeTable(response.data);
                }
            });
        }

        function buildResposeTable(data) {
            $("#response").empty();
            if (data.length !== 0) {
                data.forEach(function (project) {
                    var companies_list = '', users_list = '';
                    project.meta.connected_company.forEach( function (company) {
                        companies_list += '<div>' + company.post_title + '</div>';
                    });
                    project.meta.connected_user.forEach( function (user) {
                        users_list += '<div>' + user.data.user_login + '</div>';
                    });
                    var tr = '<tr>' +
                        '<td scope="col"><input type="checkbox" id="' + project.term_id + '"/></td>' +
                        '<td scope="col">' + project.term_id + '</td>' +
                        '<td scope="col"><b>' + project.name + '</b></td>' +
                        '<td scope="col">' + project.meta.alias + '</td>' +
                        '<td scope="col">' + users_list + '</td>' +
                        '<td scope="col">' + companies_list + '</td>' +
                        '<td scope="col">' + project.meta.owner_id + '</td>' +
                        '<td scope="col"> Project man</td>' +
                        '<td scope="col"><button class="action-btn btn btn-success" data-id="' + project.ID + '">Edit</button>' + '</td></tr>';
                    $("#response").append(tr);
                });
            } else {
                $('#response').append('<div>List is empty.</div>');
            }
        }

        $('#response').on('click', '.action-btn', function () {
            //do something
        });

        $(document).ready(function () {
            init();
        });
    })(jQuery);
</script>
