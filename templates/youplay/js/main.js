(function () {

  "use strict";

  // Variables
  // =========================================================================================
  var $html = $('html'),
    $document = $(document),
    $window = $(window),
    i = 0;


  // Scripts initialize
  // ===================
  document.write('<script async src="https://www.youtube.com/iframe_api"></script>');

  $(window).on('load', function () {

    // =================================================================================
    // Preloader
    // =================================================================================
    var $preloader = $('#page-preloader');
    $preloader.delay(500).fadeOut('slow');

    setTimeout(function () {
      $(".countdown").addClass("scaled");
    }, 1000);

    var yt_player = $(".player");
    if (yt_player.length) {
      yt_player.mb_YTPlayer({
        mute: true,
        containment: '.video-wrapper',
        showControls: false,
        autoPlay: true,
        loop: true,
        startAt: 0,
        quality: 'default'
      });
      $(".btn-stop-video").on("click", function () {
        yt_player.YTPTogglePlay();
        $(this).toggleClass("paused");
      });
      $(".btn-mute-video").on("click", function () {
        yt_player.YTPToggleVolume();
        $(this).toggleClass("paused");
      });
    }


  });

  $document.ready(function () {

    // =================================================================================
    // Image Slider
    // =================================================================================
    var imgSlider = $('#slides');
    if (imgSlider.length) {
      imgSlider.superslides({
        animation: 'fade',
        play: 10000
      });
    }

    // =================================================================================
    // Contact Form
    // =================================================================================
    var contactForm = $(".contact-form, .question-form");
    if (contactForm.length) {
      var contactResault = $("body").append("<span class='form-resault'></span>").find(".form-resault");
      contactForm.each(function () {
        var this_form = $(this);
        var contactFormInput = this_form.find(".form-control.required");

        contactFormInput.on("blur", function () {
          if (!$.trim($(this).val())) {
            $(this).parent().addClass("input-error");
          }
        });

        contactFormInput.on("focus", function () {
          $(this).parent().removeClass("input-error");
        });

        this_form.on("submit", function () {
          var form_data1 = $(this).serialize();
          if (!contactFormInput.parent().hasClass("input-error") && contactFormInput.val()) {
            $.ajax({
              type: "POST",
              url: "php/contact.php",
              data: form_data1,
              success: function () {
                contactResault.addClass("correct");
                contactResault.html("Your data has been sent!");
                setTimeout(function () {
                  contactResault.removeClass("incorrect").removeClass("correct");
                }, 4500);
              }
            });
          } else {
            if (contactFormInput.val() === "") {
              var contactFormInputEmpty = contactFormInput.filter(function () {
                return $(this).val() === "";
              });
              contactFormInputEmpty.parent().addClass("input-error");
            }
            contactResault.addClass("incorrect");
            contactResault.html("You must fill in all required fields");
            setTimeout(function () {
              contactResault.removeClass("incorrect").removeClass("correct");
            }, 4500);
          }
          return false;
        });
      });
    }

    // =================================================================================
    // jQuery ajaxChimp
    // =================================================================================
    var chimpForm = $('.subscription-form form');
    chimpForm.ajaxChimp({
      callback: function () {
        var panel = $('.js-result');
        setTimeout(function () {
          panel.removeClass("error").removeClass("success");
        }, 4500);
      },
      language: 'cm',
      url: '//adr.us14.list-manage.com/subscribe/post?u=474217a166648c3e7e0c53b55&amp;id=57bd6ccefc'
      //XXX.us13.list-manage.com/subscribe/post?u=YYY&amp;id=ZZZ
    });
    $.ajaxChimp.translations.cm = {
      'submit': 'Submitting...',
      0: 'We have sent you a confirmation email',
      1: 'Please enter a value',
      2: 'An email address must contain a single @',
      3: 'The domain portion of the email address is invalid (the portion after the @: )',
      4: 'The username portion of the email address is invalid (the portion before the @: )',
      5: 'This email address looks fake or invalid. Please enter a real email address'
    };

    // =================================================================================
    // Countdown
    // =================================================================================
    var countDown = $('.countdown');
    if (countDown.length) {
      countDown.each(function () {
        var item = $(this),
          date = new Date(),
          settings = [],
          time = item[0].getAttribute('data-time'),
          type = item[0].getAttribute('data-type'),
          format = item[0].getAttribute('data-format');
        date.setTime(Date.parse(time)).toLocaleString();
        settings[type] = date;
        settings['format'] = format;
        item.countdown(settings);
      });
    }

    // =================================================================================
    // Swiper
    // =================================================================================
    var slider_main = $('.slider-main');
    if (slider_main.length) {
      var slider_main = new Swiper('.slider-main', {
        nextButton: '.arrow-next',
        prevButton: '.arrow-prev',
        speed: 600,
        autoHeight: true,
      });
      slider_main.slideTo(1, 1, true);
    }

    // =================================================================================
    // Color Switcher
    // =================================================================================
    var switcher = $("#style-switcher");
    var switcher_toggle = switcher.find(".toggle-switcher");
    if (switcher.length) {
      switcher_toggle.on("click", function (e) {
        e.preventDefault();
        switcher.toggleClass("active");
      });
      var color_stylesheet = $("#colors");
      var color_link = $("#style-switcher .colors > li > a");
      color_link.each(function () {
        var it = $(this);
        it.on("click", function () {
          var color_src = it.attr("data-color-src");
          color_stylesheet.attr("href", color_src);
          return false;
        });
      });
    };

    // =================================================================================
    // FSS
    // =================================================================================
    function initialise() {
      scene.add(mesh);
      scene.add(light);
      container.appendChild(renderer.element);
      window.addEventListener('resize', resize);
    }
    function resize() {
      var width = container.offsetWidth, // No need to query these twice, when in an onresize they can be expensive
        height = container.offsetHeight;
      renderer.setSize(width, height);
      scene.remove(mesh); // Remove the mesh and clear the canvas
      renderer.clear();
      geometry = new FSS.Plane(width, height, 10, 12); // Recreate the plane and then mesh
      mesh = new FSS.Mesh(geometry, material);
      scene.add(mesh); // Readd the mesh
    }
    function animate() {
      now = Date.now() - start;
      light.setPosition(300 * Math.sin(now * 0.001), 200 * Math.cos(now * 0.0005), 60);
      renderer.render(scene);
      requestAnimationFrame(animate);
    }
    var canvasAnim = $('.fss');
    if (canvasAnim.length) {
      var container = document.getElementById('fss'),
        renderer = new FSS.CanvasRenderer(),
        scene = new FSS.Scene(),
        light = new FSS.Light('#111122', '#00C5FF'),
        geometry = new FSS.Plane(container.offsetWidth, container.offsetHeight, 10, 12),
        material = new FSS.Material('#FFFFFF', '#FFFFFF'),
        mesh = new FSS.Mesh(geometry, material),
        now, start = Date.now();

      initialise();
      resize();
      animate();
    }

    // =================================================================================
    // Backgound gradient
    // =================================================================================
    var bg_grad = $('.bg-gradient');
    var colors = new Array(
      [62, 35, 255],
      [60, 255, 60],
      [255, 35, 98],
      [45, 175, 230],
      [255, 0, 255],
      [255, 128, 0]);
    var step = 0;
    var colorIndices = [0, 1, 2, 3];
    //transition speed
    var gradientSpeed = 0.002;

    function updateGradient() {
      var c0_0 = colors[colorIndices[0]];
      var c0_1 = colors[colorIndices[1]];
      var c1_0 = colors[colorIndices[2]];
      var c1_1 = colors[colorIndices[3]];

      var istep = 1 - step;
      var r1 = Math.round(istep * c0_0[0] + step * c0_1[0]);
      var g1 = Math.round(istep * c0_0[1] + step * c0_1[1]);
      var b1 = Math.round(istep * c0_0[2] + step * c0_1[2]);
      var color1 = "rgb(" + r1 + "," + g1 + "," + b1 + ")";

      var r2 = Math.round(istep * c1_0[0] + step * c1_1[0]);
      var g2 = Math.round(istep * c1_0[1] + step * c1_1[1]);
      var b2 = Math.round(istep * c1_0[2] + step * c1_1[2]);
      var color2 = "rgb(" + r2 + "," + g2 + "," + b2 + ")";

      bg_grad.css({ background: "-webkit-gradient(linear, left top, right top, from(" + color1 + "), to(" + color2 + "))" });
      bg_grad.css({ background: "-moz-linear-gradient(left, " + color1 + " 0%, " + color2 + " 100%)" });
      bg_grad.css({ background: "-ms-linear-gradient(left, " + color1 + " 0%, " + color2 + " 100%)" });

      step += gradientSpeed;
      if (step >= 1) {
        step %= 1;
        colorIndices[0] = colorIndices[1];
        colorIndices[2] = colorIndices[3];

        colorIndices[1] = (colorIndices[1] + Math.floor(1 + Math.random() * (colors.length - 1))) % colors.length;
        colorIndices[3] = (colorIndices[3] + Math.floor(1 + Math.random() * (colors.length - 1))) % colors.length;
      }
    }
    if (bg_grad.length) {
      setInterval(updateGradient, 10);
    }

  });/*document ready end*/

})();/*main function end*/

jQuery(function ($) {
  $("#menudashboard a")
    .click(function (e) {
      var link = $(this);

      var item = link.parent("li");

      if (item.hasClass("active")) {
        item.removeClass("active").children("a").removeClass("active");
      } else {
        item.addClass("active").children("a").addClass("active");
      }

      if (item.children("ul").length > 0) {
        var href = link.attr("href");
        link.attr("href", "#");
        setTimeout(function () {
          link.attr("href", href);
        }, 300);
        e.preventDefault();
      }
    })
    .each(function () {
      var link = $(this);
      if (link.get(0).href === location.href) {
        link.addClass("active").parents("li").addClass("active");
        return false;
      }
    });
});