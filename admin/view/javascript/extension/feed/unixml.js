
/************************copyright***************************/
/*                                                          */
/* telegram: https://t.me/PrutNikolay                       */
/* forum: https://opencartforum.com/profile/18336-exploits/ */
/* email: info@microdata.pro                                */
/* site: https://unixml.pro                                 */
/*                                                          */
/************************copyright***************************/

//Функции экспорта

  //loaded_export_setting - скрипты после загрузки окна настроек фида
    function loaded_export_setting(feed){

      $('[data-toggle="tooltip"]').on('click', function(){$('.tooltip').remove();});

      //автовысота поля ввода
      function fixTextareaSize(textarea) {
        textarea.style.height = 'auto'
        textarea.style.height = textarea.scrollHeight + 2 + "px"
      }

      $('textarea').on('input', function (e) {
        fixTextareaSize(e.target);
      });

      $('textarea').each(function(e) {
        fixTextareaSize(this);
      });
      //автовысота поля ввода

      //скрипты опций. Пункт 3.2
        option_multiplier_key++;
        $('.add-option-block').on('click', function(){
          $('.close-option-list').click();
          html = $('#get-option-html').html().replace(/777/g, option_multiplier_key);
          $('.option-block-list').append(html);
          option_multiplier_key++;
        });

        $(document).on('click', '.get-select-option', function(){
          get_option($(this));
        });
        $(document).on('keyup', '.get-select-option', function(){
          get_option($(this));
        });

        function get_option(input){
          input.addClass('w50');
          block = input.data('option-block');
          value = input.val();
          $.ajax({
            url: 'index.php?route=' + unixml_path + '/exportAutocompleteOption' + unixml_token + '&filter_name=' +  encodeURIComponent(value),
            dataType: 'json',
            success: function(json) {
              $('.close-option-list').click();
              html = '<div class="option-list"><span class="close-option-list"><small>' + text_js_close_list + '</small> <i class="fa fa-times" aria-hidden="true"></i></span>';
              if(json.length > 0){
                $.each(json, function (index, value) {
                  html += '<div class="option-list-item option-clickable option-list-item-' + value['option_id'] + '" data-option-id="' + value['option_id'] + '">';
                    html += '<div class="option-list-item-name">id ' + value['option_id'] + ': <b>' + value['name'] + '</b> <small>(' + text_js_in + ' ' + value['products'] + ' ' + text_js_products + ')</small></div>';
                    html += '<input type="hidden" value="' + value['option_id'] + '" name="unixml_' + feed + '_option_multiplier[' + block + '][]">';
                    html += '<div class="option-list-item-values">' + text_js_values + ' ' + value['values'] + '</div>';
                    html += '<span class="delete-option-item"><i class="fa fa-times" aria-hidden="true"></i></span>';

                  html += '</div>';
                });
              }else{
                html += '<div class="option-list-item">' + text_js_options_not_found + '</div>';
              }
              html += '</div>';

              input.after(html);
            }
          });
        }

        $(document).on('click', '.close-option-list', function(){
          $(this).parent().parent().find('.get-select-option').removeClass('w50');
          $(this).parent('.option-list').remove();
        });

        jQuery(function($){
          $(document).mouseup(function (e){
            var div = $(".option-list");
            if (!div.is(e.target) && div.has(e.target).length === 0) {
              $('.close-option-list').click();
            }
          });
        });

        $(document).on('click', '.option-list .option-list-item.option-clickable', function(){
          block = $(this).parent().parent().find('.get-select-option').data('option-block');
          option_id = $(this).data('option-id');
          $('.option-block-' + block + ' .option-list-placeholder').hide();
          $('.option-block-' + block + ' .option_select_scroll .option-list-item-' + option_id).remove();
          $('.option-block-' + block + ' .option_select_scroll').append($(this));
        });

        $('#unixml_' + feed + '_option_multiplier_status').on('change', function(){
          if($(this).val() == 1){
            $('.hideoption' + feed).slideDown(100);
          }else{
            $('.hideoption' + feed).slideUp(100);
          }
        });
      //скрипты опций. Пункт 3.2

      //скрипты категорий.
      $('#unixml_' + feed + '_category_match tbody tr').each(function(index, element) {
        detectAutocomplete(index, feed);
      });
      //скрипты категорий. Пункт 3.7

      //скрипты атрибутов. Пункт 3.8
      $('#unixml_attribute_status').on('change', function(){
        if($(this).val() != 1){
          $('#hideattr').slideDown(100);
        }else{
          $('#hideattr').slideUp(100);
        }
      });
      $('#unixml_attributes tbody tr').each(function(index, element) {
        attributeautocomplete(index);
      });
      //скрипты атрибутов. Пункт 3.8

      //скрипты групп наценки. Пункты 3.9
      $('#unixml_product_markup_table tbody tr').each(function(index, element) {
        markupautocomplete(index);
      });

      $(document).on('click', '.importMarkup', function(e){
        e.preventDefault();
        $('#import_feed').val($(this).data('feed'));
        $('#import_row').val($(this).data('row'));
        $('#import_to_markup').modal('show');
      });
      //скрипты групп наценки. Пункты 3.9

      //общие скрипты для взаимодействия элементов окна настроек
      $('#form-unixml-export').height($(window).height() - 130);
      $('.export-navigation-list').css({'max-height': $(window).height() - 176});
      setTimeout(function(){$('.export-navigation-fast input').focus();},1000);

      frm = $('#form-unixml-export');

      $(document).on('click', '.to-top', function(){
        $('.export-block-1').click();
      });

      $('.export-navigation-fast input').keyup(function(){
        find_text = $(this).val();
        if(find_text){
          $('.export-navigation-fast li ul li').addClass('hidden');
          $('.export-navigation-fast li').each(function() {
            li_text = $(this).data('search');
            if (li_text.indexOf(find_text) >= 0) {
              $(this).removeClass('hidden');
            }
          });
        }else{
          $('.export-navigation-fast li ul li').removeClass('hidden');
        }

      });

      $(document).on('click', '.export-navigation-list li', function (e) {
        e.stopPropagation();
        this_id = $(this).data('id');
        frm.animate({scrollTop: $('#' + this_id).offset().top - frm.offset().top + frm.scrollTop() - 15}, 200);
        $('#' + this_id).addClass('active_export_field');
        setTimeout(function(){
          $('#' + this_id).removeClass('active_export_field');
        },1200);
        setTimeout(function(){
          $('#' + this_id).find('input[type="text"]').focus();
        },200);
      });

      //gotoset
      $(document).on('click', '.gotoset', function (e) {
        id = $(this).data('id');
        e.stopPropagation();
        $('.export-navigation-list li[data-id="export-block-' + id + '"]').click();
      });
      //gotoset

      $(document).on('click', '.export-navigation li', function () {
        $(".export-navigation").addClass('hold');
        this_class = $(this).attr('class');
        $(".export-navigation li").removeClass('active');
        $(this).addClass('active');
        frm.animate({scrollTop: $('#' + this_class).offset().top - frm.offset().top + frm.scrollTop() - 10}, 200);
        setTimeout(function(){
          $(".export-navigation").removeClass('hold');
        }, 205);
      });

      $.fn.isInViewport = function() {
        var wh = $(window).height() - 120;
        var elementTop = $(this).offset().top + parseInt(wh);
        var elementBottom = elementTop + $(this).outerHeight();
        var viewportTop = $(window).scrollTop();
        var viewportBottom = viewportTop + $(window).height();
        return elementBottom > viewportTop && elementTop < viewportBottom;
      };

      $('#form-unixml-export').on('resize scroll', function() {
        $(".export-navigation li").removeClass('active');
        $('.export-block-item').each(function() {
          if ($(this).isInViewport()) {
            active_block = $(this).attr('id');
          }
        });
        $('.' + active_block).addClass('active');
        if(active_block != 'export-block-1'){
          $('.to-top').fadeIn(100);
        }else{
          $('.to-top').fadeOut(100);
        }
        $('.setting_item_top').text($('.' + active_block).text());
      });

      links();
      setInterval(function(){
        if($('#export_setting').is(":visible")){
          getXMLFileInfo();
        }
      }, 5000);

      $('#input-products').autocomplete({
        'source': function(request, response) {
          $.ajax({
            url: 'index.php?route=' + unixml_path + '/exportAutocompleteProduct' + unixml_token + '&filter_name=' +  encodeURIComponent(request) + '&feed=' + $('input[name="feed"]').val(),
            dataType: 'json',
            data: $('#unixml_products input').serialize(),
            method: 'POST',
            success: function(json) {
              response($.map(json, function(item) {
                return {
                  label: item['name'],
                   edit: item['edit'],
                   view: item['view'],
                  value: item['product_id']
                }
              }));
            }
          });
          $('#input-products').unbind("blur");
        },
        'select': function(item) {
          $('#input-products').unbind("blur");
          $('#unixml_products' + item['value']).remove();
          html_row  = '<div id="unixml_products' + item['value'] + '">';
          html_row += '<i class="fa fa-minus-circle"></i> ' + item['label'];
          html_row += ' <a target="_blank" href="' + item['edit'] + '" title="' + text_js_edit_product + '" data-toggle="tooltip"><i class="fa fa-pencil" aria-hidden="true"></i></a>';
          html_row += ' <a target="_blank" href="' + item['view'] + '" title="' + text_js_view_product + '" data-toggle="tooltip"><i class="fa fa-eye" aria-hidden="true"></i></a>';
          html_row += '<input type="hidden" name="unixml_' + feed + '_products[]" value="' + item['value'] + '" /></div>';
          $('#unixml_products').append(html_row);
          $('#export-block-2-1 .dropdown-menu li[data-value="' + item['value'] + '"]').remove();
        }
      });
      //общие скрипты для взаимодействия элементов окна настроек
    }
  //loaded_export_setting

  //addCategoryMatch - функция добавления строки соответствий категорий. Пункт 3.7
    function addCategoryMatch(feed) {
      html  = '<tr id="category_match_row' + category_match_row + '">';
      html += '  <td class="text-left" style="width: 22%;"><input type="text" name="unixml_' + feed + '_category_match[' + category_match_row + '][category_name]" value="" class="form-control" /><input type="hidden" name="unixml_' + feed + '_category_match[' + category_match_row + '][category_id]" value="" /></td>';
      html += '  <td class="text-left" style="width: 22%;">';
      html += '   <input type="text" name="unixml_' + feed + '_category_match[' + category_match_row + '][xml_name]" value=""  class="form-control" />';
      html += '  </td>';
      html += '  <td class="text-left" style="width: 13%;">';
      html += '   <input type="text" name="unixml_' + feed + '_category_match[' + category_match_row + '][markup]" value="" placeholder="' + text_js_markup + '" class="form-control" />';
      html += '  </td>';
      html += '  <td class="text-left" style="width: 41%;">';
      html += '   <textarea name="unixml_' + feed + '_category_match[' + category_match_row + '][custom]" value="" placeholder="' + text_js_tags + '" class="form-control" ></textarea>';
      html += '  </td>';
      html += '  <td class="text-center" style="width: 2%;"><button type="button" onclick="$(\'#category_match_row' + category_match_row + '\').remove();" data-toggle="tooltip" title="' + text_js_delete + '" class="btn btn-danger"><i class="fa fa-minus-circle"></i></button></td>';
      html += '</tr>';

      $('#unixml_' + feed + '_category_match tbody').append(html);

      detectAutocomplete(category_match_row, feed);

      category_match_row++;
    }
  //addCategoryMatch

  //addCategoryMatch - функция добавления строки соответствий атрибутов. Пункт 3.8
    function addAttribute(feed) {
      html  = '<tr id="attribute-row' + attribute_row + '">';
      html += '  <td class="text-left" style="width: 40%;"><input type="text" name="unixml_' + feed + '_attributes[' + attribute_row + '][attribute_name]" value="" placeholder="' + text_js_attr_oc + '" class="form-control" /><input type="hidden" name="unixml_' + feed + '_attributes[' + attribute_row + '][attribute_id]" value="" /></td>';
      html += '  <td class="text-left"><input type="text" name="unixml_' + feed + '_attributes[' + attribute_row + '][xml_name]" value="" placeholder="' + text_js_attr_oc + '" class="form-control" /></td>';
      html += '  <td class="text-center"><button type="button" onclick="$(this).parent().parent().remove();" data-toggle="tooltip" title="' + text_js_delete + '" class="btn btn-danger"><i class="fa fa-minus-circle"></i></button></td>';
      html += '</tr>';

      $('#unixml_attributes tbody').append(html);

      attributeautocomplete(attribute_row);

      attribute_row++;
    }
  //addCategoryMatch

  //addProductMarkup - функция добавления строки наценки на группы товаров. Пункт 3.9
    function addProductMarkup(feed) {
      html  = '<tr data-row="' + product_markup_row + '" id="product_markup_row' + product_markup_row + '">';
      html += '  <td class="text-left va-top" style="width: 15%;"><input type="text" name="unixml_' + feed + '_product_markup[' + product_markup_row + '][name]" value="" placeholder="' + text_js_group + '" class="form-control" /><button class="btn btn-info importMarkup" data-feed="' + feed + '" data-row="' + product_markup_row + '" title="' + text_js_import_products + '" data-toggle="tooltip"><i class="fa fa-upload" aria-hidden="true"></i> ' + text_js_import_products2 + '</button></td>';
      html += '  <td class="text-left" style="width: 67%;">';
      html += '   <input type="text" name="unixml_' + feed + '_product_markup_input' + product_markup_row + '" value="" placeholder="' + text_js_entry_name + '" id="input-markup-products' + product_markup_row + '" class="form-control" />';
      html += '   <div id="unixml_' + feed + '_markup_products' + product_markup_row + '" class="well well-sm" style="height: 250px; overflow: auto;">';
      html += '   </div>';
      html += '  </td>';
      html += '  <td class="text-left va-top" style="width: 13%;"><input type="text" name="unixml_' + feed + '_product_markup[' + product_markup_row + '][markup]" value="" placeholder="' + text_js_markup2 + '" class="form-control" /></td>';
      html += '  <td class="text-center va-top"><button type="button" onclick="$(\'#product_markup_row' + product_markup_row + '\').remove();" data-toggle="tooltip" title="' + text_js_delete + '" class="btn btn-danger"><i class="fa fa-minus-circle"></i></button></td>';
      html += '</tr>';
      $('#unixml_product_markup_table tbody').append(html);

      markupautocomplete(product_markup_row);

      product_markup_row++;
    }
  //addProductMarkup

  //addReplaceRow - функция добавления строки замен. Пункт 3.10
    function addReplaceRow(feed) {
      html  = '<tr id="replace_name-row' + replace_name_row + '">';
      html += '  <td class="text-left" style="width: 40%;"><input type="text" name="unixml_' + feed + '_replace_name[' + replace_name_row + '][name_from]" value="" placeholder="' + text_js_replace_from + '" class="form-control" /></td>';
      html += '  <td class="text-left">';
      html += '   <input type="text" name="unixml_' + feed + '_replace_name[' + replace_name_row + '][name_to]" value="" placeholder="' + text_js_replace_to + '" class="form-control" />';
      html += '  </td>';
      html += '  <td class="text-left">';
      html += '   <div class="replace-where-div">';
      html += '    <div><input type="checkbox" name="unixml_' + feed + '_replace_name[' + replace_name_row + '][replace_where][]" value="1" class="checkbox_exp_mini" id="rr-' + replace_name_row + '-1">';
      html += '    <label for="rr-' + replace_name_row + '-1">&nbsp;' + text_js_replace_name + '<label></div>';
      html += '    <div><input type="checkbox" name="unixml_' + feed + '_replace_name[' + replace_name_row + '][replace_where][]" value="2" class="checkbox_exp_mini" id="rr-' + replace_name_row + '-2">';
      html += '    <label for="rr-' + replace_name_row + '-2">&nbsp;' + text_js_replace_model + '<label></div>';
      html += '    <div><input type="checkbox" name="unixml_' + feed + '_replace_name[' + replace_name_row + '][replace_where][]" value="3" class="checkbox_exp_mini" id="rr-' + replace_name_row + '-3">';
      html += '    <label for="rr-' + replace_name_row + '-3">&nbsp;' + text_js_replace_man + '<label></div>';
      html += '    <div><input type="checkbox" name="unixml_' + feed + '_replace_name[' + replace_name_row + '][replace_where][]" value="4" class="checkbox_exp_mini" id="rr-' + replace_name_row + '-4">';
      html += '    <label for="rr-' + replace_name_row + '-4">&nbsp;' + text_js_replace_desc + '<label></div>';
      html += '    <div><input type="checkbox" name="unixml_' + feed + '_replace_name[' + replace_name_row + '][replace_where][]" value="5" class="checkbox_exp_mini" id="rr-' + replace_name_row + '-5">';
      html += '    <label for="rr-' + replace_name_row + '-5">&nbsp;' + text_js_replace_url + '<label></div>';
      html += '    <div><input type="checkbox" name="unixml_' + feed + '_replace_name[' + replace_name_row + '][replace_where][]" value="6" class="checkbox_exp_mini" id="rr-' + replace_name_row + '-6">';
      html += '    <label for="rr-' + replace_name_row + '-6">&nbsp;' + text_js_replace_image + '<label></div>';
      html += '    <div><input type="checkbox" name="unixml_' + feed + '_replace_name[' + replace_name_row + '][replace_where][]" value="7" class="checkbox_exp_mini" id="rr-' + replace_name_row + '-7">';
      html += '    <label for="rr-' + replace_name_row + '-7">&nbsp;' + text_js_replace_attr_name + '<label></div>';
      html += '    <div><input type="checkbox" name="unixml_' + feed + '_replace_name[' + replace_name_row + '][replace_where][]" value="8" class="checkbox_exp_mini" id="rr-' + replace_name_row + '-8">';
      html += '    <label for="rr-' + replace_name_row + '-8">&nbsp;' + text_js_replace_attr_val + '<label></div>';
      html += '   </div>';
      html += '  </td>';
      html += '  <td class="text-center"><button type="button" onclick="$(this).parent().parent().remove();" data-toggle="tooltip" title="' + text_js_delete + '" class="btn btn-danger"><i class="fa fa-minus-circle"></i></button></td>';
      html += '</tr>';

      $('#unixml_' + $('#current_feed').val() + '_replace_names tbody').append(html);

      replace_name_row++;
    }
  //addReplaceRow

  //addParam - функция добавления строки дополнительных статических параметров. Пункт 3.12
    function addParam(feed) {
      html  = '<tr id="param-row' + param_row + '">';
      html += ' <td class="text-left" style="width: 40%;"><input type="text" name="unixml_' + feed + '_additional_params[' + param_row + '][param_name]" value="" placeholder="' + text_js_3_12_name + '" class="form-control" /></td>';
      html += ' <td class="text-left"><input type="text" name="unixml_' + feed + '_additional_params[' + param_row + '][param_text]" value="" placeholder="' + text_js_3_12_text + '" class="form-control" /></td>';
      html += ' <td class="text-center"><button type="button" onclick="$(this).parent().parent().remove();" data-toggle="tooltip" title="' + text_js_delete + '" class="btn btn-danger"><i class="fa fa-minus-circle"></i></button></td>';
      html += '</tr>';

      $('#unixml_additional_params tbody').append(html);

      param_row++;
    }
  //addParam

  //detectAutocomplete - функция детекта какой автокомплит запускать в зависимости от фида
    function detectAutocomplete(index, feed){
      switch (feed) {
        case "google":
          googleautocomplete(index);
          break;
        case "facebook":
          facebookautocomplete(index);
          break;
        case "kidstaff":
          kidstaffautocomplete(index);
          break;
        default:
          //categoryautocomplete(index);
      }
      categoryautocomplete(index);
    }
  //detectAutocomplete

  //categoryautocomplete - функция автокомплит категории
    function categoryautocomplete(index) {
      $('input[name=\'unixml_' + feed + '_category_match[' + index + '][category_name]\']').autocomplete({
        'source': function(request, response) {
          $.ajax({
            url: 'index.php?route=' + unixml_path + '/exportAutocompleteCategory' + unixml_token + '&filter_name=' +  encodeURIComponent(request),
            dataType: 'json',
            success: function(json) {
              console.log(json);
              response($.map(json, function(item) {
                return {
                  label: item.name,
                  value: item.category_id
                }
              }));
            }
          });
        },
        'select': function(item) {
          $('input[name=\'unixml_' + feed + '_category_match[' + index + '][category_name]\']').val(item['label']);
          $('input[name=\'unixml_' + feed + '_category_match[' + index + '][category_id]\']').val(item['value']);
        }
      });
    }
  //categoryautocomplete

  //facebookautocomplete - функция автокомплит facebook
    function facebookautocomplete(index) {
      $('input[name=\'unixml_facebook_category_match[' + index + '][xml_name]\']').attr('placeholder', text_js_entry_google_cat + 'facebook');
      $('input[name=\'unixml_facebook_category_match[' + index + '][xml_name]\']').autocomplete({
        'source': function(request, response) {
          $.ajax({
            url: 'index.php?route=' + unixml_path + '/exportGoogleCategory' + unixml_token + '&filter_name=' +  encodeURIComponent(request),
            dataType: 'json',
            success: function(json) {
              response($.map(json, function(item) {
                return {
                  label: item.name,
                  value: item.category_id
                }
              }));
            }
          });
        },
        'select': function(item) {
          $('input[name=\'unixml_facebook_category_match[' + index + '][xml_name]\']').val(item['label']);
        }
      });
    }
  //facebookautocomplete

  //googleautocomplete - функция автокомплит google
    function googleautocomplete(index) {
      $('input[name=\'unixml_google_category_match[' + index + '][xml_name]\']').attr('placeholder', text_js_entry_google_cat + 'google');
      $('input[name=\'unixml_google_category_match[' + index + '][xml_name]\']').autocomplete({
        'source': function(request, response) {
          $.ajax({
            url: 'index.php?route=' + unixml_path + '/exportGoogleCategory' + unixml_token + '&filter_name=' +  encodeURIComponent(request),
            dataType: 'json',
            success: function(json) {
              response($.map(json, function(item) {
                return {
                  label: item.name,
                  value: item.category_id
                }
              }));
            }
          });
        },
        'select': function(item) {
          $('input[name=\'unixml_google_category_match[' + index + '][xml_name]\']').val(item['label']);
        }
      });
    }
  //googleautocomplete

  //kidstaffautocomplete - функция автокомплит kidstaff
    function kidstaffautocomplete(index) {
      $('input[name=\'unixml_kidstaff_category_match[' + index + '][xml_name]\']').attr('placeholder', text_js_entry_google_cat + 'kidstaff');
      $('input[name=\'unixml_kidstaff_category_match[' + index + '][xml_name]\']').autocomplete({
        'source': function(request, response) {
          $.ajax({
            url: 'index.php?route=' + unixml_path + '/exportKidstaffCategory' + unixml_token + '&filter_name=' +  encodeURIComponent(request),
            dataType: 'json',
            success: function(json) {
              response($.map(json, function(item) {
                return {
                  label: item.name,
                  value: item.category_id
                }
              }));
            }
          });
        },
        'select': function(item) {
          $('input[name=\'unixml_kidstaff_category_match[' + index + '][xml_name]\']').val(item['label']);
        }
      });
    }
  //kidstaffautocomplete

  //attributeautocomplete - функция автокомплит атрибутов
    function attributeautocomplete(index) {
      $('input[name=\'unixml_' + feed + '_attributes[' + index + '][attribute_name]\']').autocomplete({
        'source': function(request, response) {
          $.ajax({
            url: 'index.php?route=' + unixml_path + '/exportAutocompleteAttribute' + unixml_token + '&filter_name=' +  encodeURIComponent(request),
            dataType: 'json',
            success: function(json) {
              response($.map(json, function(item) {
                return {
                  label: item.name,
                  value: item.attribute_id
                }
              }));
            }
          });
        },
        'select': function(item) {
          $('input[name="unixml_' + feed + '_attributes[' + index + '][attribute_name]"]').val(item['label']);
          $('input[name="unixml_' + feed + '_attributes[' + index + '][attribute_id]"]').val(item['value']);
        }
      });
    }
  //attributeautocomplete

  //markupautocomplete - функция автокомплит товаров в группах наценки
    function markupautocomplete(index) {
      $('#input-markup-products' + index).autocomplete({
        'source': function(request, response) {
          $.ajax({
            url: 'index.php?route=' + unixml_path + '/exportAutocompleteProduct&token=' + unixml_token + '&filter_name=' +  encodeURIComponent(request) + '&feed=' + $('input[name="feed"]').val(),
            dataType: 'json',
            success: function(json) {
              response($.map(json, function(item) {
                return {
                  label: item['name'],
                  edit: item['edit'],
                  view: item['view'],
                  value: item['product_id']
                }
              }));
            }
          });
        },
        'select': function(item) {
          $('#input-markup-products' + index).val('');
          $('#unixml_' + feed + '_markup_products' + index + '-' + item['value']).remove();

          html_row  = '<div id="unixml_' + feed + '_markup_products' + index + '-' + item['value'] + '">';
          html_row += '<i class="fa fa-minus-circle"></i> ' + item['label'];
          html_row += ' <a target="_blank" href="' + item['edit'] + '" title="' + text_js_edit_product + '" data-toggle="tooltip"><i class="fa fa-pencil" aria-hidden="true"></i></a>';
          html_row += ' <a target="_blank" href="' + item['view'] + '" title="' + text_js_view_product + '" data-toggle="tooltip"><i class="fa fa-eye" aria-hidden="true"></i></a>';
          html_row += '<input type="hidden" name="unixml_' + feed + '_product_markup[' + index + '][products][]" value="' + item['value'] + '" /></div>';
          $('#unixml_' + feed + '_markup_products' + index).append(html_row);

        }
      });
    }
  //markupautocomplete

  //feed_list_search - поиск по фидам
    $(document).on('keyup', '#feed_list_search', function(){
      $('#load_exports').load('index.php?route=' + unixml_path + '/exportLoadfeedList' + unixml_token + '&search=' + $(this).val(), function(){
      });
    });
  //feed_list_search

  //fa-minus-circle - удаление строки из списка товаров
    $(document).on('click', '.well.well-sm .fa-minus-circle', function() {
      $(this).parent().remove();
    });
  //fa-minus-circle

  //unixml_quantity - раскрытие подпункта статус в наличии
    $(document).on('change', '#unixml_quantity', function(){
      if($(this).val() == 1){
        $('#hideblock_quantity').slideDown(100);
      }else{
        $('#hideblock_quantity').slideUp(100);
      }
    });
  //unixml_quantity

  //unixml_xml_name - проставка в ссылках названия файла фида
    $(document).on('keyup', '#unixml_xml_name', function() {
      feed = $(this).data('feed');
      if($(this).val() == ''){
        $('#url_xml span').text(feed);
      }else{
        $('#url_xml span').text($(this).val());
      }
      $('#url_xml').attr('href',  $('#url_xml span').text());
    });
  //unixml_xml_name

  //export_trash - отправка фида в корзину
    $(document).on('click', '.export_trash', function() {
      feed = $(this).data('feed');
      $.ajax({
        url: 'index.php?route=' + unixml_path + '/exportFeedToTrash&token=' + unixml_token + '&feed=' +  feed,
        dataType: 'json',
        success: function(json) {
          update_export_list();
        }
      });
    });
  //export_trash

  //trash_list_toggle - скрытие и раскрытие корзины
    $(document).on('click', '#trash_list_toggle', function() {
      $('#trash_list_toggle_body').slideToggle(100);
      text1 = text_js_slideup + ' <i class="fa fa-angle-down" aria-hidden="true"></i>';
      text2 = text_js_slidedown + ' <i class="fa fa-angle-right" aria-hidden="true"></i>';

      if($(this).html() == text1){
        $(this).html(text2);
        status = 1;
      }else{
        $(this).html(text1);
        status = 0;
      }

      $.ajax({url: 'index.php?route=' + unixml_path + '/exportTrashToggle&token=' + unixml_token + '&status=' +  status});
    });
  //trash_list_toggle

  //export_to_list - убрать с корзины обратно в список
    $(document).on('click', '.export_to_list', function() {
      feed = $(this).data('feed');
      $.ajax({
        url: 'index.php?route=' + unixml_path + '/exportFeedToList&token=' + unixml_token + '&feed=' +  feed,
        dataType: 'json',
        success: function(json) {
          update_export_list();
        }
      });
    });
  ////export_to_list

  //upload_file_import - импорт настроек в импотре с файла
    $(document).on('click', '.upload_file_import', function() {
      import_id = $(this).data('import');
      $('#form-upload').remove();
      $('body').prepend('<form enctype="multipart/form-data" id="form-upload" style="display: none;"><input type="file" name="file" /></form>');
      $('#form-upload input[name=\'file\']').trigger('click');
      if (typeof timer != 'undefined') {clearInterval(timer);}
      timer = setInterval(function() {
        if ($('#form-upload input[name=\'file\']').val() != '') {
          $('#price_import_export').prepend('<div class="imp-success" style="margin-right:4px;"><i class="fa fa-hourglass-start" aria-hidden="true"></i> ' + text_js_start_process_import + ' ' + import_id + '...</div>');
          clearInterval(timer);
          $.ajax({
            url: 'index.php?route=' + unixml_path + '/importSettingImport' + unixml_token + '&import_id=' + import_id,
            type: 'post',
            dataType: 'json',
            data: new FormData($('#form-upload')[0]),
            cache: false,
            contentType: false,
            processData: false,
            success: function(json) {
              if(json['error']){
                alert(json['error']);
                $('.imp-success').remove();
              }else{
                $('#price_setting_load').load('index.php?route=' + unixml_path + '/importLoadPriceSetting' + unixml_token + '&id=' + import_id, function(){
                  get_link_key(import_id);
                  $('.imp-success').remove();
                  $('#price_import_export').prepend('<div class="imp-success" style="margin-right:4px;"><i class="fa fa-check-circle-o" aria-hidden="true"></i> ' + text_js_import_success + '</b>.</div>');
                  setTimeout(function(){
                    $('.imp-success').remove();
                  }, 5000);
                  update_import_list();
                });
              }
            }
          });
        }
      }, 500);
    });
  //upload_file_import

  //upload_file - импорт настроек в экспорт с файла
    $(document).on('click', '.upload_file', function() {
      feed = $(this).data('feed');
      $('#form-upload').remove();
      $('body').prepend('<form enctype="multipart/form-data" id="form-upload" style="display: none;"><input type="file" name="file" /></form>');
      $('#form-upload input[name=\'file\']').trigger('click');
      if (typeof timer != 'undefined') {clearInterval(timer);}
      timer = setInterval(function() {
        if ($('#form-upload input[name=\'file\']').val() != '') {
          $('#export_setting_close').before('<div class="imp-success" style="position:absolute;margin-left:-115px;"><i class="fa fa-hourglass-start" aria-hidden="true"></i> ' + text_js_start_process_import + ' ' + feed + '...</div>');
          clearInterval(timer);
          $.ajax({
            url: 'index.php?route=' + unixml_path + '/exportSettingImport' + unixml_token + '&feed=' + feed,
            type: 'post',
            dataType: 'json',
            data: new FormData($('#form-upload')[0]),
            cache: false,
            contentType: false,
            processData: false,
            success: function(json) {
              if(json['error']){
                alert(json['error']);
                $('#export_setting_close').before('');
                $('.imp-success').remove();
              }else{
                $('#export_setting_load').load('index.php?route=' + unixml_path + '/exportLoadFeedSetting' + unixml_token + '&feed=' + feed, function(){
                  $('.imp-success').remove();
                  $('#export_setting_close').before('<div class="imp-success" style="position:absolute;margin-left:-115px;"><i class="fa fa-check-circle-o" aria-hidden="true"></i> ' + text_js_import_success + '</b>.</div>');
                  setTimeout(function(){
                    $('.imp-success').remove();
                  }, 5000);
                });
              }
            }
          });
        }
      }, 500);
    });
  //upload_file

  //tab-export - при клике на экспорт - подгрузка всех фидов
    $(document).on('click', '[href="#tab-export"]', function(){
     update_export_list();
     setTimeout(function(){
       $('#feed_list_search').focus();
     }, 150);
    });
  //tab-export

  //export_setting - загрузка настроек фида в окно
    $(document).on('click', '.export_setting', function(){
     feed = $(this).data('feed');
       $('#current_feed').val(feed);
       $('#export_setting_load').load('index.php?route=' + unixml_path + '/exportLoadFeedSetting' + unixml_token + '&feed=' + feed, function(){
       });
    });
  //export_setting

  //export_system - загрузка файла контроллера в окно
    $(document).on('click', '.export_system', function(){
     feed = $(this).data('feed');
     $('#export_system_load').load('index.php?route=' + unixml_path + '/exportLoadControllerFile' + unixml_token + '&feed=' + feed);
    });
  //export_system

  //save_export_system_item - сохранение файла контроллера
    $(document).on('click', '#save_export_system_item', function(){
     $.ajax({
       url: $('#export_system_file').data('action'),
       dataType: 'json',
       data: {'filedata' : $('#export_system_file').val()},
       method: 'post',
       success: function(json) {
         if(json){
           $('#export_system_message').fadeIn(300);
           setTimeout(function(){
             $('#export_system_message').fadeOut(600);
           }, 1000);
         }
       }
     });
    });
  //save_export_system_item

  //save_export_item - сохранение настроек
    $(document).on('click', '#save_export_item', function(){
     $('#save_export_message').before('<span class="save_export_message_tmp">' + text_js_saving + '...</span>');
     setTimeout(function(){
       $.ajax({
         url: $('#form-unixml-export').attr('action'),
         dataType: 'json',
         data: $('#form-unixml-export').serialize(),
         method: 'post',
         success: function(json) {
           if(json){
             update_export_list();
             $('.save_export_message_tmp').remove();
             $('#save_export_message').fadeIn(300);
             setTimeout(function(){
               $('#save_export_message').fadeOut(600);
             }, 1000);
           }
         }
       });
     }, 50);
    });
  //save_export_item

  //hidden.bs.modal - обновление списка фидов при закрытии настроек
    $('#export_setting').on('hidden.bs.modal', function () {
      update_export_list();
    });
  //hidden.bs.modal

  //update_export_list - функция сортировки фидов
    function update_export_list(code = false){
      $('#load_exports').load('index.php?route=' + unixml_path + '/exportLoadfeedList' + unixml_token + '', function(){
        $('#export_counter').text($('#load_exports>tr').length);
        if(code){
          setTimeout(function(){
            $('.export_setting[data-feed="' + code + '"]').click();
          }, 100);
        }

        $('#load_exports>tr>td').each(function(index, element) {
          $(this).css({'width': $(this).outerWidth()});
        });

        $("#load_exports").sortable({
          axis: "y",
          start: function(e, ui){ui.placeholder.height(ui.item.height()-1);},
          update: function(e, ui){
            sorts = [];
            $('#load_exports>tr').each(function(index, element) {
              index++;
              $(this).find('.list_sort').text(index);
              sorts[index] = $(this).data('feed');
            });
            $.ajax({
              url: 'index.php?route=' + unixml_path + '/exportSaveFeedSorts' + unixml_token + '',
              method: 'post',
              data: {'sorts': sorts},
              dataType: 'json',
              success: function(json) {
              }
            });
          },
        }).disableSelection();

      });
      update_trash_list(); //запускаем обновление и корзины
    }
  //update_export_list

  //export_save - добавление фида
    $('#export_save').on('click', function(){
      code = $('#price_code').val();
      copyset = $('#copy_and_setting').prop('checked');
      $.ajax({
        url: 'index.php?route=' + unixml_path + '/exportAddFeed' + unixml_token,
        method: 'post',
        dataType: 'json',
        data: {'from' : $('#add_export_from').val(), 'code' : code, 'name' : $('#price_feed').val(), 'copyset' : copyset},
        success: function(json) {
          if(json['error']){
            alert(json['error']);
          }else{
            $('#add_export_close').click();
            update_export_list(code);
            alert(json['success']);
          }
        }
      });
    });
  //export_save

  //delete_feed - удаление фида
    $(document).on('click', '#delete_feed', function(){
      code = $(this).data('feed');
      $.ajax({
        url: 'index.php?route=' + unixml_path + '/exportDeleteFeed' + unixml_token,
        method: 'post',
        dataType: 'json',
        data: {'code' : code},
        success: function(json) {
          $('#export_system_close').click();
          update_export_list();
        }
      });
    });
  //delete_feed

  //trash_h3 - сворачивание корзины при клике на заголовок корзины
    $('#trash_h3').on('click', function(){
      $('#trash_list_toggle').click();
    });
  //trash_h3

  //update_trash_list - функция загрузки корзины
    function update_trash_list(){
      $('#load_trash').load('index.php?route=' + unixml_path + '/exportLoadTrashList' + unixml_token + '', function(){
        $('#trash_counter').text($('#load_trash>tr').length);
        if(!$('#load_trash>tr').length){
          $('#load_trash').html('<tr><td class="text-center" colspan="5">' + text_js_trash_empty + '.</td></tr>');
        }
      });
    }
  //update_trash_list

  //пингуем каждую минуту что бы сессия не оборвалась
    setInterval(function(){$.ajax({url:'index.php?route=' + unixml_path + unixml_token});},60000);
  //пингуем

  //категории - сворачивание, выбор и снятие галочек
    $(document).on('click', '.category_item_name_span', function(e){
      $(this).next('.category_child_block').slideToggle(200);
      mp = $(this).find('span');
      mp.text((mp.text() == '+')? '-' : '+');
    });
    $(document).on('click', '.select_all', function(){
      inputs = $(this).parent().find('input');
      $(inputs).each(function() {
        $(this).prop('checked',true);
      });
    });
    $(document).on('click', '.unselect_all', function(){
      inputs = $(this).parent().find('input');
      $(inputs).each(function() {
        $(this).prop('checked',false);
      });
    });
  //категории

  //опции - работа с опциями
    $(document).on('click', '.delete-option-block', function(){
      $('.option-block-' + $(this).data('option-block')).remove();
    });
    $(document).on('click', '.delete-option-item', function(){
      if($(this).parent().parent().children('.option-list-item').length == 1){
        $(this).parent().parent().find('.option-list-placeholder').show();
      }
      $(this).parent().remove();
    });
    $(document).on('click', '.option-list-placeholder', function(){
      $('input[data-option-block="' + $(this).data('option-block') + '"]').focus().click();
    });
  //опции

  //example_view - просмотр контроллера
    $('#example_view').on('click', function(){
      $('.export_system[data-feed="' + $('#add_export_from').val() + '"]').click();
    });
  //example_view

  //load_system_setting_popup - загрузка просмотра контроллера
    $(document).on('click', '.load_system_setting_popup', function(){
      feed =  $(this).data('feed');
      $('#export_setting_close').click();
      setTimeout(function(){
        $('.export_system[data-feed="' + feed + '"]').click();
      }, 100);
    });
  //load_system_setting_popup

  //goto_feed_setting - переход в настройки фида
    $(document).on('click', '.goto_feed_setting', function(){
      feed =  $(this).data('feed');
      $('#export_system_close').click();
      setTimeout(function(){
        $('.export_setting[data-feed="' + feed + '"]').click();
      }, 100);
    });
  //goto_feed_setting

  //ссылки - при смене ключа или названия файла - меняем ссылки
    $(document).on('keyup', '#unixml_secret, #unixml_xml_name', function() {
      links();
    });
    function links(){
      key = '';
      if($('#unixml_secret').val()){
        key = '&key=' + $('#unixml_secret').val();
      }
      $('#direct-link span').text(key);

      setTimeout(function(){
        $('#row-direct-link a').attr('href', $('#direct-link').text());
        $('#row-direct-link input').val($('#direct-link').text());
        $('#row-cron-link a').attr('href', $('#direct-link').text() + '&cron=file');
        $('#row-cron-link input').val($('#direct-link').text() + '&cron=file');
        $('#row-cron-command input').val("usr/bin/wget -O - -q -t 1 '" + $('#direct-link').text() + "&cron=file'");
        if(!$('#unixml_xml_name').val()){
          $('#unixml_xml_name').val($('#current_feed').val());
        }
        $('#row-file-link input').val($('#unixml_xml_name').prev().text() + $('#unixml_xml_name').val() + '.xml');
        $('#row-file-link .filelink').attr('href', $('#row-file-link input').val());
        $('#row-file-link .filelink_goto').attr('href', $('#row-file-link input').val());
        getXMLFileInfo();
      }, 10);
    }
  //ссылки

  //копирование ссылок
    $(document).on('click', '#export-block-6-1 input[type="text"]', function(){
      $(this).select();
      document.execCommand("copy");
      this_copy = $(this).parent().find('.tocopy');
      this_copy.html(text_js_copied + ' <i class="fa fa-copy" aria-hidden="true"></i>').addClass('copied');
      setTimeout(function(){
        this_copy.html(text_js_copy + ' <i class="fa fa-copy" aria-hidden="true"></i>').removeClass('copied');
      }, 1000);
    });
    $(document).on('click', '.tocopy', function(){
      $(this).parent().find('input').click();
    })
  //копирование ссылок

  //getXMLFileInfo - функция просмотра информации о файле
    function getXMLFileInfo(){
      $.ajax({
        url: 'index.php?route=' + unixml_path + '/exportGetXMLFileInfo&token=' + unixml_token + '&file=' +  $('#unixml_xml_name').val(),
        dataType: 'json',
        success: function(json) {
          $('#row-file-link input').next().text(json);
        }
      });
    }
  //getXMLFileInfo

  //import_start - функция импортирования товаров для наценки
    $(document).on('click', '#import_start', function(){
      $('#import_stat').html(text_js_importing);
      feed = $('#import_feed').val();
      row = $('#import_row').val();
      $.ajax({
        url: 'index.php?route=' + unixml_path + '/exportImportMarkupProduct' + unixml_token,
        method: 'post',
        dataType: 'json',
        data: {'products' : $('#import_textarea').val(), 'import_serapator' : $('#import_serapator').val(), 'import_field' : $('#import_field').val()},
        success: function(json) {
          if(json['error']){
            $('#import_stat').html('<div style="color:red;">' + json['error'] + '</div>');
          }else{
            if(!json['success']){
              $('#import_stat').html('<div style="color:red;">' + text_js_not_import_pro + json['count'] + '</div>');
            }else{
              $('#import_stat').html(text_js_import_pro_ok + json['success'] + '/' + json['count']);
            }
            if(json['products']){
              if($('#clear_old').prop('checked')){
                $('#unixml_' + feed + '_markup_products' + row).html("");
              }
              $.each(json['products'], function (index, value) {
                $('#unixml_' + feed + '_markup_products' + row + '-' + value['product_id']).remove();

                html_row  = '<div id="unixml_' + feed + '_markup_products' + row + '-' + value['product_id'] + '">';
                html_row += '<i class="fa fa-minus-circle"></i> ' + value['name'];
                html_row += ' <a target="_blank" href="' + value['edit'] + '" title="' + text_js_edit_product + '" data-toggle="tooltip"><i class="fa fa-pencil" aria-hidden="true"></i></a>';
                html_row += ' <a target="_blank" href="' + value['view'] + '" title="' + text_js_view_product + '" data-toggle="tooltip"><i class="fa fa-eye" aria-hidden="true"></i></a>';
                html_row += '<input type="hidden" name="unixml_' + feed + '_product_markup[' + row + '][products][]" value="' + value['product_id'] + '" /></div>';
                $('#unixml_' + feed + '_markup_products' + row).append(html_row);

              });
            }
          }
        }
      });
    });
  //import_start

