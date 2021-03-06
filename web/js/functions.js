// глобальная настройка
var global_data = {};
global_data.baseSettings = {
    classifierId: 0,
    classifierName: '',
    leaf : false
};

// вспомогательные функции

// функция отображения модального окна для показа дерева классификатора
var showClassifierModal = function () {
        $('#classifiermodal-activate').trigger('click'); 
      //  $('#classifiermodal').ajaxModal();
        var jsData = $.parseJSON(global_classifier_tree);        
        var //$buildedTree = $('.classifier_tree').buildTree(jsData,$('#classifier-search'));
            $buildedTree = $('.classifier_tree').buildTree.call($('.classifier_tree'),jsData,$('#classifier-search'));
            $buildedTree.on('select_node.jstree',function(event,data){
            global_data.baseSettings.classifierId=data.node.id; 
            global_data.baseSettings.classifierName=data.node.text; 
            global_data.baseSettings.leaf = (data.node.children.length === 0);
        });        
    }
// Вывод всплывающего информационного сообщения
var showMessage = function(message) {
    $('<div class="center"><p>' + message +'</p>'+ global_data.close_button+'</div>').modal({
        closeExisting : false,
        blockerClass: 'ajaxBlocker'
    });
}
//Сохранение модели из запроса
var saveModelRequest = function() {
    saveModel('Request');
}
 // сохранение модели
var saveModel = function(method) {
    var path = '/model/save-model';
    if (method === 'Request') {
        path = '/model/save-model-request'
    }
    var $frm = $('#form-for-model'),
        model = $frm.attr('name');
    var $techChars = $('#techCharacteristic'),
        enabledForm;
    $frm.validate();    
    if ($techChars.length === 1) {
        $techChars.validate();
    }
    addFormValidationRules();
    enabledForm = ($techChars.length === 1) ? $techChars.valid() : true;
    if (typeof tinyMCE === 'object') {
        if (tinyMCE.editors.length > 0) {
            $.each(tinyMCE.editors,function(idx,elem) {
                var id_el = elem.id;
                $('#'+id_el).val(elem.getContent());
            });
        }
    }        
    if($frm.valid() && enabledForm) {
        var elements = $frm.serialize(),
           frmParams = {  
                        form : {
                            frm_data : elements 
                        }, 
                        model : model
                    };
        if ($frm.hasClass('edit_form')) {
            frmParams.id = $frm.data('element');
        }
        if ($techChars.length === 1) {
            frmParams.form.tech_data = $techChars.serialize();
        }
        $.ajax({
            url: global_data.baseURI + path,
            type: 'POST',
            dataType: 'json',
            data: frmParams,
            success: function(res) {
                var msg = $.parseJSON(global_data.messages); 
                console.log(msg);
               // return;
                if (res.hasOwnProperty('STATUS')) {
                    var flash_msg;
                    flash_msg = res.MESSAGE;

                    global_data.utils.ajax.write_session_value('FLASH_SAVE_MODAL',{                            
                            status : res.STATUS,
                            message : flash_msg
                        },reload
                    );                    
                } else {
                    alert('Неизвестная ошибка сервера');
                }
              // location.reload();
               // var status = $.parseJSON(response);
                }
        });
        
    }
    // правила валидации    
};
// правила валидации для форм по умолчанию
var addFormValidationRules = function() {
    $('.req').map(function(idx,elem) {
        $(elem).rules("add", {
	required: true});
    });
    $('.mail').map(function(idx,elem) {
        $(elem).rules("add", {
	email: true});
    });
    $('#repeatPassword').rules("add",{
        equalTo: "#password"
    });/*
	$('.digit').map( function(idx,elem) {
		$(elem).rules("add",{
			//	digits : true,

		});
	});*/
    
} 
/**
  * переместить элемент из левой панели в правую и наоборот
  * @param {type} $elem
  * @param {string} panel
  * @returns {undefined}
  */
 function moveElementBetweenPanels($elem,panel) {	
    if (panel !== 'left') {
        panel = 'right';
    }
    var pos = $elem.data('position'),
        panelColumn = '.'+ panel + '-column',
        $panel = $(panelColumn),
        $elements = $('.selectable',$panel);
    if ($elements.length > 0) {
        var firstElem = true;
        for (var i =( pos - 1);i>=0;i--) {
            var currentColumn = panelColumn +' .selectable[data-position="'+ i +'"]';
            if ($(currentColumn).length == 1) {
                $(currentColumn).after($elem);
                firstElem = false;
                break;
            }
        }
        if (firstElem) {
            $panel.prepend($elem);
        }
    } else {
        $elem.appendTo($panel);
    }
 }

