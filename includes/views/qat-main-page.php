<?php
include plugin_dir_path( __FILE__ ) . 'share/qat-menu-top.php';
include_once plugin_dir_path( __FILE__ ) . '../rest-api/class-qat-ticket.php';

$qat_ticket          = new Qat_Ticket();
$ticket_types        = $qat_ticket->get_ticket_types();
$ticket_status_list  = $qat_ticket->get_ticket_status_list();
$ticket_urgency_list = $qat_ticket->get_ticket_urgency_list();
$users               = get_users(); //ADD RESPONDER ROLE HERE TODO

?>
<div class="filters mb-1">
    <input type="text" id="searchVal" placeholder="Search by id or title" class="form-control mr-1">
    <select id="typeTicket" class="form-control mr-1">
        <option value="" selected="selected">Type</option>
		<?php foreach ( $ticket_types as $type ) { ?>
            <option value="<?php echo $type['meta_value'] ?>">
				<?php
				    echo $type['meta_value'];
				?>
            </option>
		<?php } ?>
    </select>
    <select id="statusTicket" class="form-control mr-1">
        <option value="" selected="selected">Status</option>
		<?php foreach ( $ticket_status_list as $status ) { ?>
            <option value="<?php echo $status['meta_value'] ?>">
				<?php
				    echo $status['meta_value'];
				?>
            </option>
		<?php } ?>
    </select>
    <button id="resetFilter" class="btn btn-default mr-1">Reset</button>
    <button type="button" data-toggle="collapse" data-target="#collapseAddTicketForm" aria-expanded="false" aria-controls="collapseAddTicketForm" class="btn btn-success">Add</button>
</div>
<div class="collapse mb-1" id="collapseAddTicketForm">
    <div class="card card-body">
            <div class="form-group">
                <label for="ticketTitle">Title</label>
                <input type="text" class="form-control" id="ticketTitle" placeholder="Title">
            </div>
            <div class="form-group">
                <label for="ticketContent">Content</label>
                <textarea class="form-control" id="ticketContent">
                </textarea>
            </div>
            <div class="form-group">
                <label for="typeTicketForm">Type</label>
                <select id="typeTicketForm" class="form-control mr-1">
			        <?php foreach ( $ticket_types as $type ) { ?>
                        <option value="<?php echo $type['meta_value'] ?>" <?php if($type['meta_value'] == $ticket_types[0]['meta_value']){echo "selected";}?>>
					        <?php
					        echo $type['meta_value'];
					        ?>
                        </option>
			        <?php } ?>
                </select>
            </div>
            <div class="form-group">
                <label for="projectIdForm">Project</label>
                <select id="projectIdForm" class="form-control mr-1">
			        <?php foreach ( $ticket_types as $type ) { ?>
                        <option value="<?php echo $type['meta_value'] ?>" <?php if($type['meta_value'] == $ticket_types[0]['meta_value']){echo "selected";}?>>
					        <?php
					        echo $type['meta_value'];
					        ?>
                        </option>
			        <?php } ?>
                </select>
            </div>
            <div class="form-group">
                <label for="urgencyStatus">Urgency</label>
                <div class="btn-group btn-group-toggle" data-toggle="buttons">
			        <?php foreach ( $ticket_urgency_list as $urgency ) { ?>
                        <label class="btn btn-danger <?php if($urgency['meta_value'] == $ticket_urgency_list[0]['meta_value']){echo "active";}?>" data-id="<?php echo $urgency['meta_value'];?>">
                            <input type="radio" name="urgencyStatus"/> <?php echo $urgency['meta_value']; ?>
                        </label>
			        <?php } ?>
                </div>
            </div>
            <div class="form-group">
                <label for="responderIdForm">Responder</label>
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
            <button id="addTicket" class="btn btn-primary">Add Ticket</button>
    </div>
