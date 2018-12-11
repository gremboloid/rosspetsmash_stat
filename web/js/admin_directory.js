$(function() {

    var classifierId = 0,
        ajax = global_data.utils.ajax;
    var modelName = $('.directory_tbl').attr('id');
    var leaf = false;
    // функция отображения модального окна для показа дерева классификатора
    var showClassifierModal = function () {
        $('#classifiermodal-activate').trigger('click');        
        var jsData = $.parseJSON(global_classifier_tree);        
        $('.classifier_tree').buildTree(jsData,$('#classifier-search')).on('select_node.jstree',function(event,data){
            classifierId=data.node.id; 
            leaf = (data.node.children.length == 0);
        });        
    }
    // обработка нажатия ENTER в строке поиска
    $('#directory-search').keyup(function(event) {
        if(event.keyCode == 13){
            $('#directory_search_btn').trigger('click');
        }
    });
    // запись в сессию выбранного производителя для настройки формы по умолчанию
    $('#select-contractor').change(function(){
        var v = $(this).val();
        ajax.write_session_value('CONTRACTOR_ID',v);
    });
    // обработка клика на странице
    $('body').click(function(e){
        if (e.target.id !== 'last-classifier') {
             $('ul.child-elements').addClass('hide');            
        }
    });
    // выделение элемента справочника
    $('.check-directory-element').change(function(){
        var $tr = $(this).parents('tr');
        if ($(this).prop('checked') === true) {
            $tr.addClass('row_selected');
        } else {
            $tr.removeClass('row_selected');
        }
    });
    // выделение всех элементов на странице
    $('#check_all').change(function() {
        var $els = $('.check-directory-element');
        if ($(this).prop('checked') === true) {
           $els.prop('checked',true).parents('tr').addClass('row_selected');
        } else {
            $els.prop('checked', false).parents('tr').removeClass('row_selected');
        }
    });
    // выбор числа строк на странице
    $('#select-rows-count').change(function(){
        var select_option = $(this).children('option:selected');
        $('#rows_count').val(select_option.val());
        $('#dyr-filter-submit').trigger('click');
    });
    // раскрыть меню классификатора
    $('#last-classifier').click(function(){
        $('.child-elements').toggleClass('hide');
    });
    // выбор подраздела классификатора
    $('.child-elements li > a').click(function() {
        $('#classifier').val($(this).data('classifier'));
        $('#dyr-filter-submit').trigger('click');
    });
    // вызов модального окна выбора классификатора
    $('#get_classifier').click(function(e) {        
       showClassifierModal();      
       $('#select-classifier').addClass('filter');       
    });
    // обработка выбора классификатора
    $('#select-classifier').click(function(e){
        var cId = parseInt(classifierId);
        var selectOption = $('#select-rows-count').children('option:selected'); 
        if ($('#select-classifier').hasClass('filter')) {
           if (!!(cId)) {
               $('#classifier').val(cId);
              // $('#select-rows-count')
               $('#rows_count').val(selectOption.val());               
               $('#dyr-filter-submit').trigger('click');
           }
        }
	else {
            if (!leaf && modelName != 'Classifier') {
                showMessage('Должен быть выбран раздел классификатор не имеющий подразделов');
                return;
            }         
                    $.ajax({
                        url: global_data.baseURI + '/custom/get-parent-classifier',
                        type: 'POST',
                        dataType: 'json',
                        data: {
                            id : classifierId
                        },
                        success: function(response) {
                                    $('#classifier_section').text(response.message);
                                    $('#replace_model').add('#replace_classifier').removeClass('hide');
                                }                           
                        });            
	}
       $.modal.close(); 
    }); 
        // выбор групповых операций
    $('#select_group_operations').change(function() {
        //скрыть подпись
        $('.subscription-for-group > div').addClass('hide');
        var $optSelect =  $(this),
            $option = $optSelect.children('option:selected');            
        switch ($option.val()) {
            case 'replace_classifier':
            case 'replace_model':
                showClassifierModal();                    
                break;
            default:
                break;
        }
    });
    
    // Изменение классификатора
    $('#change-classifier').click(function (e) {
        e.preventDefault();
        showClassifierModal();
    });
    // навигация по классификатору
    $('.classifier-navigation').click(function(e) {        
        e.preventDefault();        
        var cId = $(this).data('classifier');
        var $selectOption = $('#select-rows-count').children('option:selected'); 
        $('#classifier').val(cId);
        $('#rows_count').val($selectOption.val());
        $('#dyr-filter-submit').trigger('click');
    });
    // Выполнение групповых операций
    $('#group_operation_btn').click(function(e) {
        console.log (modelName);
        var $els = $('.check-directory-element:checked');        
        if ($els.length == 0) {
            showMessage('Не выбран ни один элемент таблицы');
            return;
        }
        var elementsList = $els.map(function(){
            return $(this).val();
        }).get();
        var selectedVal = $('#select_group_operations option:selected').val();
        switch (selectedVal) {            
            case 'replace_model':
            case 'replace_classifier':
                $.ajax({
                    url: global_data.baseURI + '/custom/parent-classifier-change',
                    type: 'POST',
                dataType: 'json',
                    data: {
                        object: {
                            elements: elementsList,
                            classifier : classifierId,
                            model: modelName 
                        }
                    },
                    success: function(response) {
                        if (response.hasOwnProperty('errorCode')) {
                            showMessage(response.message);
                            return;
                        }
                        showMessage(response.message);
                        location.reload();
                    }
                });
                break;                
        }                
    });
    // Поиск по разделу
    $('#directory_search_btn').click(function(){
        var searchVal = $('#directory-search').val();
        var select_option = $('#select-rows-count').children('option:selected'); 
         $('#search').val('');
         $('#searchFlag').val('1');
        if (searchVal.length !== 0) {
            $('#search').val(searchVal);
        }
        $('#rows_count').val(select_option.val());
        $('#dyr-filter-submit').trigger('click');
    });
    // добавление или изменение элемента справочника
     $('#directory_add_btn').add('.editModel').click(function() {
         var ajaxData = {
            model : modelName
         };
         if (this.id != 'directory_add_btn') {
             var elementId = global_data.utils.getNumbers($(this).parents('tr').attr('id'));
         }
         ajaxData.id  = elementId;
         $.ajax({
            url: global_data.baseURI + '/model/display-form',
            type: 'POST',
            data: ajaxData,
            dataType: 'json',
            success: function(response) {
               console.log(response);
               if (response.hasOwnProperty('STATUS')) {
                   if (response.STATUS != 1) {
                        showMessage(response.MESSAGE) ;
                        return;
                    }
                    $(response.HTML_DATA).ajaxModal();
                    var isNewForm = ( $('#form-for-model').hasClass('new_form')) ? true : false;
                    global_data.utils.addEmailButton();
                    // реакция на изменения бренда в форме
                    var $changedElement = $('.ajax_changed');
                    if ($changedElement.length > 0) {
                        $changedElement.change(function(){
                        $.ajax({
                                url: global_data.baseURI + '/custom/get-contractor-name',
                                type: 'GET',  
                            dataType: 'json',
                                data: { id : $(this).val() },
                                success: function(response) {
                                    console.log(response)
                                    $('.comtractor_position').text(response);
                                    if (response.hasOwnProperty('errorCode')) {
                                        console.log(response.message);
                                        return;
                                    }
                                    $('.contractor_position').text(response.message);
                                }
                            });
                        });
                    } 
                    // добавление визуального редактора при необходимости                            
                    var $mceEdit = $('.mce-editable');
                    if ($mceEdit.length > 0) {  
                        tinymce.remove();
                        tinymce.init({
                            selector: '.mce-editable',
                            menubar: false
                        });
                    }
                    // добавление редактора дат при необходимости
                    global_data.utils.addDatePicker();
                     //////////////классификатор
                    if (modelName == 'Classifier') {
                        var ajax = global_data.utils.ajax;
                        ajax.read_session_value('TECH_CHARS',function(res){
                            console.log(res);                             
                            var techElement = JSON.parse(res.message),
                                attrIndex = $('tr.attr').length;
                            // добавить аттрибуты для раздела классификатора
                            $('#add-attribite').on('click',function(){
                                var nameChar =  $('#NameChar').val();
                                    if (!nameChar) {
                                        alert('Не введено название характеристики');
                                        return;
                                    }
                                $('#table-message').remove(); 
                                var name = $('#NameChar').val(),
                                    $unitOfMeasure = $('#UnitOfMeasureId').find('option:selected'),
                                    $typeData = $('#TypeData').find('option:selected'),
                                    $necessarily = $('#Necessarily').find('option:selected'),
                                    possibleVal = $('#Restriction').val();
                                var rowTemplate = '<tr class="attr" data-index="'+ attrIndex +'"><td>' +
                                    name +
                                    '</td><td>' + $typeData.text() +
                                    '</td><td>' + possibleVal + 
                                    '</td><td>' + $unitOfMeasure.text() + 
                                    '<td>' + $necessarily.text() + 
                                    '</td><td><span class="glyphicon glyphicon-remove-sign modal-remove" title="Удалить запись"></span></td></tr>';
                                $('#techCharacteristicAvailable').find('tbody').append(rowTemplate);
                                var newTechElement = {
                                    'name' : name,
                                    'unitOfMeasureId' : $unitOfMeasure.val(),
                                    'typeDataId' : $typeData.val(),
                                    'possibleValue' : possibleVal ,
                                    'necessarily' : $necessarily.val()
                                };
                                if (isNewForm) {
                                    alert('new_form');
                                } else {
                                    newTechElement['classifierId'] = elementId;                                
                                }
                                techElement[attrIndex++] = newTechElement;
                                ajax.write_session_value('TECH_CHARS',techElement);                                
                            });
                            // удалить аттрибут из раздела классификатора
                            $('#techCharacteristicAvailable').on('click','.modal-remove',function(){
                                var $attrRow = $(this).closest('tr'),
                                    rowIdx = global_data.utils.getNumbers($attrRow.data('index')),
                                    rowId = global_data.utils.getNumbers($attrRow.data('attr'));
                                if (!rowId) {
                                    techElement.splice(rowIdx,1);
                                    ajax.write_session_value('TECH_CHARS',techElement);
                                    $attrRow.remove();
                                } else {
                                     $.ajax({
                                        url: global_data.baseURI + '/custom/delete-characteristic',
                                        type: 'GET',
                                        data: {
                                            id : rowId
                                        },
                                        success: function(response) {                 
                                            $attrRow.remove();
                                        }
                                    });
                                }                                
                            });
                            
                        });
                    }
               }
               else {
                   showMessage('Ошибка открытия формы');
               }
           }
       });         
     });
     // удаление записи из формы
     $('.deleteModel').click(function(){
        var deleteFlag = confirm('Вы действительно хотите удалить данную запись');
        if(deleteFlag) {
             var elementId = global_data.utils.getNumbers($(this).parents('tr').attr('id'));
             $.ajax({
                    url: global_data.baseURI + '/model/delete-model',
                    type: 'POST',
                dataType: 'json',
                    data: {
                     model: modelName,
                        id: elementId 
                    },
                    success: function(response) {
                        console.log(response);
                        var flashMsg = {
                            message: response.message
                        };                        
                        if (response.hasOwnProperty('errorCode')) {
                            flashMsg.status = 2;
                        } else {
                            flashMsg.status = 1;
                        }
                        global_data.utils.ajax.write_session_value('FLASH_SAVE_MODAL',flashMsg,reload);                    
                    }
                });
        }
     });
         // переход к разделу классификатора 
     $('.gotoClassifier').click(function() {
         var modelId = global_data.utils.getNumbers($(this).parents('tr').attr('id'));
        $.ajax({
             url: global_data.baseURI + '/model/get-classifier-id',
             type: 'POST',
             dataType: 'json',
             data: {
                 model_id : modelId
             },
            success: function(response) {
                if (response.hasOwnProperty('errorCode')) {
                    showMessage('Ошибка запроса');
                    return;
                }
                var classifier_id = global_data.utils.getNumbers(response.message);
                    $('#classifier').val(classifier_id);
                    var select_option = $('#select-rows-count').children('option:selected'); 
                    $('#rows_count').val(select_option.val()); 
                    $('#dyr-filter-submit').trigger('click');
                
                        //location.reload();
                }
            });
            
     });
    
});