// конфигурация среды
if (typeof Array.isArray === "undefined") {
    Array.isArray = function (arg) {
        return Object.prototype.toString.call(arg) === "[object Array]";
    };
}
// хэширующая функция
String.prototype.hashCode = function() {
  var hash = 0, i, chr;
  if (this.length === 0) return hash;
  for (i = 0; i < this.length; i++) {
    chr   = this.charCodeAt(i);
    hash  = ((hash << 5) - hash) + chr;
    hash |= 0; // Convert to 32bit integer
  }
  return hash;
};        

// Вспомогательные функции

/**
 * релоад (обертка)
 * @returns {undefined}
 */        
var reload = function() {
    location.reload();
}
/**
 * выравнивание колонок на главной
 * @returns {undefuned}
 */
var columnsAlignment = function() {
    var $columns = $('#main-page .content-wrapper');
        if ($columns.length > 0 ) {
            var maxHeight = 0;
            $columns.map(function() {
                var height = $(this).height();
                maxHeight = (maxHeight < height ) ? height : maxHeight;
            });
            $columns.height(maxHeight);
        }
}
/**
 * Вычисление итоговой суммы для формы экспорт или импорт
 * @returns {undefined}
 */
var calculateFormSummary = function() { 
    var $blocks = $('.country_block').not('#country_block_0'),cId,elementId,
            len = $blocks.length,
            $inputs;
        
    $('.summary').not('#new-summary-element').each( function(i) {        
        var countS = 0,priceS = 0;
        elementId = $('.model_summary',this).attr('id');
        for (var j = 0 ; j < $blocks.length ; j++) {            
            cId = $blocks.eq(j).data('country');
            $inputs = $('#' + elementId + '_' + cId ).parent('tr').find('input');
            countS+=  global_data.utils.getNumbers($inputs.eq(0).val());
            priceS+=  global_data.utils.getNumbers($inputs.eq(1).val());
           // console.log ($inputs.length);
        }
        $('.count_model',this).text(countS);
        $('.price_model',this).text(global_data.utils.addSpaces(priceS));
     //   console.log(elementId);
      //  console.log($blocks.length);
        //}
    }); 
    $('#country_block_0').removeClass('hide');
}
// действия в зависимости от того, включено ли редактирование пароля
var isPasswordEdit = function(e) {
    if (e.value == 1) {
        $('#block_password').add('#block_repeatPassword').removeClass('hide');
    } else {
        $('#block_password').add('#block_repeatPassword').addClass('hide');
    }
};
// действия в зависимости от того, выбрана ли опытные образец
var isModelPrototype = function (e) {
    if (e.value == 1) {
        $('#block_year').addClass('hide');
    } else {
        $('#block_year').removeClass('hide');    
    }
};
// действия в зависимости от выбранной страны происхождения модели
var isLocalizationExist = function(e) {
    var $bl = $('#block_localizationLevel');
    if (e.value == 1) {
        $bl.addClass('hide');
    } else {
        $bl.removeClass('hide');
    }
};
//Расширкние JQuery
/**
 * Открывает модальное окно с заданным содержимым 
 * @returns {$.fn.ajaxModal|_$.fn.ajaxModal.obj}
 */
$.fn.ajaxModal = function() {
    var obj=this;      
    obj.modal({
        closeExisting : false,
        blockerClass: 'ajaxBlocker',
        clickClose: false
    });     
    obj.on('modal:after-close',function () {
        this.remove();
    });
    var $contractorSelection = $('#j-selector-contractor');  
    if ($('#j-selector-filter').length == 1) {
        $('#j-selector-filter').on('keyup',function(){
            var filterValue = $(this).val().toLowerCase();
            var $filterElements = $('.left-column .selectable');
            if (filterValue == '') {
                $filterElements.removeClass('hide');
            } else {
                $filterElements.map(function(){
                    var elem = $(this);
                    var text = elem.text().toLowerCase();
                    if (text.indexOf(filterValue) === -1) {
                        $(elem).addClass('hide');
                    } else {
                        elem.removeClass('hide');
                    }                    
                });
            }            
        });
    }
    if ($contractorSelection.length == 1) {
        $contractorSelection.on('change',function() {
            $('.left-column .selectable').removeClass('hide');
            var filterContractorValue = $contractorSelection.find('option:selected').val();
            
            if (filterContractorValue != 0) {
                $('.left-column .selectable').not('div[data-contractor="'+ filterContractorValue +'"]').addClass('hide');
            }
        });
    }
    return obj;
}
/**
 * Построение дерева
 * @param {type} treeData объект с данными для построения дерева
 * @param {type} searchElem input-элемент для поиска
 * @returns {$.fn.buildTree} оюъект jstree
 */
