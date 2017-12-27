//Career

var allowed_file_size = "524288"; //512 KB allowed file size
var allowed_file_types = [
  "application/msword",
  "application/vnd.openxmlformats-officedocument.wordprocessingml.document",
  "application/pdf"
]; //Allowed file types
var border_color = "#C2C2C2"; //initial input border color
var maximum_files = 1; //Maximum number of files allowed

$("#email_form").submit(function(e) {
  e.preventDefault(); //prevent default action

  document.getElementById("loader").style.display = "block";
  document.getElementById("submit-button").style.display = "none";

  proceed = true;

  //simple input validation
  $($(this).find("input[data-required=true], textarea[data-required=true]"))
    .each(function() {
      if (!$.trim($(this).val())) {
        //if this field is empty
        $(this).css("border-color", "red"); //change border color to red
        proceed = false; //set do not proceed flag
      }
      //check invalid email
      var email_reg = /^([\w-\.]+@([\w-]+\.)+[\w-]{2,4})?$/;
      if (
        $(this).attr("type") == "email" &&
        !email_reg.test($.trim($(this).val()))
      ) {
        $(this).css("border-color", "red"); //change border color to red
        proceed = false; //set do not proceed flag
      }
    })
    .on("input", function() {
      //change border color to original
      $(this).css("border-color", border_color);
    });

  //check file size and type before upload, works in modern browsers
  if (window.File && window.FileReader && window.FileList && window.Blob) {
    var total_files_size = 0;
    if (this.elements["file_attach"].files.length > maximum_files) {
      alert("Can not select more than " + maximum_files + " file(s)");
      document.getElementById("loader").style.display = "none";
      document.getElementById("submit-button").style.display = "block";
      proceed = false;
    }
    $(this.elements["file_attach"].files).each(function(i, ifile) {
      if (ifile.value !== "") {
        //continue only if file(s) are selected
        if (allowed_file_types.indexOf(ifile.type) === -1) {
          //check unsupported file
          document.getElementById("loader").style.display = "none";
          document.getElementById("submit-button").style.display = "block";
          alert(ifile.name + " is unsupported file type!");
          proceed = false;
        }
        total_files_size = total_files_size + ifile.size; //add file size to total size
      }
    });
    if (total_files_size > allowed_file_size) {
      document.getElementById("loader").style.display = "none";
      document.getElementById("submit-button").style.display = "block";
      alert("Make sure total file size is less than 500 KB!");
      proceed = false;
    }
  }

  //if everything's ok, continue with Ajax form submit
  if (proceed) {
    var post_url = $(this).attr("action"); //get form action url
    var request_method = $(this).attr("method"); //get form GET/POST method
    var form_data = new FormData(this); //Creates new FormData object

    $.ajax({
      //ajax form submit
      url: post_url,
      type: request_method,
      data: form_data,
      dataType: "json",
      contentType: false,
      cache: false,
      processData: false
    }).done(function(res) {
      //fetch server "json" messages when done
      if (res.type == "error") {
        $("#upload-results").prepend(
          '<div class="error alert alert-danger text-center"><strong>' +
            res.text +
            "</strong></div>"
        );
        document.getElementById("loader").style.display = "none";
        document.getElementById("submit-button").style.display = "block";
      }
      if (res.type == "done") {
        $("#upload-results").html(
          '<div class="success alert alert-success text-center"><strong>' +
            res.text +
            "</strong></div>"
        );
      }
    });
  }
});