//Функции экспорта

//Функции импорта

  window.server_counter = 1;
  update_import_list();

  //addLangRow - функция добавления строки для других языков
    function addLangRow(){
      lang_row = $('#lang_row').val();

      html = $('#example_lang_row').html().replace(/777888777/g, lang_row);
      $('#lang_row_table tbody').append(html);
      lang_row++;

      $('#lang_row').val(lang_row);
    }
  //addLangRow

  //new
    //addAdditional - функция добавления строки для других данных
      function addAdditional(){
        additionals_row = $('#additionals_row').val();

        html = $('#example_additionals_row').html().replace(/777888777/g, additionals_row);
        $('#additionals_row_table tbody').append(html);
        additionals_row++;

        $('#additionals_row').val(additionals_row);
      }
    //addAdditional
  //new

  //update_import_list - функция загрузки списка импортов в модуле
    function update_import_list(){
      $('#load_prices').load('index.php?route=' + unixml_path + '/importLoadPrices' + unixml_token, function(){
        //проверка, идет ли процесс импорта
        $.ajax({
          url: 'index.php?route=' + unixml_path + '/checkTheImportProcess' + unixml_token,
          method: 'post',
          dataType: 'json',
          success: function(json) {
            //получили данные за файл, и через 3 сек опрашиваем заново со сравнением результатов
            setTimeout(function(){
              $.ajax({
                url: 'index.php?route=' + unixml_path + '/checkTheImportProcess' + unixml_token,
                method: 'post',
                dataType: 'json',
                data: json,
                success: function(json) {
                  $.each(json, function (index, value) {
                    if(value == true){ //если есть процесс
                      $('.price_start').hide();
                      $('.price_pause[data-id="' + index + '"]').show();
                      $('.price_setting[data-id="' + index + '"], .price_delete[data-id="' + index + '"], .price_delete_product[data-id="' + index + '"]').hide(); //прячем настройки,удаление,удаление товаров
                      $('#load_prices tr.price_list_' + index + ' td.status_in_list').html('<span style="color:green;"><i class="fa fa-spinner fa-spin" aria-hidden="true"></i> ' + text_js_import_processing + '<small></small></span>');
                      // import_progress = setInterval(function(){ //каждые 1 сек опрашиваем сервер для получения статуса
                      //   get_status(index);
                      // }, 1000);
                    }
                  });
                }
              });
            }, 1100);
          }
        });
        //проверка, идет ли процесс импорта
      });
    }
  //update_import_list

  //price_setting - загрузка настроек в окно настроек импорта
    $(document).on('click', '.price_setting', function(){
      id = $(this).data('id');
      $('#price_setting_load').load('index.php?route=' + unixml_path + '/importLoadPriceSetting' + unixml_token + '&id=' + id, function(){
        get_link_key(id);
      });
    });
  //price_setting

  //get_link_key - функция получения связующих полей
    function get_link_key(id){
      $.ajax({
        url: 'index.php?route=' + unixml_path + '/importGetLinkKey' + unixml_token + '&id=' + id,
        method: 'post',
        dataType: 'json',
        success: function(json) {
          $('#link_key_info').html('<div>XML: ' + json['xml'] + ' <i class="fa fa-long-arrow-right" aria-hidden="true"></i> Opencart: ' + json['oc'] + '</div>');
        }
      });
    }
  //get_link_key

  //price_file - разблокировка кнопки добавления импорта
    $(document).on('keyup', '#price_file', function() {
      if($('#price_file').val() == ''){
        $('#price_save').attr('disabled', 'disabled');
      }else{
        $('#price_save').removeAttr('disabled');
      }
    });
  //price_file

  //price_delete - удаление прайса
    $(document).on('click', '.price_delete', function(){
      if(confirm(text_js_confirm)){
        $.ajax({
          url: 'index.php?route=' + unixml_path + '/importDeletePrice' + unixml_token + '&id=' + $(this).data('id'),
          method: 'post',
          dataType: 'json',
          success: function(json) {
            update_import_list();
          }
        });
      }
    });
  //price_delete

  //button-upload - загрузка файла XML на сервер
    $(document).on('click', '#button-upload', function() {
      $('#form-upload').remove();
      $('body').prepend('<form enctype="multipart/form-data" id="form-upload" style="display: none;"><input type="file" name="file" /></form>');
      $('#form-upload input[name=\'file\']').trigger('click');
      if (typeof timer != 'undefined') {clearInterval(timer);}

      timer = setInterval(function() {
        if ($('#form-upload input[name=\'file\']').val() != '') {
          clearInterval(timer);

          $.ajax({
            url: 'index.php?route=' + unixml_path + '/importUploadFile' + unixml_token + '',
            type: 'post',
            dataType: 'json',
            data: new FormData($('#form-upload')[0]),
            cache: false,
            contentType: false,
            processData: false,
            beforeSend: function() {
              $('#button-upload').button('loading');
            },
            complete: function() {
              $('#button-upload').button('reset');
            },
            success: function(json) {
              if (json['error']) {
                alert(json['error']);
              }

              if (json['success']) {
                $('#price_save').removeAttr('disabled');
                $('#price_file').val(json['filename']);
              }
            },
            error: function(xhr, ajaxOptions, thrownError) {
              alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
            }
          });
        }
      }, 500);
    });
  //button-upload

  //price_save
    $(document).on('click', '#price_save', function(){
      $.ajax({
        url: 'index.php?route=' + unixml_path + '/importSavePrice' + unixml_token,
        method: 'post',
        data: $('#price_name, #price_comment, #price_file'),
        dataType: 'json',
        success: function(json) {
          $('#upload_price_close').click();
          update_import_list();
        }
      });
    });
  //price_save - сохранение нового импорта

  //import_read_xml - считывание XML для отображения в админке
    $(document).on('click', '#import_read_xml', function(){
      id = $('input[name="unixml_import_id"]').val();
      $('#import_read_xml').text(text_js_uploading);
      $.ajax({
        url: 'index.php?route=' + unixml_path + '/importReadXml' + unixml_token + '&import_id=' + id,
        method: 'post',
        data: $('#setting_price_item input[name="price_file"], #setting_price_item input[name="login"], #setting_price_item input[name="pass"]'),
        dataType: 'json',
        success: function(json) {
          $('#import_read_xml').text(text_js_import_read_xml);
          $('#xml_res').html(json);
          $('.h4_div_frst + div').slideUp(200);
          $('.h4_div_frst').find('span').text('+');
        }
      });
    });
  //import_read_xml

  //addImportManufacturerMatch - функция добавления строки соответствий производителей
    function addImportManufacturerMatch() {
      manufacturer_match_row = $('#manufacturer_match_row').val();

      html = $('#example_row_manufacturer').html().replace(/777888777/g, manufacturer_match_row);
      $('#replace_manufacturer tbody').append(html);
      manufacturer_match_row++;

      $('#manufacturer_match_row').val(manufacturer_match_row);
    }
  //addImportManufacturerMatch

  //loadXMLCategories - функция подгрузки категорий из фида
    function loadXMLCategories(){
      $('#category_xml_loader').html('<strong style="color:red;margin-top:10px;">' + text_js_loading_xml_cats + ' <i class="fa fa-spinner fa-spin" aria-hidden="true"></i></strong>');
      $.ajax({
        url: 'index.php?route=' + unixml_path + '/importGetXMLCategories' + unixml_token
              + '&xml=' +  encodeURIComponent($('.price_file_block input').val())
              + '&import_id=' +  encodeURIComponent($('input[name="unixml_import_id"]').val())
              + '&import_root=' +  encodeURIComponent($('input[name="unixml_import_xml_root"]').val())
              + '&import_categories=' +  encodeURIComponent($('input[name="unixml_import_xml_categories"]').val())
              + '&import_category=' +  encodeURIComponent($('input[name="unixml_import_xml_category"]').val())
              + '&import_category_id=' +  encodeURIComponent($('input[name="unixml_import_xml_category_id"]').val())
              + '&import_category_parent_id=' +  encodeURIComponent($('input[name="unixml_import_xml_category_parent_id"]').val())
              + '&import_category_name=' +  encodeURIComponent($('input[name="unixml_import_xml_category_name"]').val()),
        dataType: 'json',
        success: function(json) {
          $('#category_xml_loader').html(text_js_loaded_xml_cats);
          setTimeout(function(){
            $('#category_xml_loader').html('');
          },2000);
        }
      });
    }
  //loadXMLCategories

  //addImportCategoryMatch - функция добавления строки соответствий категорий
    function addImportCategoryMatch() {
      category_match_row = $('#category_match_row').val();

      html = $('#example_row_category').html().replace(/777888777/g, category_match_row);

      $('#replace_category tbody').append(html);

      importcategoryautocomplete(category_match_row);
      importcategoryxmlautocomplete(category_match_row); //NEW

      category_match_row++;

      $('#category_match_row').val(category_match_row);
    }
  //addImportCategoryMatch

  //importcategoryautocomplete - функция автокомплит категории
    function importcategoryautocomplete(index) {
      $('input[name=\'replace_category[' + index + '][ocname]\']').autocomplete({
        'source': function(request, response) {
          $.ajax({
            url: 'index.php?route=' + unixml_path + '/importAutocompleteCategory' + unixml_token + '&filter_name=' +  encodeURIComponent(request),
            dataType: 'json',
            success: function(json) {
              console.log(json);
              response($.map(json, function(item) {
                return {
                  label: item.name,
                  value: item.category_id
                }
              }));
            }
          });
        },
        'select': function(item) {
          $('input[name=\'replace_category[' + index + '][oc]\']').val(item['value']);
          $('input[name=\'replace_category[' + index + '][ocname]\']').val(item['label']);
        }
      });
    }
  //importcategoryautocomplete

  //importcategoryxmlautocomplete - функция автокомплит категории xml
    function importcategoryxmlautocomplete(index) {
      $('input[name=\'replace_category[' + index + '][xml]\']').autocomplete({
        'source': function(request, response) {
          $.ajax({
            url: 'index.php?route=' + unixml_path + '/importAutocompleteCategoryXML' + unixml_token + '&filter_name=' +  encodeURIComponent(request)
                  + '&xml=' +  encodeURIComponent($('.price_file_block input').val())
                  + '&import_id=' +  encodeURIComponent($('input[name="unixml_import_id"]').val()),
            dataType: 'json',
            success: function(json) {
              console.log(json);
              response($.map(json, function(item) {
                return {
                  label: item,
                  value: item
                }
              }));
            }
          });
        },
        'select': function(item) {
          $('input[name=\'replace_category[' + index + '][xml]\']').val(item['label']);
        }
      });
    }
  //importcategoryxmlautocomplete

  //addImportAttributeMatch - функция добавления строки соответствий категорий
    function addImportAttributeMatch() {
      attribute_match_row = $('#attribute_match_row').val();

      html = $('#example_row_attribute').html().replace(/777888777/g, attribute_match_row);
      $('#replace_attribute tbody').append(html);
      attribute_match_row++;

      $('#attribute_match_row').val(attribute_match_row);
    }
  //addImportAttributeMatch

  //h4_div - разворачивание/сворачивание в правом блоке
    $(document).on('click', '.h4_div', function(){
      $(this).toggleClass('minus');
      $(this).next('div').slideToggle(200);
      $(this).find('span').text($(this).find('span').text() == '-' ? '+' : '-');
    });
  //h4_div

  //button-upload-popup - функция загрузки файла в окне настроек
    $(document).on('click', '#button-upload-popup', function() {
      $('#form-upload').remove();
      $('body').prepend('<form enctype="multipart/form-data" id="form-upload" style="display: none;"><input type="file" name="file" /></form>');
      $('#form-upload input[name=\'file\']').trigger('click');
      if (typeof timer != 'undefined') {clearInterval(timer);}

      timer = setInterval(function() {
        if ($('#form-upload input[name=\'file\']').val() != '') {
          clearInterval(timer);

          $.ajax({
            url: 'index.php?route=' + unixml_path + '/importUploadFile' + unixml_token,
            type: 'post',
            dataType: 'json',
            data: new FormData($('#form-upload')[0]),
            cache: false,
            contentType: false,
            processData: false,
            beforeSend: function() {
              $('#button-upload').button('loading');
            },
            complete: function() {
              $('#button-upload').button('reset');
            },
            success: function(json) {
              if (json['error']) {
                alert(json['error']);
              }

              if (json['success']) {
                $('#setting_price_item input[name="price_file"]').val(json['filename']);
              }
            },
            error: function(xhr, ajaxOptions, thrownError) {
              alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
            }
          });
        }
      }, 500);
    });
  //button-upload-popup

  //import_clear_data - очистка информации по импорту
    $(document).on('click', '#import_clear_data', function(){
      id = $('input[name="unixml_import_id"]').val();
      $.ajax({
        url: 'index.php?route=' + unixml_path + '/importClearData' + unixml_token + '&import_id=' + id,
        method: 'post',
        dataType: 'json',
        success: function(json) {
          alert(text_js_tmp_deleted);
          $('#price_setting .close').click();
          update_import_list();
        }
      });
    });
  //import_clear_data

  //save_and_start - сохраняем и запускаем импорт
    $(document).on('click', '#save_and_start', function(){
      save_price_item($('input[name="unixml_import_id"]').val());
    });
  //save_and_start

  //save_and_start - сохраняем и запускаем импорт
    $(document).on('click', '#save_price_item', function(){
      save_price_item();
    });
  //save_and_start

  //save_price_item - функция сохранения настроек импорта и если есть в start - запуск импорта
    function save_price_item(start = false){
      $.ajax({
        url: 'index.php?route=' + unixml_path + '/importSavePriceSetting' + unixml_token + '',
        method: 'post',
        data: $('#price_setting input[type="text"], #price_setting input[type="hidden"], #price_setting select, #price_setting textarea, #price_setting input[type="checkbox"]:checked'),
        dataType: 'json',
        success: function(json) {
          $('#price_setting_load').html(text_js_import_set_save);
          $('#price_setting_load + .modal-footer').hide();
          update_import_list();
          setTimeout(function(){
            $('#price_setting_close').click();
            $('#price_setting_load').html('');
            $('#price_setting_load + .modal-footer').show();
            if(start){
              $('.price_start[data-id="' + start + '"]').click();
            }
          },1500);
        }
      });
    }
  //save_price_item

  //load_item_set - загрузка параметров поля
    $(document).on('click', '.load_item_set', function(){
      $('#price_setting_item_set_load').html('<div class="text-center">' + text_js_loading + '</div>');
      $('#price_setting_item_set_load').load('index.php?route=' + unixml_path + '/importLoadPriceSettingItem' + unixml_token + '&set=' + $(this).data('set') + '&item=' + $(this).data('item') + '&id=' + $(this).data('price'));
    });
  //load_item_set

  //save_price_setting_item_set - сохранение параметров поля
    $(document).on('click', '#save_price_setting_item_set', function(){
      id = $('#setting_item input[type="hidden"]').val();
      $.ajax({
        url: 'index.php?route=' + unixml_path + '/importSavePriceSettingItemSet' + unixml_token + '',
        method: 'post',
        data: $('#setting_item input[type="hidden"], #setting_item input[type="text"], #setting_item input[type="checkbox"]:checked, #setting_item select, #setting_item textarea'),
        dataType: 'json',
        success: function(json) {
          $('#price_setting_item_set_load').html(text_js_import_set_save2);
          get_link_key(id);
          setTimeout(function(){
            $('#price_setting_item_set_load').html('<div class="text-center">' + text_js_loading + '</div>');
            $('#price_setting_item_set_close').click();
          },1500);

        }
      });
    });
  //save_price_setting_item_set

   $('[data-toggle="popover"]').popover({placement:'right',html:true});

  //dok - появления кнопки удаления товаров
    $('#dok').change(function(){
      if($('#dok:checked').val()){
        $('#delete_price_products').fadeIn(300);
      }else{
        $('#delete_price_products').fadeOut(100);
      }
    });
  //dok - появления кнопки удаления товаров

  //delete_price_products - удаление товаров поставщика
    $(document).on('click', '.price_delete_product', function(){
      $('#id_delete_product_xml').val($(this).data('id'));
    });
    $(document).on('click', '#delete_price_products', function(){
      id = $('#id_delete_product_xml').val();
      di = 0;
      if($('#di:checked').val()){
        di = 1;
      }
      $('#price_delete_product_load').html('<h3 class="text-center">' + text_js_deleting_wait + ' <i class="fa fa-spinner fa-spin" aria-hidden="true"></i></h3>');
      $('#price_delete_product_load+div').slideUp(50);
      $.ajax({
        type: 'POST',
        url: 'index.php?route=' + unixml_path + '/deleteProducts' + unixml_token + '&id=' + id + '&di=' + di,
        success: function(json) {
          if(json > 0){
            $('#price_delete_product_load').html('<h3 class="text-center">' + text_js_deleted_pro + ': ' + json + '</h3>');
          }else{
            $('#price_delete_product_load').html('<h3 class="text-center">' + text_js_xml_pro_not_found + '</h3>');
          }
        }
      });
    });
  //delete_price_products - удаление товаров поставщика

  //price_start - запуск импорта с админки
    $(document).on('click', '.price_start', function(){
      id = $(this).data('id');
      $('.price_start').hide(); //прячем остальные кнопки запуска
      $('.price_pause[data-id="' + id + '"]').show(); //показываем только кнопку текущего импорта
      $('.price_setting[data-id="' + id + '"], .price_delete[data-id="' + id + '"], .price_delete_product[data-id="' + id + '"]').hide(); //прячем настройки,удаление,удаление товаров

      $('#stat_title strong').text($(this).data('name'));
      $('#stat_title span').text(id);
      if (typeof import_progress != 'undefined') {clearInterval(import_progress);} //есть есть обращение на сервер по забору данных импорта - останавливаем для нового импорта

      if(id != $('#ciwin').val()){ //если это новый запуск импорта
        import_main(id); //Запускаем функцию выгрузки
      }

      //вкл
      import_progress = setInterval(function(){ //каждые 1 сек опрашиваем сервер для получения статуса
        get_status(id);
      }, 1000);
    });
  //price_start - запуск импорта с админки

  //import_main - функция запуска импорта
    function import_main(id){
      $('.import_status').html("");
      $('#load_prices tr.price_list_' + id + ' td.status_in_list').html('<span style="color:green;"><i class="fa fa-spinner fa-spin" aria-hidden="true"></i> ' + text_js_import_processing + '<small></small></span>');
      $('#server_query_counter').html(text_js_iter + ' - ' + window.server_counter);
      $.ajax({
        type: 'POST',
        url: 'index.php?route=' + unixml_path + '/import' + unixml_token + '&import_id=' + id,
        statusCode: {
          200: function(json) {
            if(json != 'pause' && json != 'final'){
              response_to_log(json, '200', json);
            }
          },
          405: function(json) {
            window.server_counter++;
            //import_main(id);
            response_to_log(json, '405', json.responseText);
          },
          500: function(json) {
            window.server_counter++;
            //import_main(id);
            response_to_log(json, '500', json.responseText);
          },
          503: function(json) {
            window.server_counter++;
            //import_main(id);
            response_to_log(json, '503', json.responseText);
          },
          504: function(json) {
            window.server_counter++;
            //import_main(id);
            response_to_log(json, '504', json.responseText);
          }
        }
      });
    }
  //import_main - функция запуска импорта

  //response_to_log - функция логирования ответов сервера
    function response_to_log(json, code, text){
      if ($.isPlainObject(json)){
        if(text == ''){
          text = text_js_server_response + json.statusText + text_js_uni_start_again;
        }
      }
      $('#server-import-response-log').slideDown(300);
      $('#server-import-response-log tbody').append('<tr><td>' + (window.server_counter - 1) + '</td><td>' + code + '</td><td>' + text + '</td></tr>');
    }
  //response_to_log

  //get_status - функция проверки статуса импорта
    function get_status(id){
      $('#ciwin').val(id);
      $.ajax({
        type: 'POST',
        url: 'index.php?route=' + unixml_path + '/getImportProgress' + unixml_token + '&import_id=' + id,
        success: function(json) {

          if(json['error']){
            $('#server-import-error-log').append('<div>' + json['error'] + '</div>');
          }

          //пауза
          if(json['pause']){
            clearInterval(import_progress); //импорт на паузе
            $('#pause_process').hide();
            $('#resume_process').show();
            $('#current_import_status').html('&nbsp; <span style="color:red;font-weight:bold;">' + text_js_import_on_pause + '</span>');
            update_import_list();
          }else{
            $('#pause_process').show();
            $('#resume_process').hide();
            $('#current_import_status').html('&nbsp; <span style="color:green;">' + text_js_import_processing + '</span>');
          }
          //пауза

          pi_html = '<div class="row">';
            pi_html += '<div class="col-sm-4 stat-left-nav">';
              fori = 1;
              $.each(json['steps'], function (index, value) {
                pi_html += '<span class="stat-span-' + index + '" data-index="' + index + '">' + fori + ') ' + value['name'] + '</span>';
                fori++;
              });
            pi_html += '</div>';

            pi_html += '<div class="col-sm-8 stat-right-nav">';
              $.each(json['steps'], function (index, value) {
                pi_html += '<div class="stat-block-' + index + '">';
                  pi_html += '<h3>' + value['name'] + '</h3>';
                  if(value['memory']){ //если есть данные
                    pi_html += '<div>' + text_js_max_mem + json['memory'] + ' Mb.</div>';
                    pi_html += '<div>' + text_js_curr_mem + value['memory'] + ' Mb.</div>';
                    pi_html += '<div>' + text_js_all_sql + value['sql'] + '</div>';
                    pi_html += '<div>' + text_js_all_time + value['time'] + '</div>';

                    if(index == 'category'){
                      pi_html += '<div>' + text_js_new_cat + value['category'] + '</div>';
                    }
                    if(index == 'product'){
                      pi_html += '<div>' + text_js_new_attr + value['attributes'] + '</div>';
                      pi_html += '<div>' + text_js_new_pro + value['add'] + '</div>';
                      pi_html += '<div>' + text_js_upd_pro + value['update'] + '</div>';
                    }
                    if(index == 'image'){
                      pi_html += '<div>' + text_js_new_img + value['image'] + '</div>';
                      $('.price_list_' + id + ' td.status_in_list span small').html(text_js_process_image + ': <b>' + value['image'] + '</b>');
                    }
                    if(index == 'images'){
                      pi_html += '<div>Загружено доп. фото: ' + value['images'] + '</div>';
                      $('.price_list_' + id + ' td.status_in_list span small').html(text_js_process_images + ': <b>' + value['images'] + '</b>');
                    }
                    if(index == 'finish'){
                      pi_html += text_js_import_finish;
                    }
                  }else{
                    pi_html += '<div>' + text_js_continue + '</div>';
                  }

                pi_html += '</div>';

                if(index == 'finish' && value['memory'] && value['time']){
                  clearInterval(import_progress); //импорт завершен
                  $('#pause_process, #resume_process').hide();
                  $('#current_import_status').text("");
                  update_import_list();
                }

              });
            pi_html += '</div>';
          pi_html += '</div>';

          $('.import_status').html(pi_html);
          $('.stat-span-' + json['step']).click();
        }
      });
    }
  //get_status

  //pause_process - пауза процесса импорта
    $(document).on('click', '#pause_process', function(e){
      $.ajax({
        url: 'index.php?route=' + unixml_path + '/importPauseProcess' + unixml_token + '&id=' + $('#ciwin').val()
      });
    });
  //pause_process

  //resume_process - продолжение процесса импорта
    $(document).on('click', '#resume_process', function(e){
      id = $('#ciwin').val();
      $('#price_start_close').click();
      $('#current_import_status').text('');
      $.ajax({
        url: 'index.php?route=' + unixml_path + '/importResumeProcess' + unixml_token + '&id=' + id,
        dataType: 'json',
        method: 'post',
        success: function(json) {
          $('#ciwin').val(""); //обнуляем current id window для того что бы система заново запустила процесс выгрузки в строке $(document).on('click', '.price_start', function(){
          setTimeout(function(){
            $('.price_start[data-id="' + id + '"]').click();
            update_import_list();
          }, 500);
        }
      });
    });
  //resume_process

  $(document).on('click', '.stat-left-nav>span', function(){
    $('.stat-right-nav>div').hide();
    $('.stat-right-nav>div.stat-block-' + $(this).data('index')).show();
    $('.stat-left-nav>span').removeClass('active');
    $(this).addClass('active');
  });