$.fn.buildTree = function (treeData,searchElem) {
    var treeObj = this;
    var firstStart = !(treeObj.hasClass('jstree'));
    treeObj.jstree({
        "plugins" : [ "search" ],
        'core' : {
                'data' : treeData } 
    });
    if (firstStart) {
        treeObj.on('ready.jstree',function(){
            if (searchElem != null) { 
                var to = false;                                    
                searchElem.on('keyup', function() {
                if(to) { clearTimeout(to); }
                to = setTimeout (function (){
                    var v = searchElem.val();                                                                   
                    treeObj.jstree(true).search(v);
                    },250); 
                });
            }   
            treeObj.on('select_node.jstree',function (event,data) {
                return data.instance.toggle_node(data.node);
                });
            });
        }
return treeObj;
}
/**
 * Установка значений периода по умолчанию
 * @param {type} obj
 * @returns {undefined}
 */
$.fn.setDefaultPeriodValues =  function(obj) {
       if(!this.hasClass('date-period')) {
           return ;
       }
        var months=$('.month',this),
            years=$('.year',this);
        months.eq(0).find("option[value='"+ obj.start_month +"']").attr("selected", "selected");
        months.eq(1).find("option[value='"+ obj.end_month +"']").attr("selected", "selected");
        years.eq(0).find("option[value='"+ obj.start_year +"']").attr("selected", "selected");
        years.eq(1).find("option[value='"+ obj.end_year +"']").attr("selected", "selected");
}


 // модуль с дополнительными функциями
