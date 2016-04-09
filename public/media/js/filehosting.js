$("#comment-button").click(function() {
    $.ajax({
        type: "POST",
        data: {comment: $("#comment-field").val()},
        url: location.pathname + "/comment/post",
        success: processPostData,
    });
});

$(".comments-reply-button").on('click', function() {
    var parentCommentId = $(this).attr('id').split("-").pop();
    showReplyForm(parentCommentId, $(this).parent().parent().parent());
});

function processPostData(data, parentTag) {
    var parsed = JSON.parse(data);
    if(parsed.error == 2) {
        $("#" + parsed.type + "-form").show("fast");
        $("#" + parsed.type +"-error-label").remove();
        $("#" + parsed.type + "-form-group").addClass("has-error");
        $("#" + parsed.type + "-form-group")
            .append("<label style='display: none' id='"+ parsed.type +"-error-label' class='control-label' for='"+ parsed.type +"'>Вы не заполнили поле комментария</label>")
        $("#" + parsed.type +"-error-label").show("fast");
    } else if(parsed.error == false) {
        var newComment = $("#comment-dummy").clone();
        newComment.attr('id', "comment-" + parsed.result.id); // set element id
        newComment.children(".pull-right").children("small").html(parsed.result.date); // set comment date
        newComment.children(".media-body").children(".user-name").html(parsed.result.author); // set comment author
        newComment.children(".media-body").children(".comment-text").html(parsed.result.text); // set comment text
        newComment.children(".media-body").children("p").children("small").children("a").attr('id', "reply-" + parsed.result.id);
        newComment.append("<div class='children' style='margin-left: 25px;'></div>");
        newComment.children(".media-body").children("p").children("small").children("a").on('click', function() {
            var parentCommentId = $(this).attr('id').split("-").pop();
            showReplyForm(parentCommentId, newComment.children(".media-body"));
        });
        $("#"+ parsed.type +"-field").val("");
        $("#" + parsed.type +"-error-label").remove();
        $("#" + parsed.type + "-form-group").removeClass("has-error");
        if(parsed.type == "comment") {
            $("#file-comments-list").append(newComment);
            $("#no-comments-found").hide("fast");
        } else {
            parentTag.parent().children(".children").append(newComment);
        }
        newComment.show("fast");
    }
}

function showReplyForm(parentCommentId, parentCommentTag) {
    parentTag = parentCommentTag || $("#comment-" + parentCommentId);
    if($("#reply-form") != null) {
        $("#reply-form").remove();
    }
    var replyForm = $("#comment-form").clone();
    replyForm.attr('id', "reply-form");
    replyForm.appendTo(parentTag);
    replyForm.children(".page-header").children("h3").html("Ответить на комментарий");
    replyForm.children(".form-group").removeClass("has-error");
    replyForm.children(".form-group").children("#comment-error-label").remove();
    replyForm.children(".form-group").attr('id', "reply-form-group")
    replyForm.children(".form-group").children("textarea").attr('id', "reply-field");
    replyForm.children(".form-group").children("textarea").attr('name', "reply");
    var replyButton = replyForm.children(".form-group").children("#comment-button");
    replyButton.attr('id', "reply-button");
    replyButton.click(function() {
        replyForm.hide("fast");
        $.ajax({
            type: "POST",
            data: {comment: $("#reply-field").val()},
            url: location.pathname + "/comment/"+ parentCommentId +"/post",
            success: function(data) {
                processPostData(data, parentTag);
            },
        });
    });
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