var itemCounter = 1;
var itemMainImage = 1;
var value;
var module;
var position;
var acceptFile = 0;
var selectVideo;
var selectGallary;

var id_module_page = 0;

var id_tag;


//function upload image
function uploadMultiImage($p, $i, $pd, $form, $formName) {

    var url = $("#data-url-multi-img").attr("url")
    var id = $i
    var parent = $p
    var formData = new FormData($("form")[0]);
    var containerContent = $("#" + $pd).find("#main-image-" + parent + "-" + id).html();


    // console.log(formData);
    $.ajax({
        url: url + "&formName=" + $formName + "&id=" + $i,  //Server script to process data
        type: "POST",
        data: formData,
        beforeSend: function () {
            $("#" + $pd).find("#main-image-" + parent + "-" + id).html("<img src=\''.\yii\helpers\Url::to('@web/images/loading.svg').'\' >");
        },
        success: function (response) {


            if (response.uploaded == 1) {
                $("#" + $pd).find("#main-image-" + parent + "-" + id).html("<img style=\'width:80px\' src=\'" + response.url + "\' >");
                containerContent = $("#" + $pd).find("#main-image-" + parent + "-" + id).html();

                $("#" + $pd).find("#" + $form + "-image_id-" + $p + "-" + $i).val(response.id);

            } else {
                alert(response.error.message);
                $("#" + $pd).find("#main-image-" + parent + "-" + id).html(containerContent);
            }


        },
        error: function () {
            alert("ERROR at PHP side!!");
        },
        //Options to tell jQuery not to process data or worry about content-type.
        cache: false,
        contentType: false,
        processData: false
    });


}

function openUploadMultiFile($p, $i, $pd, $form) {

    $("#" + $pd).find("#" + $form + "-mainimage-" + $p + "-" + $i).trigger('click');

}


function openUploadTheme(form) {
    $("#" + form + "-zipped_file").trigger('click');
}

function uploadTheme($form, $formName) {
    var url = $("#data-url-theme").attr("url");
    var webDir = $("#web-directory").val();
    var formData = new FouploadMultiImagermData($("form")[0]);

    $ImageSize = '';

    var containerContent = $("#load-svg-block").html();

    $.ajax({
        url: url + "&formName=" + $formName,  //Server script to process data
        type: "POST",
        data: formData,

        beforeSend: function () {
            $("#load-svg-block").html('<img src="' + webDir + 'images/loading.svg" >');
        },

        success: function (response) {
            var url = $("#data-url-upload-zip").attr("url");
            if (response.uploaded == 1) {
                $("#load-svg-block").html('');
                $.ajax({
                    url: url,
                    type: "POST",
                    data: {
                        "file_id": response.id
                    },
                    success: function (r) {
                        if (r) {
                            alert('تم با موفقیت ذخیره شد');
                        } else {
                            alert('تم ذخیره نشد!!');
                        }
                    }
                });
            } else {
                alert(response.error.message);
                $("#load-svg-block").html(containerContent);
            }


        },
        error: function () {
            alert("ERROR at PHP side!!");
        },
        //Options to tell jQuery not to process data or worry about content-type.
        cache: false,
        contentType: false,
        processData: false
    });


}


function openUploadFileForItems($p, $i, $pd, $form) {

    $("#" + $pd).find("#" + $form + "-mainfile-" + $p + "-" + $i).trigger('click');

}

function uploadFileForItems($p, $i, $pd, $form, $formName) {
    var url = $("#data-url-file-for-items").attr("url");
    var id = $i
    var parent = $p
    var formData = new FormData($("form")[0]);
    var containerContent = $("#" + $pd).find("#main-file-" + parent + "-" + id).html();
    var downloadFileTranslate = $('#add-item').attr('download-file');


    $ImageSize = '';


    $.ajax({
        url: url + "&formName=" + $formName + "&id=" + $i,  //Server script to process data
        type: "POST",
        data: formData,

        beforeSend: function () {
            $("#" + $pd).find("#main-file-" + parent + "-" + id).html("<img src=\''.\yii\helpers\Url::to('@web/images/loading.svg').'\' >");
        },

        success: function (response) {
            if (response.uploaded == 1) {
                $("#" + $pd).find("#main-file-" + parent + "-" + id).html("<a href=\'" + response.url + "\' class='btn btn-success'>" + downloadFileTranslate + "</a>");
                containerContent = $("#" + $pd).find("#main-file-" + parent + "-" + id).html();

                $("#" + $pd).find("#" + $form + "-file_id-" + $p + "-" + $i).val(response.id);

            } else {
                alert(response.error.message);
                $("#" + $pd).find("#main-file-" + parent + "-" + id).html(containerContent);
            }


        },
        error: function () {
            alert("ERROR at PHP side!!");
        },
        //Options to tell jQuery not to process data or worry about content-type.
        cache: false,
        contentType: false,
        processData: false
    });


}


function openUploadFile(form) {
    $("#" + form + "-mainimage").trigger('click');

}

