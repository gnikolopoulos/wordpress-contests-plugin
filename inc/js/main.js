(function($) {

  $('.contest-entry').validate({
    rules: {
      email: {
        required: true,
        email: true,
      },
      first_name: {
        required: true,
        minlength: 5
      },
      last_name: {
        required: true,
        minlength: 10
      },
      address: {
        required: true,
        minlength: 20
      },
      address_city: {
        required: true,
        minlength: 5
      },
      address_state: {
        required: true,
        minlength: 2
      },
      address_zip: {
        required: true,
        minlength: 5,
        maxlength: 5
      },
      entryfile: {
        required: true,
        extension: "jpg|png|gif|pdf|zip|avi|mp4|mov|wmv|mp3|7z"
      }
    },
    messages: {
      entryfile: {
        required: "Did you forget to include a file?",
        extension: "Only video, audio, zip and PDF files are allowed."
      }
    },
    errorContainer: "#validation",
    errorLabelContainer: "#validation",
    wrapper: "",
  });

  $('.btn').on('click', function(e) {
    e.preventDefault();
    var fname = $('#first-name').val();
    var lname = $('#last-name').val();
    var email = $('#email').val();
    var address = $('#address').val();
    var city = $('#address_city').val();
    var state = $('#address_state').val();
    var zip = $('#address_zip').val();
    var phone = $('#phone').val();
    var security = $('#security').val();
    var file = $('#entryfile').files;
    var upload_nonce = $('#upload_nonce').val();
    var url = $('#url').val();
    var error = 1;

    if( $('#rules').is(":checked") && $('#age').is(":checked") && $('#citizen').is(":checked") && $('.contest-entry').valid() ) {
      $('#validation').hide();
      error = 0;
      $('.btn').html('<i class="fa fa-spinner fa-pulse"></i> Please wait...')
    } else {
      $('#validation').show('fast').html("You must check all three checkboxes to continue");
      error = 1;
    }

    if( error == 0 ) {
      // Image upload
      var formData = new FormData();
      formData.append("action", "upload-attachment");
      var fileInputElement = document.getElementById("entryfile");
      formData.append("async-upload", fileInputElement.files[0]);
      formData.append("name", fileInputElement.files[0].name);

      //also available on page from _wpPluploadSettings.defaults.multipart_params._wpnonce
      formData.append("_wpnonce", upload_nonce);
      var xhr = new XMLHttpRequest();
      xhr.onreadystatechange=function(){
        if (xhr.readyState==4 && xhr.status==200){
          var xhrdata = JSON.parse(xhr.responseText);
          var file_id = xhrdata.data.id;
          $.ajax({
            url: ajax.ajax_url,
            type: "POST",
            dataType : "json",
            data: {
              action:'entry_add',
              fname:fname,
              lname:lname,
              email:email,
              address:address,
              city:city,
              state:state,
              zip:zip,
              phone:phone,
              file:file_id,
              nonce:security,
            },
            success: function( response ) {
              //console.log( response );
              $('.contest-entry').hide('500', function() {
                $('.message > h2').html( response["text"] );
                $('.message').show('fast');
              });

              if( /thanks/i.test(response['text']) ) {
                $('#item_code').val( response['id'] )

                $("div#paypal > form").submit();
              }
            }
          });
        }
      }
      xhr.open("POST",url+"/wp-admin/async-upload.php",true);
      xhr.send(formData);
    }

  });

})( jQuery );