</div>
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
                Title
            </td>
            <td scope="col" width="60px">
                Type
            </td>
            <td scope="col" width="60px">
                Replies(new)
            </td>
            <td scope="col" width="80px">
                Updated on
            </td>
            <td scope="col" width="80px">
                Status
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
 (function( $ ) {
    $('#resetFilter').click(function(){
        $('#searchVal').val('');
        $('#typeTicket').val('');
        $('#statusTicket').val('');
        init();
    });
    function emptyForm() {
        $('#ticketTitle').val('');
        $('#responderIdForm').val('');
        tinyMCE.activeEditor.setContent('');
        $('#collapseAddTicketForm').collapse('hide');
    }
    function filter() {
        var data = {};
        data['search_val'] = $('#searchVal').val();
        data['type'] = $('#typeTicket').val();
        data['status'] = $('#statusTicket').val();
        if ( data['search_val'] === '' &&  data['type'] === '' && data['status'] === '' ) {
            init();
        } else {
            data['user_login'] = window.qat_data.qat_username;
            $.ajax({
                type: "POST",
                headers: {'Autorization': window.qat_data.qat_token},
                url: window.qat_data.qat_ajax_url + '/getTicketsByFilter',
                data: data,
                success: function (response) {
                    buildResposeTable(response.data);
                }
            });
        }
    }
    $('#addTicket').click(function () {
        var hasError = false;
        if($('#ticketTitle').val() === '' ) {
            hasError = true;
            $('#ticketTitle').css('border-color', 'red');
        }
        if($('#responderIdForm').val() === '' ) {
            hasError = true;
            $('#responderIdForm').css('border-color', 'red');
        }
        if(!hasError){
            var data = {
                user_login: window.qat_data.qat_username,
                post_title: $('#ticketTitle').val(),
                post_content: tinyMCE.activeEditor.getContent({format : 'raw'}),
                meta: [{
                    key: 'urgency',
                    value: $('label.active').attr('data-id'),
                },{
                    key: 'type',
                    value: $('#typeTicketForm').val()
                },{
                    key: 'responder_id',
                    value: $('#responderIdForm').val()
                },{
                    //PROJECT ID IS STATIC TODO
                    key: 'project_id',
                    value: 5
                }]
            }
            $.ajax({
                type: 'POST',
                headers: {'Autorization': window.qat_data.qat_token},
                url: window.qat_data.qat_ajax_url + '/ticket',
                data: data,
                success: function (response) {
                    resetFilter();
                    removeErrorStatus();
                    emptyForm();
                }
            });
        }
    });

    function removeErrorStatus() {
        $('#ticketTitle').css('border-color', '#7e8993');
        $('#responderIdForm').css('border-color', '#7e8993');
    }

    function init() {
        $.ajax({
            type: 'POST',
            headers: {'Autorization': window.qat_data.qat_token},
            url: window.qat_data.qat_ajax_url + '/tickets',
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
            data.forEach(function (tiket) {
                var tr = '<tr>' +
                    '<td scope="col"><input type="checkbox" id="' + tiket.ID + '"/></td>' +
                    '<td scope="col">' + tiket.ID + '</td>' +
                    '<td scope="col"><b>' + tiket.post_title + '</b>' + '<p>' + tiket.post_content + '</p></td>' +
                    '<td scope="col">' + tiket.meta.type + '</td>' +
                    '<td scope="col">' + tiket.comment_count + ' (' + tiket.new_comments_count + '+)</td>' +
                    '<td scope="col">' + tiket.last_update + '</td>' +
                    '<td scope="col">' + tiket.meta.status + '</td>' +
                    '<td scope="col"><button class="set-status btn btn-danger" data-status="trash" data-id="' + tiket.ID + '">Cancel</button>' +
                    '<button class="set-status btn btn-success" data-status="draft" data-id="' + tiket.ID + '">Done</button></td></tr>';
                $("#response").append(tr);
            });
        } else {
            $('#response').append('<div>List is empty.</div>');
        }
    }

    $('#response').on('click', '.set-status', function () {
        $.ajax({
            type: 'POST',
            headers: {'Autorization': window.qat_data.qat_token},
            url: window.qat_data.qat_ajax_url + '/setTicketStatus',
            data: {
                ID: $(this).attr('data-id'),
                post_status: $(this).attr('data-status')
            },
            success: function () {
                init();
            }
        });
    });

    $('#selectAll').change(function () {
        if ($(this).prop("checked")) {
            $('#response input[type="checkbox"]').prop('checked', true);
        } else {
            $('#response input[type="checkbox"]').prop('checked', false);
        }
    });

    $('#searchVal').change(function () {
        filter();
    });

    $('#statusTicket').change(function () {
        filter();
    });

    $('#typeTicket').change(function () {
        filter();
    });

    $(document).ready(function () {
        init();
        tinymce.init({ selector:'#ticketContent' });

    });
})( jQuery );

</script>
