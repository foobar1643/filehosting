$(document).on('change', '.btn-file :file', function() {
  var input = $(this),
      numFiles = input.get(0).files ? input.get(0).files.length : 1,
      label = input.val().replace(/\\/g, '/').replace(/.*\//, '');
  input.trigger('fileselect', [numFiles, label]);
});

$(document).ready( function() {
    $('.btn-file :file').on('fileselect', function(event, numFiles, label) {
        var input = $(this).parents('.input-group').find(':text'),
            log = numFiles > 1 ? numFiles + ' files selected' : label;
        if( input.length ) {
            input.val(log);
        } else {
            if( log ) alert(log);
        }
    });
});

function displayReplyForm(commentId) {
    var oldReply = document.getElementById("reply-form");
    if(oldReply != null) {
        oldReply.parentNode.removeChild(document.getElementById("reply-form"));
    }
    var replyForm = document.getElementById("comment-form").cloneNode(true);
    replyForm.id = "reply-form";
    var replyChilds = replyForm.childNodes;
    replyChilds[1].childNodes[1].innerHTML = "Ответить на комментарий";
    replyChilds[3].childNodes[3].name = "reply";
    replyChilds[3].childNodes[3].id = "reply";
    replyChilds[5].childNodes[1].onclick = function() {
        post(location.pathname  + "/comment/" + commentId + "/post",
        {comment: document.getElementById('reply').value})
    };
    document.getElementById("comment-" + commentId).appendChild(replyForm);
}

function post(path, params, method) {
    method = method || "post";
    var form = document.createElement("form");
    form.setAttribute("method", method);
    form.setAttribute("action", path);

    for(var key in params) {
        if(params.hasOwnProperty(key)) {
            var hiddenField = document.createElement("input");
            hiddenField.setAttribute("type", "hidden");
            hiddenField.setAttribute("name", key);
            hiddenField.setAttribute("value", params[key]);

            form.appendChild(hiddenField);
         }
    }
    document.body.appendChild(form);
    form.submit();
}