//Функции импорта

//Функции сервиса

  //start_delete - поиск и удаление лишних фото
    $(document).on('click', '#start_delete', function(e){
      e.preventDefault();
      $('#start_delete').hide();
      $('#start_delete').after('<div id="count_delete" class="btn btn-success">' + text_js_skan_file_db + ' <i class="fa fa-spinner fa-spin" id="fa-spin" aria-hidden="true"></i></div>');
      $.ajax({
        url: 'index.php?route=' + unixml_path + '/serviceDeleteImage' + unixml_token + '',
        dataType: 'json',
        data: $('#unixml_delete_direct, #unixml_delete_table'),
        method: 'post',
        success: function(json) {
          $('#count_delete').html(json);
          setTimeout(function(){
            $('#count_delete').hide();
            $('#start_delete').show();
          }, 3000);
        }
      });
    });
  //start_delete

  //search_double - поиск дублей товаров
    $(document).on('click', '#search_double', function(e){
      e.preventDefault();
      $('#double_skan_result').text("");
      $('#delete_double').hide();
      btn_text = $('#search_double').html();
      $('#search_double').html(text_js_service_double + ' <i class="fa fa-spinner fa-spin" id="fa-spin" aria-hidden="true"></i>');
      $.ajax({
        url: 'index.php?route=' + unixml_path + '/serviceSearchDouble' + unixml_token + '',
        dataType: 'json',
        data: $('#unixml_double_field'),
        method: 'post',
        success: function(json) {
          $('#search_double').html(btn_text);

          if(Object.keys(json['products']).length == 1000){
            alert(text_js_1000_pro);
          }

          if(json['products'].length !== 0){
            result = '<table class="table table-bordered">';
            result += text_js_double_table_header;
            $.each(json['products'], function (index, product) {

              result += '<tr class="original">';
                result += '<td><img class="img-response" src="' + product['image'] + '"></td>';
                result += '<td>Оригинал</td>';
                result += '<td>' + index + '</td>';
                result += '<td>' + product['name'] + '</td>';
                result += '<td>' + product['model'] + '</td>';
                result += '<td>' + product['sku'] + '</td>';
                result += '<td>' + product['price'] + '</td>';
                result += '<td class="text-right"><a href="' + product['edit'] + '" data-toggle="tooltip" title="' + text_js_edit_product + '" class="btn btn-primary" target="_blank"><i class="fa fa-pencil"></i></a> <a href="' + product['view'] + '" data-toggle="tooltip" target="_blank" title="' + text_js_view_product + '" class="btn btn-success"><i class="fa fa-eye"></i></a></td>';
              result += '</tr>';

              $.each(product['double'], function (double_id, double) {
                result += '<tr class="double">';
                  result += '<td><img class="img-response" src="' + double['image'] + '"></td>';
                  result += '<td>' + text_js_double_pro + '</td>';
                  result += '<td>' + double_id + '</td>';
                  result += '<td>' + double['name'] + '</td>';
                  result += '<td>' + double['model'] + '</td>';
                  result += '<td>' + double['sku'] + '</td>';
                  result += '<td>' + double['price'] + '</td>';
                  result += '<td class="text-right"><a href="' + double['edit'] + '" data-toggle="tooltip" title="' + text_js_edit_product + '" class="btn btn-primary" target="_blank"><i class="fa fa-pencil"></i></a> <a href="' + double['view'] + '" data-toggle="tooltip" target="_blank" title="' + text_js_view_product + '" class="btn btn-success"><i class="fa fa-eye"></i></a></td>';
                result += '</tr>';
              });

            });
            result += '</tbody></table>';
            $('#double_skan_result').html(result);
            $('#delete_double').show();
            $('#delete_double').attr('data-delete', json['delete']);
          }else{
            alert(text_js_double_pro_not_found);
          }

        }
      });
    });
  //search_double

  //delete_double - удаление дублей товаров
    $(document).on('click', '#delete_double', function(e){
      e.preventDefault();
      $(this).prop('disabled', true);
      $('#delete_double').after('<div id="count_double">' + text_js_double_deleted + ' <i class="fa fa-spinner fa-spin" id="fa-spin" aria-hidden="true"></i></div>');
      $.ajax({
        url: 'index.php?route=' + unixml_path + '/serviceDeleteDouble' + unixml_token + '',
        dataType: 'json',
        data: {'delete': $('#delete_double').data('delete')},
        method: 'post',
        success: function(json) {
          $('#count_double').html(json);
          $('#start_double').fadeOut(500);
          $('#delete_double').hide();
          setTimeout(function(){
            $('#double_skan_result .double').remove();
          }, 3000);
          setTimeout(function(){
            $('#count_double').text('');
          }, 5000);
        }
      });
    });
  //delete_double

  //start_url - поиск дублей ЧПУ
    $(document).on('click', '#start_url', function(e){
      e.preventDefault();
      $(this).prop('disabled', true);
      btn_text = $('#start_url').html();
      $('#start_url').html(text_js_double_scannign + ' <i class="fa fa-spinner fa-spin" id="fa-spin" aria-hidden="true"></i>');
      $.ajax({
        url: 'index.php?route=' + unixml_path + '/serviceUrlDouble' + unixml_token + '',
        dataType: 'json',
        method: 'post',
        success: function(json) {
          $('#start_url').html(btn_text);
          if(json.length){
            table = '<table class="table table-bordered">';
            table += text_js_url_table_header;
            $.each(json, function (index, value) {
              table += '<tr>';
              table += '<td>' + value['type'] + '</td>';
              table += '<td>' + value['name'] + '</td>';
              table += '<td>';
              $.each(value['keywords'], function (index, keyword_value) {
                table += '<div class="input-group"><span class="input-group-addon"><img src="language/' + keyword_value['language_code'] + '/' + keyword_value['language_code'] + '.png"></span>'; //3.x only
                table += '<input data-id="' + keyword_value['seo_url_id'] + '" type="text" class="form-control" value="' + keyword_value['keyword'] + '">';
                table += '<span class="input-group-addon save_url_item"><i class="fa fa-floppy-o" aria-hidden="true"></i></span></div>';
              });
              table += '</td>';
              table += '</tr>';
            });
            table += '</table>';
          }else{
            table = text_js_double_url_not_found;
          }
          $('#start_url').before(table);
          setTimeout(function(){$('#start_url').fadeOut(500);}, 1000);
        }
      });
    });
  //start_url

  //start_noimage - поиск товаров без фото
    $(document).on('click', '#start_noimage', function(e){
      e.preventDefault();
      btn_text = $('#start_noimage').html();
      $('#start_noimage').html(text_js_wo_image_scannign + ' <i class="fa fa-spinner fa-spin" id="fa-spin" aria-hidden="true"></i>');
      $.ajax({
        url: 'index.php?route=' + unixml_path + '/serviceSearchNoImage' + unixml_token + '',
        dataType: 'json',
        data: $('#unixml_double_field'),
        method: 'post',
        success: function(json) {
          $('#start_noimage').html(btn_text);
          result = '<table class="table table-bordered table-no-image">';
          result += text_js_wo_image_table_header;
          if(json.length !== 0){
            product_i = 1;
            $.each(json, function (product_id, product) {
              result += '<tr><td>' + product_i + '</td>';
              result += '<td>';
              if(product['image']){
                result += product['image'];
              }
              if(product['images']){
                $.each(product['images'], function (image_i, image) {
                  result += '<div> - доп фото ' + image + '</div>';
                });
              }
              result += '</td>';
              result += '<td>' + product['product_id'] + '</td>';
              result += '<td>' + product['name'] + '</td>';
              result += '<td>' + product['model'] + '</td>';
              result += '<td>' + product['sku'] + '</td>';
              result += '<td>' + product['price'] + '</td>';
              result += '<td class="text-right"><nobr><a href="' + product['edit'] + '" data-toggle="tooltip" title="' + text_js_edit_product + '" class="btn btn-primary" target="_blank"><i class="fa fa-pencil"></i></a> <a href="' + product['view'] + '" data-toggle="tooltip" target="_blank" title="' + text_js_view_product + '" class="btn btn-success"><i class="fa fa-eye"></i></a></nobr></td></tr>';
              product_i++;
            });
            $('#delete_double').show();
          }else{
            result += '<tr class="original"><td class="text-center" colspan="8">' + text_js_image_scan_not_found + '</td></tr>';
          }
          result += '</tbody></table>';
          $('#noimage_result').html(result);
        }
      });
    });
  //start_noimage

  //delete_noimage - удаление дефектных фото
    $(document).on('click', '#delete_noimage', function(){
      btn_text = $('#start_noimage').html();
      $('#delete_noimage').html('Обработка базы и фото <i class="fa fa-spinner fa-spin" id="fa-spin" aria-hidden="true"></i>');
      $.ajax({
        url: 'index.php?route=' + unixml_path + '/serviceDeleteNoImage' + unixml_token + '',
        dataType: 'json',
        success: function(json) {
          $('#delete_noimage').html(json);
          setTimeout(function(){
            $('#delete_noimage').html(btn_text);
          }, 3000);
        }
      });
    });
  //delete_noimage

  //delete_data - удаление всех данных
    $(document).on('click', '#delete_data', function(){
      $('#deletedata_result').html('<span class="btn btn-warning">' + text_js_db_scannign_del + ' <i class="fa fa-spinner fa-spin" id="fa-spin" aria-hidden="true"></i></span>');
      $.ajax({
        url: 'index.php?route=' + unixml_path + '/serviceDeleteData' + unixml_token + '',
        data: $('#delete_list input:checked'),
        dataType: 'json',
        method: 'post',
        success: function(json) {
          $('#deletedata_result').html('<span class="btn btn-success">' + json + '</span>');
          setTimeout(function(){
            $('#deletedata_result').text("");
          }, 3000);
        }
      });
    });
  //delete_data

  $(document).on("click",function(){
    setTimeout(function(){
      $('[data-toggle="tooltip"]').tooltip('hide');
    },500);
  });

//Функции сервиса
