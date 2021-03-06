var requestType = "comment";

$(".comments-reply-button").on('click', function() {
    var parentCommentId = $(this).attr('id').split("-").pop();
    showReplyForm(parentCommentId, $(this).parent().parent().parent());
});

$("#comment-button").click(function() {
    removeErrorElements('comment');
    postComment($("#comment-field").val(), null)
});

function postComment(comment, parentId) {
    requestType = "comment";
    var data = {
        "comment": comment,
        "csrf_token": $("#csrf-token-input").val()
    };
    if(parentId != null) {
        data.parentComment = parentId;
        requestType = "reply";
    }
    $.ajax({
        type: "POST",
        data: data,
        url: location.pathname,
        success: handleJsonResponse,
    });
}

function showReplyForm(parentId, parentObj) { // shows comment reply form to user
    parent = parentObj || $("#comment-" + parentId);
    if($("#reply-form") != null) {
        $("#reply-form").remove();
    }
    var replyFormHtml = $("#reply-form-template").html();
    var replyForm = document.createElement("form");
    $(replyForm).attr('id', "reply-form");
    $(replyForm).html(replyFormHtml);
    $(replyForm).appendTo(parent);
    $("#reply-button").click(function() {
        removeErrorElements('reply');
        postComment($("#reply-field").val(), parentId);
    });
}

function showErrors(response) { // shows errors to user
    errors = response.errors;
    var formType = requestType;
    removeErrorElements(formType);
    $("#" + formType + "-form-group").addClass("has-error");
    for(var error in errors) {
        var errorLabel = $("#error-label-template").html();
        errorLabel = errorLabel.replace("%errorId%", error);
        errorLabel = errorLabel.replace("%errorText%", errors[error]);
        $("#" + formType + "-form-group").append(errorLabel);
    }
}

function showComment(comment) {
    var commentType = getCommentType(comment.parentId);
    var commentDepth = getCommentDepth(comment.path);
    var commentElement = document.createElement("div");
    var commentTemplate = $("#new-comment-template").html();
    $(commentElement).attr('class', "media");
    $(commentElement).attr('id', "comment-" + comment.id);
    $(commentElement).css('display', "none");
    commentTemplate = commentTemplate.replace("%commentDate%", comment.date);
    commentTemplate = commentTemplate.replace("%commentAuthor%", htmlspecialchars(comment.author));
    commentTemplate = commentTemplate.replace("%commentText%", nl2br(htmlspecialchars(comment.text)));
    commentTemplate = commentTemplate.replace("%commentId%", comment.id);
    $(commentElement).html(commentTemplate);
    var replyButton = $(commentElement).find("#reply-" + comment.id);
    if(commentDepth < 5) {
        $(replyButton[0]).on('click', function() {
            var parentCommentId = $(this).attr('id').split("-").pop();
            showReplyForm(parentCommentId, $(commentElement).children(".media-body"));
        });
    } else {
        $(replyButton[0]).parent().parent().remove();
    }
    removeErrorElements(commentType);
    $("#"+ commentType +"-field").val("");
    if(commentType == "comment") {
        $("#file-comments-list").append(commentElement);
        $("#no-comments-found").hide("fast");
    } else {
        $("#comment-" + comment.parentId).children(".children").append(commentElement);
    }
    $(commentElement).show("fast");
    //incrementCommentsCount();
}

function removeCommentsFallback() { // removes fallback elements for non-javascript users
    $(".comments-reply-button").removeAttr("href");
    $("#fallback-reply-form").remove();
    $("#comment-button").attr("type", "button");
}

function removeErrorElements(formType) {
    $("#" + formType + "-form-group").children(".real-label").remove();
    $("#" + formType + "-form-group").removeClass("has-error");
}

function incrementCommentsCount() {
    var commentsCount = parseInt($("#total-comments-count").html().replace(/\D+/gi, ''));
    $("#total-comments-count").html((commentsCount + 1));
}

function getCommentDepth(matPath) {
    return matPath.split(".").length;
}

function htmlspecialchars(text) {
    var specialChars = {
        "<": "&lt;",
        ">": "&gt;",
        "'": "&#39;",
        '"': "&quot;",
        "&": "&amp;"
    };
    return text.replace(/[<>'"&]/g, function(s) { return specialChars[s] });
}

function nl2br(text) {
    return text.replace(new RegExp('\n', 'g'), "<br>");
}

function getCommentType(parentId) {
    if(parentId != null) {
        return "reply";
    } else {
        return "comment";
    }
}

function handleJsonResponse(response) {
    if(response.errors != null) {
        showErrors(response);
    } else {
        $("#reply-form").hide("fast");
        showComment(response.comment);
    }
}

function toggleForm(formType) {
    var button = $("#" + formType + "-button");
    var field = $("#" + formType + "-field");
    button.attr('disabled', !button.attr('disabled'));
    field.attr('disabled', !field.attr('disabled'));
    if(field.hasClass('loading')) {
        field.removeClass('loading');
    } else {
        field.addClass('loading');
    }
}

function ajaxRequestError(event, request, settings) {
    var data = {
        "errors": { "serverError": request.status + ": " + request.statusText }
    };
    showErrors(data);
}

window.onload = function() {
    removeCommentsFallback();

    $(document).ajaxStart(function() { toggleForm(requestType) });
    $(document).ajaxStop(function() { toggleForm(requestType) });
    $(document).ajaxError(ajaxRequestError);
}