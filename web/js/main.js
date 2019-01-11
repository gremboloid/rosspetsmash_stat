// начальная загрузка
$(function(){
    var tableView = { },
        utils = global_data.utils;
    columnsAlignment();
    utils.addDatePicker();
    utils.sendForm();
// Отображение выпадающего списка в статистике использования конструктора отчетов 
    var $repLog = $('.replog');
    if ($repLog.length > 0) {
        $repLog.on('click',function() {
            var idContractor = global_data.utils.getNumbers($(this).parent('tr').attr('id')),
                $currentSubElement = $('#tr' + idContractor);
            if ($currentSubElement.hasClass('loaded')) {
                $currentSubElement.slideToggle();
            } else {
                $('#current-contractor').val(idContractor);
                var frmData = $('#frmcontrol').serialize();
                $.ajax({
                     url: global_data.baseURI + '/model/detail-log-info',
                     type: 'POST',
                     dataType: 'json',
                     data: { frm_data : frmData},
                     success: function(response) {
                        $currentSubElement.find('.block-contractor').html(response.message);
                        $currentSubElement.addClass('loaded');
                        $currentSubElement.slideToggle();
                    }
                })
            }
        });
    }
    $(window).on('resize',function(){
        columnsAlignment();
    }); 
    // показ модального окна при клике на ссылку
    $('a[data-modal]').click(function(event) {
        var mdl_settings = {
            fadeDuration: 250
        };
        if ($(this).hasClass('noclose')) {
            mdl_settings.showClose = false;
        }
        event.preventDefault();        
        $(this).modal( mdl_settings );
    });
    	// функция отображения выпадающего меню
    $('.js-dropdown').not('.dropdown-menu').click(function(){
            $(this).contents('.dropdown-menu').toggleClass('hide');
    });
    $('body').click(function(e) {
            var $target = $(e.target);
            if (!$target.hasClass('js-dropdown') && $target.parents('.js-dropdown').length === 0) {
                    $('.dropdown-menu').addClass('hide');
            }
    });
// кнопка забыли пароль
$('#SendEmail').click(function(e) {
    e.preventDefault();
    var $frm = $('#MailSenderForm');
    $frm.validate();
    addFormValidationRules();
    if ($frm.valid()) {
        $.ajax({
            url: global_data.baseURI + '/custom/is-email-exist',
            type: 'POST',
        dataType: 'json',
            data: { email : $('#user_email').val() },
            success: function(response) {
                if (response.STATUS != 1) {
                    showMessage('<div>'+response.MESSAGE+'</div>');
                    return;
                }
                 $.ajax({
                    url: global_data.baseURI + '/custom/send-new-password',
                    type: 'POST',
                    dataType: 'json',
                    data: { email : $('#user_email').val() },
                    success: function(response) {
                        showMessage(response.MESSAGE);
                    }
                });
            }
        });
        
    } else {
    }
});
    
    // постраничная навигация
    $('.pagination-click').click(function(event) {
        event.preventDefault();
        var select_option = $('#select-rows-count').children('option:selected');
        if (select_option.length > 0) {
            $('#rows_count').val(select_option.val()); 
        }
        $('#current_page').val($(this).data('page'));
        $('#if-submit').add('#dyr-filter-submit').trigger('click');
    });    
    
    /**
     * Выбор элементв формы фильтров
     */
    $('#filterDataForm select').add('#time_filter').change(function(){
        var select_option = $('#select-rows-count').children('option:selected');
        if (select_option.length > 0) {
            $('#rows_count').val(select_option.val()); 
        }
       $('#if-submit').add('#dyr-filter-submit').trigger('click');
    });
    // сортировка таблицы
        $('.directory_tbl th[data-sort]').bind('click',function(){
       tableView.sortTable = $(this).data('sort');
       tableView.filterForm = $('#filterDataForm');
       tableView.sortType = $('#sortType');
       tableView.sortColumn = $('#sortColumn');
       tableView.sortColumn.val(tableView.sortTable);
       if (tableView.sortType.val() === 'ASC') {
           tableView.sortType.val('DESC');
       }
       else {
           tableView.sortType.val('ASC');
       }
       tableView.filterForm.submit();
   });
    // отображение формы ввода
    $('.viewForm').add('.list-of-forms .new-form').click(function() {
        var ifId,
            ajaxMethod,
            ajaxParams = [];
        if ($(this).hasClass('new-form')) {
            var $ctor = $('#new-form-ctr'),                
                $smonth = $('#nf_period_block .month'),
                $syear = $('#nf_period_block .year'),
                ftype = global_data.utils.getNumbers($(this).data('type'));
                ajaxParams.push(ftype);
                ajaxParams.push({  month : $smonth.children('option:selected').val(),
                                    year : $syear.children('option:selected').val()});
            if ($ctor.length !== 0) {
                if ($ctor.hasClass('current-contractor')) { 
                    ajaxParams.push($ctor.val());
                } else {
                    ajaxParams.push($ctor.children('option:selected').val());
                }
            }
            ajaxMethod = 'get-new-form';
            
        } else {
            ifId = global_data.utils.getNumbers($(this).parents('tr').attr('class'));
            ajaxMethod = 'get-exist-form';
            ajaxParams.push(ifId);
        }
        var $informMessage = $('<p>Пожалуйста подождите...</p>');
            $informMessage.modal({
                closeExisting: false,
                escapeClose: false,
                clickClose: false,
                closeButton: false,
                showClose: false
            }); 
        $.ajax({
            url: global_data.formsURI + '/' + ajaxMethod,
            type: 'POST',
            dataType: "json",
            data: {params: ajaxParams} ,
            success: function(response) {
              //  console.log(response);
                $.modal.close();
                if (response.hasOwnProperty('errorCode')) {
                    $('<div class="center"><p>' + response.message +'</p>'+ global_data.close_button+'</div>').modal();
                        return;
                }
                var elForm = '.el_of_form input';
                $('.main-wrapper').prepend(response.message);
                var defMonth = $('#if-month').val(),
                    defYear = $('#if-year').val();
                global_data.utils.numberFormat(elForm);
                $('.winclose').add('#input_form_cancel').click (function() {                    
                    $('.rstat-mask').remove();
                    $('.input_form').remove();                                      
                });
                $('.table_form').on('click' ,'.el_of_form input' , function() {
                    var val = $(this).val();
                    if (global_data.utils.getNumbers(val) == 0) {
                        $(this).val('');
                    }
                });
                $('.table_form').on('blur' ,'.el_of_form input' , function() {
                    var val = $(this).val();
                    if (val == '') {
                        $(this).val(0);
                    }                        
                });
                $('.table_form').on('keyup' ,'.el_of_form input' , function() {
                    global_data.utils.numberFormat(this);
                });
                // поиск моделей
                $('#models-filter').on('keyup',function(){
                    var filterValue = $(this).val().toLowerCase(),
                    $filterElements = $('.model_name').not('#new-element-row .model_name');
                    if (filterValue == '') {
                        $('.el_of_form').not('#new-element-row').removeClass('hide');
                    } else {
                        $filterElements.map(function(){
                            var elem = $(this).parents('.el_of_form'),
                                text = $(this).text().toLowerCase();
                            if (text.indexOf(filterValue) === -1) {
                                $(elem).addClass('hide');
                            } else {
                                elem.removeClass('hide');
                            }                    
                });
            }
            
        });
                // скрыть/показать блоки со странами
                $('#form_select_country').change(function () {
                    var selectOption = $(this).children('option:selected'),
                        val = selectOption.val();
                    if (val == 0) {
                        calculateFormSummary();
                    }
                    var $block = $('tbody.country_block');
                    $block.addClass('hide');
                    $('#country_block_' + val).removeClass('hide');                        
                });
                $(document).scrollTop(20);
/**
* Выбор периодов
* --------------------------------------------
*/
                $('#get_period').click(function(){
                    $.ajax({
                        url: global_data.baseURI + '/custom/show-date-change',
                        type: 'POST',
                        dataType: "json",
                        data: {params: {
                                        obj : {
                                            selected : {
                                                month : defMonth,
                                                year : defYear
                                            }
                                        }
                            }} ,
                        success: function(response) {
                     //       console.log(response);
                            if (response.hasOwnProperty('errorCode')) {
                               $('<div class="center"><p>' + response.message +'</p>'+ global_data.close_button+'</div>').modal();
                                return;
                            }
                            var mdl = $(response.message);
                            mdl.ajaxModal();
                            // кнопка 'ок'
                            $('#change-date-in-form').click(function(e) {
                                e.preventDefault();
                                var formData = $('#change-period-form').serialize() + '&contractorId=' + $('.if_contractor').val() +
                                                '&typeId=' + $('.if_type').val();
                                $.ajax({
                                    url: global_data.formsURI + '/check-form-exist',
                                    type: 'POST',
                                    dataType: "json",
                                    data: { 
                                        frm_data : formData                                                
                                    } ,
                                    success: function(response) {
                                      //  console.log(response);
                                        if (response.hasOwnProperty('errorCode')) {
                                            $('#change-period-form fieldset').after('<p class="error">'+ response.message +'</p>');
                                        } else {
                                            $('#period-month').text($('#select-month option:selected').text());
                                            $('#period-year').text($('#select-year option:selected').text());
                                            $('#if-month').val($('#select-month option:selected').val());
                                            $('#if-year').val($('#select-year option:selected').val());
                                            $.modal.close();
                                        }
                                    }
                                });
                                
                            });
                        }
                    });
                });
                //  Сохранить форму
                $('#input_form_save').on('click', function () { 
                    if ($(this).hasClass('disabled')) {
                        return false;
                    };
                    $('#save-form-button').off('click');
                    $('#save-form-button').on('click',function(){
                        $.modal.close();
                        var ajaxParams = {},
                            $elList = $('.el_of_form').not('#new-element-row'),
                            $ifDate = $('.if_date'),
                            $ec = $('.economic_form_body'),
                            $ifContractor = $('.if_contractor');
                        ajaxParams = {
                            formType : $('.input_form').attr('id'),
                            date : $ifDate.map(function() {
                                    return { type : $(this).attr('name'), value : $(this).prop('value')};
                                }).get(),
                            action : $('.table_form').attr('id'),
                            formId : ifId,
                            contractorId : $ifContractor.val(),
                            comment: $('#comment').val(),                           
                            rows_list : $elList.map(function (){ 
                                    return { 
                                        prod_id : this.id,
                                        row : $(this).children('.frm_model').attr('id'),
                                        values_list : $(this).find('input').map(function(){
                                        return { type : $(this).attr('name'), value : $(this).prop('value')};
                                        }).get()
                                    };
                                }).get()                           
                        };
                        if ($('#actuality').length != 0) {
                            if ($('#actuality').prop('checked')) {
                                ajaxParams.actuality = 1;
                            } else {
                                ajaxParams.actuality = 0;
                            }
                        }
                        if ($('#form-confirm-data').prop('checked')) {
                            ajaxParams.confirm = 1;
                        } else {
                            ajaxParams.confirm = 0;
                        }
                        if ($ec.length === 1) {
                            ajaxParams.eId = $ec.attr('Id');
                        }
                        $.ajax({
                            url: global_data.formsURI + '/form-save',
                            type: 'POST',
                            dataType: "json",
                            data: { 
                                val : JSON.stringify(ajaxParams)                                                
                            } ,
                            success: function(result) {
                            //    console.log('Форма сохранена');
                               console.log(result);
                                if (!result.hasOwnProperty('STATUS')) {
                                    var $msg = $('<div>Непредвиденная ошибка сервера</div>');
                                        $msg.modal(); 
                                        return;
                                }
                                if (result.STATUS == 3) {
                                    var $msg = $('<div>'+result.MESSAGE+'</div>');
                                    $msg.modal(); 
                                }
                                if(result.STATUS == 1) {
                                    global_data.utils.ajax.write_session_value('FLASH_SAVE_MODAL',{                            
                                        status : 0,
                                        message : 'Форма успешно сохранена'
                                        },reload
                                    );
                                    //$('.close-button').trigger('click');
                                    //$('.winclose').trigger('click');
                                }
                                if(result.STATUS == 2) {
                                    $('#input_form_save').addClass('disabled');
                                    $('#confirm-data-row').removeClass('hide');
                                    $('.table_form input').prop('readonly',true);
                                    if (result.ELEMENTS.length > 0) {
                                        $.each(result.ELEMENTS, function(idx,el){
                                            $('<div class="center"><p>Форма не сохранена. Проверьте корректность данных в заполняемой форме.</p>' + global_data.close_button +'</div>').modal();
                                            var $elem = $('#' + el.NAME),$cl_name;
                                            if ($elem.hasClass('el_of_form')) {
                                                $cl_name = $elem;
                                            } else {
                                                $cl_name = $elem.parent('.el_of_form');
                                            }
                                            $cl_name.addClass('warn');
                                            var cols = $('td',$cl_name).length + 1;
                                            var elem = '<tr class="msg_warn"><td colspan="'+ cols +'">' 
                                                + el.MESSAGE + '</td></tr>';
                                            $('input',$cl_name).removeAttr('readonly');
                                            $(elem).insertAfter($cl_name);
                                        });
                                    }
                                }
                            }
                        });                                                
                    });
                    $('#confirmSave-activate').trigger('click');
                });
                $('#save-form-ok-button').one('click', function() {
                    location.reload();
                });  
                // Удалить модель из формы
                $('.table_form').on('click' ,'.if-model-remove' , function() {
                    var $delButton = $('#delete-model-from-form'),
                        target;
                    $delButton.off('click');
                    target = $(this).closest('tr');
                    $delButton.one('click',null,target,function(e) {
                        var modelId = e.data.children('td').first().data('model'),
                            $close = $('.close-button','#confirmDeleteModel'),
                            params = {
                                elements : {
                                    contractorId : $('.if_contractor').val(),
                                    modelId : global_data.utils.getNumbers(modelId)
                                },
                                ifId : ifId
                            };
                             $.ajax({
                                url: global_data.formsURI + '/delete-models',
                                type: 'POST',
                                dataType: "json",
                                data: { 
                                     object : params
                                } ,
                            success: function(result) {
                             //   console.log(result);
                                $('td[data-model='+modelId+']').parent().remove();
                                $close.trigger('click');                                 
                            }
                        });
                        
                    });
                    $('#confirmDeleteModel-activate').trigger('click');
                    
                });                
                // добавить модель
                $('.table_form').on('click','.add-model',function(e) {
                    var contractorId = $('.if_contractor').val(),
                        formType = global_data.utils.getNumbers($('.input_form').attr('id')),
                        classifier = $(this).data('classifier'),
                        param = {};                  
                        param.contractor = contractorId;
                        param.classifier = classifier;
                        param.type = formType; 
                        param.formId = ifId ;
                     $.ajax({
                        url: global_data.formsURI + '/get-models',
                        type: 'POST',
                        dataType: "json",
                        data: { 
                                 object : param                                               
                            } ,
                            success: function(result) {
                              //  console.log(result);
                                if (result.hasOwnProperty('errorCode')) {
                                    $('<div class="center"><p>' + result.message +'</p>'+ global_data.close_button+'</div>').modal();
                                    return;
                                }
                                var $mdl = $(result.message);
                                $mdl.ajaxModal();
                                var $treeObj = $('.check-list-items').jstree({
                                        "checkbox" : {
                                        "cascade_to_hidden" : true,
                                        "three_state" : true,
                                        "keep_selected_style" : false,
                                              "tie_selection" : true,
                                                 "whole_node" : true													},
                                                    "plugins" : [ "checkbox" ]
                                    }).on('ready.jstree',function() {                                                                
                                        $('.check-list-items').on('select_node.jstree',function (event,data) {
                                            var orig = data.event.originalEvent.originalTarget;
                                                if (!$(orig).hasClass('jstree-checkbox')) {
                                                    return data.instance.toggle_node(data.node);
                                                }
                                        }).on('before_open.jstree',function(event, data) {	
                                                data.instance.uncheck_all();
                                            });
                                    });
                                    $('input:checkbox').change( function (e) {
                                         $(this).parent('label').toggleClass('blue');
                                    });
                                    $('#add-model-submit').on('click',function(){
                                        var mId,
                                            $checkedEls = $treeObj.jstree('get_bottom_checked'),
                                            chkElements = [];
                                        if ($checkedEls.length > 0) {
                                            for (mId in $checkedEls ) {
                                                chkElements.push(global_data.utils.getNumbers($checkedEls[mId]));
                                            }
                                            $.ajax({
                                                url: global_data.formsURI + '/add-models',
                                                type: 'POST',
                                            dataType: "json",
                                                data: { 
                                                    object : { models_list : chkElements,
                                                        contractorId : contractorId ,
                                                        formId : ifId,
                                                        type : formType
                                                    }                                               
                                                } ,
                                                success: function(models) {
                                                    var model,
                                                        $newRow,
                                                        $frmModel,
                                                        modelId,
                                                        modelName,
                                                        classifierId,
                                                        $targetTr,
                                                        classifierName;
                                                    if (models.type == 1) {
                                                        for ( model in models.models_list) {                                                        
                                                            modelId = models.models_list[model].model_id;
                                                            modelName = models.models_list[model].model_name; 
                                                            var modelElement = models.models_list[model].rows[0];
                                                            classifierId = $('#modelid'+modelId).data('classifier');
                                                            classifierName = $('#modelid'+modelId).data('classifiername');
                                                            $newRow = $('#new-element-row').clone(true);
                                                            $newRow.removeClass('hide').removeAttr('id');
                                                            $frmModel = $newRow.children('.frm_model');
                                                            $frmModel.attr('id','id'+ modelId).attr('data-model',modelId);
                                                            $frmModel.find('span.ib-clickable').text(modelName).attr('data-id',modelId);
                                                            if (models.hasOwnProperty('form_id')) {
                                                                $newRow.attr('id','id'+ modelElement.production);
                                                            }
                                                            $targetTr = $('#classifierid_'+classifierId);
                                                            if ($targetTr.length > 0) {
                                                                $targetTr.after($newRow) ;
                                                            } else {
                                                                $targetTr = $('#new-classifier-row').clone();
                                                                $targetTr.removeClass('hide').removeAttr('id');
                                                                $targetTr.attr('id' , 'classifierid_' + classifierId);
                                                                $targetTr.children('td').text(classifierName);
                                                                $('.clcontainer').after($targetTr);
                                                                $targetTr.after($newRow) ;
                                                            }                                                                                                                                                                                                                                                                                                                                                            //    alert(models_list[model].model_name);
                                                        }
                                                    }
                                                    if (models.type == 2) { 
                                                        var $newSummaryRow,
                                                            $newSummaryModel,
                                                            $targetSummary,
                                                            $target,
                                                            countryId,
                                                            rowIdx,
                                                            rowElement;
                                                        $targetSummary = $('#id'+classifier);
                                                        for ( model in models.models_list) {
                                                            modelId = models.models_list[model].model_id;
                                                            modelName = models.models_list[model].model_name; 
                                                            classifierName = $('#modelid'+modelId).data('classifiername');
                                                            classifierId = $('#modelid'+modelId).data('classifier');
                                                            for (rowIdx in models.models_list[model].rows) {
                                                                rowElement = models.models_list[model].rows[rowIdx];
                                                                $newRow = $('#new-element-row').clone(true);
                                                                $newRow.removeClass('hide').removeAttr('id');
                                                                $frmModel = $newRow.children('.frm_model');
                                                                $frmModel.attr('id','id'+ modelId+'_'+rowElement.country).attr('data-model',modelId);
                                                                if (models.hasOwnProperty('form_id')) {
                                                                   $newRow.attr('id','id'+ rowElement.production+'_'+rowElement.country);
                                                                }
                                                                $frmModel.find('span.ib-clickable').text(modelName).attr('data-id',modelId);
                                                                $target = $('#classifierid' + classifierId +'_' + rowElement.country);
                                                                if ($target.length > 0) {
                                                                    $target.after($newRow);  
                                                                } else {
                                                                    $target = $('#new-classifier-row').clone();
                                                                    $target.removeClass('hide').removeAttr('id');
                                                                    $target.children('td').text(classifierName);
                                                                    $('.clcontainer_'+rowElement.country ).last().after($target);
                                                                    $target.after($newRow) ;                                                                
                                                                }
                                                            }
                                                            //alert ($target_tr.attr('id'));

                                                            $newSummaryRow = $('#new-summary-element').clone(true);
                                                            $newSummaryRow.removeClass('hide').removeAttr('id');
                                                            $newSummaryModel = $newSummaryRow.children('.model_summary');
                                                            $newSummaryModel.attr('id','id' + modelId).attr('data-model',modelId);
                                                            $newSummaryModel.find('span.ib-clickable').text(modelName).attr('data-id',modelId);                                                        
                                                            $targetSummary = $('#classifierid'+classifierId);
                                                            if ($targetSummary.length > 0) {
                                                                $targetSummary.after($newSummaryRow) ;
                                                            } else {
                                                                $targetSummary = $('#new-classifier-row').clone();
                                                                $targetSummary.removeClass('hide').removeAttr('id');
                                                                $targetSummary.attr('id' , 'classifierid_' + classifierId);
                                                                $targetSummary.children('td').text(classifierName);
                                                                $('.clcontainer').last().after($targetSummary);
                                                                $targetSummary.after($newSummaryRow) ;                                                            
                                                            }
                                                        }                                                 
                                                    }
                                                    $('.close-modal').trigger('click');                                                    
                                                }
                                            });
                                        }
                                        
                                    });
                            }   
                    });
                });
                // добавить новую страну в форму
                $('#add_country').on('click', function() {
                    var params = {},
                    formType = global_data.utils.getNumbers($('.input_form').attr('id')),
                    contractorId = contractor_id = $('.if_contractor').val();
                    params.form_id = ifId;
                    params.form_type = formType;
                    params.contractor_id = contractorId;
                    $.ajax({
                        url: global_data.formsURI + '/get-countries',
                        type: 'POST',
                        dataType: "json",
                        data: { 
                            params : params                                            
                        } ,
                        success: function(result) {
                           //console.log(result);
                            if (result.hasOwnProperty('errorCode')) {
                               $('<div class="center"><p>' + result.message +'</p>'+ global_data.close_button+'</div>').modal();
                                return;
                            }
                            var $mdl = $(result.message);
                            $mdl.ajaxModal();
                            $('.checkable input').on('change', function() {
                                $(this).parent('label').toggleClass('blue');
                            });
                            $('#add-country-submit').on('click',function() {
                                var countries = $('.checkable.blue input').map(function() {
                                    return this.value;
                                }).toArray();
                                var params = {
                                        countries_list : countries,
                                        form_id : ifId,
                                        form_type : formType,
                                        contractor_id : contractorId                                        
                                    };
                                $.ajax({
                                    url: global_data.formsURI + '/add-countries',
                                    type: 'POST',
                                    dataType: "json",
                                    data: { 
                                        params : params                                            
                                    } ,
                                    success: function(result) {
                                      //  console.log(result);
                                        var text,
                                            mes = $.parseJSON(global_data.messages),
                                            $formSelect = $('#form_select_country'); 
                                        for (var idx = 0;idx < countries.length; idx++ ) {
                                            text = $('#element'+ countries[idx]).text();                                                
                                            $formSelect.append('<option value="'+countries[idx]+'">'+text+'</option>');                                                
                                        }
                                        $('.country_block').last().after(result.message);
                                        $('.el_of_form input').removeAttr('disabled');
                                        $formSelect.find('option[value="0"]').text(mes.all_countries_select);                                            
                                        $('.country_block').addClass('hide');
                                        $('.country_block').last().removeClass('hide');
                                        $('#form_select_country option').last().prop('selected', 'selected');                                    
                                        $.modal.close();                                        
                                        $('.check.modal').remove();
                                    }
                                });
                                
                                
                            });
                            
                                                                                                              
                        }
                    });
                    
                });
                // удалить страну из формы
                $('#remove_country').on('click',function() {
                    var $optSelect =  $('#form_select_country'),
                        $option = $optSelect.children('option:selected'),
                        $formSelect = $('#form_add_country'),
                        countryId = $option.val(),
                        countryName = $option.text(),
                        ctor = $('.if_contractor').val();
                    if (($option).val() == 0) {
                        return;
                    }
                    var params = {};
                    if (typeof ftype != 'undefined') {
                        params.form_type = ftype;
                    }
                    if (typeof ifId != 'undefined') {
                        params.input_form_id = ifId;
                    }
                    params.contractor_id = ctor;
                    params.country_id =countryId;
                     $.ajax({
                        url: global_data.formsURI + '/delete-countries',
                        type: 'POST',
                        dataType: "json",
                        data: { 
                            params : params                                            
                        } ,
                        success: function(result) {
                            if (result.hasOwnProperty('errorCode')) {
                                $('<div class="center"><p>' + result.message +'</p>'+ global_data.close_button+'</div>').modal();
                                return;
                            }
                            $option.remove();
                            $formSelect.append('<option value="'+countryId+'">'+countryName+'</option>');                                
                            //  $('#form_select_country :last').attr("selected", "selected");
                            $('#country_block_'+ countryId).remove();
                            $('#country_block_0').removeClass('hide'); 
                            
                       }
                   });
                    
                    
                });             
                // Обработчик подтверждения сохранения формы
                $('#form-confirm-data').change(function() {
                    if ($(this).prop('checked')) {
                        $('#input_form_save').removeClass('disabled');
                        $('#confirm-data-row').addClass('confirmed');
                    } else {
                        $('#input_form_save').addClass('disabled');
                        $('#confirm-data-row').removeClass('confirmed');            
                    }
                });
                // вывод всплывающего окна
                $('.table_form').on('click','.inform-block .ib-clickable',function() {
                    $('.informer').html('');
                    var $iBlock = $(this).parent('.inform-block');
                    $.ajax({
                        url: global_data.baseURI + '/model/display-info-block',
                        type: 'POST',
                        dataType: 'json',
                        data: {
                            object: $(this).data('model'),
                            id : $(this).data('id')
                        },
                        success: function(res) { 
                            if (res.hasOwnProperty('STATUS')) {
                                if (res.STATUS == 1) {
                                    $iBlock.children('.informer').removeClass('hide').html(res.HTML_DATA);
                                    // закрытие всплывающего окна
                                    $('.inf-close').click(function(){
                                        $('.informer').html('');
                                    });
                                }
                            }
                        }
                    });
                });
            }
        });
        
    });
    // Удалить форму
    $('.deleteForm').click (function () {
        $('#delete-form').off('click');
        var ifId = global_data.utils.getNumbers($(this).parents('tr').attr('class'));  
        $('#confirmDeleteForm-activate').trigger('click');
        //подтверждение удаления формы
        $('#delete-form').on('click',null,ifId,function(e) {
            $.ajax({
                url: global_data.formsURI + '/delete-form',
                type: 'POST',
                dataType: "json",
                data: { id: ifId } ,
                success: function(res) {
                   var msg = $.parseJSON(global_data.messages),status,flash_msg; 
                   
                    // return;
                                        
                    if (res.hasOwnProperty('errorCode')) {
                        status = 1;
                        flash_msg = msg.save_model;
                    } else {
                        status = 0;
                        flash_msg = res.message;
                    }             
                    global_data.utils.ajax.write_session_value('FLASH_SAVE_MODAL',{                            
                            'status' : status,
                            'message' : flash_msg
                        },reload
                    );                                  
                }
            });
        });
    });
       // обработчик кнопки (выгрузка классификатора)
    $('#getclassifier').on('click',function() {

           window.location = global_data.baseURI + '/custom/get-classifier-cs';          
    });
    if (typeof global_classifier_tree == 'string') {
        if ($('#classifier_tree').length == 1) {
            var jsData = $.parseJSON(global_classifier_tree);
            $('#classifier_tree').buildTree(jsData,$('#classifier-search'));
        }
    }            
});
