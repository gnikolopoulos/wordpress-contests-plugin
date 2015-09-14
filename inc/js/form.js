(function($) {

  $('.contest-entry').validate({
    rules: {
      email: {
        required: true,
        email: true,
        remote: {
          url: ajax.ajax_url,
          type: "post",
          data: {
            action: 'email_check',
            email: function() {
              return $( "#email" ).val();
            }
          }
        }
      },
      email2: {
        required: true,
        email: true,
        equalTo: "#email"
      },
      first_name: {
        required: true
      },
      last_name: {
        required: true
      },
      address: {
        required: true
      },
      address_city: {
        required: true
      },
      address_state: {
        required: true
      },
      address_zip: {
        required: true
      },
      phone: {
        phoneUS: true
      },
      item_title: {
        required: true
      },
      item_description: {
        required: true
      },
      entryfile: {
        required: true,
        extension: "jpg|png|gif|pdf|txt|zip|avi|mp4|mov|wmv|mp3|7z"
      },
      terms: {
        required: true
      },
      age: {
        required: true
      },
      citizen: {
        required: true
      }
    },
    messages: {
      first_name: {
        required: "First name is a required field"
      },
      last_name: {
        required: "Last name is a required field"
      },
      address: {
        required: "You need to enter your address"
      },
      address_city: {
        required: "Please fill in your city"
      },
      address_state: {
        required: "Plase enter the state you are in"
      },
      address_zip: {
        required: "Please enter your ZIP code"
      },
      email: {
        required: "You have to enter your email",
        remote: jQuery.validator.format("{0} has already joined.")
      },
      email2: {
        required: "Please enter your email again for verification"
      },
      entryfile: {
        required: "Did you forget to include a file?",
        extension: "Only video, audio, zip, txt and PDF files are allowed."
      },
      terms: {
        required: "You have to accept the rules of this contest"
      },
      age: {
        required: "You have to be at least 18 years old to join"
      },
      citizen: {
        required: "You must be a U.S citizen or have a Green Card to join"
      }
    },
    errorContainer: "#validation",
    errorLabelContainer: "#validation",
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
    var title = $('#item_title').val();
    var description = $('#item_description').val();
    var security = $('#security').val();
    var error = 1;

    if( $('.contest-entry').valid() ) {
      $('#validation').hide().html('');
      error = 0;
      $('.btn').html('<i class="fa fa-spinner fa-pulse"></i> Please wait...')
    } else {
      error = 1;
    }

    if( error == 0 ) {
      var fd = new FormData();
      fd.append("action", "entry_add");
      fd.append("nonce", security);
      fd.append("phone", phone);
      fd.append("description", description);
      fd.append("title", title);
      fd.append("zip", zip);
      fd.append("state", state);
      fd.append("city", city);
      fd.append("address", address);
      fd.append("email", email);
      fd.append("fname", fname);
      fd.append("lname", lname);
      $.each(jQuery('#entryfile')[0].files, function(i, file) {
        fd.append('entryfile[]', file);
      });

      $.ajax({
        url: ajax.ajax_url,
        data: fd,
        type: 'POST',
        contentType: false,
        processData: false,
        cache: false,
        responseType: 'json',
        dataType: 'json',
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
        },
        xhr: function() {
          $('progress').show('fast');
          myXhr = $.ajaxSettings.xhr();
          if(myXhr.upload){
              myXhr.upload.addEventListener('progress',showProgress, false);
          } else {
              console.log("Uploadress is not supported.");
          }
          return myXhr;
        }
      });
    }

  });

  function showProgress(evt) {
    if (evt.lengthComputable) {
      var percentComplete = (evt.loaded / evt.total) * 100;
      //console.log(percentComplete);
      $('progress').val(percentComplete);
    }
  }

})( jQuery );