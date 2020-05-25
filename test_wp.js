
$(document).ready(function() {


    $( "#test_wp_send" ).click(function(e){

        e.preventDefault();

        let form = $('#test_wp_mail')

        $.ajax({
            url: form.attr( 'action' ),
            method: 'post',
            data: form.serialize(),
            success: function(data){

                form.trigger( 'reset' );
                $('#test_wp_message').text(data.message);

            },
            error: function (jqXHR, exception) {
                console.log(jqXHR.responseJSON.error);
                $('#test_wp_message').text(jqXHR.responseJSON.error);
            }
        });
    })

    $( "#test_wp_message" ).click(function(e){

        $(this).text('');;
    });



});