function uploadImage($form, $formName, $service) {
    var url = $("#data-url-img").attr("url");
    var webDir = $("#web-directory").val();
    if ($form == 'category' && $service == null) {
        $main = 'main-image-category';
        var formData = new FormData($("form")[1]);
    } else {
        $main = 'main-image';
        var formData = new FormData($("form")[0]);
    }
    $ImageSize = '';
    if ($form != 'news' && $form != 'advertisement') {
        $ImageSize = 'width:80px;'
    }


    else {
        $ImageSize = 'margin-top:10px';
    }
    var containerContent = $("#" + $main).html();

    $.ajax({
        url: url + "&formName=" + $formName,  //Server script to process data
        type: "POST",
        data: formData,

        beforeSend: function (e) {
            $('#' + $main).html('<img src="' + webDir + 'images/loading.svg" >');
        },

        success: function (response) {

            if (response.uploaded == 1) {
                $("#" + $main).html("<img  style=\'" + $ImageSize + "\'  src=\'" + response.url + "\' >");
                containerContent = $("#" + $main).html();
                $("#" + $form + "-image_id").val(response.id);
            } else {
                alert(response.error.message);
                $("#" + $main).html(containerContent);
            }


        },
        error: function () {
            alert("ERROR at PHP side!!");
        },
        //Options to tell jQuery not to process data or worry about content-type.
        cache: false,
        contentType: false,
        processData: false
    });


}

function uploadMulti($form, $formName) {
    var url = $("#upload-multi-image").attr("url");
    var formData = new FormData($("form")[0]);
    var containerContent = $("#image-gallary").html();
    var webDir = $("#web-directory").val();
    $.ajax({
        url: url + "&formName=" + $formName,  //Server script to process data
        type: "POST",
        data: formData,
        beforeSend: function () {
            $("#image-gallary").prepend('<div id="loading" class="col-md-6 " style="margin-top:10px;"><img id="loading" src="' + webDir + 'images/loading.svg" ></div>');
        },
        success: function (response) {
            if (response.uploaded == 0) {
                alert(response.error.message);
            } else {
                $("#image-gallary").find("#loading").remove();
                $.each(response, function (key, val) {
                    if (response[key].uploaded == 1) {
                        $("#image-gallary").prepend('<div this-id="' + response[key].id + '" this-image="image-' + response[key].id + '" class="col-md-6 contain-image-gallary" style="margin-top:10px;"><div class="hidden"><input typ="text" class="input-image-gallary" name="News[images][]" value="' + response[key].id + '"></div><button  type="button" class="btn btn-danger btn-sm remove-image-gallary" this-image="image-' + response[key].id + '"><i class="fa fa-close"></i></button><img src="' + response[key].url + '" ></div>')
                    }
                })
            }

        },
        error: function () {

            alert("ERROR at PHP side!!");
        },
        //Options to tell jQuery not to process data or worry about content-type.
        cache: false,
        contentType: false,
        processData: false
    });

}

function openMultiUpload(form) {
    $("#" + form + "-files").trigger('click');
}

function openUploadVideo(form) {
    $("#" + form + "-main_video").trigger('click');
}

function uploadVideo(form, formName) {
    var url = $("#ajax-upload-url").attr("url");
    var selector = 'main-video';
    var formData = new FormData($("form")[0]);
    var containerContent = $("#" + selector).html();
    var webDir = $("#web-directory").val();

    // console.log(formData);
    $.ajax({
        url: url + "?key=main_video&formName=" + formName,  //Server script to process data
        type: "POST",
        data: formData,
        beforeSend: function () {
            $('.my-box-video-' + form).html('<div class="progress"><div class="progress-bar progress-bar-success progress-bar-striped active" role="progressbar" aria-valuenow="40" aria-valuemin="0" aria-valuemax="100" style="width: 0%;"></div></div>');
            $('.my-box-video-' + form + '.progress .progress-bar').animate({width: "30%"}, 100);
            $('#' + selector).html('<img src="' + webDir + 'images/loading.svg" >');
            //$('#' + selector).html('<img src="'+webDir+'/images/loading.svg" >');
        },
        success: function (response) {
            setTimeout(function () {
                $('.my-box-video-' + form + '.progress .progress-bar').css({width: "100%"});
                setTimeout(function () {
                    if (response.uploaded == 1) {
                        $("#" + selector).html("<video src=\'" + response.url + "\' controls>");
                        containerContent = $("#" + selector).html();
                        $("#" + form + "-video_id").val(response.id);
                    } else {
                        alert(response.error.message);
                        $("#" + selector).html(containerContent);
                    }
                    $('.my-box-video-' + form + '  .progress').addClass("hidden");
                }, 100);
            }, 500);

        },
        error: function () {
            alert("ERROR at PHP side!!");
        },
        //Options to tell jQuery not to process data or worry about content-type.
        cache: false,
        contentType: false,
        processData: false
    });


}

function deleteSettingImage($form, $image, $main) {
    $("#" + $form + "-" + $image).val(1);
    $(this).addClass('hidden');
    $("#" + $main).html('');
}

function openUploadFileSetting($form, $image) {
    //  alert( $("#"+$form+"-"+$image).length)
    $("#" + $form + "-" + $image).trigger('click');

}