$(function() {
    global_data.utils = {
        ajax : {
            write_session_value : function(name,value,success) {
                var sc = null;
                if (typeof success == 'function') {
                   sc = success;
               }
               $.ajax ({ 
                    url: global_data.baseURI + '/custom/write-session-value',
                    type: 'POST',
                    data: {
                        name : name,
                        param: JSON.stringify(value)
                    },
                    success: sc 
                });
            },
            read_session_value : function(name,success) {
                var sc = null;
                if (typeof success == 'function') {
                   sc = success;
               }
               $.ajax({
                   url: global_data.baseURI + '/custom/read-session-value',
                   type: 'POST',
                   dataType: 'json',
                   data: { name : name },
                   success: sc
               });
           }
        },
        getMessage : function (str) {
            if (typeof global_data.loadedMessages != 'object') {
                global_data.loadedMessages = JSON.parse(global_data.messages);
            }
            if (global_data.loadedMessages.hasOwnProperty(str)) {
                return global_data.loadedMessages[str];
            }
        },
        /**
         * разбиение чисел по разрядам для инпутов
         * @param {type} el
         * @returns {undefined}
         */
       numberFormat : function(el) {
          $(el).each (function() {
              var val = $(this).val();
                   val=val.replace(/[^0-9]/g, '');
                   val = val.replace(/^0/, '');
                    if (val == '') {
                        val = '0';
                    }
                   val = val.replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1 ');        
                   $(this).val(val); 
               });
           },
        /**
         * Разбиение чисел по разрядам
         * @param {type} str
         * @returns {String}
         */
        addSpaces : function (str) {
            if (typeof str === 'number') {
               str = str.toString();
           }
           str = str.replace(/[^0-9]/g, '');
           str = str.replace(/^0/, '');
           if (str == '') {
               str = '0';
           }
           return str.replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1 ');
       },
        /**
         *  инициализировать окно выбора (создание обработчиков событий
         * @returns {undefined}
         */
        initSelectModal : function() {
            var $lc = $('.left-column'),
                $rc = $('.right-column'),
                $elList,
                $erList,
                $allRow = $('.all-select-row'),
                $mbtns = $('.modal-btn');
            $lc.on('click', '.selectable',function() {
                moveElementBetweenPanels($(this));        
                $allRow.addClass('hide');
                $('#j-selector-filter').val('').trigger('keyup');
            });
            $rc.on('click', '.selectable',function() {
                moveElementBetweenPanels($(this),'left');           
                if ($rc.children('.selectable').length == 0) {
                    $allRow.removeClass('hide'); 
                }
            });
            if ($mbtns.length > 0) {
                $mbtns.on('click',function(){
                    switch (this.id) {
                        case 'select-all':
                            $elList = $('.left-column .selectable').not('.hide');
                            $allRow.addClass('hide');                    
                            $elList.each(function(){
                                moveElementBetweenPanels($(this));
                            });
                        break;
                        case 'clear-all':
                            $erList = $('.right-column .selectable');                                
                            $erList.each(function(){
                                moveElementBetweenPanels($(this),'left');
                            });
                            $allRow.removeClass('hide');
                        break;
                    }
                });
            }        
        },
        /**
        * вернуть число
        * @param {type} str
        * @returns {unresolved}
        */
         getNumbers : function(str) {
            if (typeof str === 'number')
               return str;
            return parseInt(str.replace(/[^\d]/g,""));  
         },
                 // инициалтзировать объект period_obj
         initPeriodObject : function(container) {    
            var months=$('.month',container),
                years=$('.year',container),
                period_obj = {};
            period_obj.start_month=parseInt(months[0].value),
            period_obj.end_month=parseInt(months[1].value),
            period_obj.start_year=parseInt(years[0].value),
            period_obj.end_year=parseInt(years[1].value);
            return period_obj;
        },
        // проверка валидности периода
        checkValidPeriod : function(obj) {
            if (typeof obj !== 'object') {
                return false;
            }
            var second_check = ( obj.start_year === obj.end_year && obj.end_month >= obj.start_month )
            if (obj.start_year < obj.end_year || second_check) {
                return true;
            }
            return false;     
        },
        initPeriodObjectFromParams : function(params) {    
            var period_obj = {};
            period_obj.start_month=params.start_period.month,
            period_obj.end_month=params.end_period.month,
            period_obj.start_year=params.start_period.year,
            period_obj.end_year=params.end_period.year;
            return period_obj;
        },
                // для расширяемого списка е-иайл адресов
        addEmailButton : function() {
            var $add_email_btn = $('#add_email');
            if ($add_email_btn.length === 1) {
                var email_idx = $('#email_list input').length;
                $add_email_btn.click(function(e) {
                e.preventDefault();
                var inp_val = $('#email2').val(),
                elist = $('#email_list');
                if (inp_val.match(/^.+@.+\..+$/)) {                                        
                    elist.append('<div class="email_list_block"><input id="email_list'+ 
                    email_idx + '" class="email-element" name="email_list['+ 
                    email_idx +']" readonly value="'+ inp_val +
                     '"><div title="Удалить" class="icon_remove"></div></div>'); 
                    email_idx++;
                } else {
                    alert ('Неверный формат Email адреса');
                }                  
            });
                // обработчик удаления элемента списка
                $('#email_list').click('.icon_remove',function(e){
                    var cl_el = e.target;
                    if ($(cl_el).hasClass('icon_remove')) {
                        var $parent = $(cl_el).parent();
                        $parent.remove();
                    }
                });
            }
        },
        // для полей ввода с датами инициализация Datepicker'а
        addDatePicker : function(){
            var $date_edit = $('.format-date') 
            if ($date_edit.length > 0) {
                $date_edit.datepicker({
                    dateFormat: "dd.mm.yy",
                    beforeShow: function() {
                        setTimeout(function(){
                        $('.ui-datepicker').css('z-index', 29);
                        }, 0);
                    }
                });
            }
        },
            // стандартный обработчик формы обработчик для формы левой панели
        sendForm : function() {
            var $sendForm = $('.lf-btn-submit');
            if ($sendForm.length == 0) {
                return false;
            }
            $sendForm.on('click',function(e) {
                e.preventDefault();
                var frmId = $(this).data('form'), 
                    $frm = $('#' + frmId);
                $frm.validate();
                addFormValidationRules();
                if($frm.valid()) {
                   //var elements = $frm.serialize();
                    $frm.submit();
                }
            });
        },
        initForms : {
            initClassifierSelect : function() {
                $('#add_classifier_id').on('click',function(e){
                    e.preventDefault();
                    var $classifierModal = showClassifierModal();
                    $('#select-classifier').off('click');
                    // обработка выбора классификатора
                    $('#select-classifier').on('click',function(e){
                        var cId = parseInt(global_data.baseSettings.classifierId);
                        if (!global_data.baseSettings.leaf) {
                            showMessage('Должен быть выбран раздел классификатор не имеющий подразделов');
                            return;
                        }
                        var ajaxData = {
                            classifierId : cId
                        }
                        $.ajax({
                            url: global_data.baseURI + '/model-request/get-form-characteristic',
                            type: 'POST',
                            data: ajaxData,
                            dataType: 'json',
                            success: function(response) {
                                var $formChar = $('#techCharacteristic');
                                $formChar.remove();
                                if (response.hasOwnProperty('errorCode')) {
                                    return;
                                }                                       
                                $('#form-for-model').after(response.message);                    
                            }
                        });            
                        $('.close-modal').trigger('click');
                        $('#block_classifierId .dop-text').html('<span>Выбранный раздел классификатора: </span>' + global_data.baseSettings.classifierName);
                        $('#add_classifier_id').text('Изменить');
                        $('input[name="classifierId"]').val(cId);            
                      //  console.log(cId);

                    });        
                });
            },
            initBrandSelect : function() {
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
        }
        }
    }
});