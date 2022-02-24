<?php
include plugin_dir_path( __FILE__ ) . 'share/qat-menu-top.php';
include_once plugin_dir_path( __FILE__ ) . '../rest-api/class-qat-project.php';

$qat_project = new Qat_Project();
$users       = get_users(); //ADD RESPONDER ROLE HERE TODO
$projects    = $qat_project->get_project_list();

?>
<div class="filters mb-1">
    <input type="text" id="searchVal" placeholder="Search by id or name" class="form-control mr-1"/>
    <select id="connectedUsers" class="form-control mr-1">
        <option value="" selected="selected">Connected users</option>
		<?php foreach ( $users as $user ) { ?>
            <option value="<?php echo $user->ID ?>">
				<?php
				echo $user->user_login;
				?>
            </option>
		<?php } ?>
    </select>
    <button id="resetFilter" class="btn btn-default mr-1">Reset</button>
    <button type="button" data-toggle="collapse" data-target="#collapseAddCompanyForm" aria-expanded="false"
            aria-controls="collapseAddCompanyForm" class="btn btn-success">Add
    </button>
</div>
<div class="collapse mb-1" id="collapseAddCompanyForm">
    <div class="card card-body">
        <div class="form-group">
            <label for="companyName">Name</label>
            <input type="text" class="form-control" id="companyName" placeholder="Name">
        </div>
        <div class="form-group">
            <label for="companyContent">Describe</label>
            <textarea class="form-control" id="companyContent">
                </textarea>
        </div>
        <div class="form-group">
            <label for="addWebsite">Website</label>
            <input type="text" id="newWebsite" placeholder="website.com"/>
            <button class="btn btn-success" id="addWebsite">Add</button>
            <div id="website-box">
            </div>
        </div>
        <div class="form-group">
            <label for="projectIdForm">Projects</label>
            <select id="responderIdForm" class="form-control mr-1">
                <option value="" selected="selected">Project</option>
				<?php foreach ( $projects as $project ) { ?>
                    <option value="<?php echo $project->term_id ?>">
						<?php
						echo $project->name;
						?>
                    </option>
				<?php } ?>
            </select>
        </div>
        <div class="form-group">
            <label for="responderIdForm">Connected users</label>
            <select id="responderIdForm" class="form-control mr-1">
                <option value="" selected="selected">Responder</option>
				<?php foreach ( $users as $user ) { ?>
                    <option value="<?php echo $user->ID ?>">
						<?php
						echo $user->user_login;
						?>
                    </option>
				<?php } ?>
            </select>
        </div>
        <div id="errorList">
        </div>
        <button id="addCompany" class="btn btn-primary">Add company</button>
    </div>
</div>
<div class="table-list">
    <table width="100%">
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
                Website
            </td>
            <td scope="col">
                Connected users
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

        $('#resetFilter').click(function () {
            $('#searchVal').val('');
            $('#connectedUsers').val('');
            init();
        });

        $('#searchVal').change(function () {
            filter();
        });

        $('#connectedUsers').change(function () {
            filter();
        });

        function filter() {
            var data = {};
            data['search_val'] = $('#searchVal').val();
            data['connected_users'] = $('#connectedUsers').val();
            if ( data['search_val'] === '' &&  data['connected_users'] === '') {
                init();
            } else {
                data['user_login'] = window.qat_data.qat_username;
                $.ajax({
                    type: "POST",
                    headers: {'Autorization': window.qat_data.qat_token},
                    url: window.qat_data.qat_ajax_url + '/getCompaniesByFilter',
                    data: data,
                    success: function (response) {
                        buildResposeTable(response.data);
                    }
                });
            }
        }

        $('#addWebsite').click(function(){
            var url = $('#newWebsite').val();
            var elem = '<div class="new-url" title="Click to remove" data-value="' + url +'"><a href="' + url + '">' + url + '</a></div>';
            $("#website-box").append(elem);
        });

        $('.new-url').click(function(){
            $(this).remove();
        });

        function emptyForm() {
            $('#companyName').val('');
            $('#addWebsite').val('');
            $('#responderIdForm').val('');
            $('#website-box').empty();
            tinyMCE.activeEditor.setContent('');
            $('#collapseAddCompanyForm').collapse('hide');
        }

        function init() {
            $.ajax({
                type: 'POST',
                headers: {'Autorization': window.qat_data.qat_token},
                url: window.qat_data.qat_ajax_url + '/companies',
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
                data.forEach(function (company) {
                    var users_list = '';
                    company.meta.connected_users.forEach( function (user) {
                        users_list += '<div>' + user.data.user_login + '</div>';
                    });
                    var tr = '<tr>' +
                        '<td scope="col"><input type="checkbox" id="' + company.ID + '"/></td>' +
                        '<td scope="col">' + company.ID + '</td>' +
                        '<td scope="col"><b>' + company.post_title + '</b></td>' +
                        '<td scope="col"><a href="' + company.meta.website + '">' + company.meta.website + '</a></td>' +
                        '<td scope="col">' + users_list + '</td>' +
                        '<td scope="col"><button class="action-btn btn btn-success" data-id="' + company.ID + '">TODO</button>' + '</td></tr>';
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