function uploadImageSetting($form, $formName, $main, $key, $image) {
    var url = $("#data-url-img-setting").attr("url")
    var formData = new FormData($("form")[0]);
    $ImageSize = 'width:100px;'
    var containerContent = $("#" + $main).html();

    // console.log(formData);
    $.ajax({
        url: url + "&formName=" + $formName + "&key=" + $key,  //Server script to process data
        type: "POST",
        data: formData,
        beforeSend: function () {


        },
        success: function (response) {


            if (response.uploaded == 1) {
                $("#" + $main).html("<img  style=\'" + $ImageSize + "\'  src=\'" + response.url + "\' >");
                containerContent = $("#" + $main).html();
                $("#" + $form + "-" + $image).val(response.id);
                $("#" + $image + "-delete-button" ).removeClass('hidden');
            } else {
                alert(response.error.message);
                $("#" + $main).html(containerContent);
            }


        },
        error: function () {
            alert("ERROR at PHP side!!");
        },
        //Options to tell jQuery not to process data or worry about content-type.
        cache: false,
        contentType: false,
        processData: false
    });
}

// function progress(e){
//
//     if(e.lengthComputable){
//         var max = e.total;
//         var current = e.loaded;
//
//         var Percentage = (current * 100)/max;
//         console.log(Percentage);
//
//
//         if(Percentage >= 100)
//         {
//             // process completed
//         }
//     }
// }

$(document).ready(function () {
    $("#remove-image").click(function () {
        $("#main-image").html("");
        $("#news-image_id").val("");
    });
    $("#remove-video").click(function () {
        $("#main-video").html("");
        $("#news-video_id").val("");
    });

    $(".close-modal-image").click(function () {
        $("#selectImageModal-multi .image-box img").removeClass("selected");
        $("#selectImageModal-multi .image-box").removeClass("one-selected");
        $("#selectImageModal-multi #btn-select-files").removeClass("one-selected");
        $("#selectImageModal-multi .overlay").addClass("hidden");
        $("#selectImageModal-multi").modal("hide");
        
        $("#selectImageModal-one .image-box img").removeClass("selected");
        $("#selectImageModal-one .image-box").removeClass("one-selected");
        $("#selectImageModal-one #btn-select-files").removeClass("one-selected");
        $("#selectImageModal-one .overlay").addClass("hidden");
        $("#selectImageModal-one").modal("hide");
    });
    $("#select-one-image").click(function () {
        $("#selectImageModal-multi .image-box").addClass("one-selected");
        $("#selectImageModal-multi #btn-select-files").addClass("one-selected");
        $("#selectImageModal-multi").modal("show");
        
        $("#selectImageModal-one .image-box").addClass("one-selected");
        $("#selectImageModal-one #btn-select-files").addClass("one-selected");
        $("#selectImageModal-one").modal("show");
    });

    $("#select-multi-image").click(function () {
        $("#selectImageModal-multi").modal("show");
        
        $("#selectImageModal-one").modal("show");
    });
    $("#selectImageModal-multi").on("click", ".image-box", function () {
        if ($(this).hasClass("one-selected")) {
            $("#selectImageModal-multi .image-box img").removeClass("selected");
            $("#selectImageModal-multi .overlay").addClass("hidden");
            
            $("#selectImageModal-one .image-box img").removeClass("selected");
            $("#selectImageModal-one .overlay").addClass("hidden");
        }
        if ($(this).find("img").hasClass("selected")) {
            if ($(this).hasClass("one-selected")) {
                $("#btn-select-files").attr("this-src", "");
            }
            $("#btn-select-files").attr("this-id", "");
            $(this).find("img").removeClass("selected");
            $(this).find(".overlay").addClass("hidden");
        } else {
            if ($(this).hasClass("one-selected")) {
                $("#btn-select-files").attr("this-src", $(this).attr("this-src")).attr("this-id", $(this).attr("this-id"));
            }
            $(this).find("img").addClass("selected");
            $(this).find(".overlay").removeClass("hidden");
        }
    });
    $("#selectImageModal-one").on("click", ".image-box", function () {
        if ($(this).hasClass("one-selected")) {
            $("#selectImageModal-multi .image-box img").removeClass("selected");
            $("#selectImageModal-multi .overlay").addClass("hidden");
            
            $("#selectImageModal-one .image-box img").removeClass("selected");
            $("#selectImageModal-one .overlay").addClass("hidden");
        }
        if ($(this).find("img").hasClass("selected")) {
            if ($(this).hasClass("one-selected")) {
                $("#btn-select-files").attr("this-src", "");
            }
            $("#btn-select-files").attr("this-id", "");
            $(this).find("img").removeClass("selected");
            $(this).find(".overlay").addClass("hidden");
        } else {
            if ($(this).hasClass("one-selected")) {
                $("#btn-select-files").attr("this-src", $(this).attr("this-src")).attr("this-id", $(this).attr("this-id"));
            }
            $(this).find("img").addClass("selected");
            $(this).find(".overlay").removeClass("hidden");
        }
    });
    });
