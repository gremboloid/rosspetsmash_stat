$(function() {

    var $availableReports = $('#reports_available'), // окно доступных отчетов
        $btnChange = $('.constructor_action'), // Кнопка "изменить" параметров отчета
        $bc = $('#block-classifier'), // левый блок выбора классификатора
        $classifierBlock = $('.change-classifier-block'), // блок элементов выбора классификатора
        params = global_data.reports.params, // алиас глобального объекта параметров отчетов
        $excel = $('#get-excel-report'), // кнопка выгрузки в Excel
        $loadIndicators = $('#load-indicators'), // кнопка загрузки данных для индикаторов
        utils = global_data.utils,        
        ajax = utils.ajax,
        $br = $('#reports_out'),
        periods = params.periods.periods_list,
        defaultPeriod = params.periods.default_params, // периоды
        $blockContent = $('.content-block'),
        reportsList = params.report_list, // индивидуальные параметры отчетов
        reportSettings, // контейнер для индивидуальных параметров  
        selectedReportIdx = 0,
        evtFlag;
    
    
// вспомогательные функции

var saveReportParams = function(func) {   
    var reportHash = JSON.stringify(params).hashCode();
    $('#report-settings-hash').text(reportHash);
    ajax.write_session_value('REPORT_HASH',reportHash,func);
}

var clearReportSettings = function (reportList) {
    if (typeof reportList === 'object') {
        
        for (var prop in reportList) {
            if (reportList[prop].hasOwnProperty('manufacturers_list') ) {
                reportList[prop].manufacturers_list = null;
            }
            if (reportList[prop].hasOwnProperty('models_list') ) {
                reportList[prop].models_list = null;
            }
            if (reportList[prop].hasOwnProperty('sub_classifier') ) {
                reportList[prop].sub_classifier = null;
            }
        } 
        //params.report_list = {};
    }
    
}
    // показать/скрыть элементы выбора страны и региона
var showHideDSBlock = function() {
    var $sc = $('#select_country'),
        $sr = $('#select_region');
    if (params.datasource_id == 4 || params.datasource_id == 5 ) {
        $sc.removeClass('hide');
        $sr.addClass('hide');
    } else {
        $sc.addClass('hide')
    }
    if (params.datasource_id == 13) {
        $sr.removeClass('hide');
    }
};
// усиановка заданных значений периода на странице
var setPeriodValuesToHtml = function (block,obj) {
    var span_array = block.find('span');
    var start_month = obj.start_month > 9 ? obj.start_month : '0' + obj.start_month;
    var end_month = obj.end_month > 9 ? obj.end_month : '0' + obj.end_month;
    span_array.eq(0).html(start_month);
    span_array.eq(1).html(obj.start_year);
    span_array.eq(2).html(end_month);
    span_array.eq(3).html(obj.end_year);    
};

// получить количество месяцев в периоде
var getPeriodAmount = function(period) {
    var ms = period.start_period.month,
        me = period.end_period.month,
        ys = period.start_period.year,
        ye = period.end_period.year,
        fy;
    if (ys === ye) {
        return me - ms + 1;
    }
    if (ys > ye) return 0;
    fy = (ye - ys - 1) * 12;
    return me + fy + 13 - ms;
};

// Добавить выбор шага периода
var addStepPeriod = function() {
    var amount = getPeriodAmount(periods[0]);
    $('#step_select').html('');    
    var ajaxData = {           
            action : {
                namespace : 'Rosagromash', 
                className : 'DatePeriods',
                method: 'getStepPeriodOptions'},          
            
            params : {
                cols : amount
            }
        };
    $.ajax({
        url: global_data.reportsURI + '/get-step-period',
        type: 'POST',
    dataType: 'json',
        data: {
                cols : amount
            },
        success: function(response) {
            if (!response.hasOwnProperty('errorCode')) {
                $('#step_select').html(response.message);
                $('#step').removeClass('hide');
            }
           // $('#periods_list').html(response);
        }
    });            
};
var addParams = function () {
    var $informMessage = $('<p>Сохранение параметров...</p>');
   /* $informMessage.modal({
        closeExisting: false,
        escapeClose: false,
        clickClose: false,
        closeButton: false,
        showClose: false
    });*/
    saveReportParams(function() {        
      //  $.modal.close();
    });
}
   // ----------------------------------------------------------------
   // начальная загрузка
    saveReportParams(); // начальное сохранение параметров отчета
    
   // События конструктора отчетов
   
   // обработка нажатия на селектор выбора типа отчета
   $('input.select-report + label').on('click',function () {
        var $parent = $(this).parent('div'),
            $sibling = $parent.next();
        if ($parent.hasClass('active')) {
           if ($sibling.hasClass('settings')) {
                $sibling.toggleClass('hide');
                return;
            }
        }
        $('.settings').addClass('hide');
        var $container = $(this).parent('div');
        $container.find('.report_params').trigger('click');        
   });
   // событие, выбор отчетов
    $('input.select-report').on('change', function(){
        var that = this,    
            selectedReport,
            $frSelect = $('#full-report-classifier-select'),
            $frOpt,
            excelReports;
        $('#rstat-select-datasource option').removeAttr('disabled');
        selectedReport = $(that).val();
        $classifierBlock.removeClass('hide');
        params.selected_report = selectedReport;
        excelReports = ['default','economic','manufacturers', 'full' ];
        if (excelReports.indexOf(selectedReport) !== -1) {
            $excel.removeClass('hide');
        }
        if ( selectedReport === 'full') {
            $loadIndicators.removeClass('hide');
            $('#current-classifier').addClass('hide');
            $('#current-classifier-full').removeClass('hide');
            $('#block-tree').addClass('hide');
            $frOpt = $frSelect.find('option:selected');
            params.full_classifier_id = $frOpt.val();
            $('#current-classifier-full').text($frOpt.text());        
        } else {
            $loadIndicators.addClass('hide');
            $frSelect.addClass('hide');
            $('#current-classifier').removeClass('hide');
            $('#current-classifier-full').addClass('hide');
        }
        if (selectedReport !== 'economic') {
            $('.not_economic_report').removeClass('disabled');
        }
        if (selectedReport == 'full' || selectedReport == 'modelsContractor') {
            $('#rstat-select-datasource option[value="13"]').prop('selected', 'selected');        
            params.datasource_id = 13;        
            $('#rstat-select-datasource').trigger('change');
            $('.special_data_source').addClass('disabled');
        } else if (selectedReport == 'economic') {
            $('#rstat-select-datasource option[value="14"]').prop('selected', 'selected');        
            params.datasource_id = 14;        
            $('#rstat-select-datasource').trigger('change');
            $('.not_economic_report').addClass('disabled');
        } else  {
            if (params.datasource_id == 13 || params.datasource_id == 14) {
                $('#rstat-select-datasource option[value="1"]').prop('selected', 'selected');
                params.datasource_id == 1;
                $('#rstat-select-datasource').trigger('change');
                $('#rstat-select-datasource option[value="13"]').attr('disabled','disabled');
            }
        } 
        ajax.write_session_value('report_params',params,addParams);                
    });
    // выбор страны, региона и-т-д
    $('.add-modal').on('click',function(e){
        var $parent = $(this).parent('div'),
            action;
        switch(this.id) {
            case 'add-country':
                action = 'get-countries';
                break;
            case 'add-region':
                action = 'get-regions';
                break;
        }
        $.ajax({
                url: global_data.reportsURI + '/' + action,
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.hasOwnProperty('errorCode')) {
                        showMessage(response.message);
                        return;
                    }
                    var $lst = $parent.siblings('.fs-container').find('.list-items-block'),
                        $mdl = $(response.message);
                    $mdl.ajaxModal();
                    var $rc = $('.right-column'),
                        $lc = $('.left-column'),
                        $erList,
                        $btns = $('.btn_side-2 > div');
                    utils.initSelectModal();
                    $btns.on('click', function(){
                        switch (this.id) {
                            case 'btnok':
                                var arr=[],
                                    str='',
                                    $allRow = $parent.siblings('.fs-container').find('.all_selected'),
                                    alias=$('.selector-right-header').attr('id');
                                $erList = $rc.find('.selectable'); 
                                $erList.each(function (index) {
                                    arr.push(utils.getNumbers(this.id));
                                    str += $(this).text();
                                    if (index !== ($erList.length)-1) {
                                        str += ', ';
                                    }                                     
                                });
                                if ($lc.find('.selectable').length === 0 || $rc.find('.selectable').length === 0) {
                                    $allRow.removeClass('hide');
                                    $lst.html('');
                                    params[alias] = '';
                                    params['all_countries_flag'] = 1;
                                } else {
                                    $allRow.addClass('hide');
                                    params[alias] = arr.toString();
                                    $lst.html(str);
                                    params['all_countries_flag'] = 0;
                                }
                                ajax.write_session_value('report_params',params,addParams);                         
                                $.modal.close();                                
                                break;
                            case 'btncancel':
                                $.modal.close();
                                break;    
                                
                        }
                    });
                    
                }
            });
        
    });
    // События нажатия кнопки выбора индивидуальных параметров отчета
    $('.report_params').on('click', function(){
        var $currentBlock = $(this).parent('div.list-element'),        
            blockAlias = $currentBlock.attr('id'),
            $sibling = $currentBlock.next(),
            $reportSelected = $('#selected-reports'),
            reportSelectedText = '',
            $reportsList = $('.list-reports > .list-element');
     /*  if ($sibling.hasClass('settings')) {
            $sibling.toggleClass('hide');
        }*/
        $('.settings').addClass('hide');
        if (!$currentBlock.hasClass('active')) {
            $reportsList.removeClass('active');
            $currentBlock.addClass('active');
            reportSelectedText = '<div class="sub_block_list_elementk_list_element">'+ $currentBlock.find('label').text() +'</div>';
            $reportSelected.html(reportSelectedText); 
            $('input',$currentBlock).trigger('change').prop("checked","checked");
        }
        reportSettings = params.report_list;
        if (!reportSettings) {
           reportSettings = {};
        }
        if (!reportSettings.hasOwnProperty(blockAlias) || Array.isArray(reportSettings[blockAlias])) {
            reportSettings[blockAlias] = {};
        }
        if (!$sibling.hasClass('settings')) {
            var ajaxData = {
                rType: blockAlias
 
            };
            $.ajax({
                url: global_data.reportsURI + '/get-settings',
                type: 'POST',
                dataType: "json",
                data: ajaxData,
                success: function(response) {
                    if (response.hasOwnProperty('errorCode')) {
                        alert(response.message);
                    } else {
                        $sibling = $currentBlock.next();
                        if (!$sibling.hasClass('settings'))  {
                            var $units,
                            $selectElements,
                            $actionBtns,
                            $flags; 
                            if ($currentBlock.hasClass('active')) {
                                $currentBlock.after(response.message); 
                                $sibling = $currentBlock.next();
                                $actionBtns = $('.reports-settings-action');
                                $units = $sibling.find('.units input');
                                $flags = $sibling.find('input[type=checkbox]').not('.units input');
                                $selectElements = $sibling.find('select.values');                            
                            }
                            // для изменения ед. измерений
                            $units.on('change',function(e){
                                var selectFlag = false,
                                unitList=[];
                                $units.each(function (index) {                                            
                                    if ($(this).prop('checked')) {
                                        selectFlag = true;
                                        unitList.push($(this).attr('name'));
                                    } 
                                });
                                if(!selectFlag) {
                                    $units.first().prop('checked','checked');
                                    unitList.push($units.first().attr('name'));    
                                }
                                reportSettings[blockAlias]['units'] = unitList.toString();
                                params.report_list = reportSettings;
                                if (this.id == 'price' || this.id == 'averagesalary' || this.id == 'fond') {
                                    var $settings = $(this).parents('.units'),
                                        chkCount = $settings.find('input.price_unit:checkbox:checked').length;
                                    if (chkCount > 0) {
                                       $settings.find('.price_dimensions.cl6').removeClass('hide');
                                    } else {
                                        $settings.find('.price_dimensions.cl6').addClass('hide');
                                    }
                                }  
                                ajax.write_session_value('report_params',params,addParams);
                            });
                            // для селекторов
                            $selectElements.on('change',function(e) {
                                var name = $(this).attr('name'),
                                    $selectOption = $(this).children('option:selected'),
                                    values= $selectOption.val();
                                reportSettings[blockAlias][name] = values;
                                params.report_list = reportSettings;
                                ajax.write_session_value('report_params',params,addParams);
                                
                            });
                            // для флажков
                            $flags.on('change',function(e) {
                                var name = $(this).attr('name');
                                if ($(this).prop('checked')) {
                                    reportSettings[blockAlias][name] = 'on';
                                } else{ 
                                    reportSettings[blockAlias][name] = 'off'
                                }
                                params.report_list = reportSettings;                
                                ajax.write_session_value('report_params',params,addParams);                                                  
                            });
                            
                            // для кнопок
                            $actionBtns.on('click', function(e) { 
                                var $parent = $(this).parent('div'),
                                    presents = 0,
                                    action,
                                    outParams = {
                                        report_type: blockAlias
                                    };
                                if ($('#presents').prop('checked')) {
                                   presents = 1;
                                }
                                switch(this.id) {
                                   case 'select_contractors':
                                        action = 'get-manufacturers';
                                        outParams.presents = presents;
                                        break;
                                    case 'select_models':
                                        action = 'get-models';
                                        break;
                                    case 'select_sub_classifier':
                                        action = 'get-sub-classifier';
                                        break;
                                }
                                $.ajax({
                                    url: global_data.reportsURI + '/' + action,
                                    type: 'POST',
                                    dataType: "json",
                                    data: outParams,
                                    success: function(response) {
                                        if (response.hasOwnProperty('errorCode')) {
                                            alert(response.message);
                                        } else {
                                            var $lst = $parent.siblings('.fs-container').find('.list-items-block'),
                                                $mdl = $(response.message),
                                                blockSave = blockAlias;
                                            $mdl.ajaxModal();
                                            var $rc = $('.right-column'), 
                                                $lc = $('.left-column'),
                                                $erList,
                                                alias=$('.selector-right-header').attr('id'),
                                                $btns=$('.report-modal.modal-buttons-block .btn'); 
                                            utils.initSelectModal();
                                            $btns.on('click', function() {
                                                switch (this.id) {
                                                    case 'btnok':
                                                        var arr=[],
                                                            str='',
                                                            $allRow = $parent.siblings('.fs-container').find('.all_selected');
                                                            $erList = $rc.find('.selectable');
                                                        $erList.each(function (index) {                                            ;
                                                            arr.push(utils.getNumbers(this.id));
                                                            str += $(this).text();
                                                            if (index !== ($erList.length)-1) {
                                                                str += ', ';
                                                            }
                                                        });
                                                        if ($lc.find('.selectable').length === 0 || $rc.find('.selectable').length === 0) {
                                                            $allRow.removeClass('hide');
                                                            $lst.html('');
                                                            reportSettings[blockSave][alias] = '';  
                                                        } else {
                                                            $lst.html(str);
                                                            $allRow.addClass('hide');
                                                            reportSettings[blockSave][alias] = arr.toString();                                                                    
                                                        }
                                                        params.report_list = reportSettings;
                                                        ajax.write_session_value('report_params',params,addParams);                                                                                
                                                        $.modal.close();                                                                                                              
                                                    break;
                                                    case 'btncancel':
                                                        $.modal.close();
                                                    break;
                                                }
                                            });
                                        }
                                    }
                                });                                                               
                            });

                        }
                    }
                }
            });
        } else {
            $sibling.toggleClass('hide');
        }                        
    });
    // Событие нажатия на кнопку "изменить"
    $btnChange.add('#create-report').on('click', function() {  
        var btnId = $(this).attr('id'),            
            $bcl = $('#change_classifier'),
            $bdc = $('#change_datasource'),
            $bpr = $('#change_periods'),
            $bra = $('#reports_available');                     
        $blockContent.addClass('hide');
        switch (btnId) { 
            case 'get_classifier':
                $("#rstat-select-classifier option[value='"+params.full_classifier_id +"']").attr("selected", "selected");
                var $dynamic = $bcl.find('.dynamic-load'),
                    isDynamic = ($dynamic.length === 1) ? true : false;
                $bcl.removeClass('hide');
                if (isDynamic) {
                    $.getJSON(global_data.baseURI + '/custom/get-classifier-json', function (treeData){
                        $('#classifier_tree').buildTree($.parseJSON(treeData),$('#classifier-search')).on('changed.jstree',function(event,data){
                            params.classifier_id=data.selected;
                            var $cc = $('#current-classifier'),i,j;
                            console.log(data);
                            if (params.hasOwnProperty('report_list')) {
                                var report_list = params.report_list;
                                clearReportSettings(report_list);
                                $('.settings').remove();
                            }
                            ajax.write_session_value('report_params',params,addParams);
                            $cc.html('');
                            for(i = 0, j = data.selected.length; i < j; i++) {
                                $cc.append('<div class="sub_block_body">'+ data.instance.get_node(data.selected[i]).text +'</div>');
                            }
                            
                            
                           // $cc.
                        });                        
                         $('#classifier_tree').removeClass('dynamic-load');
                    });
                }
            break;
            case 'get_datasource':
                $("#rstat-select-datasource option[value='"+params.datasource_id +"']").attr("selected", "selected");
                showHideDSBlock();
                $bdc.removeClass('hide');
            break;
            case 'get_periods':
            // инициализация селекторов
                $('.list-periods > .date-period').map(function(index) {
                    var $elem = $(this),
                        periodObj=utils.initPeriodObjectFromParams(periods[index]);
                        $elem.setDefaultPeriodValues(periodObj);
                });
                $bpr.removeClass('hide');
            break;                    
            case 'get_selected_reports':
                $bra.removeClass('hide');
            break;
            case 'create-report':
                var $repCnt=$('#report-container'), // контейнер отчетов
                    $repWrap,// контейнер навигации
                    $arrows,
                    $repNum,
                    $repCount,
                    $spinner;
                $spinner = $('<div id="report-loader" class="spinner"></div>');
                $repWrap = $('.select_report_wrapper');
                $arrows = $repWrap.find('a');
                $repNum = $('#repNum');
                $repCount = $('#repCount');
                $repCnt.html('');
                $('#report-loader').remove();
                $br.append($spinner);
                $br.removeClass('hide');
                
                $.ajax({
                    url: global_data.reportsURI + '/create-report',
                    type: 'GET',
                    data: {hash : $('#report-settings-hash').text() },
                    dataType: "json",                   
                    success: function(response) {
                        var reportsCount = response.length,
                            report,
                            currentReportIdx=1;
                        $spinner.remove();
                        for (report in response) { 
                            $repCnt.append(response[report].message);                            
                        }
                        if (reportsCount > 1) {
                            $repNum = $('#repNum');
                            $repCount = $('#repCount');
                            $repCount.text(reportsCount);
                            $repNum.text(currentReportIdx);
                            $repWrap = $('.select_report_wrapper');
                            $repWrap.removeClass('hide');
                            $arrows = $repWrap.find('a');
                            showHideReport();
                            if (!evtFlag) {
                                evtFlag = true;
                                $arrows.on('click', function() {
                                    var arrow = $(this).attr('id');
                                    if (arrow == 'leftArrow') {
                                        if (selectedReportIdx == 0) {
                                            selectedReportIdx = reportsCount-1;
                                        }
                                        else {
                                            selectedReportIdx--;
                                        }
                                    }
                                    else {
                                        if (selectedReportIdx === reportsCount - 1) {
                                            selectedReportIdx = 0;
                                        }
                                        else {
                                            selectedReportIdx++;
                                        }                                                                                 
                                    }
                                    showHideReport();
                                });
                            }                                
                        }
                        else {
                            $repWrap.addClass('hide');
                        }                                                                        
                    }
                }); 
            break;
        }
    });
       // обработчик кнопки (выгрузка отчета в Excel)
    $excel.on('click',function() {
           window.location = global_data.reportsURI + '/create-excel-report';        
    }); 
    // Обработчик изменения периода
    $('.list-periods').on('change','select',function(target){
        var $parent = $(this).parents('.date-period'),
            obj = utils.initPeriodObject($parent),
            idx,
            listElem;
        if (utils.checkValidPeriod(obj)) {
            idx = $parent.index();
            listElem = $('.sub_block_list_element.period').eq(idx);
            setPeriodValuesToHtml(listElem,obj);
            periods[idx].start_period.month = obj.start_month;
            periods[idx].start_period.year = obj.start_year;
            periods[idx].end_period.month = obj.end_month;
            periods[idx].end_period.year = obj.end_year;
            ajax.write_session_value('report_params',params,addParams);
            if ($('.date-period').length === 2) {
                addStepPeriod();
            }
        }
    });
        // Инициализация события выбора шага периода
    $('#step_select').on('click',function() {
        var selected = $('option:selected',this).val();
            params.periods.period_step = selected;
            ajax.write_session_value('report_params',params,addParams);            
    });
    // обработчик добавления нового периода
    $('#new-period-button').on('click',function(){
        $('#step').addClass('hide');
        var count = $('.list-periods > div').length;
        var $newPeriod = $('#new_period').clone(true),
            $newPeriodElement = $('#new-period-element').clone();

        var periodObj=utils.initPeriodObjectFromParams(defaultPeriod);
        $newPeriod.attr('id','period'+(count+1));
        $newPeriod.setDefaultPeriodValues(periodObj);                        
        $newPeriod.removeClass('hide');
        setPeriodValuesToHtml($newPeriodElement,periodObj);
        $('.list-periods').append($newPeriod);
        $newPeriodElement.removeClass('hide').removeAttr('id');
        periods[count] =  $.extend(true, {}, defaultPeriod);
        utils.ajax.write_session_value('report_params',params,addParams);
        $('.sub_block_list_wrapper.periods-list').append($newPeriodElement);
    });
        // обработчик удаления периода
    $('.change_period').on('click',function(){
        var $parent = $(this).parents('.date-period');
        var idx = $parent.index();
        var periodsCount = $('.list-periods > .date-period').length;
        if (periodsCount !== 1) {
            $parent.remove();
            if (periodsCount == 2) {
                addStepPeriod();
            }
            $('.sub_block_list_wrapper .period').eq(idx).remove();
            periods.splice(idx,1);
            ajax.write_session_value('report_params',params,addParams);
        }
    });
    
        
    
    $('#report_list_button').on('click',function(){
        $('.settings').addClass('hide');
        $('#get_selected_reports').trigger('click');
    });
    // обработчик выбора источника данных
    $('#rstat-select-datasource').on('change',function() {        
        var val = $(this).val(),text = $('option:selected',this).text();  
        var report_list = params.report_list;
        params.datasource_id = val;
        $('#current-datasource').text(text) ;
       // params.report_list = {};
        clearReportSettings(report_list);
        $('.settings').remove();
        ajax.write_session_value('report_params',params,addParams);
        
        showHideDSBlock();
    });    
    // обработчик выбора раздела классификатора для полного отчета
    $('#rstat-select-classifier').on('change', function() {
        var val = $(this).val(),
            text = $('option:selected',this).text();
            params.full_classifier_id = val;
        $('#current-classifier-full').text(text);
        ajax.write_session_value('report_params',params,addParams);
    });
    // обработчик флажка (объединить регионы)
    $('#all_regions').on('change', function () {
        
       if ($(this).prop('checked')) {
           params.all_regions_together = 'on';
       } else {
           params.all_regions_together = 'off';
       }
       ajax.write_session_value('report_params',params,addParams);       
    });
    
   
});

