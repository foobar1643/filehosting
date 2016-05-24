$(".comments-reply-button").on('click', function() {
    var parentCommentId = $(this).attr('id').split("-").pop();
    showReplyForm(parentCommentId, $(this).parent().parent().parent());
});

$("#comment-button").click(function() {
    postComment($("#comment-field").val(), null)
});

function postComment(comment, parentId) {
    if(parentId == null) {
        var data = {"comment": comment};
    } else {
        var data = {
            "comment": comment,
            "parentComment": parentId
        };
    }
    $.ajax({
        type: "POST",
        data: data,
        url: location.pathname + "/comment?ajax=true",
        success: handleJsonResponse,
    });
}

function showReplyForm(parentId, parentObj) { // shows comment reply form to user
    parent = parentObj || $("#comment-" + parentId);
    if($("#reply-form") != null) {
        $("#reply-form").remove();
    }
    var replyForm = $("#comment-form").clone();
    replyForm.attr('id', "reply-form");
    replyForm.appendTo(parent);
    replyForm.children(".page-header").children("h3").html("Ответить на комментарий");
    replyForm.children(".form-group").removeClass("has-error");
    replyForm.children(".form-group").children(".real-label").remove();
    replyForm.children(".form-group").attr('id', "reply-form-group")
    replyForm.children(".form-group").children("textarea").attr('id', "reply-field");
    replyForm.children(".form-group").children("textarea").attr('name', "reply");
    var replyButton = replyForm.children(".form-group").children("#comment-button");
    replyButton.attr('id', "reply-button");
    replyButton.click(function() {
        replyForm.hide("fast");
        postComment($("#reply-field").val(), parentId)
    });
}

function showErrors(response) { // shows errors to user
    errors = response.errors;
    var formType = getCommentType(response.parentId);
    removeErrorElements(formType);
    $("#" + formType + "-form").show("fast");
    $("#" + formType + "-form-group").addClass("has-error");
    for(var error in errors) {
        var errorLabel = $("#error-label-dummy").clone();
        errorLabel.attr('id', "error-label-" + error);
        errorLabel.attr('class', "control-label real-label");
        errorLabel.css('display', "block");
        errorLabel.html(errors[error]);
        $("#" + formType + "-form-group").append(errorLabel);
    }
}

function showComment(comment) {
    var commentType = getCommentType(comment.parentId);
    var commentDepth = getCommentDepth(comment.path);
    var commentElement = $("#comment-dummy").clone();
    commentElement.attr('id', "comment-" + comment.id);
    commentElement.children(".pull-right").children("small").html(comment.date);
    commentElement.children(".media-body").children(".user-name").html(comment.author);
    commentElement.children(".media-body").children(".comment-text").html(comment.text);
    if(commentDepth < 5) {
        commentElement.children(".media-body").children("p").children("small").children("a").attr('id', "reply-" + comment.id);
        commentElement.append("<div class='children' style='margin-left: 25px;'></div>");
        commentElement.children(".media-body").children("p").children("small").children("a").on('click', function() {
            var parentCommentId = $(this).attr('id').split("-").pop();
            showReplyForm(parentCommentId, commentElement.children(".media-body"));
        });
    } else {
        commentElement.children(".media-body").children("p").remove();
    }
    removeErrorElements(commentType);
    $("#"+ commentType +"-field").val("");
    if(commentType == "comment") {
        $("#file-comments-list").append(commentElement);
        $("#no-comments-found").hide("fast");
    } else {
        $("#comment-" + comment.parentId).children(".children").append(commentElement);
    }
    commentElement.show("fast");
    incrementCommentsCount();
}

function removeCommentsFallback() { // removes fallback elements for non-javascript users
    $(".comments-reply-button").removeAttr("href");
    $("#comment-button").attr("type", "button");
}

function removeErrorElements(formType) {
    $("#" + formType + "-form-group").children(".real-label").remove();
    $("#" + formType + "-form-group").removeClass("has-error");
}

function incrementCommentsCount() {
    var commentsCount = parseInt($("#total-comments-count").html().replace(/\D+/gi, ''));
    $("#total-comments-count").html((commentsCount + 1) + " комментариев");
}

function getCommentDepth(matPath) {
    path = matPath.split(".");
    return path.length;
}

function getCommentType(parentId) {
    if(parentId != null) {
        return "reply";
    } else {
        return "comment";
    }
}

function handleJsonResponse(response) {
    if(response.errors != false) {
        showErrors(response);
    } else {
        showComment(response.comment);
    }
}

removeCommentsFallback();