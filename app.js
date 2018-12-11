$(document).ready(function() {

    "use strict";

    /**
     * Email Validation RegEx
     */
    var email_regex = /(?:[a-z0-9!#$%&'*+/=?^_`{|}~-]+(?:\.[a-z0-9!#$%&'*+/=?^_`{|}~-]+)*|"(?:[\x01-\x08\x0b\x0c\x0e-\x1f\x21\x23-\x5b\x5d-\x7f]|\\[\x01-\x09\x0b\x0c\x0e-\x7f])*")@(?:(?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\.)+[a-z0-9](?:[a-z0-9-]*[a-z0-9])?|\[(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?|[a-z0-9-]*[a-z0-9]:(?:[\x01-\x08\x0b\x0c\x0e-\x1f\x21-\x5a\x53-\x7f]|\\[\x01-\x09\x0b\x0c\x0e-\x7f])+)\])/;

    /**
     * Master Slider Setup
     */
    var slider = new MasterSlider();
    // adds Arrows navigation control to the slider.
    slider.control('arrows');
    slider.control('bullets');

    slider.setup('masterslider', {
        width: 1600, // slider standard width
        height: 650, // slider standard height
        space: 0,
        speed: 45,
        layout: 'fullwidth',
        loop: true,
        preload: 0,
        autoplay: true,
        view: "parallaxMask"
    });

    // portfolio slider
    $(document).ready(function(){
        lightGallery(document.getElementById('lightgallery'));
    });

    /**
     * Slick Slider Instantiation
     */
    $('.single-item').slick({
        dots: true,
        prevArrow: $('.prev'),
        nextArrow: $('.next'),
        infinite: true,
        speed: 700,
        slidesToShow: 1,
        slidesToScroll: 1,
        autoplay:!0
    });

    $('.responsive').slick({
        dots: true,
        prevArrow: $('.prev'),
        nextArrow: $('.next'),
        infinite: true,
        speed: 700,
        slidesToShow: 6, // Home Page Clients Slider 
        slidesToScroll: 1,
        autoplay: true,
        responsive: [{
                breakpoint: 1024,
                settings: {
                    slidesToShow: 4,
                    slidesToScroll: 3,
                    infinite: true,
                    dots: true
                }
            },
            {
                breakpoint: 600,
                settings: {
                    slidesToShow: 2,
                    slidesToScroll: 2
                }
            },
            {
                breakpoint: 480,
                settings: {
                    slidesToShow: 1,
                    slidesToScroll: 1
                }
            }
            // You can unslick at a given breakpoint now by adding:
            // settings: "unslick"
            // instead of a settings object
        ]
    });

    $('.responsive1').slick({
        dots: true,
        prevArrow: $('.prev'),
        nextArrow: $('.next'),
        infinite: true,
        speed: 700,
        slidesToShow: 3,
        slidesToScroll: 1,
        autoplay: !0,
        responsive: [{
                breakpoint: 1024,
                settings: {
                    slidesToShow: 2,
                    slidesToScroll: 1,
                    infinite: true,
                    dots: true
                }
            },
            {
                breakpoint: 600,
                settings: {
                    slidesToShow: 2,
                    slidesToScroll: 1
                }
            },
            {
                breakpoint: 480,
                settings: {
                    slidesToShow: 1,
                    slidesToScroll: 1
                }
            }
            // You can unslick at a given breakpoint now by adding:
            // settings: "unslick"
            // instead of a settings object
        ]
    });

    // Slideshow 1
    $("#slider").slick({
        autoplay: true,
        fade: true,
        infinite: true,
        autoplaySpeed: 2500,
        prevArrow: false,
        nextArrow: false,
        cssEase: 'ease-in-out'
    });

    /**
     * Yet to be documented
     */
    $(document).on('click', '.language-id1', function() {
        $(".langauage-select1").toggleClass("hide-dropdown");
    });

    $(".dropdown-menu.langauage-select li a").click(function() {
        var selText = $(this).text();
        $(this).parents('.language-id').find('.dropdown-toggle').html(selText);
    });

    $(".dropdown-menu.langauage-select1 li a").click(function() {
        var selText = $(this).text();
        $(this).parents('.language-id1').find('.dropdown-toggle').html(selText);
    });

    /**
     * Dropdown on Services Page
     */
    $(document).on('click', '#top-nav li.dropdown', function() {
        $("#top-nav li.dropdown").toggleClass("bg-focus");
    });

    $('#top-nav li.dropdown > a.active').on('click', function(event) {
        event.preventDefault()
        $(this).parent().find('ul').first().toggle(300);
        $(this).parent().siblings().find('ul').hide(200);
        //Hide menu when clicked outside
        $(this).parent().find('ul').mouseleave(function() {
            var thisUI = $(this);
            $('html').click(function() {
                thisUI.hide();
                $('html').unbind('click');
            });
        });
    });

    /**
     * Contact Us Form Submission
     */
    $("#contact_submit").on("click", function(e) {
        e.preventDefault();
        var form_flag = true;
        $(".contactus-form").find('input').each(function(idx, elem) {
            if($(elem).val().length == 0) {
                $(elem).addClass('error-fields');
                $(elem).parent().next().html('<p class="alert alert-danger">Field is required!</p>');
                form_flag = false;
            } else {
                $(elem).removeClass('error-fields');
                $(elem).parent().next().html('');
            }
        });
        if ($("#sendertype").val().length == 0) {
            $("#sendertype").addClass('error-fields');
            $("#sendertype").parent().next().html('<p class="alert alert-danger">Field is required!</p>');
            form_flag = false;
        } else {
            $("#sendertype").removeClass('error-fields');
            $("#sendertype").parent().next().html('');
        }
        if ($("#sendermessage").val().length == 0) {
            $("#sendermessage").addClass('error-fields');
            $("#sendermessage").parent().next().html('<p class="alert alert-danger">Field is required!</p>');
            form_flag = false;
        } else {
            $("#sendertype").removeClass('error-fields');
            $("#sendertype").parent().next().html('');
        }
        if (form_flag) {
            $.ajax({
                url: $(".contactus-form").attr('action'),
                type: 'POST',
                beforeSend: function() {
                    $(".loader-overlay").toggleClass("hidden");
                },
                complete: function() {
                    $(".loader-overlay").toggleClass("hidden");
                },
                data: $(".contactus-form").serialize(),
                success: function(data) {
                    $(".contact_us_message_container").html(data.text);
                    grecaptcha.reset();
                    $("#contact_submit").attr('disabled', true);
                    $(".errorBag").html('');
                    $(".contactus-form")[0].reset();
                    $('html, body').animate({ scrollTop: $(".contactus-form").offset().top - 100 }, 'slow');
                },
                error: function(res) {
                    var errors = res.responseJSON.text;
                    $.each(errors, function (key, val) {
                        var id = '#'+ key + '-errorbag';
                        $('#'+ key).addClass('error-fields');
                        $(id).html('<p class="alert alert-danger">'+ val +'</p>');
                        $('html, body').animate({ scrollTop: $(".contactus-form").closest(".container").offset().top }, 'slow');
                        grecaptcha.reset();
                        $("#contact_submit").attr('disabled', true);
                    });
                }
            });
        } else {
            grecaptcha.reset();
            $("#contact_submit").attr('disabled', true);
            $('html, body').animate({ scrollTop: $(".contactus-form").closest(".container").offset().top }, 'slow');
        }
    });

    /**
     * Request a Quote Submit Form
     */
    $("#request-quote-submit-btn").on("click", function(e) {
        e.preventDefault();
        $.ajax({
            url: $(".requestquote-form").attr('action'),
            type: 'POST',
            beforeSend: function() {
                $(".loader-overlay").toggleClass("hidden");
                $(".errorBag").html('');
                $(".requestquote-form").find('.error-fields').removeClass('error-fields');
            },
            complete: function() {
                $(".loader-overlay").toggleClass("hidden");
            },
            data: $(".requestquote-form").serialize(),
            success: function(data) {
                $(".requestquote_message_container").html(data.text);
                grecaptcha.reset();
                $("#request-quote-submit-btn").attr('disabled', true);
                $(".errorBag").html('');
                $(".requestquote-form")[0].reset();
                $('html, body').animate({ scrollTop: $(".contact-form").offset().top - 100 }, 'slow');
            },
            error: function(res) {
                var errors = res.responseJSON.text;
                $.each(errors, function (key, val) {
                    var id = '#'+ key + '-errorbag';
                    $('#'+ key).addClass('error-fields');
                    $(id).html('<p class="alert alert-danger">'+ val +'</p>');
                    $('html, body').animate({ scrollTop: $(".requestquote-form").closest(".container").offset().top }, 'slow');
                    grecaptcha.reset();
                    $("#request-quote-submit-btn").attr('disabled', true);
                });
            }
        });
    });

    $("#looking_for").on("change", function () {
        var selected_val = $(this).val();
        $(".needs_container").empty();
        if (selected_val == 'services') {
            var response_html = '';
            services.forEach(function (element) {
                response_html += '<label class="field prepend-icon"><input class="request-checkbox" type= "checkbox" name= "senderneed[]" value= "' + element + '" >' + element + '</label >';
            });
        } else {
            var response_html = '';
            products.forEach(function (element) {
                response_html += '<label class="field prepend-icon"><input class="request-checkbox" type= "checkbox" name= "senderneed[]" value= "' + element + '" >' + element + '</label >';
            });
        }
        $(".needs_container").html(response_html);
    });

    /**
     * Horizontal Request Quote Form
     */
    $(".form-horz-quote").on("submit", function(e) {
        
        var url_link = $(this).attr('action');
        var data = $(this).serialize();
        $.ajax({
            url: $(this).attr('action'),
            type: 'POST',
            beforeSend: function() {
                $(".loader-overlay").toggleClass("hidden");
            },
            complete: function() {
                $(".loader-overlay").toggleClass("hidden");
            },
            data: $(this).serialize() + "&url_link="+url_link,

            success: function(data) {
                //alert(data.text);
                $(".modal-body").html(data.text);
                $('.modal').on('hidden.bs.modal', function () {
                    location.reload();
                    //console.log(data);
                });
            }
        
        });
    });

    /**
     * Validation before submitting Request Info Horizontal Form
     */
    $(".pre-request-info").on("click", function (e) {
        e.preventDefault();
        $(".message-bag").html("");
        var form_flag = true;
        var input_source = '';
        var parent_form = $(this).closest('.form-horz-quote');
        $(parent_form).find('input').each(function(idx, elem) {
            if($(elem).val().length == 0) {
                $(elem).addClass('horz-error-fields');
                form_flag = false;
                input_source = "Invalid Inputs!";
            } else {
                $(elem).removeClass('horz-error-fields');
            }
        });
        var phone = $(parent_form).find('input[type="tel"]');
        if (isNaN($(phone).val())) {
            $(phone).addClass('horz-error-fields');
            form_flag = false;
            input_source = "Invalid Phone Number!";
        }
        var email = $(parent_form).find('input[type="email"]');
        if (! email_regex.test($(email).val())) {
            $(email).addClass('horz-error-fields');
            form_flag = false;
            input_source = "Invalid Email!";
        }
        var sender_type = $(parent_form).find("#sendertype");
        if ($(sender_type).val().length == 0) {
            $(sendertype).addClass('horz-error-fields');
            form_flag = false;
            input_source = "Please Select Service/Product!";
        } else {
            $(sendertype).removeClass('horz-error-fields');
        }
        if (form_flag) {
            var modal_id = $(this).attr('data-target');
            $(modal_id).modal('show');
        } else {
            alert(input_source);
            
        }
    });

    /**
     * Toggle Language
     */
    $(".toggleLanguage").on("click", function(e) {
        e.preventDefault();
        var csrf_name = $('meta[name="csrf-token"]').attr('data-name');
        var csrf_value = $('meta[name="csrf-token"]').attr('content');
        var payload = {};
        payload['lang'] = $(e.target).attr('data-lang');
        payload[csrf_name] = csrf_value;
        $.ajax({
            url: $(e.target).attr('data-url'),
            type: 'POST',
            data: payload,
            success: function(data) {
                location.reload();
            }
        });
    });

    /**
     * Careers Pagination
     */
    $('#careers_pagination').twbsPagination({
        initiateStartPageClick: false,
        totalPages: $("#careers_total_page_count").val(),
        visiblePages: 7,
        firstClass: 'page-link',
        lastClass: 'page-link',
        nextClass: 'page-link',
        prevClass: 'page-link',
        onPageClick: function(event, page) {
            var csrf_name = $('meta[name="csrf-token"]').attr('data-name');
            var csrf_value = $('meta[name="csrf-token"]').attr('content');
            var payload = {};
            payload['offset'] = page;
            payload[csrf_name] = csrf_value;
            $(".current_page").html(page);
            $.ajax({
                url: $("#careers_path").val(),
                type: 'POST',
                data: payload,
                beforeSend: function() {
                    $(".loader-overlay").toggleClass("hidden");
                },
                complete: function() {
                    $(".loader-overlay").toggleClass("hidden");
                },
                success: function(data) {
                    $(".current_page").html(page);
                    var response = '';
                    $.each(data, function(idx, obj) {
                        response += '<li class="careers-block panel panel-default">' +
                            '<div class="panel-heading" role="tab" id="headingFour">' +
                            '<a role="button" data-toggle="collapse" data-parent="#accordion" href="#collapse' + obj['job_code'] + '" aria-expanded="true" aria-controls="collapse' + obj['job_code'] + '">' +
                            '<i class="more-less fa fa-plus"></i>' +
                            '<h4 class="careers-list font-weight-1">' + obj['job_title'] + '</h4>' +
                            '</a>' +
                            '<button class="btn btn-primary apply-post" data-toggle="modal" data-target="#post' + obj['job_code'] + '">Apply Now</button>' +
                            '<!-- Modal -->' +
                            '<div class="modal careers-modal fade" id="post' + obj['job_code'] + '">' +
                            '<div class="modal-dialog" role="document">' +
                            '<div class="modal-content">' +
                            '<div class="modal-header">' +
                            '<button type="button" class="close" data-dismiss="modal" aria-label="Close">' +
                            '<span aria-hidden="true"><i class="fa fa-close"></i></span>' +
                            '</button>' +
                            '</div>' +
                            '<div class="modal-body">' +
                            '<div class="content-header">' +
                            '<h1 class="paddtop1 font-weight-5 text-center" id="jobHeader' + obj['job_code'] +'">Apply for this Job</h1>' +
                            '<p class="text-center" id="jobDesc' + obj['job_code'] +'">Leave your contacts and our managers will contact you soon.</p>' +
                            '</div>' +
                            '<div class="careers-post">' +
                            '<h4>' + obj['job_title'] + '</h4>' +
                            '</div>' +
                            '<div class="smart-forms" class="applypost-form-container">' +
                            '<form class="applypost-form" method="post" action="' + obj['form_url'] + '" id="smart-form">' +
                            '<div>' +
                            '<div class="section">' +
                            '<label class="field prepend-icon" for="name">Name <span class="red">*</span></label>' +
                            '<input type="text" name="name" id="senderfirstname" class="gui-input" placeholder="Name" />' +
                            '</div>' +
                            '<!-- end section -->' +
                            '<div class="section">' +
                            '<label class="field prepend-icon" for="email">Email <span class="red">*</span></label>' +
                            '<input type="email" name="email" id="emailaddress" class="gui-input" placeholder="Email" />' +
                            '</div>' +
                            '<!-- end section -->' +
                            '<div class="section">' +
                            '<label class="field prepend-icon" for="phone">Phone <span class="red">*</span></label>' +
                            '<input type="tel" name="phone" id="telephone" class="gui-input" placeholder="Phone" />' +
                            '</div>' +
                            '<!-- end section -->' +
                            '<div class="section section-fileupload">' +
                            '<label class="field prepend-icon" for="exp">Experience in Years <span class="red">*</span></label>' +
                            '<input type="number" name="exp" id="exp" class="gui-input" placeholder="Experience In Years" />' +
                            '</div>' +
                            '<div class="section section-fileupload">' +
                            '<label class="field prepend-icon" for="resume">Resume <span class="red">*</span></label>' +
                            '<input type="file" name="resume" id="resume" class="gui-input" />' +
                            '</div>' +
                            '<!-- end section -->' +
                            '<div class="section section-captcha">' +
                            '<div class="g-recaptcha" data-callback="storeCaptchaResponse"></div>' +
                            '</div>' +
                            '<input type="hidden" id="job-code-' + obj['job_code'] + '" class="job-code" name="job_code" value="' + obj['job_code'] + '">' +
                            '<div class="result"></div>' +
                            '</div>' +
                            '<div class="form-footer">' +
                            '<button type="submit" data-btntext-sending="Sending..." class="button btn-primary career-apply-btn">Apply Now</button>' +
                            '</div>' +
                            '</form>' +
                            '</div>' +
                            '</div>' +
                            '</div>' +
                            '</div>' +
                            '</div>' +
                            '<!-- end modal -->' +
                            '</div>' +
                            '<div id="collapse' + obj['job_code'] + '" class="panel-collapse collapse" role="tabpanel" aria-labelledby="heading' + obj['job_code'] + '">' +
                            '<div class="panel-body">' + obj['job_description'] +
                            '</div>' +
                            '</div>' +
                            '</li>';
                    });
                    $("#career-accordion").html(response);
                    $('.g-recaptcha').each(function() {
                        grecaptcha.render(this, {
                            'sitekey': $("#grsk").val()
                        });
                    });
                }
            });
        }
    });

    /**
     * Careers Application
     */
    $("#career-accordion").on("click", ".career-apply-btn", function(e) {
        e.preventDefault();
        var _this = $(e.target);
        var link = $(e.target).closest(".applypost-form").attr('action');
        var name = $(e.target).closest(".applypost-form").find("#senderfirstname").val();
        var email = $(e.target).closest(".applypost-form").find("#emailaddress").val();
        var phone = $(e.target).closest(".applypost-form").find("#telephone").val();
        var exp = $(e.target).closest(".applypost-form").find("#exp").val();
        var file = $(e.target).closest(".applypost-form").find("#resume");
        var job_code = $(e.target).closest(".applypost-form").find("[name='job_code']").val();
        var csrf_name = $('meta[name="csrf-token"]').attr('data-name');
        var csrf_value = $('meta[name="csrf-token"]').attr('content');
        var re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
        var allowed_types = [
            'application/msword',
            'application/pdf',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
        ];

        // Validation
        if (!name.length) {
            alert("Invalid Name!");
            $(e.target).closest(".applypost-form").find("#senderfirstname").addClass("alert-error");
            $(e.target).closest(".applypost-form").find("#senderfirstname").val('');
            return false;
        }

        if (!re.test(email)) {
            alert("Invalid Email!");
            $(e.target).closest(".applypost-form").find("#emailaddress").addClass("alert-error");
            $(e.target).closest(".applypost-form").find("#emailaddress").val('');
            return false;
        }

        if (isNaN(phone)) {
            alert("Invalid Phone Number!");
            $(e.target).closest(".applypost-form").find("#telephone").addClass("alert-error");
            $(e.target).closest(".applypost-form").find("#telephone").val('');
            return false;
        }

        if (!$(e.target).closest(".applypost-form").find("#resume").val().length) {
            alert("Resume not uploaded!");
            return false;
        } else {
            if ($(e.target).closest(".applypost-form").find("#resume")[0].files[0].size > 1000000) {
                alert('Resume Size should not be greater than 1 MB');
                $(e.target).closest(".applypost-form").find("#resume").val('');
                return false;
            }
            if (!(allowed_types.indexOf($(e.target).closest(".applypost-form").find("#resume")[0].files[0].type) > -1)) {
                alert('Resume Should be either in a word or pdf format');
                $(e.target).closest(".applypost-form").find("#resume").val('');
                return false;
            }
        }

        var formdata = new FormData();
        formdata.append('name', name);
        formdata.append('email', email);
        formdata.append('phone', phone);
        formdata.append('exp', exp);
        formdata.append('resume', $(e.target).closest(".applypost-form").find("#resume")[0].files[0]);
        formdata.append('job_code', job_code);
        formdata.append(csrf_name, csrf_value);
        formdata.append('g-recaptcha-response', localStorage.getItem('captcha_response'));

        $.ajax({
            url: link,
            type: "POST",
            data: formdata,
            contentType: false,
            enctype: 'multipart/form-data',
            processData: false,
            beforeSend: function() {
                $(".loader-overlay").toggleClass("hidden");
                $("p.error-text").remove();
            },
            success: function(data) {
                $(e.target).closest(".applypost-form").parent().html(data.text);
                $("#jobHeader" + job_code).text("Success!");
                $("#jobDesc" + job_code).text("Your Application has been submitted successfully!");
                $('.modal').on('hidden.bs.modal', function (e) {
                    location.reload();
                })
            },
            error: function(res) {
                grecaptcha.reset();
                $.each(res.responseJSON.text, function (idx, el) {
                    $(_this).closest(".applypost-form").find("[name='" + idx + "']").addClass('horz-error-fields');
                    $(_this).closest(".applypost-form").find("[name='" + idx + "']").after("<p class='error-text'>" + el + "</p>");
                });
            },
            complete: function() {
                $(".loader-overlay").toggleClass("hidden");
            },
        });
    });

    /**
     * Work Process Page
     */
    $(".commonClass").mouseover(function()
    {  
        $(".commonClass").css("border", "15px solid #e8e8e8");
        $(".phase-listing").css("opacity", "0.5");
        $(".phase-listing1").css("opacity", "0.5");
        if($(this).hasClass("phase1-mainhead"))
        {
            $(this).css("border", "15px solid #ee1012");
            $(this).next().children().css("opacity", "1");
        }
        else if($(this).hasClass("phase2-mainhead"))
        {
            $(this).css("border", "15px solid #f9cb0b");
            $(this).next().children().css("opacity", "1");
        }
        else if($(this).hasClass("phase3-mainhead"))
        {
            $(this).css("border", "15px solid #3a8ad9");
            $(this).next().children().css("opacity", "1");
        } 
        else if($(this).hasClass("phase4-mainhead1"))
        {
            $(this).css("border", "15px solid #95c82f");
            $(this).next().children().css("opacity", "1");
        }
        else if($(this).hasClass("phase4-mainhead2"))
        {
            $(this).css("border", "15px solid #95c82f");
            $(this).next().next().children().css("opacity", "1");
        }
        else{
            
        }
    });
    $(".commonClass").mouseleave(function() {
        $(".phase1-mainhead").css("border", "15px solid #ee1012");
        $(".phase1-mainhead").next().children().css("opacity", "1");
        $(".phase2-mainhead").css("border", "15px solid #f9cb0b");
        $(".phase2-mainhead").next().children().css("opacity", "1");
        $(".phase3-mainhead").css("border", "15px solid #3a8ad9");
        $(".phase3-mainhead").next().children().css("opacity", "1");
        $(".phase4-mainhead1").css("border", "15px solid #95c82f");
        $(".phase4-mainhead1").next().children().css("opacity", "1");
        $(".phase4-mainhead2").css("border", "15px solid #95c82f");
        $(".phase4-mainhead2").next().next().children().css("opacity", "1"); 
    });
});