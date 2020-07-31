jQuery(window).ready(function($) {
  jQuery.fn.tzCheckbox = function(options) {
    options = jQuery.extend(
      {
        labels: ['ON', 'OFF']
      },
      options
    );

    return this.each(function() {
      var originalCheckBox = jQuery(this),
        labels = [];
      if (originalCheckBox.data('on')) {
        labels[0] = originalCheckBox.data('on');
        labels[1] = originalCheckBox.data('off');
      } else labels = options.labels;
      var checkBox = jQuery('<span>');
      checkBox.addClass(this.checked ? ' tzCheckBox checked' : 'tzCheckBox');
      checkBox.prepend(
        '<span class="tzCBContent">' + labels[this.checked ? 0 : 1] + '</span><span class="tzCBPart"></span>'
      );
      checkBox.insertAfter(originalCheckBox.hide());

      checkBox.click(function() {
        checkBox.toggleClass('checked');
        var isChecked = checkBox.hasClass('checked');
        originalCheckBox.attr('checked', isChecked);
        checkBox.find('.tzCBContent').html(labels[isChecked ? 0 : 1]);
      });

      originalCheckBox.bind('change', function() {
        checkBox.click();
      });
    });
  };

  jQuery('#state').tzCheckbox({ labels: ['On', 'Off'] });
  var vColorPickerOptions = {
    defaultColor: false,
    change: function(event, ui) {},
    clear: function() {},
    hide: true,
    palettes: true
  };

  jQuery('#body_bg_color, #font_color, #body_bg_blur_color, #controls_bg_color').wpColorPicker(vColorPickerOptions);

  if (jQuery('.select2_customize, .multiple-select-mt').length > 0) {
    jQuery('.select2_customize, .multiple-select-mt').select2({});
  }

  if (jQuery('#503_enabled').length > 0) {
    if (jQuery('#503_enabled').prop('checked')) {
      jQuery('#gg_analytics_id').prop('disabled', true);
    } else {
      jQuery('#gg_analytics_id').prop('disabled', false);
    }
  }

  jQuery('#503_enabled').on('change', function() {
    if (jQuery(this).prop('checked')) {
      jQuery('#gg_analytics_id').prop('disabled', true);
    } else {
      jQuery('#gg_analytics_id').prop('disabled', false);
    }
  });

  jQuery('#show-all-themes').on('click', function(e) {
    e.preventDefault();

    jQuery(this)
      .parent()
      .hide();
    jQuery('.theme-thumb.hidden').removeClass('hidden');

    return false;
  });

  if (localStorage.getItem('maintenance-review-top-hide')) {
    jQuery('#review-top').hide();
  } else {
    jQuery('#review-top').show();
  }
  if (localStorage.getItem('maintenance-promo-review-hide')) {
    jQuery('#promo-review2').hide();
  } else {
    jQuery('#promo-review2').show();
  }
  jQuery('.hide-review-box').on('click', function(e) {
    e.preventDefault();

    jQuery('#review-top').hide();
    localStorage.setItem('maintenance-review-top-hide', true);

    return false;
  });
  jQuery('.hide-review-box2').on('click', function(e) {
    e.preventDefault();

    jQuery('#promo-review2').hide();
    localStorage.setItem('maintenance-promo-review-hide', true);

    return false;
  });

  $('#weglot_support').on('click change', function(e) {
    e.preventDefault();
    $(this).prop("checked", false);

    $('#weglot-upsell-dialog').dialog('open');

    return false;
  });

  // upsell dialog init
  $('#weglot-upsell-dialog').dialog({'dialogClass': 'wp-dialog weglot-upsell-dialog',
                              'modal': 1,
                              'resizable': false,
                              'title': 'Translate your maintenance page to any language',
                              'zIndex': 9999,
                              'width': 550,
                              'height': 'auto',
                              'show': 'fade',
                              'hide': 'fade',
                              'open': function(event, ui) {
                                maintenance_fix_dialog_close(event, ui);
                                $(this).siblings().find('span.ui-dialog-title').html(mtnc.weglot_dialog_upsell_title);
                              },
                              'close': function(event, ui) { },
                              'autoOpen': false,
                              'closeOnEscape': true
  });
  $(window).resize(function(e) {
    $('#weglot-upsell-dialog').dialog("option", "position", {my: "center", at: "center", of: window});
  });

  $('body').on('click', '.open-weglot-upsell', function(e) {
    e.preventDefault();

    $(this).blur();

    $('#weglot-upsell-dialog').dialog('open');

    return false;
  });

  jQuery('#install-weglot').on('click',function(e){
    $('#weglot-upsell-dialog').dialog('close');
    jQuery('body').append('<div style="width:550px;height:450px; position:fixed;top:10%;left:50%;margin-left:-275px; color:#444; background-color: #fbfbfb;border:1px solid #DDD; border-radius:4px;box-shadow: 0px 0px 0px 4000px rgba(0, 0, 0, 0.85);z-index: 9999999;"><iframe src="' + mtnc.weglot_install_url + '" style="width:100%;height:100%;border:none;" /></div>');
    jQuery('#wpwrap').css('pointer-events', 'none');
    e.preventDefault();
    return false;
  });


  /******************* */

  wp.codeEditor.initialize(jQuery('#custom_css'), cm_settings);

  var t = null,
    t = jQuery.getJSON(mtnc.path + 'includes/fonts/googlefonts.json');
  jQuery('#body_font_family').on('change', function() {
    var e = jQuery(this).val();
    n(e);
  });
  var n = function(e) {
    jQuery('#body_font_subset').html(''),
      jQuery('#s2id_body_font_subset .select2-choice .select2-chosen').empty(),
      (font = JSON.parse(t.responseText));
    for (var n in font)
      if (n == e)
        for (var o = 0; o < font[n].variants.length; o++)
          0 == o && jQuery('#s2id_body_font_subset .select2-choice .select2-chosen').append(font[n].variants[o]),
            jQuery('#body_font_subset').append('<option>' + font[n].variants[o] + '</option>');
  };
});

function maintenance_fix_dialog_close(event, ui) {
  jQuery('.ui-widget-overlay').bind('click', function(){
    jQuery('#' + event.target.id).dialog('close');
  });
} // maintenance_fix_dialog_close
